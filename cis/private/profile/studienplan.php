<?php
/*
 * Copyright 2013 fhcomplete.org
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 *
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 *
 * Zeigt den Studienplan eines Studierenden an
 * und bietet die Möglichkeit zur Anmeldung zu Lehrveranstaltungen.
 * Dabei werden Regeln und Anmeldezeiträume der Lehrveranstaltungen berücksichtigt.
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studienordnung.class.php');
require_once('../../../include/studienplan.class.php');
require_once('../../../include/lvregel.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/prestudent.class.php');
require_once('../../../include/zeugnisnote.class.php');
require_once('../../../include/lvangebot.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/note.class.php');
require_once('../../../include/benutzerberechtigung.class.php');

echo '<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Studienplan</title>
	<link rel="stylesheet" href="../../../skin/fhcomplete.css" />
	<link rel="stylesheet" href="../../../skin/style.css.php" />
	<style>
	.empfehlung 
	{
		background-color: #FFCECE;
	}
	</style>
</head>
<body>
';

$uid = get_uid();

if(isset($_GET['uid']))
{
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);
	if($rechte->isBerechtigt('admin'))
		$uid=$_GET['uid'];
	else
		die('Keine Berechtigung für UID übergabe');
}
$p = new phrasen(getSprache());

$db = new basis_db();
$datum_obj = new datum();
// Student Laden
$student = new student();
$student->load($uid);

// ersten Status holen
$prestudent = new prestudent();
$prestudent->getFirstStatus($student->prestudent_id, 'Student');

$studiensemester_start = $prestudent->studiensemester_kurzbz;
$ausbildungssemester_start = $prestudent->ausbildungssemester;
$orgform_kurzbz = $prestudent->orgform_kurzbz;

$studienplan = new studienplan();
$studienplan_id = $studienplan->getStudienplan($student->studiengang_kz, $studiensemester_start, $ausbildungssemester_start, $orgform_kurzbz);
$studienplan->loadStudienplan($studienplan_id);

// Studienplan laden
$lehrveranstaltung = new lehrveranstaltung();
$lehrveranstaltung->loadLehrveranstaltungStudienplan($studienplan_id);
$tree = $lehrveranstaltung->getLehrveranstaltungTree();


// Angezeigte Studiensemester holen
$stsem = new studiensemester();
$stsem_arr[0]=$studiensemester_start;
$studiensemester_prev=$studiensemester_start;
for($i=1;$i<=$studienplan->regelstudiendauer;$i++)
{
	$stsem_arr[$i]=$stsem->getNextFrom($studiensemester_prev);
	$studiensemester_prev=$stsem_arr[$i];
}

// Noten des Studierenden holen
$noten_arr=array();
$zeugnisnote = new zeugnisnote();
if($zeugnisnote->getZeugnisnoten('',$uid,''))
{
	foreach($zeugnisnote->result as $row_note)
	{
		if($row_note->note!='')
			$noten_arr[$row_note->lehrveranstaltung_id][$row_note->studiensemester_kurzbz]=$row_note->note;
	}
}

$note_pruef_arr = array();
$note = new note();
$note->getAll();
foreach($note->result as $row_note)
	$note_pruef_arr[$row_note->note]=$row_note;

// LV Angebot holen
$lvangebot_arr  = array();
$lvangebot = new lvangebot();
$lvangebot->getLVAngebotFromStudienplan($studienplan_id, $stsem_arr);
foreach($lvangebot->result as $row_lvangebot)
	$lvangebot_arr[$row_lvangebot->lehrveranstaltung_id][$row_lvangebot->studiensemester_kurzbz]=$row_lvangebot;

// LVs des Studienplans laden
$lv_arr = array();
$lv = new lehrveranstaltung();
$lv->loadLehrveranstaltungStudienplan($studienplan_id);
foreach($lv->lehrveranstaltungen as $row_lva)
	$lv_arr[$row_lva->lehrveranstaltung_id]=$row_lva;

echo '<h1>'.$p->t('studienplan/studienplan').": $studienplan->bezeichnung ($studienplan_id) - $student->vorname $student->nachname</h1>";

echo '<table style="border: 1px solid black">
	<thead>
	<tr>
		<th>'.$p->t('global/lehrveranstaltung').'</th>
		<th>'.$p->t('studienplan/ects').'</th>
		<th>'.$p->t('studienplan/status').'</th>';

foreach($stsem_arr as $stsem)
{
	echo '<th>'.$stsem.'</th>';
}
echo '
	</tr>
	</thead>
	<tbody>';

// Lehrveranstaltungen anzeigen
drawTree($tree,0);

function drawTree($tree, $depth)
{
	global $uid, $stsem_arr, $noten_arr, $lvangebot_arr;
	global $datum_obj, $db, $lv_arr, $p, $note_pruef_arr;

	foreach($tree as $row_tree)
	{
		echo '<tr>';
		echo '<td>';

		// Einrückung für Subtree
		for($i=0;$i<$depth;$i++)
			echo '&nbsp;&nbsp;&nbsp;&nbsp;';

		// Bezeichnung der Lehrveranstaltung
		echo $row_tree->bezeichnung;
		echo '</td>';
		
		// ECTS Punkte
		echo '<td>'.$row_tree->ects.'</td>';
		
		// Status der LV (absolviert, offen)
		echo '<td>';

		// Note zu dieser LV vorhanden?
		if(isset($noten_arr[$row_tree->lehrveranstaltung_id]))
		{
			// Positive Note fuer diese LV vorhanden?
			$positiv=false;
			foreach($noten_arr[$row_tree->lehrveranstaltung_id] as $note)
			{
				if($note_pruef_arr[$note]->positiv)
					$positiv=true;
			}
			if($positiv)
				echo '<span class="ok">'.$p->t('studienplan/abgeschlossen').'</span>';
			else
				echo '<span class="error">'.$p->t('studienplan/negativ').'</span>';
		}
		else
		{
			echo '<span>'.$p->t('studienplan/offen').'</span>';
		}
		echo '</td>';

		// Spalten für die einzelnen Studiensemester		
		foreach($stsem_arr as $key=>$stsem)
		{
			$semester=$key+1;

			$empfehlung="";
			//Empfehlung holen
			if(isset($lv_arr[$row_tree->lehrveranstaltung_id]))
			{
				$empfohlenesSemester = $lv_arr[$row_tree->lehrveranstaltung_id]->semester;
				if($semester==$empfohlenesSemester)
					$empfehlung='class="empfehlung"';
			}

			echo '<td align="center" '.$empfehlung.'>';


			// Ist bereits eine Note für diese LV in diesem Stsem vorhanden?
			if(isset($noten_arr[$row_tree->lehrveranstaltung_id][$stsem]))
			{
				if($note_pruef_arr[$noten_arr[$row_tree->lehrveranstaltung_id][$stsem]]->positiv)
					echo '<span class="ok">'.$note_pruef_arr[$noten_arr[$row_tree->lehrveranstaltung_id][$stsem]]->anmerkung.'</span>';
				else
					echo '<span class="error">'.$note_pruef_arr[$noten_arr[$row_tree->lehrveranstaltung_id][$stsem]]->anmerkung.'</span>';
			}
			else
			{
				// Angebot der LV pruefen
				if(isset($lvangebot_arr[$row_tree->lehrveranstaltung_id])
				&& isset($lvangebot_arr[$row_tree->lehrveranstaltung_id][$stsem]))
				{				
					// LV findet statt
					$angebot = $lvangebot_arr[$row_tree->lehrveranstaltung_id][$stsem];

					// Pruefen ob eine Anmeldung möglich ist
					$anmeldungmoeglich=true;

					// Anmelde Zeitfenster pruefen
					if(!$datum_obj->between($angebot->anmeldefenster_start, $angebot->anmeldefenster_ende, date('Y-m-d H:i:s')))
					{
						$anmeldeinformation=$p->t('studienplan/anmeldungvonbis',array($datum_obj->formatDatum($angebot->anmeldefenster_start,'d.m.Y H:i'),$datum_obj->formatDatum($angebot->anmeldefenster_ende,'d.m.Y H:i')));
						$anmeldungmoeglich=false;
					}
				
					if($anmeldungmoeglich)
					{
						// Regeln Pruefen
						$lvregel = new lvregel();

						// Pruefen ob Semestersperre vorhanden ist
						if(!$lvregel->checkSemester($row_tree->studienplan_lehrveranstaltung_id, $semester))
						{
							echo '<img src="../../../skin/images/lock.png" title="'.$p->t('studienplan/anmeldunggesperrt').'">';
						}
						else
						{
							if($lvregel->isZugangsberechtigt($uid, $row_tree->studienplan_lehrveranstaltung_id, $stsem))
							{
								echo '<a href="lv_anmeldung.php">'.$p->t('studienplan/anmelden').'</a>';
							}
							else
							{
								// LV wird angeboten, Regeln für Anmeldung nicht erfüllt
								echo '<span title="'.$p->t('studienplan/regelnichterfuellt').'">X</span>';
							}
						}
					}
					else
					{
						// LV wird angeboten - Anmeldung aber noch nicht moeglich
						echo '<span title="'.$anmeldeinformation.'">X</a>';
					}
				}
				else
				{
					// LV wird in diesem Studiensemester nicht angeboten
					echo '-';
				}
			}
			echo '</td>';
		}
		echo '</tr>';
		
		// Wenn Subtree vorhanden, dann anzeigen
		if(isset($row_tree->childs))
			drawTree($row_tree->childs, $depth+1);
	}
}
echo '</table>';
echo '<br><br>'.$p->t('studienplan/legende').':<br>
<table>
<tr>
	<td><span class="empfehlung">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
	<td>'.$p->t('studienplan/legendeEmpfehlung').'</td>
</tr>
<tr>
	<td>X</td>
	<td>'.$p->t('studienplan/legendeLVwirdAngeboten').'</td>
</tr>
<tr>
	<td><img src="../../../skin/images/lock.png"></td>
	<td>'.$p->t('studienplan/legendeLock').'</td>
</tr>
</table>
';

echo '</body>
</html>';
?>
