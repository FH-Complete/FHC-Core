<?php
/* Copyright (C) 2007 Technikum-Wien
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
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
 */
require_once('../config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/preinteressent.class.php');
require_once('../../include/person.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/log.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

$user = get_uid();

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

$datum_obj = new datum();
$stsem = new studiensemester($conn);
$stsem_aktuell = $stsem->getaktorNext();

if(isset($_GET['studiengang_kz']))
	$studiengang_kz = $_GET['studiengang_kz'];
else 
	$studiengang_kz = '';

if(isset($_GET['studiensemester_kurzbz']))
	$studiensemester_kurzbz = $_GET['studiensemester_kurzbz'];
else 
	$studiensemester_kurzbz = $stsem_aktuell;

if(isset($_GET['filter']))
	$filter = $_GET['filter'];
else 
	$filter = '';

echo '<html>
	<head>
		<title>PreInteressenten</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
		<script language="Javascript">
		<!--
		function confdel()
		{
			if(confirm("Wollen Sie diesen Eintrag wirklich loeschen?"))
				return true;
			else
				return false;
		}
		-->
		</script>
	</head>
	<body class="Background_main">
	<h2>PreInteressenten</h2>
	';

if(!$rechte->isBerechtigt('admin', null, 'suid') && 
   !$rechte->isBerechtigt('preinteressent', null, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');

//DROP DOWNs anzeigen
echo "<table width='100%'><tr><td><form action='".$_SERVER['PHP_SELF']."' method='GET'>";
echo 'Studiensemester: <SELECT name="studiensemester_kurzbz">';
$stsem = new studiensemester($conn);
$stsem->getAll();
foreach ($stsem->studiensemester as $row)	
{
	if($row->studiensemester_kurzbz==$studiensemester_kurzbz)
		$selected='selected';
	else 
		$selected='';
	
	echo "<option value='$row->studiensemester_kurzbz' $selected>$row->studiensemester_kurzbz</option>";
}
echo '</SELECT>';

echo '&nbsp;&nbsp;&nbsp;Studiengang: <SELECT name="studiengang_kz">';
echo "<option value=''>-- Alle --</option>";
$stg = new studiengang($conn);
$stg->getAll('typ, kurzbz');
foreach ($stg->result as $row)
{
	if($row->studiengang_kz==$studiengang_kz)
		$selected='selected';
	else 
		$selected='';
		
	echo "<option value='$row->studiengang_kz' $selected>$row->kuerzel</option>";
}
echo '</SELECT>';
echo '&nbsp;&nbsp;&nbsp;<input type="submit" value="Anzeigen">';
echo '</form></td><td>';
echo "<form action='".$_SERVER['PHP_SELF']."' method='GET'>";
echo "<input type='text' value='$filter' name='filter'>&nbsp;";
echo "<input type='submit' size='10' value='Suchen'>";
echo '</form></td>';
echo '<td>NEU</td></tr></table>';

//FREIGEBEN / LOESCHEN
if(isset($_GET['action']))
{
	if($_GET['action']=='freigabe')
	{
		echo '<br><b>Freigabe ist noch nicht implementiert</b>';
	}
	elseif($_GET['action']=='loeschen')
	{
		//Loeschen eines Preinteressenten
		$preinteressent = new preinteressent($conn);
		if($preinteressent->load($_GET['id']))
		{
			if($preinteressent->delete($preinteressent->preinteressent_id))
			{
				echo '<br><b>Datensatz wurde geloescht</b>';
			}
			else 
			{
				echo "<br><b>Fehler beim Löschen: $preinteressent->errormsg</b>";
			}
		}
		else 
		{
			echo "<br><b>Fehler beim Laden des Datensatzes. Daten wurden NICHT gelöscht</b>";
		}
	}
	
}

//TABELLE ANZEIGEN
echo '<br>';	
echo "<table class='liste table-autosort:0 table-stripeclass:alternate table-autostripe'>
	<thead>
		<tr>
		<th class='table-sortable:default'>Nachname</th>
		<th class='table-sortable:default'>Vorname</th>
		<th class='table-sortable:default'>Geburtsdatum</th>
		<th class='table-sortable:default'>Studiensemester</th>
		<th class='table-sortable:default'>Anmerkung</th>
		<th colspan=3>Aktion</th>
		</tr>
	</thead>
	<tbody>";


$preinteressent = new preinteressent($conn);
if($filter=='')
	$preinteressent->loadPreinteressenten($studiengang_kz, $studiensemester_kurzbz);
else 
	$preinteressent->loadPreinteressenten(null, null, $filter);

foreach ($preinteressent->result as $row)
{
	echo '<tr>';
	$person = new person($conn);
	$person->load($row->person_id);
	echo "<td>$person->nachname</td>";
	echo "<td>$person->vorname</td>";
	echo "<td>".$datum_obj->convertISODate($person->gebdatum)."</td>";
	echo "<td>$row->studiensemester_kurzbz</td>";
	echo "<td>$row->anmerkung</td>";
	echo "<td><input type='button' onclick='parent.preinteressent_detail.location.href = \"preinteressent_detail.php?id=$row->preinteressent_id&selection=\"+parent.preinteressent_detail.selection; return false;' value='Bearbeiten'></td>";
	echo "<td><input type='button' onclick=\"window.location.href='".$_SERVER['PHP_SELF']."?id=$row->preinteressent_id&action=freigabe&studiensemester_kurzbz=$studiensemester_kurzbz&studiengang_kz=$studiengang_kz&filter=$filter'\" value='Freigeben'></td>";
	echo "<td><input type='button' onclick=\"if(confdel()) {window.location.href='".$_SERVER['PHP_SELF']."?id=$row->preinteressent_id&action=loeschen&studiensemester_kurzbz=$studiensemester_kurzbz&studiengang_kz=$studiengang_kz&filter=$filter'}\" value='Löschen'></td>";
	echo '</tr>';
}
echo '</tbody></table><br>';

echo '</body>';
echo '</html>';
?>