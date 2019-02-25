<?php
/* Copyright (C) 2019 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <oesi@technikum-wien.at>
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/rdf.class.php');
require_once('../include/basis_db.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if (!$rechte->isBerechtigt('lehre/lehrveranstaltung'))
	die($rechte->errormsg);

$datum_obj = new datum();

$oRdf = new rdf('LEHREINHEITDIREKT','http://www.technikum-wien.at/lehreinheitdirekt');

$lehreinheit_id = filter_input(INPUT_GET,'lehreinheit_id');

if ($lehreinheit_id == '' || !is_numeric($lehreinheit_id))
{
	die('LehreinheitID ungueltig');
}

$oRdf->sendHeader();
$db = new basis_db();

$qry = "
SELECT
	uid, vorname, nachname, gruppe_kurzbz
FROM
	lehre.tbl_lehreinheitgruppe
	JOIN public.tbl_gruppe USING(gruppe_kurzbz)
	JOIN public.tbl_benutzergruppe USING(gruppe_kurzbz)
	JOIN public.tbl_benutzer USING(uid)
	JOIN public.tbl_person USING(person_id)
WHERE
	tbl_lehreinheitgruppe.lehreinheit_id=".$db->db_add_param($lehreinheit_id, FHC_INTEGER)."
	AND tbl_gruppe.direktinskription
ORDER BY vorname, nachname";

if ($result = $db->db_query($qry))
{
	$i = 0;
	while ($row = $db->db_fetch_object($result))
	{
		$i = $oRdf->newObjekt($i);
		$oRdf->obj[$i]->setAttribut('uid', $row->uid,true);
		$oRdf->obj[$i]->setAttribut('vorname', $row->vorname,true);
		$oRdf->obj[$i]->setAttribut('nachname', $row->nachname,true);
		$oRdf->obj[$i]->setAttribut('gruppe_kurzbz', $row->gruppe_kurzbz,true);
		$oRdf->addSequence($i);
		$i++;
	}
}
$oRdf->sendRdfText();
?>
