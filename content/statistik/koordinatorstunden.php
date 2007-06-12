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
require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');

echo '
<html>
<head>
<title>Koordinatorstunden</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">';

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!');

$user = get_uid();
loadVariables($conn, $user);

if(isset($_GET['fachbereich_kurzbz']))
	$fachbereich_kurzbz = $_GET['fachbereich_kurzbz'];
else 
	die('Falsche Parameteruebergabe');
	
echo '<h1>Koordinatorstunden - Fachbereich '.$fachbereich_kurzbz.'</h1>';

$stg_arr = array();
$data = array();
$name = array();

//alle Studiengaenge holen
$studiengang = new studiengang($conn);
$studiengang->getAll();

foreach ($studiengang->result as $row)
	$stg_arr[$row->studiengang_kz]=$row->kuerzel;
	
//Alle Fachbereichsleiter des uebergebenen Studienganges holen und
//Die Anzahl der Stunden die dieser in den einzelnen Studiengaengen haelt ermitteln
$qry = "SET CLIENT_ENCODING TO 'UNICODE';
		SELECT 
			distinct on(tbl_lehreinheit.lehreinheit_id)
			tbl_benutzerfunktion.uid, 
			tbl_lehreinheitmitarbeiter.semesterstunden, 
			tbl_lehrveranstaltung.studiengang_kz,
			tbl_person.vorname,
			tbl_person.nachname
		FROM 
			public.tbl_benutzerfunktion, 
			lehre.tbl_lehreinheitmitarbeiter, 
			lehre.tbl_lehreinheit, 
			lehre.tbl_lehrveranstaltung,
			public.tbl_benutzer,
			public.tbl_person,
			lehre.tbl_lehrfach
			WHERE 
			tbl_benutzerfunktion.uid=tbl_lehreinheitmitarbeiter.mitarbeiter_uid AND
			tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
			tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
			tbl_benutzerfunktion.fachbereich_kurzbz='".addslashes($fachbereich_kurzbz)."' AND
			tbl_benutzerfunktion.funktion_kurzbz='fbk' AND
			tbl_benutzerfunktion.uid=tbl_benutzer.uid AND
			tbl_benutzer.person_id=tbl_person.person_id AND
			tbl_lehrfach.lehrfach_id=tbl_lehreinheit.lehrfach_id AND
			tbl_lehrfach.fachbereich_kurzbz='".addslashes($fachbereich_kurzbz)."' AND
			tbl_lehreinheit.studiensemester_kurzbz='$semester_aktuell'
		ORDER BY tbl_lehreinheit.lehreinheit_id, nachname, vorname
		";

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
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

echo '<table class="liste"><tr class="liste"><th>Name</th><th>Studiengang</th><th>Stunden</th></tr>';

$i=0;
foreach ($name as $uid=>$row) 
{		
	foreach ($data[$uid] as $stg=>$row2)	
	{
		echo '<tr class="liste'.($i%2).'"><td>'.$name[$uid]['vorname'].' '.$name[$uid]['nachname'].
			 '</td><td>'.$stg_arr[$stg].'</td><td>'.$row2.'</td></tr>';
		$i++;
	}
}
echo '</table>';
?>
</body>
</html>