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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */


/**
 * Zeit eine Liste mit allen Dualen Studenten die noch nicht im
 * Aufbaukurs-Studiengang angelegt wurden
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/benutzerberechtigung.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('assistenz',STUDIENGANG_KZ_QUALIFIKATIONKURSE,'suid'))
	die($rechte->errormsg);

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$datum_obj = new datum();
$stsem_obj = new studiensemester();
$stsem = $stsem_obj->getaktorNext();

echo '<!DOCTYPE HTML">
<html>
<head>
	<meta charset="UTF-8">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<title>Qualifikationskurs - Checkliste</title>
</head>
<body>
<h2>Qualifikationskurs - Checkliste</h2>
Die folgenden Personen sind als dual markiert, wurden aber noch nicht in den Qualifikationskurs übernommen:<br><br>
';

$qry = "SELECT
			nachname, vorname, gebdatum, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as stgkurzbz
		FROM
			public.tbl_person
			JOIN public.tbl_prestudent USING(person_id)
			JOIN public.tbl_studiengang USING(studiengang_kz)
		WHERE
		person_id NOT IN(SELECT person_id FROM public.tbl_prestudent WHERE studiengang_kz=".STUDIENGANG_KZ_QUALIFIKATIONKURSE.")
		AND dual
		ORDER BY nachname, vorname";



if($result = $db->db_query($qry))
{
	$i=0;

	echo '<table>';
	echo '<tr class="liste"><th>Nachname</th><th>Vorname</th><th>GebDatum</th><th>Stg</th></tr>';

	while($row = $db->db_fetch_object($result))
	{
		$i++;
		echo '<tr class="liste'.($i%2).'">';

		echo "<td>$row->nachname</td>";
		echo "<td>$row->vorname</td>";
		echo "<td>".$datum_obj->formatDatum($row->gebdatum,'d.m.Y')."</td>";
		echo "<td>$row->stgkurzbz</td>";
		echo "<td><a href='import/interessentenimport.php?nachname=$row->nachname&vorname=$row->vorname&studiengang_kz=".STUDIENGANG_KZ_QUALIFIKATIONKURSE."&ausbildungssemester=2&studiensemester_kurzbz=$stsem' target='_blank'>anlegen</a></td>";

		echo '</tr>';
	}

	echo '</table>';
	echo '<br>Anzahl:'. ($result?$db->db_num_rows($result):0);
}

echo '</body></html>';
?>
