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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *			Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *			Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *			Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/pruefling.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/reihungstest.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/log.class.php');

//	Studiengang lesen
$s=new studiengang();
$s->getAll('typ, kurzbz', false);
$studiengang=$s->result;

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$datum_obj = new datum();

if(isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete']=='prestudent')
{
	$search=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
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
		$result_obj = array();
		while($row = $db->db_fetch_object($result))
		{
			$item['vorname']=html_entity_decode($row->vorname);
			$item['nachname']=html_entity_decode($row->nachname);
			$item['stg']=html_entity_decode($row->stg);
			$item['status']=html_entity_decode($row->status);
			$item['prestudent_id']=html_entity_decode($row->prestudent_id);
			$result_obj[]=$item;
		}
		echo json_encode($result_obj);
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
		<link rel="stylesheet" href="../../skin/jquery-ui-1.9.2.custom.min.css" type="text/css">
		<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>';

			include('../../include/meta/jquery.php');
			include('../../include/meta/jquery-tablesorter.php');

echo '	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
		<script type="text/javascript">
		$(document).ready(function()
			{
				$("#t1").tablesorter(
					{
						sortList: [[1,0],[3,0]],
						widgets: ["zebra", "filter"],
						headers: {1: { sorter: "shortDate", dateFormat: "ddmmyyyy" }}
					});
				$("#t2").tablesorter(
					{
						sortList: [[6,0],[5,0]],
						widgets: ["zebra","filter"]
					});
				$("#t3").tablesorter(
					{
						sortList: [[0,0],[1,0],[3,0]],
						widgets: ["zebra","filter"]
					});
				$("#t4").tablesorter(
					{
						sortList: [[2,0],[3,0]],
						widgets: ["zebra","filter"],
						headers: {5:{sorter:false}}
					});

				if($("#pruefling_select").value == -1)
					$("#pruefling_select").after(" <input id=\'input_prestudent\' type=\'text\' name=\'prestudent\'></input>");
				
				$("#pruefling_select").change(
					function()
					{
						console.log(this.value);
						if(this.value == -1)//-1 - Option Prestudent ID eingeben
						{
							//eingabefeld für prestudent id anzeigen wenn nicht vorhanden
							if(!$("#input_prestudent").length)
								$("#pruefling_select").after(" <input id=\'input_prestudent\' type=\'text\' name=\'prestudent\'></input>");
						}
						else
						{
							//eingabefeld für prestudent id entfernen wenn vorhanden
							if($("#input_prestudent").length)
								$("#input_prestudent").remove();
						}
					}
				);								
			});
		</script>

	</head>
	<body class="Background_main">
	<h2>Reihungstest - Administration</h2>';

if(!$rechte->isBerechtigt('basis/testtool', null, 's'))
	die($rechte->errormsg.'&nbsp;&nbsp;<a href="reihungstest_administration.php">Seite neu laden</a>');

if(isset($_POST['personzuteilen']))
{
	if(!$rechte->isBerechtigt('basis/testtool', null, 'sui'))
		die($rechte->errormsg.'&nbsp;&nbsp;<a href="reihungstest_administration.php">Seite neu laden</a>');

	$prestudent = new prestudent();
	if($prestudent->load($_POST['prestudent_id']))
	{
		$rt_obj = new reihungstest();
		if($rt_obj->getPersonReihungstest($prestudent->person_id, $_POST['reihungstest_id'])===false)
		{
			$rt_obj = new reihungstest();

			$prestudent->getLastStatus($prestudent->prestudent_id, '', 'Interessent');

			if($prestudent->studienplan_id!='')
			{
				$rt_obj->person_id=$prestudent->person_id;
				$rt_obj->reihungstest_id=$_POST['reihungstest_id'];
				$rt_obj->studienplan_id=$prestudent->studienplan_id;
				$rt_obj->anmeldedatum = date('Y-m-d');
				$rt_obj->teilgenommen = false;
				$rt_obj->ort_kurzbz = null;
				$rt_obj->punkte = null;
                ($rt_obj->new) ? $rt_obj->insertamum = date('Y-m-d H:i:s'): $rt_obj->updateamum = date('Y-m-d H:i:s');
                ($rt_obj->new) ? $rt_obj->insertvon = $user : $rt_obj->updatevon = $user;

				if($rt_obj->savePersonReihungstest())
					echo '<span class="ok">Zuteilung gespeichert</span>';
				else
					echo '<span class="error">Fehler beim Speichern der Zuteilung</span>';
			}
			else
			{
				echo '<span class="error">Interessent ist keinen Studienplan zugeordnet</span>';
			}
		}
		else
			echo '<span class="error">Person ist bereits zugeteilt</span>';

	}
	else
	{
		echo '<span class="error">Fehler beim Laden des Prestudenten</span>';
	}
}
//Links
echo '<br><a href="'.CIS_ROOT.'cis/testtool/admin/auswertung.php" target="blank">Auswertung</a> |
	<a href="'.CIS_ROOT.'cis/testtool/admin/index.php" target="blank">Fragenadministration</a> |
	<a href="'.CIS_ROOT.'cis/testtool/admin/uebersichtFragen.php" target="blank">Fragenkatalog</a><br>
	<hr>';
//Anzeigen der kommenden Reihungstesttermine:
echo '<br><a href="'.$_SERVER['PHP_SELF'].'?action=showreihungstests">Anzeigen der kommenden Reihungstests</a>';

if(isset($_GET['action']) && $_GET['action']=='showreihungstests')
{
	$qry = "SELECT
				kurzbzlang,
				datum,
				anmerkung,
				uhrzeit,
				max_teilnehmer,
				insertvon,
				reihungstest_id,
				array_to_string(ARRAY(SELECT ort_kurzbz FROM public.tbl_rt_ort WHERE rt_id=tbl_reihungstest.reihungstest_id),',') as orte,
				(SELECT count(*) FROM public.tbl_rt_person
				WHERE rt_id=tbl_reihungstest.reihungstest_id) as anzahl_teilnehmer
			FROM public.tbl_reihungstest JOIN public.tbl_studiengang USING (studiengang_kz)
			WHERE datum>=CURRENT_DATE ORDER BY datum";

	if($result = $db->db_query($qry))
	{
		echo '<table id="t1" class="tablesorter">
				<thead>
					<tr>
						<th>Kurzbz</th>
						<th>Datum</th>
						<th>Orte</th>
						<th>Uhrzeit</th>
						<th>Teilnehmer</th>
						<th>Max-Teilnehmer</th>
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
			echo "<td>$row->orte</td>";
			echo "<td>$row->uhrzeit</td>";
			echo "<td>$row->anzahl_teilnehmer</td>";
			echo "<td ".($row->anzahl_teilnehmer>$row->max_teilnehmer?"style='color: red; font-weight: bold'":"").">$row->max_teilnehmer</td>";
			echo "<td>$row->anmerkung</td>";
			echo "<td>$row->insertvon</td>";
			echo "<td>$row->reihungstest_id</td>";
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
}

// Antworten eines Gebietes einer Person löschen und einen Logfile-Eintrag mit Undo-Befehl erstellen
if(isset($_POST['prestudent']) && is_numeric($_POST['prestudent']))
	$prestudent_id = $_POST['prestudent'];
else
	$prestudent_id = '';

$ps=new prestudent();
$datum=date('Y-m-d');
$ps->getPrestudentRT($datum);

$prestudent_arr = array();
//Array mit Dropdownwerten befüllen
foreach($ps->result as $prestd)
{
	$stg = new studiengang();
	$stg->load($prestd->studiengang_kz);
	$prestudent_arr[] .= $prestd->prestudent_id;
}

echo '<hr><br>Antworten eines Prüflings löschen<br>';
echo '<form name="teilgebiet_loeschen" action="'.$_SERVER['PHP_SELF'].'" method="POST">
		Prüfling: <SELECT id="pruefling_select" name="prestudent">';
echo '<OPTION value="">-- Name auswählen --</OPTION>';
echo '<OPTION id="prestudent_input" value="-1" '.($prestudent_id!='' && !array_search($prestudent_id, $prestudent_arr)?'selected':'').'>Prestudent ID eingeben</OPTION>';
echo '<OPTION value="" disabled="disabled">--------------</OPTION>';
foreach($ps->result as $prestd)
{
	$stg = new studiengang();
	$stg->load($prestd->studiengang_kz);
	if(isset($_POST['prestudent']) && $_POST['prestudent']==$prestd->prestudent_id)
		$selected='selected';
	else
		$selected='';
	echo '<OPTION class="prestudent_option" value="'.$prestd->prestudent_id.'" '.$selected.'>'.$prestd->nachname.' '.$prestd->vorname.', '.(strtoupper($stg->typ.$stg->kurzbz)).'; ID='.$prestd->prestudent_id.'; '.$prestd->gebdatum."</OPTION>\n";
}
echo '</SELECT>';
if($prestudent_id != '' && !in_array($prestudent_id, $prestudent_arr))
{
	echo ' <INPUT id="input_prestudent" type="text" name="prestudent" value="'.($prestudent_id!='-1'?$prestudent_id:'').'">';
}

if ($prestudent_id!='' && $prestudent_id!='-1')
{
	$qry = "SELECT DISTINCT(tbl_gebiet.gebiet_id),tbl_gebiet.bezeichnung,tbl_gebiet.kurzbz FROM testtool.tbl_gebiet
			JOIN testtool.tbl_ablauf USING (gebiet_id)
			JOIN public.tbl_prestudent USING (studiengang_kz)
			WHERE tbl_prestudent.prestudent_id = ".$prestudent_id."
			ORDER BY bezeichnung";
}
else
{
	$qry = "SELECT * FROM testtool.tbl_gebiet ORDER BY bezeichnung";
}

if($result = $db->db_query($qry))
{
	echo ' Gebiet: <SELECT name="gebiet">';
	echo '<OPTION value="">-- Gebiet auswählen --</OPTION>';
	echo '<OPTION value="alle">-- Alle Gebiete --</OPTION>';
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
echo '&nbsp;&nbsp;<input type="checkbox" name="deletePruefling">&nbsp;Auch Prüfling löschen&nbsp;&nbsp;';
echo '&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" value="Dieses Teilgebiet l&ouml;schen" name="deleteteilgebiet" onclick="return confirm(\'Antworten dieses Gebietes wirklich löschen?\')">&nbsp;&nbsp;&nbsp;&nbsp;';
if(isset($_POST['deleteteilgebiet']))
{
	if(!$rechte->isBerechtigt('basis/testtool', null, 'suid'))
		die($rechte->errormsg.'&nbsp;&nbsp;<a href="reihungstest_administration.php">Seite neu laden</a>');

	if(isset($_POST['prestudent']) && isset($_POST['gebiet']) &&
	   is_numeric($_POST['prestudent']) && is_numeric($_POST['gebiet']))
	{
		$pruefling = new pruefling();
		$pruefling->getPruefling($_POST['prestudent']);
		if($pruefling->pruefling_id=='')
			die('Pruefling wurde nicht gefunden');

		//UNDO Befehl zusammenbauen und Log schreiben
		$undo='';
		$db->db_query('BEGIN;');

		$qry = "SELECT * FROM testtool.tbl_pruefling_frage WHERE pruefling_id=".$db->db_add_param($pruefling->pruefling_id, FHC_INTEGER)." AND
				frage_id IN (SELECT frage_id FROM testtool.tbl_frage WHERE gebiet_id=".$db->db_add_param($_POST['gebiet']).");
				";

		if($db->db_query($qry))
		{
			while($row = $db->db_fetch_object())
			{
				$undo.=" INSERT INTO testtool.tbl_pruefling_frage(prueflingfrage_id,pruefling_id,frage_id,nummer,begintime,endtime) VALUES (".
				 		$db->db_add_param($row->prueflingfrage_id, FHC_INTEGER).', '.
				 		$db->db_add_param($row->pruefling_id, FHC_INTEGER).', '.
						$db->db_add_param($row->frage_id, FHC_INTEGER).', '.
						$db->db_add_param($row->nummer, FHC_INTEGER).', '.
						$db->db_add_param($row->begintime).', '.
						$db->db_add_param($row->endtime).');';
			}
		}
		else
		{
			$db->errormsg = 'Fehler beim Erstellen des UNDO Befehls fuer testtool.tbl_pruefling_frage';
			$db->db_query('ROLLBACK');
			return false;
		}

		$qry = "SELECT * FROM testtool.tbl_antwort
				WHERE pruefling_id=".$db->db_add_param($pruefling->pruefling_id)." AND
				vorschlag_id IN (SELECT vorschlag_id FROM testtool.tbl_vorschlag WHERE frage_id IN
				(SELECT frage_id FROM testtool.tbl_frage WHERE gebiet_id=".$db->db_add_param($_POST['gebiet'])."));
				";

		if($db->db_query($qry))
		{
			while($row = $db->db_fetch_object())
			{
				$undo.=" INSERT INTO testtool.tbl_antwort(antwort_id,pruefling_id,vorschlag_id) VALUES (".
				 		$db->db_add_param($row->antwort_id, FHC_INTEGER).', '.
				 		$db->db_add_param($row->pruefling_id, FHC_INTEGER).', '.
						$db->db_add_param($row->vorschlag_id, FHC_INTEGER).');';
			}
		}
		else
		{
			$db->errormsg = 'Fehler beim Erstellen des UNDO Befehls fuer testtool.tbl_antwort';
			$db->db_query('ROLLBACK');
			return false;
		}
		//Gebiet loeschen
		$qry = "DELETE FROM testtool.tbl_pruefling_frage where pruefling_id=".$db->db_add_param($pruefling->pruefling_id, FHC_INTEGER)." AND
				frage_id IN (SELECT frage_id FROM testtool.tbl_frage WHERE gebiet_id=".$db->db_add_param($_POST['gebiet']).");

				DELETE FROM testtool.tbl_antwort
				WHERE pruefling_id=".$db->db_add_param($pruefling->pruefling_id)." AND
				vorschlag_id IN (SELECT vorschlag_id FROM testtool.tbl_vorschlag WHERE frage_id IN
				(SELECT frage_id FROM testtool.tbl_frage WHERE gebiet_id=".$db->db_add_param($_POST['gebiet'])."));";

		if($result = $db->db_query($qry))
		{
			//Log schreiben
			$log = new log();

			$log->new = true;
			$log->sql = $qry;
			$log->sqlundo = $undo;
			$log->executetime = date('Y-m-d H:i:s');
			$log->mitarbeiter_uid = $user;
			$log->beschreibung = "Testtool-Antworten-Gebiet ".$_POST['gebiet']." von Prestudent ".$_POST['prestudent']." geloescht";

			if(!$log->save())
			{
				$db->errormsg = 'Fehler beim Schreiben des Log-Eintrages';
				$db->db_query('ROLLBACK');
				return false;
			}

			$db->db_query('COMMIT;');
			echo '<b>'.$db->db_affected_rows($result).' Antworten wurden gelöscht</b>';
		}
		else
		{
			$db->errormsg = 'Fehler beim Loeschen der Daten';
			$db->db_query('ROLLBACK');
		}
	}
	else
		echo '<span class="error">Wählen Sie bitte ein Gebiet, dessen Antworten Sie löschen wollen</span>';
}

echo '&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" value="! Alle Teilgebiete l&ouml;schen !" name="delete_all" onclick="return confirm(\'Wollen Sie wirklich ALLE Antworten des Prüflings löschen?\')"></form>';

// Alle Antworten aller Gebiete einer Person löschen und einen Logfile-Eintrag mit Undo-Befehl erstellen
if(isset($_POST['delete_all']))
{
	if(isset($_POST['prestudent']) && isset($_POST['gebiet']) &&
	   is_numeric($_POST['prestudent']) && ($_POST['gebiet'])=='alle')
	{
		$pruefling = new pruefling();
		$pruefling->getPruefling($_POST['prestudent']);
		if($pruefling->pruefling_id=='')
			die('Pruefling wurde nicht gefunden');

		//UNDO Befehl zusammenbauen und Log schreiben
		$undo='';
		$db->db_query('BEGIN;');

		$qry = "SELECT * FROM testtool.tbl_pruefling_frage where pruefling_id=".$db->db_add_param($pruefling->pruefling_id).";
				";

		if($db->db_query($qry))
		{
			while($row = $db->db_fetch_object())
			{
				$undo.=" INSERT INTO testtool.tbl_pruefling_frage(prueflingfrage_id,pruefling_id,frage_id,nummer,begintime,endtime) VALUES (".
				 		$db->db_add_param($row->prueflingfrage_id, FHC_INTEGER).', '.
				 		$db->db_add_param($row->pruefling_id, FHC_INTEGER).', '.
						$db->db_add_param($row->frage_id, FHC_INTEGER).', '.
						$db->db_add_param($row->nummer, FHC_INTEGER).', '.
						$db->db_add_param($row->begintime).', '.
						$db->db_add_param($row->endtime).');';
			}
		}
		else
		{
			$db->errormsg = 'Fehler beim Erstellen des UNDO Befehls fuer testtool.tbl_pruefling_frage';
			$db->db_query('ROLLBACK');
			return false;
		}

		$qry = "SELECT * FROM testtool.tbl_antwort WHERE pruefling_id=".$db->db_add_param($pruefling->pruefling_id).";
				";

		if($db->db_query($qry))
		{
			while($row = $db->db_fetch_object())
			{
				$undo.=" INSERT INTO testtool.tbl_antwort(antwort_id,pruefling_id,vorschlag_id) VALUES (".
				 		$db->db_add_param($row->antwort_id, FHC_INTEGER).', '.
				 		$db->db_add_param($row->pruefling_id, FHC_INTEGER).', '.
						$db->db_add_param($row->vorschlag_id, FHC_INTEGER).');';
			}
		}
		else
		{
			$db->errormsg = 'Fehler beim Erstellen des UNDO Befehls fuer testtool.tbl_antwort';
			$db->db_query('ROLLBACK');
			return false;
		}
		//Gebiet loeschen
		$qry = "DELETE FROM testtool.tbl_pruefling_frage where pruefling_id=".$db->db_add_param($pruefling->pruefling_id).";
				DELETE FROM testtool.tbl_antwort WHERE pruefling_id=".$db->db_add_param($pruefling->pruefling_id).";";

		if($result = $db->db_query($qry))
		{
			//Log schreiben
			$log = new log();

			$log->new = true;
			$log->sql = $qry;
			$log->sqlundo = $undo;
			$log->executetime = date('Y-m-d H:i:s');
			$log->mitarbeiter_uid = $user;
			$log->beschreibung = "Testtool-Antworten aller Gebiete von Prestudent ".$_POST['prestudent']." geloescht";

			if(!$log->save())
			{
				$db->errormsg = 'Fehler beim Schreiben des Log-Eintrages';
				$db->db_query('ROLLBACK');
				return false;
			}

			$db->db_query('COMMIT;');
			echo '<b> Alle '.$db->db_affected_rows($result).' Antworten wurden gelöscht</b>';
		}
		else
		{
			$db->errormsg = 'Fehler beim Loeschen der Daten';
			$db->db_query('ROLLBACK');
		}

		// Wenn Option angeklickt ist, auch den Prüfling löschen
		if (isset($_POST['deletePruefling']) && $_POST['deletePruefling'] == 'on')
		{
			$qry = "SELECT * FROM testtool.tbl_pruefling WHERE prestudent_id=".$db->db_add_param($_POST['prestudent']).";
				";

			if($db->db_query($qry))
			{
				while($row = $db->db_fetch_object())
				{
					$undo=" INSERT INTO testtool.tbl_pruefling(pruefling_id, studiengang_kz, idnachweis, registriert, prestudent_id, semester) VALUES (".
						$db->db_add_param($row->pruefling_id, FHC_INTEGER).', '.
						$db->db_add_param($row->studiengang_kz, FHC_INTEGER).', '.
						$db->db_add_param($row->idnachweis).', '.
						$db->db_add_param($row->registriert).', '.
						$db->db_add_param($row->prestudent_id, FHC_INTEGER).', '.
						$db->db_add_param($row->semester, FHC_INTEGER).');';
				}
			}
			else
			{
				$db->errormsg = 'Fehler beim Erstellen des UNDO Befehls fuer testtool.tbl_pruefling';
				$db->db_query('ROLLBACK');
				return false;
			}
			$qry = "DELETE FROM testtool.tbl_pruefling WHERE prestudent_id=".$db->db_add_param($_POST['prestudent']).";";

			if($result = $db->db_query($qry))
			{
				//Log schreiben
				$log = new log();

				$log->new = true;
				$log->sql = $qry;
				$log->sqlundo = $undo;
				$log->executetime = date('Y-m-d H:i:s');
				$log->mitarbeiter_uid = $user;
				$log->beschreibung = "Prüfling von Prestudent ".$_POST['prestudent']." geloescht";

				if(!$log->save())
				{
					$db->errormsg = 'Fehler beim Schreiben des Log-Eintrages';
					$db->db_query('ROLLBACK');
					return false;
				}

				$db->db_query('COMMIT;');
				echo '<br/><b> Prüfling wurde gelöscht</b>';
			}
		}
	}
	else
		echo '<span class="error">Um alle Antworten eines Prüflings zu löschen, wählen Sie im DropDown bitte "Alle Gebiete" aus</span>';
}

// Testergebnisse anzeigen
echo '<hr><br><form action="'.$_SERVER['PHP_SELF'].'" method="POST">Testergebnisse der Person mit der Prestudent_id <input type="text" name="prestudent_id" value="'.(isset($_POST['prestudent_id'])?$_POST['prestudent_id']:'').'"><input type="submit" value="anzeigen" name="testergebnisanzeigen"></form>';
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
			echo '<table id="t2" class="tablesorter">
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
	if(!$rechte->isBerechtigt('basis/testtool', null, 'suid'))
		die($rechte->errormsg.'&nbsp;&nbsp;<a href="reihungstest_administration.php">Seite neu laden</a>');

	$qry = "DELETE FROM testtool.tbl_antwort WHERE pruefling_id=(SELECT pruefling_id FROM testtool.tbl_pruefling WHERE prestudent_id=".$db->db_add_param(PRESTUDENT_ID_DUMMY_STUDENT).");
			DELETE FROM testtool.tbl_pruefling_frage where pruefling_id=(SELECT pruefling_id FROM testtool.tbl_pruefling WHERE prestudent_id=".$db->db_add_param(PRESTUDENT_ID_DUMMY_STUDENT).");";
	if($db->db_query($qry))
		echo ' <b>Antworten wurden gelöscht</b>';
	else
		echo ' <b>Fehler beim Löschen der Antworten</b>';
}

//Studiengang von Dummy Aendern
echo '<br><br>';
if(isset($_POST['savedummystg']) && isset($_POST['stg']))
{
	if(!$rechte->isBerechtigt('basis/testtool', null, 'su'))
		die($rechte->errormsg.'&nbsp;&nbsp;<a href="reihungstest_administration.php">Seite neu laden</a>');

	$qry = "UPDATE public.tbl_prestudent SET studiengang_kz=".$db->db_add_param($_POST['stg'])." WHERE prestudent_id=".$db->db_add_param(PRESTUDENT_ID_DUMMY_STUDENT).";
	UPDATE testtool.tbl_pruefling SET studiengang_kz=".$db->db_add_param($_POST['stg'])." WHERE prestudent_id=".$db->db_add_param(PRESTUDENT_ID_DUMMY_STUDENT).";";
	if($db->db_query($qry))
		echo '<b>Studiengang geändert!</b><br>';
	else
		echo '<b>Fehler beim Ändern des Studienganges!</b><br>';
}
$name='';
$dummystg='';
$qry = "SELECT studiengang_kz, vorname, nachname FROM public.tbl_prestudent JOIN public.tbl_person USING(person_id) WHERE prestudent_id=".$db->db_add_param(PRESTUDENT_ID_DUMMY_STUDENT);
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
echo '<hr><br>Personen zum RT hinzufügen';

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
	$stg = new studiengang();
	$stg->load($row->studiengang_kz);
	if($row->datum==date('Y-m-d'))
		$selected='selected';
	else
		$selected='';
	echo '<OPTION value="'.$row->reihungstest_id.'" '.$selected.'>'.$row->datum.' '.$row->uhrzeit.' '.(strtoupper($stg->typ.$stg->kurzbz)).' '.$row->ort_kurzbz.' '.$row->anmerkung.'</OPTION>';
}
echo '</SELECT>
<input type="submit" value="zuteilen" name="personzuteilen">
</form>';
echo "<script type='text/javascript'>
	function formatItem(row)
	{
		return row[0] + ' ' + row[1] + ' ' + row[2] + ' ' + row[3];
	}
	$('#prestudent_name').autocomplete({
		source: 'reihungstest_administration.php?autocomplete=prestudent',
		minLength:2,
		response: function(event, ui)
		{
			//Value und Label fuer die Anzeige setzen
			for(i in ui.content)
			{
				ui.content[i].value=ui.content[i].vorname+' '+ui.content[i].nachname+' '+ui.content[i].stg+' '+ui.content[i].status+' '+ui.content[i].prestudent_id;
				ui.content[i].label=ui.content[i].vorname+' '+ui.content[i].nachname+' '+ui.content[i].stg+' '+ui.content[i].status+' '+ui.content[i].prestudent_id;
			}
		},
		select: function(event, ui)
		{
			//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
			$('#prestudent_id').val(ui.item.prestudent_id);
		}
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
		gb.bezeichnung,
		gb.bezeichnung_mehrsprachig[1] as bezeichnung_de,
		gb.bezeichnung_mehrsprachig[2] as bezeichnung_en,
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
		offsetpunkte,
		antwortenprozeile,
		(SELECT SUM (zeit) AS sum FROM testtool.tbl_gebiet JOIN testtool.tbl_ablauf USING (gebiet_id) WHERE studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);
		if ($semester!='')
			$qry.=" AND semester=".$db->db_add_param($semester, FHC_INTEGER);
		$qry.="	) AS gesamtzeit,
		(SELECT count(*) FROM testtool.tbl_frage WHERE gebiet_id=gb.gebiet_id AND demo=false) AS anz_fragen,
		(SELECT SUM (zeit) AS sum FROM testtool.tbl_gebiet JOIN testtool.tbl_ablauf USING (gebiet_id) WHERE studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);
		if ($semester!='')
			$qry.=" AND semester=".$db->db_add_param($semester, FHC_INTEGER);
		$qry.="	)-'00:40:00'::time without time zone AS gesamtzeit_persoenlichkeit
		FROM testtool.tbl_ablauf
		JOIN testtool.tbl_gebiet gb USING (gebiet_id)
		JOIN public.tbl_studiengang USING (studiengang_kz)
		WHERE studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);
		if ($semester!='')
			$qry.=" AND semester=".$db->db_add_param($semester, FHC_INTEGER);

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
		echo "<table id='t3' class='tablesorter'><thead><tr>";
		echo "	<th>STG</th>
				<th>SEM</th>
				<th>KZ</th>
				<th>NR</th>
				<th>Gebiet_id</th>
				<th>Bezeichnung DE</th>
				<th>Bezeichnung EN</th>
				<th>Zeit</th>
				<th><div title='Multiple Response' style='cursor:help'>MR</div></th>
				<th>Summe Fragen</th>
				<th>Maxfragen</th>
				<th><div title='Zufallsfrage' style='cursor:help'>ZFF</div></th>
				<th><div title='Zufallsvorschlag' style='cursor:help'>ZFV</div></th>
				<th>Level-Start</th>
				<th>Level auf</th>
				<th>Level ab</th>
				<th><div title='Levelgleichverteilung' style='cursor:help'>LGV</div></th>
				<th>Maxpunkte</th>
				<th>Offset</th>
				<th><div title='Antwortenprozeile' style='cursor:help'>AWPZ</div></th>\n";
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
			<td>$row->bezeichnung_de</td>
			<td>$row->bezeichnung_en</td>";
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
			echo "<td align='center'>".($row->multipleresponse=='t'?'Ja':'Nein')."</td>
			<td align='center'>$row->anz_fragen</td>
			<td align='center'>$row->maxfragen</td>
			<td align='center'>".($row->zufallfrage=='t'?'Ja':'Nein')."</td>
			<td align='center'>".($row->zufallvorschlag=='t'?'Ja':'Nein')."</td>
			<td align='center'>$row->level_start</td>
			<td align='center'>$row->level_sprung_auf</td>
			<td align='center'>$row->level_sprung_ab</td>
			<td align='center'>".($row->levelgleichverteilung=='t'?'Ja':'Nein')."</td>
			<td align='center'>$row->maxpunkte</td>
			<td align='center'>".number_format((intval(($row->offsetpunkte*100))/100),2,',','.')."</td>
			<td align='center'>$row->antwortenprozeile</td>";
			echo "</tr>\n";
		}
		echo "</tbody>";
		echo "<tfooter>";
		echo "<tr>";
		echo "<td></td>";
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
		echo "<td></td>";
		echo "<td></td>";
		echo "</tr>\n";
		echo "</tfooter></table>";
	}
	else
		echo "Kein Eintrag gefunden!";

	if ($persoenlichkeit)
		echo "<div style='font-size:smaller'>*Das Gebiet Persönlichkeit ist mit 60 Minuten eingestellt, kann aber in der Regel in 15-20 Minuten bearbeitet werden.</div>";

	echo "<br>";
}

//Übersicht freigeschaltene Reihungstest
echo '<hr>';
echo 'Freigeschaltene Reihungstests:';

if(isset($_GET['action']) && $_GET['action']=='sperren')
{
	if(!$rechte->isBerechtigt('basis/testtool', null, 'su'))
		die($rechte->errormsg.'&nbsp;&nbsp;<a href="reihungstest_administration.php">Seite neu laden</a>');

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

$qry = "SELECT tbl_reihungstest.*,UPPER(tbl_studiengang.typ||tbl_studiengang.kurzbz)AS studiengang FROM public.tbl_reihungstest
		JOIN public.tbl_studiengang USING(studiengang_kz) WHERE freigeschaltet ORDER BY datum";

if($result = $db->db_query($qry))
{
	echo '<table id="t4" class="tablesorter">';
	echo '<thead><tr>
			<th>Stg</th>
			<th>Ort</th>
			<th>Datum</th>
			<th>Uhrzeit</th>
			<th>Anmerkung</th>
			<th>Action</th>
		</tr></thead><tbody>';
	while($row = $db->db_fetch_object($result))
	{
		echo '<tr>';
		echo '<td>'.$row->studiengang.'</td>';
		echo '<td>'.$row->ort_kurzbz.'</td>';
		echo '<td>'.$row->datum.'</td>';
		echo '<td>'.$row->uhrzeit.'</td>';
		echo '<td>'.$row->anmerkung.'</td>';
		echo '<td><a href="'.$_SERVER['PHP_SELF'].'?action=sperren&reihungstest_id='.$row->reihungstest_id.'">Sperren</a></td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
}


echo '</body>
</html>';
?>
