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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/*
 * Erstellt eine Liste der Koordinatoren eines Instituts und der Anzahl der Stunden
 * die er in den jeweiligen Studiengaengen unterrichtet
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Koordinatorstunden</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
</head>
<body>';

$user = get_uid();
loadVariables($user);
$db = new basis_db();

if(isset($_GET['oe_kurzbz']))
	$oe_kurzbz = $_GET['oe_kurzbz'];
else 
	die('Falsche Parameteruebergabe');
	
echo '<h2>Koordinatorstunden - Organisationseinheit '.$oe_kurzbz.'</h2>';

$stg_arr = array();
$data = array();
$name = array();

//alle Studiengaenge holen
$studiengang = new studiengang();
$studiengang->getAll();

foreach ($studiengang->result as $row)
	$stg_arr[$row->studiengang_kz]=$row->kuerzel;
	
//Alle Fachbereichsleiter des uebergebenen Studienganges holen und
//Die Anzahl der Stunden die dieser in den einzelnen Studiengaengen haelt ermitteln
$qry = "SELECT 
			distinct on(tbl_lehreinheit.lehreinheit_id)
			tbl_benutzerfunktion.uid, 
			tbl_lehreinheitmitarbeiter.semesterstunden, 
			tbl_lehrveranstaltung.studiengang_kz,
			tbl_person.vorname,
			tbl_person.nachname
		FROM 
			public.tbl_benutzerfunktion
			JOIN lehre.tbl_lehreinheitmitarbeiter ON(tbl_benutzerfunktion.uid=tbl_lehreinheitmitarbeiter.mitarbeiter_uid)
			JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
			JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			JOIN public.tbl_benutzer ON(tbl_benutzer.uid=tbl_benutzerfunktion.uid)
			JOIN public.tbl_person ON(tbl_person.person_id=tbl_benutzer.person_id)
			JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id)
			JOIN public.tbl_fachbereich ON(tbl_benutzerfunktion.fachbereich_kurzbz=tbl_fachbereich.fachbereich_kurzbz)
		WHERE 
			tbl_benutzerfunktion.funktion_kurzbz='fbk' AND
			tbl_fachbereich.oe_kurzbz=".$db->db_add_param($oe_kurzbz)." AND
			tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)." AND
			(tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) AND
			(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now())
		ORDER BY tbl_lehreinheit.lehreinheit_id, nachname, vorname
		";

if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{
		if(array_key_exists($row->uid, $data))
		{
			if(array_key_exists($row->studiengang_kz, $data[$row->uid]))
			{
				$data[$row->uid][$row->studiengang_kz] += $row->semesterstunden;
			}
			else 
				$data[$row->uid][$row->studiengang_kz] = $row->semesterstunden;
		}
		else 
			$data[$row->uid][$row->studiengang_kz] = $row->semesterstunden;
			
		$name[$row->uid]['vorname']=$row->vorname;
		$name[$row->uid]['nachname']=$row->nachname;
	}
}

echo '<br /><table class="liste table-autosort:0 table-stripeclass:alternate table-autostripe">
		<thead>
			<tr class="liste">
				<th class="table-sortable:default">Name</th>
				<th class="table-sortable:default">Studiengang</th>
				<th class="table-sortable:numeric">Stunden</th>
			</tr>
		</thead>
		<tbody>';

$i=0;
foreach ($name as $uid=>$row) 
{		
	foreach ($data[$uid] as $stg=>$row2)	
	{
		echo '<tr>
				<td>'.$name[$uid]['vorname'].' '.$name[$uid]['nachname'].'</td>
				<td>'.$stg_arr[$stg].'</td>
				<td>'.$row2.'</td>
			  </tr>';
		$i++;
	}
}
echo '</tbody></table>';
?>
</body>
</html>
