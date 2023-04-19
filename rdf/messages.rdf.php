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

$qry = '-- Messages sent from the given person
       SELECT mm.message_id,
               mm.relationmessage_id,
               mm.subject,
               mm.body,
               mm.insertamum AS sent,
               pr.person_id AS "recipientPersonId",
               pr.vorname AS "recipientName",
               pr.nachname AS "recipientSurname",
               ps.person_id AS "senderPersonId",
               ps.vorname AS "senderName",
               ps.nachname AS "senderSurname",
               (SELECT MAX(status) FROM public.tbl_msg_status WHERE message_id = mm.message_id AND person_id = mr.person_id) AS "lastStatus",
               (SELECT MAX(insertamum) FROM public.tbl_msg_status WHERE message_id = mm.message_id AND person_id = mr.person_id) AS "lastStatusDate",
               oe.oe_kurzbz AS "oeId",
               COALESCE(sg.bezeichnung, oe.bezeichnung) AS oe,
               mr.token
         FROM public.tbl_msg_message mm
         JOIN public.tbl_msg_recipient mr ON (mr.message_id = mm.message_id)
         JOIN public.tbl_person pr ON (pr.person_id = mr.person_id)
         JOIN public.tbl_person ps ON (ps.person_id = mm.person_id)
    LEFT JOIN public.tbl_organisationseinheit oe ON (oe.oe_kurzbz = mr.oe_kurzbz)
    LEFT JOIN public.tbl_studiengang sg ON (sg.oe_kurzbz = mr.oe_kurzbz)
        WHERE mm.person_id = '.$db->db_add_param($person_id, FHC_INTEGER).'
     GROUP BY mm.message_id,
               mm.relationmessage_id,
               mm.subject,
               mm.body,
               mm.insertamum,
               pr.person_id,
               pr.vorname,
               pr.nachname,
               ps.person_id,
               ps.vorname,
               ps.nachname,
               "lastStatus",
               "lastStatusDate",
               oe.oe_kurzbz,
               oe,
       	mr.token
	UNION
	-- Messages sent directly to the person
                        SELECT mr.message_id,
                                mm.relationmessage_id,
                                mm.subject,
                                mm.body,
                                mm.insertamum AS sent,
                                pr.person_id AS "recipientPersonId",
                                pr.vorname AS "recipientName",
                                pr.nachname AS "recipientSurname",
                                ps.person_id AS "senderPersonId",
                                ps.vorname AS "senderName",
                                ps.nachname AS "senderSurname",
                                (SELECT MAX(status) FROM public.tbl_msg_status WHERE message_id = mm.message_id AND person_id = mr.person_id) AS "lastStatus",
                                (SELECT MAX(insertamum) FROM public.tbl_msg_status WHERE message_id = mm.message_id AND person_id = mr.person_id) AS "lastStatusDate",
                                oe.oe_kurzbz AS "oeId",
                                COALESCE(sg.bezeichnung, oe.bezeichnung) AS oe,
                                mr.token
                          FROM public.tbl_msg_recipient mr
                          JOIN public.tbl_msg_message mm ON (mm.message_id = mr.message_id)
                          JOIN public.tbl_person ps ON (ps.person_id = mm.person_id)
                          JOIN public.tbl_person pr ON (pr.person_id = mr.person_id)
                     LEFT JOIN public.tbl_organisationseinheit oe ON (oe.oe_kurzbz = mm.oe_kurzbz)
                     LEFT JOIN public.tbl_studiengang sg ON (sg.oe_kurzbz = mm.oe_kurzbz)
                         WHERE mr.person_id = '.$db->db_add_param($person_id, FHC_INTEGER).'
                      GROUP BY mr.message_id,
                                mm.relationmessage_id,
                                mm.subject,
                                mm.body,
                                mm.insertamum,
                                pr.person_id,
                                pr.vorname,
                                pr.nachname,
                                ps.person_id,
                                ps.vorname,
                                ps.nachname,
                                "lastStatus",
                                "lastStatusDate",
                                oe.oe_kurzbz,
                                oe,
                                mr.token
                         UNION
-- Messages sent to a person that belongs to the recipient organisation unit
                        SELECT mrou.message_id,
                                mm.relationmessage_id,
                                mm.subject,
                                mm.body,
                                mm.insertamum AS sent,
                                pr.person_id AS "recipientPersonId",
                                pr.vorname AS "recipientName",
                                pr.nachname AS "recipientSurname",
                                ps.person_id AS "senderPersonId",
                                ps.vorname AS "senderName",
                                ps.nachname AS "senderSurname",
                                (SELECT MAX(status) FROM public.tbl_msg_status WHERE message_id = mrou.message_id AND person_id = mrou.person_id) AS "lastStatus",
                                (SELECT MAX(insertamum) FROM public.tbl_msg_status WHERE message_id = mrou.message_id AND person_id = mrou.person_id) AS "lastStatusDate",
                                oe.oe_kurzbz AS "oeId",
                                COALESCE(sg.bezeichnung, oe.bezeichnung) AS oe,
                                mrou.token
                          FROM public.tbl_person p
                          JOIN public.tbl_benutzer b ON (b.person_id = p.person_id)
                          JOIN (
                                SELECT uid, oe_kurzbz
                                  FROM public.tbl_benutzerfunktion
                                 WHERE (datum_von IS NULL OR datum_von <= NOW())
                                   AND (datum_bis IS NULL OR datum_bis >= NOW())
                                   AND funktion_kurzbz IN (\'ass\')
                          ) bf ON (bf.uid = b.uid)
                          JOIN public.tbl_msg_recipient mrou ON (mrou.oe_kurzbz = bf.oe_kurzbz)
                          JOIN public.tbl_msg_message mm ON (mm.message_id = mrou.message_id)
                          JOIN public.tbl_person ps ON (ps.person_id = mm.person_id)
                          JOIN public.tbl_person pr ON (pr.person_id = mrou.person_id)
                     LEFT JOIN public.tbl_organisationseinheit oe ON (oe.oe_kurzbz = mrou.oe_kurzbz)
                     LEFT JOIN public.tbl_studiengang sg ON (sg.oe_kurzbz = mrou.oe_kurzbz)
                         WHERE p.person_id = '.$db->db_add_param($person_id, FHC_INTEGER).'
                      GROUP BY mrou.message_id,
                                mm.relationmessage_id,
                                mm.subject,
                                mm.body,
                                mm.insertamum,
                                pr.person_id,
                                pr.vorname,
                                pr.nachname,
                                ps.person_id,
                                ps.vorname,
                                ps.nachname,
                                "lastStatus",
                                "lastStatusDate",
                                oe.oe_kurzbz,
                                oe,
                                mrou.token
';


// $db->db_add_param($person_id, FHC_INTEGER)

if($db->db_query($qry))
{
	$oRdf->sendHeader();
	while($row = $db->db_fetch_object())
	{
		$status = '';
		if ($row->lastStatus == 0)
		{
			$status = 'Unread';
		}
		else if ($row->lastStatus == 1)
		{
			$status = 'Read';
		}
		else if ($row->lastStatus == 2)
		{
			$status = 'Archived';
		}
		else if ($row->lastStatus == 3)
		{
			$status = 'Deleted';
		}

		$sender = $recipient = 'System sender'; // default fallback

		// If the sender is not the system sender
		if ($row->senderPersonId != MESSAGING_SYSTEM_PERSON_ID)
		{
			$sender = $row->senderName.' '.$row->senderSurname;
		}
		elseif ($row->oeId != null) // otherwise take the oe
		{
			$sender = $row->oe;
		}

		// If the recipient is not the system sender
		if ($row->recipientPersonId != MESSAGING_SYSTEM_PERSON_ID)
		{
			$recipient = $row->recipientName.' '.$row->recipientSurname;
		}
		elseif ($row->oeId != null) // otherwise take the oe
		{
			$recipient = $row->oe;
		}

		$i = $oRdf->newObjekt($row->message_id);
		$oRdf->obj[$i]->setAttribut('subject', $row->subject, true);
		$oRdf->obj[$i]->setAttribut('body', $row->body, true);
		$oRdf->obj[$i]->setAttribut('message_id', $row->message_id, true);
		$oRdf->obj[$i]->setAttribut('insertamum', $row->sent, true);
		$oRdf->obj[$i]->setAttribut('status', $status, true);
		$oRdf->obj[$i]->setAttribut('statusdatum', $datum_obj->formatDatum($row->lastStatusDate, 'd.m.Y H:i'), true);
		$oRdf->obj[$i]->setAttribut('sender', $sender, true);
		$oRdf->obj[$i]->setAttribut('recipient', $recipient, true);
		$oRdf->obj[$i]->setAttribut('sender_id', $row->senderPersonId, true);
		$oRdf->obj[$i]->setAttribut('recipient_id', $row->recipientPersonId, true);

		if($row->relationmessage_id!='')
			$oRdf->addSequence($row->message_id, $row->relationmessage_id);
		else
			$oRdf->addSequence($row->message_id);
	}
}
$oRdf->sendRdfText();
?>
