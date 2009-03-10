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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
 */
require_once('../config.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/pruefling.class.php');
require_once('../../include/studiengang.class.php');

if (!$conn = pg_pconnect(CONN_STRING))
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$datum_obj = new datum();
	
$user = get_uid();
$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<title>Reihungstest ADMIN</title>
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
	<meta http-equiv="content-type" content="text/html; charset=ISO-8859-9" />
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	</head>
	<body class="Background_main">
	<h2>Reihungstest - Admin</h2>';

if(!$rechte->isBerechtigt('admin', 0, 'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

//Anzeigen der kommenden Reihungstesttermine:
echo '<br><br><a href="'.$_SERVER['PHP_SELF'].'?action=showreihungstests">Anzeigen der kommenden Reihungstests</a>';

if(isset($_GET['action']) && $_GET['action']=='showreihungstests')
{
	$qry = "SELECT kurzbzlang, datum,ort_kurzbz,anmerkung, uhrzeit, insertvon,reihungstest_id
			FROM public.tbl_reihungstest JOIN public.tbl_studiengang USING (studiengang_kz)
			WHERE datum>=now() ORDER BY datum";
	
	if($result = pg_query($conn, $qry))
	{
		echo '<table class="liste table-stripeclass:alternate table-autostripe">
				<thead>
					<tr>
						<th>Kurzbz</th>
						<th>Datum</th>
						<th>Ort</th>
						<th>Uhrzeit</th>
						<th>Anmerkung</th>
						<th>InsertVon</th>
						<th>ReihungstestID</th>
					</tr>
				</thead>
				<tbody>';
		while($row = pg_fetch_object($result))
		{
			echo '<tr>';
			echo "<td>$row->kurzbzlang</td>";
			echo "<td>".$datum_obj->formatDatum($row->datum,'d.m.Y')."</td>";
			echo "<td>$row->ort_kurzbz</td>";
			echo "<td>$row->uhrzeit</td>";
			echo "<td>$row->anmerkung</td>";
			echo "<td>$row->insertvon</td>";
			echo "<td>$row->reihungstest_id</td>";
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
}

echo '<hr><br><a href="'.$_SERVER['PHP_SELF'].'?action=deletedummyanswers" onclick="return confirm(\'Dummyanworten wirklich löschen?\');">Antworten des Dummy Studenten löschen</a>';

if(isset($_GET['action']) && $_GET['action']=='deletedummyanswers')
{
	$qry = "DELETE FROM testtool.tbl_antwort WHERE pruefling_id=841;
			DELETE FROM testtool.tbl_pruefling_frage where pruefling_id=841;";
	if(pg_query($conn, $qry))
		echo ' <b>Antworten wurden gelöscht</b>';
	else 
		echo ' <b>Fehler beim Löschen der Antworten</b>';
}

//$prestudent_id=null;
$ps=new prestudent($conn);
$datum=date('Y-m-d');
$ps->getPrestudentRT($datum,true);
if ($ps->num_rows==0)
	$ps->getPrestudentRT($datum);
	
echo '<hr><br>Antworten eines Gebietes einer Person löschen<br>';
echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post" onsubmit="return confirm(\'Antworten dieses Gebietes wirklich löschen?\')">
		Person: <SELECT name="prestudent">';
foreach($ps->result as $prestd)
{
	if(isset($_POST['prestudent']) && $_POST['prestudent']==$prestd->prestudent_id)
		$selected='selected';
	else
		$selected='';
	
	echo '<OPTION value="'.$prestd->prestudent_id.'" '.$selected.'>'.$prestd->nachname.' '.$prestd->vorname."</OPTION>\n";
}
echo '</SELECT>';

$qry = "SELECT * FROM testtool.tbl_gebiet ORDER BY bezeichnung";
if($result = pg_query($conn, $qry))
{
	echo 'Gebiet: <SELECT name="gebiet">';
	while($row = pg_fetch_object($result))
	{
		if(isset($_POST['gebiet']) && $_POST['gebiet']==$row->gebiet_id)
			$selected='selected';
		else
			$selected='';
			
		echo "<OPTION  value='$row->gebiet_id' $selected>$row->bezeichnung</OPTION>";
	}
	echo '</SELECT>';
}

echo '<input type="submit" value="Teilgebiet l&ouml;schen" name="deleteteilgebiet"></form>';
if(isset($_POST['deleteteilgebiet']))
{
	if(isset($_POST['prestudent']) && isset($_POST['gebiet']) && 
	   is_numeric($_POST['prestudent']) && is_numeric($_POST['gebiet']))
	{
		$pruefling = new pruefling($conn);
		$pruefling->getPruefling($_POST['prestudent']);
		if($pruefling->pruefling_id=='')
			die('Pruefling wurde nicht gefunden');
	
		$qry = "DELETE FROM testtool.tbl_antwort 
				WHERE pruefling_id='$pruefling->pruefling_id' AND 
				vorschlag_id IN (SELECT vorschlag_id FROM testtool.tbl_vorschlag WHERE frage_id IN 
				(SELECT frage_id FROM testtool.tbl_frage WHERE gebiet_id='".$_POST['gebiet']."'));
				DELETE FROM testtool.tbl_pruefling_frage where pruefling_id='$pruefling->pruefling_id' AND
				frage_id IN (SELECT frage_id FROM testtool.tbl_frage WHERE gebiet_id='".$_POST['gebiet']."');";
		if($result = pg_query($conn, $qry))
		{
			echo '<b>'.pg_affected_rows($result).' Antworten wurden gelöscht</b>';
		}
		else 
			echo '<b>Fehler beim Löschen der Daten</b>';
	}
}

echo '<hr><br><form action="'.$_SERVER['PHP_SELF'].'" method="POST">Testergebnisse der Person mit der Prestudent_id <input type="text" name="prestudent_id"><input type="submit" value="anzeigen" name="testergebnisanzeigen"></form>';
if(isset($_POST['testergebnisanzeigen']) && isset($_POST['prestudent_id']))
{
	if(is_numeric($_POST['prestudent_id']) && $_POST['prestudent_id']!='')
	{
		$qry="SELECT nachname,vorname,person_id,prestudent_id,pruefling_id,kurzbz,tbl_frage.nummer, tbl_vorschlag.nummer as antwortnummer, tbl_vorschlag.punkte
				FROM testtool.tbl_antwort
				JOIN testtool.tbl_vorschlag USING(vorschlag_id)
				JOIN testtool.tbl_frage USING (frage_id)
				JOIN testtool.tbl_gebiet USING (gebiet_id)
				JOIN testtool.tbl_pruefling USING (pruefling_id)
				JOIN public.tbl_prestudent USING (prestudent_id)
				JOIN public.tbl_person USING (person_id)
				WHERE prestudent_id='".$_POST['prestudent_id']."'
				ORDER BY kurzbz,nummer";
		if($result = pg_query($conn, $qry))
		{
			echo '<table class="liste table-stripeclass:alternate table-autostripe">
					<thead>
					<tr>
						<th>Nachname</th>
						<th>Vorname</th>
						<th>PersonID</th>
						<th>PrestudentID</th>
						<th>PrueflingID</th>
						<th>Kurzbz</th>
						<th>Frage Nummer</th>
						<th>Antwort Nummer</th>
						<th>Punkte</th>
					</tr>
					</thead>
					<tbody>';
			while($row = pg_fetch_object($result))
			{
				echo '<tr>';
				echo "<td>$row->nachname</td>";
				echo "<td>$row->vorname</td>";
				echo "<td>$row->person_id</td>";
				echo "<td>$row->prestudent_id</td>";
				echo "<td>$row->pruefling_id</td>";
				echo "<td>$row->kurzbz</td>";
				echo "<td>$row->nummer</td>";
				echo "<td>$row->antwortnummer</td>";
				echo "<td>$row->punkte</td>";
				echo '</tr>';
			}
			echo '</tbody></table>';
		}
	}
}
echo '<hr><br>';
if(isset($_POST['savedummystg']) && isset($_POST['stg']))
{
	$qry = "UPDATE public.tbl_prestudent SET studiengang_kz='".addslashes($_POST['stg'])."' WHERE prestudent_id='13478';
	UPDATE testtool.tbl_pruefling SET studiengang_kz='".addslashes($_POST['stg'])."' WHERE prestudent_id='13478';";	
	if(pg_query($conn, $qry))
		echo '<b>Studiengang geändert!</b><br>';
	else 
		echo '<b>Fehler beim Ändern des Studienganges!</b><br>';
}
$name='';
$dummystg='';
$qry = "SELECT studiengang_kz, vorname, nachname FROM public.tbl_prestudent JOIN public.tbl_person USING(person_id) WHERE prestudent_id='13478'";
if($result = pg_query($conn, $qry))
{
	if($row = pg_fetch_object($result))
	{
		$name = $row->vorname.' '.$row->nachname;
		$dummystg=$row->studiengang_kz;
	}
}
echo "Prestudent Studiengang von $name ändern";
echo '<form action="'.$_SERVER['PHP_SELF'].'" METHOD="POST">
	<SELECT name="stg">';
$stg_obj = new studiengang($conn);
$stg_obj->getAll('typ, kurzbz');

foreach ($stg_obj->result as $row)
{
	echo '<option value="'.$row->studiengang_kz.'" '.($row->studiengang_kz==$dummystg?'selected':'').'>'.$row->kuerzel.'</option>';
}
echo '</SELECT>
<input type="submit" name="savedummystg" value="Speichern">
';
?>