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
require_once('../../config/vilesci.config.inc.php');			
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/pruefling.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/reihungstest.class.php');
require_once('../../include/studiensemester.class.php');
	
//	Studiengang lesen 
$s=new studiengang();
$s->getAll('typ, kurzbz', false);
$studiengang=$s->result;

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$datum_obj = new datum();

if(isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete']=='prestudent')
{
	$search=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
	if (is_null($search) ||$search=='')
		exit();	
	$qry = "SELECT 
				nachname, vorname, prestudent_id,
				UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) as stg, 
				get_rolle_prestudent(prestudent_id, null) as status
			FROM 
				public.tbl_person 
				JOIN public.tbl_prestudent USING(person_id)
				JOIN public.tbl_studiengang USING(studiengang_kz)
			WHERE
				lower(nachname) like '%".$db->db_escape(mb_strtolower($search))."%' OR
				lower(vorname) like '%".$db->db_escape(mb_strtolower($search))."%' OR
				lower(nachname || ' ' || vorname) like '%".$db->db_escape(mb_strtolower($search))."%' OR
				lower(vorname || ' ' || nachname) like '%".$db->db_escape(mb_strtolower($search))."%'
			";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			echo html_entity_decode($row->vorname).' '.html_entity_decode($row->nachname).'|'.html_entity_decode($row->stg).'|'.html_entity_decode($row->status).'|'.html_entity_decode($row->prestudent_id)."\n";
		}
	}
	exit;
}
	
$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//DE" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<title>Reihungstest Administration</title>
		<link rel="stylesheet" href="../../skin/jquery.css" type="text/css"/>
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">

		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
		<script type="text/javascript" src="../../include/js/jquery.js"></script> 
	</head>
	<body class="Background_main">
	<h2>Reihungstest - Administration</h2>';

if(!$rechte->isBerechtigt('basis/testtool', null, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Seite');

if(isset($_POST['personzuteilen']))
{
	$prestudent = new prestudent();
	if($prestudent->load($_POST['prestudent_id']))
	{
		$prestudent->reihungstest_id=$_POST['reihungstest_id'];
		$prestudent->new=false;
		if($prestudent->save())
			echo '<span class="ok">Zuteilung gespeichert</span>';
		else
			echo '<span class="error">Fehler beim Speichern der Zuteilung</span>';
	}
	else
	{
		echo '<span class="error">Fehler beim Laden des Prestudenten</span>';
	}	
}
//Links
echo '<br><a href="https://cis.technikum-wien.at/cis/testtool/admin/auswertung.php" target="blank">Auswertung</a> | 
	<a href="https://cis.technikum-wien.at/cis/testtool/admin/uebersichtFragen.php" target="blank">Fragenkatalog</a><br>
	<hr>';
//Anzeigen der kommenden Reihungstesttermine:
echo '<br><a href="'.$_SERVER['PHP_SELF'].'?action=showreihungstests">Anzeigen der kommenden Reihungstests</a>';

if(isset($_GET['action']) && $_GET['action']=='showreihungstests')
{
	$qry = "SELECT kurzbzlang, datum,ort_kurzbz,anmerkung, uhrzeit, insertvon,reihungstest_id, 
			(SELECT count(*) FROM public.tbl_prestudent WHERE reihungstest_id=tbl_reihungstest.reihungstest_id) as anzahl_teilnehmer
			FROM public.tbl_reihungstest JOIN public.tbl_studiengang USING (studiengang_kz)
			WHERE datum>=CURRENT_DATE ORDER BY datum";
	
	if($result = $db->db_query($qry))
	{
		echo '<table class="liste table-stripeclass:alternate table-autostripe">
				<thead>
					<tr>
						<th>Kurzbz</th>
						<th>Datum</th>
						<th>Ort</th>
						<th>Uhrzeit</th>
						<th>Teilnehmer</th>
						<th>Anmerkung</th>
						<th>InsertVon</th>
						<th>ReihungstestID</th>
					</tr>
				</thead>
				<tbody>';
		while($row = $db->db_fetch_object($result))
		{
			echo '<tr>';
			echo "<td>$row->kurzbzlang</td>";
			echo "<td>".$datum_obj->formatDatum($row->datum,'d.m.Y')."</td>";
			echo "<td>$row->ort_kurzbz</td>";
			echo "<td>$row->uhrzeit</td>";
			echo "<td>$row->anzahl_teilnehmer</td>";
			echo "<td>$row->anmerkung</td>";
			echo "<td>$row->insertvon</td>";
			echo "<td>$row->reihungstest_id</td>";
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
}

// Antworten eines Gebietes einer Person löschen
$ps=new prestudent();
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
	
	echo '<OPTION value="'.$prestd->prestudent_id.'" '.$selected.'>'.$prestd->nachname.' '.$prestd->vorname.'; ID='.$prestd->prestudent_id.'; '.$prestd->gebdatum."</OPTION>\n";
}
echo '</SELECT>';

$qry = "SELECT * FROM testtool.tbl_gebiet ORDER BY bezeichnung";
if($result = $db->db_query($qry))
{
	echo 'Gebiet: <SELECT name="gebiet">';
	while($row = $db->db_fetch_object($result))
	{
		if(isset($_POST['gebiet']) && $_POST['gebiet']==$row->gebiet_id)
			$selected='selected';
		else
			$selected='';
			
		echo "<OPTION  value='$row->gebiet_id' $selected>$row->bezeichnung ($row->kurzbz)</OPTION>";
	}
	echo '</SELECT>';
}

echo '<input type="submit" value="Teilgebiet l&ouml;schen" name="deleteteilgebiet">&nbsp;&nbsp;&nbsp;&nbsp;';
if(isset($_POST['deleteteilgebiet']))
{
	if(isset($_POST['prestudent']) && isset($_POST['gebiet']) && 
	   is_numeric($_POST['prestudent']) && is_numeric($_POST['gebiet']))
	{
		$pruefling = new pruefling();
		$pruefling->getPruefling($_POST['prestudent']);
		if($pruefling->pruefling_id=='')
			die('Pruefling wurde nicht gefunden');
	
		$qry = "DELETE FROM testtool.tbl_pruefling_frage where pruefling_id=".$db->db_add_param($pruefling->pruefling_id, FHC_INTEGER)." AND
				frage_id IN (SELECT frage_id FROM testtool.tbl_frage WHERE gebiet_id=".$db->db_add_param($_POST['gebiet']).");
				
				DELETE FROM testtool.tbl_antwort 
				WHERE pruefling_id=".$db->db_add_param($pruefling->pruefling_id)." AND 
				vorschlag_id IN (SELECT vorschlag_id FROM testtool.tbl_vorschlag WHERE frage_id IN 
				(SELECT frage_id FROM testtool.tbl_frage WHERE gebiet_id=".$db->db_add_param($_POST['gebiet'])."));";
		if($result = $db->db_query($qry))
		{
			echo '<b>'.$db->db_affected_rows($result).' Antworten wurden gelöscht</b>';
		}
		else 
			echo '<b>Fehler beim Löschen der Daten</b>';
	}
}

echo '<input type="submit" value="! Alle Teilgebiete l&ouml;schen !" name="delete_all"></form>';
if(isset($_POST['delete_all']))
{
	if(isset($_POST['prestudent']) && isset($_POST['gebiet']) && 
	   is_numeric($_POST['prestudent']) && is_numeric($_POST['gebiet']))
	{
		$pruefling = new pruefling();
		$pruefling->getPruefling($_POST['prestudent']);
		if($pruefling->pruefling_id=='')
			die('Pruefling wurde nicht gefunden');
	
		$qry = "DELETE FROM testtool.tbl_pruefling_frage where pruefling_id=".$db->db_add_param($pruefling->pruefling_id).";
				DELETE FROM testtool.tbl_antwort WHERE pruefling_id=".$db->db_add_param($pruefling->pruefling_id).";";
				
				
		if($result = $db->db_query($qry))
		{
			echo '<b> Alle '.$db->db_affected_rows($result).' Antworten wurden gelöscht</b>';
		}
		else 
			echo '<b>Fehler beim Löschen der Daten</b>';
	}
}

// Testergebnisse anzeigen
echo '<hr><br><form action="'.$_SERVER['PHP_SELF'].'" method="POST">Testergebnisse der Person mit der Prestudent_id <input type="text" name="prestudent_id"><input type="submit" value="anzeigen" name="testergebnisanzeigen"></form>';
if(isset($_POST['testergebnisanzeigen']) && isset($_POST['prestudent_id']))
{
	if(is_numeric($_POST['prestudent_id']) && $_POST['prestudent_id']!='')
	{
		$qry="SELECT nachname,vorname,person_id,prestudent_id,tbl_pruefling.pruefling_id,tbl_pruefling_frage.begintime,bezeichnung,kurzbz,tbl_frage.nummer,level, tbl_vorschlag.nummer as antwortnummer, tbl_vorschlag.punkte
				FROM testtool.tbl_antwort
				JOIN testtool.tbl_vorschlag USING(vorschlag_id)
				JOIN testtool.tbl_frage USING (frage_id)
				JOIN testtool.tbl_gebiet USING (gebiet_id)
				JOIN testtool.tbl_pruefling USING (pruefling_id)
				JOIN testtool.tbl_pruefling_frage ON (tbl_pruefling.pruefling_id=tbl_pruefling_frage.pruefling_id AND tbl_frage.frage_id =tbl_pruefling_frage.frage_id)
				JOIN public.tbl_prestudent USING (prestudent_id)
				JOIN public.tbl_person USING (person_id)
				WHERE prestudent_id=".$db->db_add_param($_POST['prestudent_id'])."
				ORDER BY kurzbz,tbl_pruefling_frage.begintime,nummer";
		if($result = $db->db_query($qry))
		{
			echo '<table class="liste table-stripeclass:alternate table-autostripe">
					<thead>
					<tr>
						<th>Nachname</th>
						<th>Vorname</th>
						<th>PersonID</th>
						<th>PrestudentID</th>
						<th>PrueflingID</th>
						<th>Beginnzeit</th>
						<th>Gebiet</th>
						<th>Frage #</th>
						<th>Level</th>
						<th>Antwort #</th>
						<th>Punkte</th>
					</tr>
					</thead>
					<tbody>';
			while($row = $db->db_fetch_object($result))
			{
				echo '<tr>';
				echo "<td>$row->nachname</td>";
				echo "<td>$row->vorname</td>";
				echo "<td>$row->person_id</td>";
				echo "<td>$row->prestudent_id</td>";
				echo "<td>$row->pruefling_id</td>";
				echo "<td>$row->begintime</td>";
				echo "<td>$row->bezeichnung ($row->kurzbz)</td>";
				echo "<td>$row->nummer</td>";
				echo "<td>$row->level</td>";
				echo "<td>$row->antwortnummer</td>";
				echo "<td>$row->punkte</td>";
				echo '</tr>';
			}
			echo '</tbody></table>';
		}
	}
}
// Antworten des Dummy Studenten löschen
echo '<hr><br><a href="'.$_SERVER['PHP_SELF'].'?action=deletedummyanswers" onclick="return confirm(\'Dummyanworten wirklich löschen?\');">Antworten von Dieter Dummy löschen</a>';

if(isset($_GET['action']) && $_GET['action']=='deletedummyanswers')
{
	$qry = "DELETE FROM testtool.tbl_antwort WHERE pruefling_id=841;
			DELETE FROM testtool.tbl_pruefling_frage where pruefling_id=841;";
	if($db->db_query($qry))
		echo ' <b>Antworten wurden gelöscht</b>';
	else 
		echo ' <b>Fehler beim Löschen der Antworten</b>';
}

//Studiengang von Dummy Aendern
echo '<br><br>';
if(isset($_POST['savedummystg']) && isset($_POST['stg']))
{
	$qry = "UPDATE public.tbl_prestudent SET studiengang_kz=".$db->db_add_param($_POST['stg'])." WHERE prestudent_id='13478';
	UPDATE testtool.tbl_pruefling SET studiengang_kz=".$db->db_add_param($_POST['stg'])." WHERE prestudent_id='13478';";	
	if($db->db_query($qry))
		echo '<b>Studiengang geändert!</b><br>';
	else 
		echo '<b>Fehler beim Ändern des Studienganges!</b><br>';
}
$name='';
$dummystg='';
$qry = "SELECT studiengang_kz, vorname, nachname FROM public.tbl_prestudent JOIN public.tbl_person USING(person_id) WHERE prestudent_id='13478'";
if($result = $db->db_query($qry))
{
	if($row = $db->db_fetch_object($result))
	{
		$name = $row->vorname.' '.$row->nachname;
		$dummystg=$row->studiengang_kz;
	}
}
echo '<form action="'.$_SERVER['PHP_SELF'].'" METHOD="POST">Studiengang von '.$name.' 
	<SELECT name="stg">';
$stg_obj = new studiengang();
$stg_obj->getAll('typ, kurzbz');

foreach ($stg_obj->result as $row)
{
	echo '<option value="'.$row->studiengang_kz.'" '.($row->studiengang_kz==$dummystg?'selected':'').'>'.$row->kuerzel.'</option>';
}
echo '</SELECT>
<input type="submit" name="savedummystg" value="Speichern">
</form>
';

// Hinzufuegen von Personen zum RT
echo '<hr><br>Personen zum RT hinzufuegen';

$rt = new reihungstest();
$rt->getAll(date('Y-m-d'));
echo '
<form action="'.$_SERVER['PHP_SELF'].'" METHOD="POST">
Person <input id="prestudent_name" name="prestudent_name" size="32" maxlength="30" value="" />
<input type="hidden" id="prestudent_id" name="prestudent_id" value="" />
<SELECT name="reihungstest_id">
';
foreach($rt->result as $row)
{
	if($row->datum==date('Y-m-d'))
		$selected='selected';
	else
		$selected='';
	echo '<OPTION value="'.$row->reihungstest_id.'" '.$selected.'>'.$row->datum.' '.$row->uhrzeit.' '.$row->anmerkung.'</OPTION>';
}
echo '</SELECT>
<input type="submit" value="zuteilen" name="personzuteilen">
</form>';
echo "<script type='text/javascript'>
function formatItem(row) 
{
    return row[0] + ' ' + row[1] + ' ' + row[2] + ' ' + row[3];
}	

$('#prestudent_name').autocomplete('reihungstest_administration.php', 
	  		  	{
	  			minChars:2,
	  			matchSubset:1,matchContains:1,
	  			width:500,
	  			formatItem:formatItem,
	  			extraParams:{'autocomplete':'prestudent'	
		  		}
	  	  }).result(function(event, item) {
	  		  $('#prestudent_id').val(item[3]);
	  	  });	  	
</script>";


// Uebersicht ueber die Teilgebiete der Studiengaenge
echo '<hr><br>&Uuml;bersicht &uuml;ber die Teilgebiete der Studieng&auml;nge';

$studiengang_kz = isset($_REQUEST['studiengang_kz'])?$_REQUEST['studiengang_kz']:1;
$semester = isset($_REQUEST['semester'])?$_REQUEST['semester']:-1;
$gesamtzeit = 0;
$persoenlichkeit = false;

echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
$stg_obj = new studiengang();
$stg_obj->getAll('typ, kurzbz, bezeichnung');
echo "\n",'Studiengang <SELECT name="studiengang_kz">
<OPTION value="">-- Bitte ausw&auml;hlen --</OPTION>';
foreach($stg_obj->result as $row)
{
	if($row->studiengang_kz==$studiengang_kz)
		$selected='selected';
	else
		$selected='';
	echo '<OPTION value="'.$row->studiengang_kz.'" '.$selected.'>'.$row->kuerzel.' - '.$row->bezeichnung.'</OPTION>';
}
echo '</SELECT>';
echo '<SELECT name="semester">
<OPTION value="">-- Alle --</OPTION>';
for ($i=0;$i<9;$i++)
{
	if ($semester==$i && $semester!='')
		echo "<option value=\"$i\" selected>$i</option>";
	else
		echo "<option value=\"$i\">$i</option>";
}		
echo '</SELECT>';
echo '&nbsp;&nbsp;<input type="submit" name="show" value="OK"></form><br>';

$qry="SELECT 
		UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) as stg,
		semester,
		studiengang_kz,
		reihung,
		gebiet_id,
		tbl_gebiet.bezeichnung,
		zeit,
		multipleresponse,
		maxfragen,
		zufallfrage,
		zufallvorschlag,
		level_start,
		level_sprung_auf,
		level_sprung_ab,
		levelgleichverteilung,
		maxpunkte,
		antwortenprozeile, 
		(SELECT SUM (zeit) AS sum FROM testtool.tbl_gebiet JOIN testtool.tbl_ablauf USING (gebiet_id) WHERE studiengang_kz='".$studiengang_kz."'";
		if ($semester!='') 
			$qry.=" AND semester='".$semester."'";				
		$qry.="	) AS gesamtzeit,
		(SELECT SUM (zeit) AS sum FROM testtool.tbl_gebiet JOIN testtool.tbl_ablauf USING (gebiet_id) WHERE studiengang_kz='".$studiengang_kz."'";
		if ($semester!='') 
			$qry.=" AND semester='".$semester."'";				
		$qry.="	)-'00:40:00'::time without time zone AS gesamtzeit_persoenlichkeit
		FROM testtool.tbl_ablauf 
		JOIN testtool.tbl_gebiet USING (gebiet_id) 
		JOIN public.tbl_studiengang USING (studiengang_kz)
		WHERE studiengang_kz='".$studiengang_kz."'";
		if ($semester!='') 
			$qry.=" AND semester='".$semester."'";
		
		$qry.=" ORDER BY stg,semester,reihung";

//echo $qry;
$row=$db->db_fetch_object($db->db_query($qry));
$num_rows=$db->db_num_rows($db->db_query($qry));
if ($studiengang_kz!=1 && $num_rows!=0)
{
	$gesamtzeit = $row->gesamtzeit;	
	if($result = $db->db_query($qry))
	{
		$num_rows=$db->db_num_rows($result);
		echo "<table class='liste table-stripeclass:alternate table-autostripe' border='0' style='width:auto'>
		
			<tbody style='width:auto; padding-left:10px'>";
		
		echo "<th style='padding-left:5px; padding-right:5px' class='table-sortable:default'>STG</th>
		<th style='padding-left:5px; padding-right:5px' class='table-sortable:default'>SEM</th>
		<th style='padding-left:5px; padding-right:5px' class='table-sortable:default'>KZ</th>
		<th style='padding-left:5px; padding-right:5px' class='table-sortable:default'>NR</th>
		<th style='padding-left:5px; padding-right:5px' class='table-sortable:default'>Gebiet_id</th>
		<th style='padding-left:5px; padding-right:5px' class='table-sortable:default'>Bezeichnung</th>
		<th style='padding-left:5px; padding-right:5px' class='table-sortable:default'>Zeit</th>
		<th style='padding-left:5px; padding-right:5px' class='table-sortable:default'><div title='Multiple Response' style='cursor:help'>MR</div></th>
		<th style='padding-left:5px; padding-right:5px' class='table-sortable:default'>Maxfragen</th>
		<th style='padding-left:5px; padding-right:5px' class='table-sortable:default'><div title='Zufallsfrage' style='cursor:help'>ZFF</div></th>
		<th style='padding-left:5px; padding-right:5px' class='table-sortable:default'><div title='Zufallsvorschlag' style='cursor:help'>ZFV</div></th>
		<th style='padding-left:5px; padding-right:5px' class='table-sortable:default'>Level-Start</th>
		<th style='padding-left:5px; padding-right:5px' class='table-sortable:default'>Level auf</th>
		<th style='padding-left:5px; padding-right:5px' class='table-sortable:default'>Level ab</th>
		<th style='padding-left:5px; padding-right:5px' class='table-sortable:default'><div title='Levelgleichverteilung' style='cursor:help'>LGV</div></th>
		<th style='padding-left:5px; padding-right:5px' class='table-sortable:default'>Maxpunkte</th>
		<th style='padding-left:5px; padding-right:5px' class='table-sortable:default'><div title='Antwortenprozeile' style='cursor:help'>AWPZ</div></th>\n";
		echo "</tr></thead>";
		echo "<tbody>";
		for($i=0;$i<$num_rows;$i++)
		{
		   $row=$db->db_fetch_object($result);
		   echo "<tr>";
		   echo "<td>$row->stg</td>
		   <td>$row->semester</td>
		   <td>$row->studiengang_kz</td>
		   <td>$row->reihung</td>
		   <td>$row->gebiet_id</td>
		   <td>$row->bezeichnung</td>";
		   if ($row->gebiet_id==7)
		   {
		   		echo "<td>00:20:00*</td>";
		   		$gesamtzeit = $row->gesamtzeit_persoenlichkeit; //Das Gebiet Persönlichkeit wird mit 20 Min. angezeigt und berechnet, läuft im System aber 60 Min.
		   		$persoenlichkeit = true;
		   }
		   else 
		   {
		   		echo "<td>$row->zeit</td>";
		   }	   	
		   echo "<td align='center'><img src='../../skin/images/".($row->multipleresponse=='t'?'true.png':'false.png')."' height='20'></td>
		   <td align='center'>$row->maxfragen</td>
		   <td align='center'><img src='../../skin/images/".($row->zufallfrage=='t'?'true.png':'false.png')."' height='20'></td>
		   <td align='center'><img src='../../skin/images/".($row->zufallvorschlag=='t'?'true.png':'false.png')."' height='20'></td>
		   <td align='center'>$row->level_start</td>
		   <td align='center'>$row->level_sprung_auf</td>
		   <td align='center'>$row->level_sprung_ab</td>
		   <td align='center'><img src='../../skin/images/".($row->levelgleichverteilung=='t'?'true.png':'false.png')."' height='20'></td>
		   <td align='center'>$row->maxpunkte</td>
		   <td align='center'>$row->antwortenprozeile</td>";
		   echo "</tr>\n";
		}
		echo "<tr>";
		echo "<td></td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "<td align='right'>Gesamt&nbsp;</td>";
		echo "<td>".$gesamtzeit."</td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "</tr>\n";
	}
	else
		echo "Kein Eintrag gefunden!";

	echo "</tbody></table>";
	if ($persoenlichkeit)
		echo "<div style='font-size:smaller'>*Das Gebiet Persönlichkeit ist mit 60 Minuten eingestellt, kann aber in der Regel in 15-20 Minuten bearbeitet werden.</div>";
	
	echo "<br>";
}

//Übersicht freigeschaltene Reihungstest
echo '<hr>';
echo 'Freigeschaltene Reihungstests:';

if(isset($_GET['action']) && $_GET['action']=='sperren')
{
	$rt = new reihungstest();
	if($rt->load($_GET['reihungstest_id']))
	{
		$rt->freigeschaltet=false;
		$rt->new=false;
		if(!$rt->save())
			echo 'Fehler beim Sperren:'.$rt->errormsg;
	}
	else
	{
		echo 'Fehler beim Laden des Reihungstests';
	}
}

$qry = "SELECT * FROM public.tbl_reihungstest WHERE freigeschaltet ORDER BY datum";

if($result = $db->db_query($qry))
{
	echo '<table class="liste">';
	echo '<tr>
			<th>Stg</th>
			<th>Ort</th>
			<th>Datum</th>
			<th>Uhrzeit</th>
			<th>Anmerkung</th>
			<th>Action</th>
		</tr>';
	while($row = $db->db_fetch_object($result))
	{
		echo '<tr>';
		echo '<td>'.$row->studiengang_kz.'</td>';
		echo '<td>'.$row->ort_kurzbz.'</td>';
		echo '<td>'.$row->datum.'</td>';
		echo '<td>'.$row->uhrzeit.'</td>';
		echo '<td>'.$row->anmerkung.'</td>';
		echo '<td><a href="'.$_SERVER['PHP_SELF'].'?action=sperren&reihungstest_id='.$row->reihungstest_id.'">Sperren</a></td>';
		echo '</tr>';
	}
	echo '</table>';
}

	
echo '</body>
</html>';
?>