<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/rdf.class.php');
require_once('../include/datum.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

$uid = get_uid();
$datum_obj = new datum();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('basis/message', null, 's'))
	die($rechte->errormsg);

$oRdf = new rdf('MESSAGES','http://www.technikum-wien.at/messages');

if (isset($_GET['person_id']) && is_numeric($_GET['person_id']))
	$person_id = $_GET['person_id'];
else
	die('Parameter person_id is missing');

$db = new basis_db();
$qry = "
SELECT
	m.message_id AS message_id,
	m.subject AS subject,
	m.body AS body,
	m.insertamum AS insertamum,
	m.relationmessage_id AS relationmessage_id,
	(SELECT COALESCE(titelpre,'') || ' ' || COALESCE(vorname,'') || ' ' || COALESCE(nachname,'') || ' ' || COALESCE(titelpost,'') FROM public.tbl_person WHERE person_id = m.person_id) as sender,
	(SELECT COALESCE(titelpre,'') || ' ' || COALESCE(vorname,'') || ' ' || COALESCE(nachname,'') || ' ' || COALESCE(titelpost,'') FROM public.tbl_person WHERE person_id = r.person_id) as recipient,
	m.person_id as sender_id,
	r.person_id as recipient_id,
	MAX(ss.status) as status,
	MAX(ss.insertamum) as statusdatum
FROM public.tbl_msg_message m
     JOIN public.tbl_msg_recipient r USING(message_id)
     JOIN public.tbl_msg_status ss ON(r.message_id = ss.message_id AND ss.person_id = r.person_id)
WHERE m.person_id = ".$db->db_add_param($person_id, FHC_INTEGER)."
GROUP BY m.message_id, m.subject, m.body, m.insertamum, m.relationmessage_id, sender, recipient, sender_id, recipient_id
UNION ALL
SELECT
	m.message_id AS message_id,
	m.subject AS subject,
	m.body AS body,
	m.insertamum AS insertamum,
	m.relationmessage_id AS relationmessage_id,
	(SELECT COALESCE(titelpre,'') || ' ' || COALESCE(vorname,'') || ' ' || COALESCE(nachname,'') || ' ' || COALESCE(titelpost,'') FROM public.tbl_person WHERE person_id = m.person_id) as sender,
	(SELECT COALESCE(titelpre,'') || ' ' || COALESCE(vorname,'') || ' ' || COALESCE(nachname,'') || ' ' || COALESCE(titelpost,'') FROM public.tbl_person WHERE person_id = r.person_id) as recipient,
	m.person_id as sender_id,
	r.person_id as recipient_id,
	MAX(ss.status) as status,
	MAX(ss.insertamum) as statusdatum
FROM public.tbl_msg_recipient r
     JOIN public.tbl_msg_status ss USING(message_id, person_id)
     JOIN public.tbl_msg_message m USING(message_id)
WHERE r.person_id = ".$db->db_add_param($person_id, FHC_INTEGER)."
GROUP BY m.message_id, m.subject, m.body, m.insertamum, m.relationmessage_id, sender, recipient, sender_id, recipient_id
ORDER BY insertamum";

if($db->db_query($qry))
{
	$oRdf->sendHeader();
	while($row = $db->db_fetch_object())
	{
		$status = '';
		if ($row->status == 0)
		{
			$status = 'Unread';
		}
		else if ($row->status == 1)
		{
			$status = 'Read';
		}
		else if ($row->status == 2)
		{
			$status = 'Archived';
		}
		else if ($row->status == 3)
		{
			$status = 'Deleted';
		}

		$i=$oRdf->newObjekt($row->message_id);
		$oRdf->obj[$i]->setAttribut('subject',$row->subject,true);
		$oRdf->obj[$i]->setAttribut('body',$row->body,true);
		$oRdf->obj[$i]->setAttribut('message_id',$row->message_id,true);
		$oRdf->obj[$i]->setAttribut('insertamum',$row->insertamum,true);
		$oRdf->obj[$i]->setAttribut('status',$status,true);
		$oRdf->obj[$i]->setAttribut('statusdatum',$datum_obj->formatDatum($row->statusdatum,'d.m.Y H:i'),true);
		$oRdf->obj[$i]->setAttribut('sender',$row->sender,true);
		$oRdf->obj[$i]->setAttribut('recipient',$row->recipient,true);
		$oRdf->obj[$i]->setAttribut('sender_id',$row->sender_id,true);
		$oRdf->obj[$i]->setAttribut('recipient_id',$row->recipient_id,true);

		if($row->relationmessage_id!='')
			$oRdf->addSequence($row->message_id, $row->relationmessage_id);
		else
			$oRdf->addSequence($row->message_id);
	}
}
$oRdf->sendRdfText();
?>
