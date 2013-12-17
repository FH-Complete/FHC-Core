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
require_once('../../../include/benutzergruppe.class.php');

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
$datum_obj = new datum();
$db = new basis_db();

if(isset($_GET['getAnmeldung']))
{
	$lehrveranstaltung_id=$_GET['lehrveranstaltung_id'];
	$stsem = $_GET['stsem'];

	echo $p->t('studienplan/LehrveranstalungWaehlen').'
		<form action="'.$_SERVER['PHP_SELF'].'?uid='.$db->convert_html_chars($uid).'" method="POST">
		<input type="hidden" name="action" value="anmeldung" />
		<input type="hidden" name="stsem" value="'.$db->convert_html_chars($stsem).'" />';
	$lehrveranstaltung = new lehrveranstaltung();
	if($kompatibel = $lehrveranstaltung->loadLVkompatibel($lehrveranstaltung_id))
	{
		$anzahl=0;
		foreach($kompatibel as $lvid)
		{
			$lvangebot = new  lvangebot();
			$lvangebot->getAllFromLvId($lvid, $stsem);
			if(isset($lvangebot->result[0]))
			{
				$lv = new lehrveranstaltung();
				$lv->load($lvid);

				$angebot = $lvangebot->result[0];
				if($angebot->AnmeldungMoeglich())
				{
					$anzahl++;
					// LV wird angeboten und Anmeldefenster ist offen

					$bngruppe = new benutzergruppe();
					if(!$bngruppe->load($uid, $lvangebot->result[0]->gruppe_kurzbz, $stsem))
						echo '<br><input type="radio" value="'.$lvid.'" name="lv"/>'.$lv->bezeichnung;
					else
					{
						// Bereits angemeldet
						echo '<br><input type="radio" disabled="true" value="'.$lvid.'" name="lv" /><span class="ok">'.$lv->bezeichnung.'</span> - Bereits angemeldet';
					}
				}
				else
				{
					// LV wird angeboten, Anmeldefenster ist aber nicht offen
					echo '<br><input type="radio" disabled="true" value="'.$lvid.'" name="lv" /><span style="color:gray;">'.$lv->bezeichnung.' - '.$angebot->errormsg.'</span>';
				}
			}
		}
	}
	if($anzahl>0)
		echo '<br><br><input type="submit" value="Anmelden" /></form>';
	else
		echo '<br><br>'.$p->t('studienplan/AnmeldungDerzeitNichtMoeglich');
	exit();
}
echo '<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Studienplan</title>
	<link rel="stylesheet" href="../../../skin/fhcomplete.css" />
	<link rel="stylesheet" href="../../../skin/style.css.php" />
	<link rel="stylesheet" href="../../../skin/jquery.css" />
	<link rel="stylesheet" href="../../../skin/jquery-ui-1.9.2.custom.min.css" />
	<script type="text/javascript" src="../../../include/js/jquery1.9.min.js"></script>

	<script type="text/javascript">
	$(document).ready(function() {
		$("#dialog").dialog({ autoOpen: false });
	});

	function OpenAnmeldung(lehrveranstaltung_id, stsem)
	{
		$("#dialog").load("studienplan.php?getAnmeldung=true&lehrveranstaltung_id="+lehrveranstaltung_id+"&stsem="+stsem+"&uid='.$db->convert_html_chars($uid).'");
		$("#dialog").dialog("open");
	}
	</script>
</head>
<body>
<div id="dialog" title="Anmeldung">Anmeldung</div>
';

if(isset($_POST['action']) && $_POST['action']=='anmeldung')
{
	$lehrveranstaltung_id = $_POST['lv'];
	$stsem = $_POST['stsem'];

	$lvangebot = new lvangebot();
	$lvangebot->getAllFromLvId($lehrveranstaltung_id, $stsem);

	if(isset($lvangebot->result[0]))
	{
		if($lvangebot->result[0]->AnmeldungMoeglich())
		{
			// Benutzer einschreiben
			//echo "Anmeldung zur LV: ".$_POST['lv'].$_POST['stsem'];
			$bngruppe = new benutzergruppe();

			if(!$bngruppe->load($uid, $lvangebot->result[0]->gruppe_kurzbz, $stsem))
			{
				$bngruppe->uid = $uid;
				$bngruppe->gruppe_kurzbz = $lvangebot->result[0]->gruppe_kurzbz;
				$bngruppe->studiensemester_kurzbz = $stsem;
				$bngruppe->new=true;
				if($bngruppe->save())
				{
					echo '<span class="ok">Sie wurden erfolgreich in die Lehrveranstaltung eingeschrieben</span>';
				}
			}
			else
			{
				echo '<span class="error">Sie sind bereits zu dieser Lehrveranstaltung angemeldet'.$uid.'/'.$lvangebot->result[0]->gruppe_kurzbz.'/'.$stsem.' '.$bngruppe->errormsg.'</span>';
			}
		}
		else
			echo $lvangebot->result[0]->errormsg;
	}
	else
		echo 'Keine Anmeldung moeglich';
}

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

$prestudent->getLastStatus($student->prestudent_id, 'Student');
$studienplan_id = $prestudent->studienplan_id;

$studienplan = new studienplan();
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
$lvangebot->getLVAngebotFromStudienplan($studienplan_id, $stsem_arr,true);
foreach($lvangebot->result as $row_lvangebot)
	$lvangebot_arr[$row_lvangebot->lehrveranstaltung_id][$row_lvangebot->studiensemester_kurzbz]=$row_lvangebot;

// LVs des Studienplans laden
$lv_arr = array();
$lv = new lehrveranstaltung();
$lv->loadLehrveranstaltungStudienplan($studienplan_id);
foreach($lv->lehrveranstaltungen as $row_lva)
	$lv_arr[$row_lva->lehrveranstaltung_id]=$row_lva;

echo '<h1>'.$p->t('studienplan/studienplan').": $studienplan->bezeichnung ($studienplan_id) - $student->vorname $student->nachname ( $student->uid )</h1>";

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
		echo "\n<tr>";
		echo '<td>';

		// Einrückung für Subtree
		for($i=0;$i<$depth;$i++)
			echo '&nbsp;&nbsp;&nbsp;&nbsp;';

		$lvkompatibel = new lehrveranstaltung();
		$lvkompatibel_arr = $lvkompatibel->loadLVkompatibel($row_tree->lehrveranstaltung_id);
		$lvkompatibel_arr[]=$row_tree->lehrveranstaltung_id;

		$lvregel = new lvregel();
		if($lvregel->exists($row_tree->studienplan_lehrveranstaltung_id))
		{
			if($lvregel->isAbgeschlossen($uid, $row_tree->studienplan_lehrveranstaltung_id))
				echo '<span class="ok">';
			else
				echo '<span class="error">';
		}
		else
			echo '<span>';

		// Bezeichnung der Lehrveranstaltung
		echo $row_tree->bezeichnung;
		echo '</span>';
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
			if(!$row_tree->stpllv_pflicht)
				echo '<span>'.$p->t('studienplan/optional').'</span>';
			else
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
				// Angebot der LV und der Kompatiblen pruefen
				$anmeldungmoeglich=false;
				$angemeldet=false;
				$semesterlock=false;
				$regelerfuellt=true;
				$anmeldeinformation='';
				$angebot_vorhanden=false;

				// Regeln Pruefen
				$lvregel = new lvregel();

				// Pruefen ob Semestersperre vorhanden ist
				if(!$lvregel->checkSemester($row_tree->studienplan_lehrveranstaltung_id, $semester))
				{
					$semesterlock=true;
				}
				else
				{
					if(!$lvregel->isZugangsberechtigt($uid, $row_tree->studienplan_lehrveranstaltung_id, $stsem))
					{
						$regelerfuellt=false;
					}
				}

				foreach($lvkompatibel_arr as $row_lvid)
				{
					// Angebot der LV pruefen
					if(isset($lvangebot_arr[$row_lvid])
					&& isset($lvangebot_arr[$row_lvid][$stsem]))
					{				
						$angebot_vorhanden=true;
						// LV findet statt
						$angebot = $lvangebot_arr[$row_lvid][$stsem];

						if($angebot->gruppe_kurzbz!='')
						{
							// Pruefen ob bereits angemeldet
							$bngruppe = new benutzergruppe();
							if($bngruppe->load($uid, $angebot->gruppe_kurzbz, $stsem))
							{
								// Bereits angemeldet
								$angemeldet=true;
							}
						}

						// Pruefen ob eine Anmeldung möglich ist
						if($angebot->AnmeldungMoeglich())
						{
							if(!$angemeldet)
								$anmeldungmoeglich=true;
						}
						else
							$anmeldeinformation.=$angebot->errormsg;
					}
				}

				if($semesterlock)
				{
					echo '<img src="../../../skin/images/lock.png" title="'.$p->t('studienplan/anmeldunggesperrt').'">';
				}
				else
				{
					if($angebot_vorhanden)
					{
						if($anmeldungmoeglich)		
							echo '<a href="#" onclick="OpenAnmeldung(\''.$row_tree->lehrveranstaltung_id.'\',\''.$stsem.'\'); return false;">'.$p->t('studienplan/anmelden').'</a>';
						else
							echo '<span title="'.$anmeldeinformation.'">X</a>';

						if(!$regelerfuellt)
							echo '<span title="'.$p->t('studienplan/regelnichterfuellt').'">X</span>';
					}
					else
					{
						// LV wird nicht angeboten
						echo '-';
					}
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
