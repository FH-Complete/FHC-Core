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
 *			Stefan Puraner <stefan.puraner@technikum-wien.at>
 *
 * Zeigt den Studienplan eines Studierenden an
 * und bietet die Möglichkeit zur Anmeldung zu Lehrveranstaltungen.
 * Dabei werden Regeln und Anmeldezeiträume der Lehrveranstaltungen berücksichtigt.
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../config/global.config.inc.php');
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
require_once('../../../include/konto.class.php');
require_once('../../../include/lvinfo.class.php');
require_once('../../../include/addon.class.php');
require_once('../../../include/anrechnung.class.php');

$uid = get_uid();

if(isset($_GET['uid']))
{
	// Administratoren duerfen die UID als Parameter uebergeben um den Studienplan
	// von anderen Personen anzuzeigen

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);
	if($rechte->isBerechtigt('admin'))
		$uid=$_GET['uid'];
}

$p = new phrasen(getSprache());
$datum_obj = new datum();
$db = new basis_db();

if(isset($_GET['getAnmeldung']))
{
	// Liefert das Formular zur Anmeldung zu Lehrveranstaltungen zurueck

	$lehrveranstaltung_id=$_GET['lehrveranstaltung_id'];
	$stsem = $_GET['stsem'];

	echo $p->t('studienplan/LehrveranstalungWaehlen').'
		<form action="'.$_SERVER['PHP_SELF'].'?uid='.$db->convert_html_chars($uid).'" method="POST">
		<input type="hidden" name="action" value="anmeldung" />
		<input type="hidden" name="stsem" value="'.$db->convert_html_chars($stsem).'" />';
	$lehrveranstaltung = new lehrveranstaltung();
	$anzahl=0;

	// Die Anmeldung ist zur Lehrveranstaltung selbst und zu den dazu kompatiblen Lehrveranstaltungen moeglich
	$kompatibel = $lehrveranstaltung->loadLVkompatibel($lehrveranstaltung_id);

	$datum = new datum();
	$kompatibel[]=$lehrveranstaltung_id;
	$kompatibel = array_unique($kompatibel);
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
				{
					// User ist noch nicht angemeldet
					echo '<br><input type="radio" value="'.$lvid.'" name="lv"/>'.$lv->bezeichnung.' (Anmeldung bis '.$datum->formatDatum($angebot->anmeldefenster_ende,"d.m.Y").')';
				}
				else
				{
					// Bereits angemeldet
					echo '<br><input type="radio" disabled="true" value="'.$lvid.'" name="lv" /><span class="ok">'.$lv->bezeichnung.'</span><img src="../../../skin/images/information.png" title="'.$p->t('studienplan/bereitsAngemeldet').'"/>';
				}
			}
/*			else
			{
				// LV wird angeboten, Anmeldefenster ist aber nicht offen oder keine Gruppe zugeteilt
				echo '<br><input type="radio" disabled="true" value="'.$lvid.'" name="lv" /><span style="color:gray;">'.$lv->bezeichnung.'</span><img src="../../../skin/images/information.png" title="'.$angebot->errormsg.'" />';
			}*/
		}
	}

	if($anzahl>0)
		echo '<br><br><input type="submit" value="'.$p->t('studienplan/anmelden').'" /></form>';
	else
		echo '<br><br>'.$p->t('studienplan/AnmeldungDerzeitNichtMoeglich');
	exit();
}
echo '<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>'.$p->t('studienplan/studienplan').'</title>
	<link rel="stylesheet" href="../../../skin/fhcomplete.css" />
	<link rel="stylesheet" href="../../../skin/style.css.php" />
	<link rel="stylesheet" href="../../../skin/jquery.css" />
	<link rel="stylesheet" href="../../../skin/jquery-ui-1.9.2.custom.min.css" />
	<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
	';

	// ADDONS laden
	$addon_obj = new addon();
	$addon_obj->loadAddons();
	foreach($addon_obj->result as $addon)
	{
	    if(file_exists('../../../addons/'.$addon->kurzbz.'/cis/init.js.php'))
	        echo '<script type="application/x-javascript" src="../../../addons/'.$addon->kurzbz.'/cis/init.js.php" ></script>';
	}

	// Wenn Seite fertig geladen ist Addons aufrufen
	echo '
	<script>
	$( document ).ready(function()
	{
	    if(typeof addon  !== \'undefined\')
	    {
	        for(i in addon)
	        {
	            addon[i].init("cis/private/profile/studienplan.php", {});
	        }
	    }
	});
	</script>
	';

echo '
	<script type="text/javascript">
	$(document).ready(function() {
		$("#dialog").dialog({ autoOpen: false, width: "auto" });
	});

	function OpenAnmeldung(lehrveranstaltung_id, stsem)
	{
		$("#dialog").load("studienplan.php?getAnmeldung=true&lehrveranstaltung_id="+lehrveranstaltung_id+"&stsem="+stsem+"&uid='.$db->convert_html_chars($uid).'");
		$("#dialog").dialog("open");
	}
	</script>
</head>
<body>
<div id="dialog" title="'.$p->t('studienplan/Anmeldung').'">'.$p->t('studienplan/Anmeldung').'</div>
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
			$bngruppe = new benutzergruppe();

			if(!$bngruppe->load($uid, $lvangebot->result[0]->gruppe_kurzbz, $stsem))
			{
				$bngruppe->uid = $uid;
				$bngruppe->gruppe_kurzbz = $lvangebot->result[0]->gruppe_kurzbz;
				$bngruppe->studiensemester_kurzbz = $stsem;
				$bngruppe->new=true;
				if($bngruppe->save())
				{
					echo '<span class="ok">'.$p->t('studienplan/einschreibungErfolgreich').'</span>';
					// Menue neu Laden damit die LV unter Meine LV gleich angezeigt wird
					echo '<script>window.parent.menu.location.reload();</script>';
				}
			}
			else
			{
				echo '<span class="error">'.$p->t('studienplan/bereitsAngemeldet').'</span>';
			}
		}
		else
			echo $lvangebot->result[0]->errormsg;
	}
	else
		echo $p->t('studienplan/AnmeldungNichtMoeglich');
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

$prestudent->getLastStatus($student->prestudent_id, '', 'Student');
$studienplan_id = $prestudent->studienplan_id;

$studienplan = new studienplan();
$studienplan->loadStudienplan($studienplan_id);

// Studienplan laden
$lehrveranstaltung = new lehrveranstaltung();
$lehrveranstaltung->loadLehrveranstaltungStudienplan($studienplan_id);
$tree = $lehrveranstaltung->getLehrveranstaltungTree();


/*
 Vom Semesterstart des Studierenden ausgehend werden die Studiensemester geladen.
 Es werden mindestens so viele Studiensemester geladen wie die Regelstudiendauer des
 Studienplanes angibt.
*/
// Angezeigte Studiensemester holen
$stsem = new studiensemester();
$stsem_arr[0]=$studiensemester_start;
$studiensemester_prev=$studiensemester_start;
for($i=1;$i<$studienplan->regelstudiendauer;$i++)
{
	$stsem_arr[$i]=$stsem->getNextFrom($studiensemester_prev);
	$studiensemester_prev=$stsem_arr[$i];
}

/*
 Wenn Studierende ueber der Regelstudiendauer hinaus studierenen, wird das aktuelle Studiensemester
 nicht angezeigt. Deshalb wird in solchen faellen immer bis zum aktuellen+2 Studiensemester geladen.
*/
$stsem_obj = new studiensemester();
$aktornext = $stsem_obj->getaktorNext();
$stsemToShow = $stsem_obj->jump($aktornext,2);

if(!in_array($stsemToShow,$stsem_arr))
{
	for($i=count($stsem_arr);$i<50;$i++)
	{
		if(!$stsem_arr[$i]=$stsem->getNextFrom($studiensemester_prev))
		{
			unset($stsem_arr[$i]);
			break;
		}
		$studiensemester_prev=$stsem_arr[$i];
		if($stsemToShow==$studiensemester_prev)
		{
			break;
		}
	}
}

// Noten des Studierenden holen
$noten_arr=array();
$zeugnisnote = new zeugnisnote();
if($zeugnisnote->getZeugnisnoten('',$uid,''))
{
	foreach($zeugnisnote->result as $row_note)
	{
		if($row_note->note!='')
		{
			$noten_arr[$row_note->lehrveranstaltung_id][$row_note->studiensemester_kurzbz]=$row_note->note;
		}
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
	<tr style="border: 1px solid black" valign="top">
		<th>'.$p->t('global/lehrveranstaltung').'</th>';

if(CIS_STUDIENPLAN_SEMESTER_ANZEIGEN)
	echo '<th>'.$p->t('global/semester').'</th>';

echo '<th>'.$p->t('studienplan/ects').'</th>
	  <th>'.$p->t('studienplan/status').'</th>';

foreach($stsem_arr as $stsem)
{
	echo '<th>';

	echo $stsem;
	$konto = new konto();
	$cp = $konto->getCreditPoints($uid, $stsem);
	if($cp!==false)
		echo '<span  title="'.$p->t('studienplan/reduzierteCP',array($cp)).'" ><br><img src="../../../skin/images/information.png" alt="Information"/></span>';
	echo '</th>';
}
echo '
	</tr>
	</thead>
	<tbody>';

// Lehrveranstaltungen anzeigen
drawTree($tree,0);

function drawTree($tree, $depth)
{
	global $uid, $stsem_arr, $noten_arr, $lvangebot_arr, $aktornext;
	global $datum_obj, $db, $lv_arr, $p, $note_pruef_arr, $student;

	foreach($tree as $row_tree)
	{
		$style='';
		if(!empty($row_tree->childs))
		{
			$bstart='<b>';$bende='</b>';
			$style=' style="background-color:#EEEEEE"';
		}
		else
		{
			$bstart='';$bende='';
		}

		switch($row_tree->lehrtyp_kurzbz)
		{
			case 'modul':
				$icon='<img src="../../../skin/images/modul.png"> ';
				$style=' style="background-color:#CCCCCC"';
				$termine='';
				break;
			case 'lv':
				$icon='<img src="../../../skin/images/lv.png"> ';
				if (!defined('CIS_STUDIENPLAN_LVPLANLINK_ANZEIGEN') || CIS_STUDIENPLAN_LVPLANLINK_ANZEIGEN)
					$termine="<a href='../lvplan/stpl_week.php?type=lva&lva=" . $row_tree->lehrveranstaltung_id . "' target='_blank'><img src='../../../skin/images/date_magnify.png' title='Termine' alt='Termine'></a>";
				break;
			default:
				$icon='';
		}


		echo '<tr'.$style.'>
			<td>'.$bstart;

		// Einrückung für Subtree
		for($i=0;$i<$depth;$i++)
			echo '&nbsp;&nbsp;&nbsp;&nbsp;';

		$lvkompatibel = new lehrveranstaltung();
		$lvkompatibel_arr = $lvkompatibel->loadLVkompatibel($row_tree->lehrveranstaltung_id);
		$lvkompatibel_arr[]=$row_tree->lehrveranstaltung_id;

		$abgeschlossen=false;
		$lvregel = new lvregel();
		$lvregelExists = $lvregel->exists($row_tree->studienplan_lehrveranstaltung_id);
		if($lvregelExists)
		{
			if($lvregel->isAbgeschlossen($uid, $row_tree->studienplan_lehrveranstaltung_id))
				$abgeschlossen=true;
			else
				$abgeschlossen=false;
		}
		$lvinfo = new lvinfo();
		switch(getSprache())
		{
		    case 'German':
			$sprache = 'de';
			break;
		    case 'English':
			$sprache = 'en';
			break;
		    default:
			$sprache = 'de';
		}
		if($lvinfo->exists($row_tree->lehrveranstaltung_id, getSprache()))
		    echo $icon." ".$termine." <a href=\"#\" class='Item' onClick=\"javascript:window.open('../lehre/ects/preview.php?lv=$row_tree->lehrveranstaltung_id&language=$sprache','Lehrveranstaltungsinformation','width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes');\">".$row_tree->kurzbz.' - '.$row_tree->bezeichnung."</a>";
		else
		// Bezeichnung der Lehrveranstaltung
		    echo $icon." ".$termine." ".$row_tree->kurzbz.' - '.$row_tree->bezeichnung.'('.$row_tree->lehrveranstaltung_id.')';
		echo $bende.'</td>';

		// Semester
		if(CIS_STUDIENPLAN_SEMESTER_ANZEIGEN)
			echo '<td>'.$row_tree->semester.'</td>';

		// ECTS Punkte
		echo '<td>'.$row_tree->ects.'</td>';

		// Status der LV (absolviert, offen)
		echo '<td>';

		// Note zu dieser LV vorhanden?

		$lv_kompatibel = new lehrveranstaltung();
		$kompatibleLVs = $lv_kompatibel->loadLVkompatibel($row_tree->lehrveranstaltung_id);

		if(isset($noten_arr[$row_tree->lehrveranstaltung_id]))
		{
			// Positive Note fuer diese LV vorhanden?
			$positiv=false;
			foreach($noten_arr[$row_tree->lehrveranstaltung_id] as $note)
			{
				if($note_pruef_arr[$note]->positiv)
					$positiv=true;
			}

			if(!$positiv)
			{
				//echo '<span class="error">'.$p->t('studienplan/negativ').'</span>';
				if(count($kompatibleLVs) > 0)
				{
					checkKompatibleLvs($kompatibleLVs, $student, $row_tree, $noten_arr, $note_pruef_arr, $p, $uid, true);
				}
				else
				{
					echo '<span class="error">'.$p->t('studienplan/negativ').'</span>';
				}
			}
            elseif($positiv)
            {
                echo '<span class="ok">'.$p->t('studienplan/abgeschlossen').'</span>';
            }
			elseif($lvregelExists)
			{
				if($abgeschlossen || $positiv)
				{
					echo '<span class="ok">'.$p->t('studienplan/abgeschlossen').'</span>';
				}
				else
				{
					if ($row_tree->benotung)
						echo '<span>'.$p->t('studienplan/offen').'</span>';
				}
			}
			else
			{
				if ($row_tree->benotung)
					echo '<span>'.$p->t('studienplan/offen').'</span>';
			}
		}
		//check if compatible course has grade
		elseif(count($kompatibleLVs) > 0)
		{
			checkKompatibleLvs($kompatibleLVs, $student, $row_tree, $noten_arr, $note_pruef_arr, $p, $uid);
		}
		else
		{
			if(!$row_tree->stpllv_pflicht)
			{
				echo '<span>'.$p->t('studienplan/optional').'</span>';
			}
			else
			{
				if ($row_tree->benotung)
					echo '<span>'.$p->t('studienplan/offen').'</span>';
			}
		}
		echo '</td>';

		// Spalten für die einzelnen Studiensemester
		foreach($stsem_arr as $key=>$stsem)
		{
			$semester=$key+1;

			$tdclass=array();
			//Empfehlung holen
//			if(isset($lv_arr[$row_tree->lehrveranstaltung_id]))
//			{
//				$empfohlenesSemester = $lv_arr[$row_tree->lehrveranstaltung_id]->semester;
//				if($semester==$empfohlenesSemester)
//					$tdclass[]='empfehlung';
//			}

			$tdinhalt='';
			$found = false;

			// Ist bereits eine Note für diese LV in diesem Stsem vorhanden?
			if(isset($noten_arr[$row_tree->lehrveranstaltung_id][$stsem]))
			{
				if($note_pruef_arr[$noten_arr[$row_tree->lehrveranstaltung_id][$stsem]]->positiv)
					$tdinhalt .= '<span class="ok">'.$note_pruef_arr[$noten_arr[$row_tree->lehrveranstaltung_id][$stsem]]->anmerkung.'</span>';
				else
					$tdinhalt .= '<span class="error">'.$note_pruef_arr[$noten_arr[$row_tree->lehrveranstaltung_id][$stsem]]->anmerkung.'</span>';
				$found=true;
			}
			elseif(count($kompatibleLVs) > 0)
			{

                $i = 0;
                while(!$found && $i < count($kompatibleLVs))
                {
                    foreach($kompatibleLVs as $komp)
                    {
                        $anrechnung = new anrechnung();
                        $anrechnung->getAnrechnungPrestudent($student->prestudent_id, $row_tree->lehrveranstaltung_id, $komp);

                        if(count($anrechnung->result) == 1)
                        {
                            $lv = $anrechnung->result[0]->lehrveranstaltung_id_kompatibel;
                            if(isset($noten_arr[$lv][$stsem]))
                            {
                                $found = true;
                                if($note_pruef_arr[$noten_arr[$lv][$stsem]]->positiv)
                                        $tdinhalt .= '<span class="ok">'.$note_pruef_arr[$noten_arr[$lv][$stsem]]->anmerkung.'</span>';
                                else
                                        $tdinhalt .= '<span class="error">'.$note_pruef_arr[$noten_arr[$lv][$stsem]]->anmerkung.'</span>';
                            }
                        }
                        $i++;
                    }
                }
			}

			if(!$found)
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
					//check if rules are fulfilled just for actual or next studiensemester
					if($stsem === $aktornext)
					{
						$result = $lvregel->isZugangsberechtigt($uid, $row_tree->studienplan_lehrveranstaltung_id, $stsem);
						if((is_array($result)) && ($result[0] !== true))
						{
							$regelerfuellt=false;
						}
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
					$tdinhalt.= '<img src="../../../skin/images/not-available.png" title="'.$p->t('studienplan/anmeldunggesperrt').'">';
				}
				else
				{
					if($angebot_vorhanden)
					{
						$tdclass[]='angebot';
						if($angemeldet)
						{
							$tdinhalt.= '<a href="#" onclick="OpenAnmeldung(\''.$row_tree->lehrveranstaltung_id.'\',\''.$stsem.'\'); return false;"><img src="../../../skin/images/ja.png" title="'.$p->t('studienplan/legendeAngemeldet').'" /></a>';
						}
						else
						{
							if($anmeldungmoeglich)
								$tdinhalt.= '<a href="#" onclick="OpenAnmeldung(\''.$row_tree->lehrveranstaltung_id.'\',\''.$stsem.'\'); return false;"><img src="../../../skin/images/anmelden.png" title="'.$p->t('studienplan/anmelden').'" height="15px" /></a>';
							else
								$tdinhalt.= '<span title="'.$anmeldeinformation.'">-</a>';

							if(!$regelerfuellt)
								$tdinhalt= '<span title="'.$p->t('studienplan/regelnichterfuellt').'">X</span>';
						}
					}
					else
					{
						// LV wird nicht angeboten
						$tdinhalt.= '-';
					}
				}
			}
			$class=implode(' ',$tdclass);
			echo '<td align="center" class="'.$class.'">';
			echo $tdinhalt;
			echo '</td>';
		}
		echo '</tr>';

		// Wenn Subtree vorhanden, dann anzeigen
		if(!empty($row_tree->childs))
			drawTree($row_tree->childs, $depth+1);
	}
}

function checkKompatibleLvs($kompatibleLVs, $student, $row_tree, $noten_arr, $note_pruef_arr, $p, $uid, $negativeNote= null)
{
	$positiv = false;
	$found = false;
	$i = 0;
	while(!$found && $i < count($kompatibleLVs))
	{
		foreach($kompatibleLVs as $komp)
		{

			$anrechnung = new anrechnung();
			$anrechnung->getAnrechnungPrestudent($student->prestudent_id, $row_tree->lehrveranstaltung_id, $komp);

			if(count($anrechnung->result) == 1)
			{
				$lv = $anrechnung->result[0]->lehrveranstaltung_id_kompatibel;
				if(isset($noten_arr[$lv]))
				{
					$positiv=false;
					foreach($noten_arr[$lv] as $note)
					{
						if($note_pruef_arr[$note]->positiv)
							$positiv=true;
					}

					$found = true;
				}
				else
				{
					/* wenn zu mehreren kompatiblen lvs eine Anrechnung existiert
					 * darf found nicht auf false gesetzt werden wenn es zuvor bereits auf true gesetzt wurde
					 */
					if(!$found)
						$found = false;
				}
			}
			$i++;
		}
	}

	if($found)
	{
		if($positiv)
		{
			echo '<span class="ok">'.$p->t('studienplan/abgeschlossen').'</span>';
		}
		else
		{
			echo '<span class="error">'.$p->t('studienplan/negativ').'</span>';
		}
	}
	elseif(!$found)
	{
		if(!$row_tree->stpllv_pflicht)
		{
			echo '<span>'.$p->t('studienplan/optional').'</span>';
		}
		else
		{
			if(($negativeNote!= null) && ($negativeNote == true))
			{
				echo '<span class="error">'.$p->t('studienplan/negativ').'</span>';
			}
			else
			{
				if ($row_tree->benotung)
					echo '<span>'.$p->t('studienplan/offen').'</span>';
			}
		}
	}
}


echo '</table>';
echo '<br><br>'.$p->t('studienplan/legende').':<br>
<table>
<!--<tr>
	<td><span class="empfehlung">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
	<td>'.$p->t('studienplan/legendeEmpfehlung').'</td>
</tr>-->
<tr>
	<td></td>
	<td></td>
</tr>
<tr>
	<td><span class="angebot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
	<td>'.$p->t('studienplan/legendeLVwirdAngeboten').'</td>
</tr>
<tr>
	<td align="center"><img src="../../../skin/images/anmelden.png"></td>
	<td>'.$p->t('studienplan/Anmeldung').'</td>
</tr>
<tr>
	<td align="center"><img src="../../../skin/images/ja.png"></td>
	<td>'.$p->t('studienplan/legendeAngemeldet').'</td>
</tr>
<tr>
	<td align="center"><img src="../../../skin/images/not-available.png"></td>
	<td>'.$p->t('studienplan/legendeLock').'</td>
</tr>
</table>
';

echo '</body>
</html>';
?>
