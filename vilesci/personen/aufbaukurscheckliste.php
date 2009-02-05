<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 */
/**
 * Zeit eine Liste mit allen Dualen Studenten die noch nicht im
 * Aufbaukurs-Studiengang angelegt wurden
 */

require_once('../config.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/datum.class.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Herstellen der Datenbankverbindung');

$datum_obj = new datum();
$stsem_obj = new studiensemester($conn);
$stsem = $stsem_obj->getaktorNext();

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
<title>Aufbaukurs - Checkliste</title>
</head>
<body>
<h2>Aufbaukurs - Checkliste</h2>
Die folgenden Personen sind als dual markiert, wurden aber noch nicht in den Aufbaukurs übernommen:<br><br>
';

$qry = "SELECT 
			nachname, vorname, gebdatum, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as stgkurzbz
		FROM 
			public.tbl_person 
			JOIN public.tbl_prestudent USING(person_id) 
			JOIN public.tbl_studiengang USING(studiengang_kz)
		WHERE 
		person_id NOT IN(SELECT person_id FROM public.tbl_prestudent WHERE studiengang_kz=10002)
		AND dual
		ORDER BY nachname, vorname";



if($result = pg_query($conn, $qry))
{
	$i=0;

	echo '<table>';
	echo '<tr class="liste"><th>Nachname</th><th>Vorname</th><th>GebDatum</th><th>Stg</th></tr>';
	
	while($row = pg_fetch_object($result))
	{
		$i++;
		echo '<tr class="liste'.($i%2).'">';
		
		echo "<td>$row->nachname</td>";
		echo "<td>$row->vorname</td>";
		echo "<td>".$datum_obj->formatDatum($row->gebdatum,'d.m.Y')."</td>";
		echo "<td>$row->stgkurzbz</td>";
		echo "<td><a href='import/interessentenimport.php?nachname=$row->nachname&vorname=$row->vorname&studiengang_kz=10002&ausbildungssemester=2&studiensemester_kurzbz=$stsem' target='_blank'>anlegen</a></td>";
				
		echo '</tr>';
	}
	
	echo '</table>';
	echo '<br>Anzahl:'.pg_num_rows($result);
}

echo '</body></html>';
?>