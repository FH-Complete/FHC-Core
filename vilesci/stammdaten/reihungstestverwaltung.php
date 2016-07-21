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
 *          Manfred Kindl		< manfred.kindl@technikum-wien.at >
 */
/**
 * Reihungstest
 *
 * - Anlegen und Bearbeiten von Terminen
 * - Export von Anwesenheitslisten als Excel
 * - Uebertragung der Ergebnis-Punkte ins FAS
 *
 * Parameter:
 * excel ... wenn gesetzt, dann wird die Anwesenheitsliste als Excel exportiert
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/reihungstest.class.php');
require_once('../../include/ort.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/pruefling.class.php');
require_once('../../include/person.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/Excel/excel.php');
require_once('../../include/adresse.class.php');

define('REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND', '5');

if (!$db = new basis_db())
{
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
}

$user = get_uid();
$datum_obj = new datum();
$stg_kz = (isset($_GET['stg_kz']) ? $_GET['stg_kz'] : '-1');
$reihungstest_id = (isset($_GET['reihungstest_id']) ? $_GET['reihungstest_id'] : '');
$prestudent_id = (isset($_GET['prestudent_id']) ? $_GET['prestudent_id'] : '');
$rtpunkte = (isset($_GET['rtpunkte']) ? $_GET['rtpunkte'] : '');
$neu = (isset($_GET['neu']) ? true : false);
$stg_arr = array();
$error = false;

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('lehre/reihungstest'))
{
	die($rechte->errormsg);
}

$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz', false);

//Studierende als Excel Exportieren
if(isset($_GET['excel']))
{
	$reihungstest = new reihungstest();
	if($reihungstest->load($_GET['reihungstest_id']))
	{
		// Creating a workbook
		$workbook = new Spreadsheet_Excel_Writer();
		$workbook->setVersion(8);
		// sending HTTP headers
		$workbook->send("Anwesenheitsliste_Reihungstest_".$reihungstest->datum.".xls");

		// Creating a worksheet
		$worksheet =& $workbook->addWorksheet("Reihungstest");
		$worksheet->setInputEncoding('utf-8');
		//Formate Definieren
		$format_bold =& $workbook->addFormat();
		$format_bold->setBold();

		$worksheet->write(0,0,'Anwesenheitsliste Reihungstest '.$datum_obj->convertISODate($reihungstest->datum).' '.$reihungstest->uhrzeit.' Uhr '.$reihungstest->anmerkung.', erstellt am '.date('d.m.Y'), $format_bold);
		//Ueberschriften
		$i=0;
		$worksheet->write(2,$i,"Vorname", $format_bold);
		$maxlength[$i] = 7;
		$worksheet->write(2,++$i,"Nachname", $format_bold);
		$maxlength[$i] = 8;
		$worksheet->write(2,++$i,"Geburtsdatum", $format_bold);
		$maxlength[$i] = 12;
		$worksheet->write(2,++$i,"Studiengang", $format_bold);
		$maxlength[$i] = 11;
		$worksheet->write(2,++$i,"bereits absolvierte RTs", $format_bold);
		$maxlength[$i] = 18;
		$worksheet->write(2,++$i,"EMail", $format_bold);
		$maxlength[$i] = 5;
		$worksheet->write(2,++$i,"Einstiegssemester", $format_bold);
		$maxlength[$i] = 15;
		$worksheet->write(2,++$i,"STRASSE", $format_bold);
		$maxlength[$i] = 6;
		$worksheet->write(2,++$i,"PLZ", $format_bold);
		$maxlength[$i] = 3;
		$worksheet->write(2,++$i,"ORT", $format_bold);
		$maxlength[$i] = 3;

		$qry = "SELECT *,
			(SELECT kontakt FROM tbl_kontakt
			WHERE kontakttyp='email' AND person_id=tbl_prestudent.person_id AND zustellung=true LIMIT 1) as email,
			(SELECT ausbildungssemester FROM public.tbl_prestudentstatus
			WHERE prestudent_id=tbl_prestudent.prestudent_id
				AND datum=(SELECT MAX(datum) FROM public.tbl_prestudentstatus
							WHERE prestudent_id=tbl_prestudent.prestudent_id
							AND status_kurzbz='Interessent') LIMIT 1) as ausbildungssemester
			FROM
				public.tbl_prestudent
				JOIN public.tbl_person USING(person_id)
			WHERE reihungstest_id=".$db->db_add_param($reihungstest->reihungstest_id, FHC_INTEGER)." ORDER BY nachname, vorname";

		if($result = $db->db_query($qry))
		{
			$zeile=3;
			while($row = $db->db_fetch_object($result))
			{
				$i=0;
				$pruefling = new pruefling();

				$prestudent = new prestudent();
				$prestudent->getPrestudenten($row->person_id);
				$rt_in_anderen_stg='';
				foreach($prestudent->result as $item)
				{
					if($item->prestudent_id!=$row->prestudent_id)
					{
						if(defined('FAS_REIHUNGSTEST_PUNKTE') && FAS_REIHUNGSTEST_PUNKTE)
							$erg = $pruefling->getReihungstestErgebnis($item->prestudent_id, true);
						else
							$erg = $pruefling->getReihungstestErgebnis($item->prestudent_id);
						if($erg!=0)
						{
							$rt_in_anderen_stg.=number_format($erg,2).' Punkte im Studiengang '.$studiengang->kuerzel_arr[$item->studiengang_kz]."; ";
						}
					}
				}

				$worksheet->write($zeile,$i, $row->vorname);
				if(strlen($row->vorname)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->vorname);

				$worksheet->write($zeile,++$i,$row->nachname);
				if(strlen($row->nachname)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->nachname);

				$worksheet->write($zeile,++$i,$datum_obj->convertISODate($row->gebdatum));
				if(strlen($row->gebdatum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->gebdatum);

				$worksheet->write($zeile,++$i,$studiengang->kuerzel_arr[$row->studiengang_kz]);
				if(strlen($studiengang->kuerzel_arr[$row->studiengang_kz])>$maxlength[$i])
					$maxlength[$i] = mb_strlen($studiengang->kuerzel_arr[$row->studiengang_kz]);

				$worksheet->write($zeile,++$i,$rt_in_anderen_stg);
				if(strlen($rt_in_anderen_stg)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($rt_in_anderen_stg);

				$worksheet->write($zeile,++$i,$row->email);
				if(strlen($row->email)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->email);

				$worksheet->write($zeile,++$i,$row->ausbildungssemester);
				if(strlen($row->ausbildungssemester)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->ausbildungssemester);

 				$adresse = new adresse();
				$adresse->loadZustellAdresse($row->person_id);

				$worksheet->write($zeile,++$i,$adresse->strasse);
				if(strlen($adresse->strasse)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($adresse->strasse);

				$worksheet->write($zeile,++$i,$adresse->plz);
				if(strlen($adresse->plz)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($adresse->plz);

				$worksheet->write($zeile,++$i,$adresse->ort);
				if(strlen($adresse->ort)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($adresse->ort);

				$zeile++;
			}
		}
		//Die Breite der Spalten setzen
		foreach($maxlength as $i=>$breite)
			$worksheet->setColumn($i, $i, $breite+2);

		$workbook->close();
	}
	else
	{
		echo 'Reihungstest wurde nicht gefunden!';
	}
	return;
} ?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Reihungstest</title>
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<!--<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">-->
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<!--<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>-->
		<script type="text/javascript" src="../../include/js/jquery.js"></script>
		<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
		<link href="../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
		<script src="../../include/js/jquery1.9.min.js" type="text/javascript"></script>
		
		<link href="../../skin/jquery.ui.timepicker.css" rel="stylesheet" type="text/css"/>
		<script src="../../include/js/jquery.ui.timepicker.js" type="text/javascript" ></script>

		<script type="text/javascript">
			$(document).ready(function()
			{
				$(".datepicker_datum").datepicker($.datepicker.regional['de']);

				$( ".timepicker" ).timepicker({
					showPeriodLabels: false,
					hourText: "Stunde",
					minuteText: "Minute",
					hours: {starts: 7,ends: 22},
					rows: 4,
				});

				$("#ort").autocomplete({
					source: "../lehre/reservierung_autocomplete.php?autocomplete=ort_aktiv",
					minLength:2,
					response: function(event, ui)
					{
						//Value und Label fuer die Anzeige setzen
						for(i in ui.content)
						{
							ui.content[i].value=ui.content[i].ort_kurzbz;
							ui.content[i].label=ui.content[i].ort_kurzbz+" "+ui.content[i].bezeichnung;
						}
					},
					select: function(event, ui)
					{
						//Ausgewaehlte Ressource zuweisen und Textfeld wieder leeren
						$("#ort_kurzbz").val(ui.item.uid);
					}
				});

				$(".aufsicht_uid").autocomplete({
					source: "../../cis/private/tools/zeitaufzeichnung_autocomplete.php?autocomplete=kunde",
					minLength:2,
					response: function(event, ui)
					{
						//Value und Label fuer die Anzeige setzen
						for(i in ui.content)
						{
							//ui.content[i].value=ui.content[i].uid;
							ui.content[i].value=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
							ui.content[i].label=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
						}
					},
					select: function(event, ui)
					{
						//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
						$(this.id).val(ui.item.uid);
					}
					
					});

				$("#t1").tablesorter(
				{
					sortList: [[3,0]],
					widgets: ["zebra"]
				});

				$(".tablesorter").each(function(i,v)
				{
					$("#"+v.id).tablesorter(
					{
						widgets: ["zebra"],
						sortList: [[3,0]],
						headers: {0: { sorter: false}}
					})
				});
			});
		</script>
	</head>
	<body class="Background_main">
		<h2>Reihungstest - Verwaltung</h2>
<?php
// Speichern eines Termines
if(isset($_POST['speichern']))
{

	if(!$rechte->isBerechtigt('lehre/reihungstest', null, 'sui'))
	{
		die($rechte->errormsg);
	}

	$reihungstest = new reihungstest();

	if(isset($_POST['reihungstest_id']) && $_POST['reihungstest_id']!='')
	{
		//Reihungstest laden
		if(!$reihungstest->load($_POST['reihungstest_id']))
		{
			die($reihungstest->errormsg);
		}

		$reihungstest->new = false;
	}
	else
	{
		//Neuen Reihungstest anlegen
		$reihungstest->new=true;
		$reihungstest->insertvon = $user;
		$reihungstest->insertamum = date('Y-m-d H:i:s');
	}

	//Datum und Uhrzeit pruefen
	if($_POST['datum']!='' && !$datum_obj->checkDatum($_POST['datum']))
	{
		echo '<span class="input_error">Datum ist ungueltig. Das Datum muss im Format DD.MM.JJJJ eingegeben werden<br></span>';
		$error = true;
	}
	if($_POST['uhrzeit']!='' && !$datum_obj->checkUhrzeit($_POST['uhrzeit']))
	{
		echo '<span class="input_error">Uhrzeit ist ungueltig. Die Uhrzeit muss im Format HH:MM angegeben werden!<br></span>';
		$error = true;
	}

	if(!$error)
	{
		$reihungstest->studiengang_kz = $_POST['studiengang_kz'];
		//$reihungstest->ort_kurzbz = $_POST['ort_kurzbz'];
		$reihungstest->anmerkung = $_POST['anmerkung'];
		$reihungstest->datum = $datum_obj->formatDatum($_POST['datum']);
		$reihungstest->uhrzeit = $_POST['uhrzeit'];
		$reihungstest->updateamum = date('Y-m-d H:i:s');
		$reihungstest->freigeschaltet = isset($_POST['freigeschaltet']);
		$reihungstest->max_teilnehmer = filter_input(INPUT_POST, 'max_teilnehmer', FILTER_VALIDATE_INT);
		$reihungstest->oeffentlich = filter_input(INPUT_POST, 'oeffentlich', FILTER_VALIDATE_BOOLEAN);
		$reihungstest->stufe = filter_input(INPUT_POST, 'stufe', FILTER_VALIDATE_INT);
		$reihungstest->anmeldefrist = $datum_obj->formatDatum($_POST['anmeldefrist']);
		$reihungstest->updatevon = $user;

		if($reihungstest->save())
		{
			if (isset($_POST['ort_kurzbz']) && $_POST['ort_kurzbz']!='')
			{
				if($rechte->isBerechtigt('lehre/reihungstestOrt', null, 'sui'))
				{
					$orte_zugeteilt = new reihungstest();
					$orte_zugeteilt->getOrteReihungstest($reihungstest->reihungstest_id);
					$zugeteilt = false;
					foreach ($orte_zugeteilt->result AS $row)
					{
						if ($row->ort_kurzbz == $_POST['ort_kurzbz'])
						{
							$zugeteilt = true;
							break;
						}
					}
					// Check, ob der Raum schon diesem RT zugeteilt ist
					if ($zugeteilt == false)
					{
						$add_ort = new reihungstest();
						$add_ort->new = true;
						$add_ort->rt_id = $reihungstest->reihungstest_id;
						$add_ort->ort_kurzbz = $_POST['ort_kurzbz'];
						$add_ort->uid = null;
						
						if ($add_ort->saveOrtReihungstest())
						{
							echo '<b>Daten wurden erfolgreich gespeichert</b> <script>window.opener.StudentReihungstestDropDownRefresh();</script>';
						}
						else
							echo '<span class="input_error">Fehler beim Speichern der Raumzuordnung: '.$db->convert_html_chars($reihungstest->errormsg).'</span>';
					}
					else 
						echo '<span class="input_error">Der Raum '.$_POST['ort_kurzbz'].' ist bereits diesem Reihungstest zugeteilt</span>';
				}
				else 
					die($rechte->errormsg);
			}
			$reihungstest_id = $reihungstest->reihungstest_id;
			$stg_kz = $reihungstest->studiengang_kz;
		}
		else
		{
			echo '<span class="input_error">Fehler beim Speichern der Daten: '.$db->convert_html_chars($reihungstest->errormsg).'</span>';
		}
	}
	$neu=false;
}

if(isset($_POST['raumzuteilung_speichern']))
{
	if(!$rechte->isBerechtigt('lehre/reihungstest', null, 'su'))
	{
		die($rechte->errormsg);
	}
	
	$raumzuteilung = new reihungstest();
	
	if(isset($_POST['reihungstest_id']) && $_POST['reihungstest_id']!='')
	{
		//Reihungstest laden
		if(!$raumzuteilung->load($_POST['reihungstest_id']))
		{
			die($raumzuteilung->errormsg);
		}
		$prestudent_ids = $_POST['checkbox'];

		foreach ($prestudent_ids AS $key=>$value)
		{
			// UID aus POST-String auslesen
			$raumzuteilung->new = false;
			$raumzuteilung->rt_id = $_POST['reihungstest_id'];
			$raumzuteilung->rt_id_old = $_POST['reihungstest_id'];
			$raumzuteilung->person_id = $key;
			$raumzuteilung->anmeldedatum = date('Y-m-d H:i:s');;
			$raumzuteilung->teilgenommen = false;
			$raumzuteilung->ort_kurzbz = $_POST['raumzuteilung'];
			$raumzuteilung->punkte = 0;
			if (!$raumzuteilung->savePersonReihungstest())
			{
				echo '<span class="input_error">Fehler beim Speichern der Daten: '.$db->convert_html_chars($reihungstest->errormsg).'</span>';
			}
		}
		$reihungstest_id = $_POST['reihungstest_id'];
		//$stg_kz = $save_aufsicht->studiengang_kz;
	}
	$neu=false;
}

// Uebertraegt die Punkte eines Prestudenten ins FAS
if(isset($_GET['type']) && $_GET['type']=='savertpunkte')
{
	$prestudent = new prestudent();
	$prestudent->load($prestudent_id);

	if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('assistenz', $prestudent->studiengang_kz, 'suid'))
	{
		$prestudent->rt_punkte1 = str_replace(',','.',$rtpunkte);
		$prestudent->punkte = str_replace(',','.',$prestudent->rt_punkte1 + $prestudent->rt_punkte2);
		$prestudent->reihungstestangetreten=true;
		$prestudent->save(false);
	}
	else
	{
		echo '<span class="input_error"><br>Sie haben keine Berechtigung zur Uebernahme der Punkte fuer '.$db->convert_html_chars($row->nachname).' '.$db->convert_html_chars($row->vorname).'</span>';
	}
}

// Uebertraegt alle Punkte eines Reihungstests ins FAS
if(isset($_GET['type']) && $_GET['type']=='saveallrtpunkte')
{
	$errormsg='';
	$qry = "SELECT prestudent_id, tbl_prestudent.studiengang_kz, nachname, vorname, tbl_studiengang.oe_kurzbz
			FROM public.tbl_prestudent JOIN public.tbl_person USING(person_id) JOIN public.tbl_studiengang USING(studiengang_kz)
			WHERE reihungstest_id=".$db->db_add_param($reihungstest_id, FHC_INTEGER);
	// AND (rt_punkte1='' OR rt_punkte1 is null)";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			if($rechte->isBerechtigt('student/stammdaten', $row->oe_kurzbz,'suid'))
			{
				$prestudent = new prestudent();
				$prestudent->load($row->prestudent_id);

				$pruefling = new pruefling();
				if(defined('FAS_REIHUNGSTEST_PUNKTE') && FAS_REIHUNGSTEST_PUNKTE)
					$rtpunkte = $pruefling->getReihungstestErgebnis($row->prestudent_id,true);
				else
					$rtpunkte = $pruefling->getReihungstestErgebnis($row->prestudent_id);

				$prestudent->rt_punkte1 = str_replace(',','.',$rtpunkte);
				$prestudent->punkte = str_replace(',','.',$prestudent->rt_punkte1 + $prestudent->rt_punkte2);
				$prestudent->reihungstestangetreten=true;

				$prestudent->save(false);
			}
			else
			{
				$errormsg .= "<br>Sie haben keine Berechtigung zur Uebernahme der Punkte fuer $row->nachname $row->vorname";
			}
		}
		if($errormsg!='')
		{
			echo '<span class="input_error">'.$db->convert_html_chars($errormsg).'</span>';
		}
	}
}

if(isset($_POST['aufsicht']))
{

	if(!$rechte->isBerechtigt('lehre/reihungstest', null, 'su'))
	{
		die($rechte->errormsg);
	}

	$save_aufsicht = new reihungstest();

	if(isset($_POST['reihungstest_id']) && $_POST['reihungstest_id']!='')
	{
		//Reihungstest laden
		if(!$save_aufsicht->load($_POST['reihungstest_id']))
		{
			die($save_aufsicht->errormsg);
		}
		$aufsichtspersonen = $_POST['aufsicht'];

		foreach ($aufsichtspersonen AS $key=>$value)
		{
			// UID aus POST-String auslesen
			$length = (strrpos($value, ')')) - (strpos($value, '('));
			$uid = substr($value,strpos($value, '(')+1, $length-1);
			$save_aufsicht->new = false;
			$save_aufsicht->rt_id = $_POST['reihungstest_id'];
			$save_aufsicht->ort_kurzbz = $key;
			$save_aufsicht->uid = $uid;
			if (!$save_aufsicht->saveOrtReihungstest())
			{
				echo '<span class="input_error">Fehler beim Speichern der Daten: '.$db->convert_html_chars($reihungstest->errormsg).'</span>';
			}			
		}
		$reihungstest_id = $save_aufsicht->reihungstest_id;
		$stg_kz = $save_aufsicht->studiengang_kz;
	}
	$neu=false;
}
if(isset($_POST['delete_ort']))
{
	if(!$rechte->isBerechtigt('lehre/reihungstestOrt', null, 'suid'))
	{
		die($rechte->errormsg);
	}

	if(isset($_POST['reihungstest_id']) && $_POST['reihungstest_id']!='')
	{
		$delete_ort = new reihungstest();
		
		if (!$delete_ort->deleteOrtReihungstest($_POST['reihungstest_id'], $_POST['delete_ort']))
			echo '<span class="input_error">Fehler beim löschen der Raumzuordnung: '.$db->convert_html_chars($reihungstest->errormsg).'</span>';
		
		$reihungstest_id = $_POST['reihungstest_id'];
	}
	$neu=false;
}
//var_dump($_POST);

echo '<br><table width="100%"><tr><td>';


echo "<SELECT name='studiengang' onchange='window.location.href=this.value'>";
if($stg_kz==-1)
	$selected='selected';
else
	$selected='';

echo "<OPTION value='".$_SERVER['PHP_SELF']."?stg_kz=-1' $selected>Alle Studiengaenge</OPTION>";
foreach ($studiengang->result as $row)
{
	$stg_arr[$row->studiengang_kz] = $row->kuerzel;
	if($stg_kz=='')
		$stg_kz=$row->studiengang_kz;
	if($row->studiengang_kz==$stg_kz)
		$selected='selected';
	else
		$selected='';

	echo "<OPTION value='".$_SERVER['PHP_SELF']."?stg_kz=$row->studiengang_kz' $selected>".$db->convert_html_chars($row->kuerzel)."</OPTION>"."\n";
}
echo "</SELECT>";

//Reihungstest DropDown
$reihungstest = new reihungstest();
if($stg_kz==-1)
	$reihungstest->getAll(date('Y').'-01-01'); //Alle Reihungstests ab diesem Jahr laden
else
	$reihungstest->getReihungstest($stg_kz,'datum DESC,uhrzeit DESC');

echo "<SELECT name='reihungstest' id='reihungstest' onchange='window.location.href=this.value'>";
foreach ($reihungstest->result as $row)
{
	//if($reihungstest_id=='')
	//	$reihungstest_id=$row->reihungstest_id;
	if($row->reihungstest_id==$reihungstest_id)
		$selected='selected';
	elseif ($selected=='' && $reihungstest_id=='' && $row->datum==date('Y-m-d'))
		$selected='selected';
	else
		$selected='';

	echo '<OPTION value="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&reihungstest_id='.$row->reihungstest_id.'" '.$selected.'>'.$db->convert_html_chars($row->datum.' '.$datum_obj->formatDatum($row->uhrzeit,'H:i').' '.$studiengang->kuerzel_arr[$row->studiengang_kz].' '.$row->ort_kurzbz.' '.$row->anmerkung).'</OPTION>';
	echo "\n";
}
echo '</SELECT>';
echo "<INPUT type='button' value='Anzeigen' onclick='window.location.href=document.getElementById(\"reihungstest\").value;'>";
echo "</td>";
echo "<td align='right'>";
if($rechte->isBerechtigt('basis/testtool', null, 'suid'))
{
	echo '<a href="reihungstest_administration.php">Administration</a><br>';
}
echo '<a href="../../cis/testtool/admin/auswertung.php?'.($reihungstest_id!=''?"reihungstest=$reihungstest_id":'').'" target="_blank">Auswertung</a>';
echo "</td></tr></table><br>";

if($reihungstest_id=='')
	$neu=true;
$reihungstest = new reihungstest();

if(!$neu)
{
	if(!$reihungstest->load($reihungstest_id))
		die('Reihungstest existiert nicht');
}
else
{
	if($stg_kz!=-1 && $stg_kz!='')
		$reihungstest->studiengang_kz = $stg_kz;
	$reihungstest_id='';
	$reihungstest->datum = date('Y-m-d');
	$reihungstest->uhrzeit = date('H:i:s');
	$reihungstest->anmeldefrist = date('Y-m-d', time() - 60 * 60 * 24);
}
//Formular zum Bearbeiten des Reihungstests
?>
<input type='button' value='Neuen Termin anlegen' onclick='window.location.href="<?php echo $_SERVER['PHP_SELF'] ?>?stg_kz=<?php echo $stg_kz ?>&neu=true"' >
<hr>
<form id='rt_form' method='POST' action='<?php echo $_SERVER['PHP_SELF'] ?>'>
<input type='hidden' value='<?php echo $reihungstest->reihungstest_id ?>' name='reihungstest_id' />

	<table>
		<tr>
			<td>Studiengang</td>
			<td>
				<select name='studiengang_kz'>
				<?php if($reihungstest->studiengang_kz)
						$selected = '';
					else
						$selected = 'selected'; ?>

				<option value='' <?php echo $selected ?>>-- keine Auswahl --</option>
					<?php foreach ($studiengang->result as $row)
					{
						if($row->studiengang_kz==$reihungstest->studiengang_kz)
							$selected = 'selected';
						else
							$selected = ''; ?>

						<option value="<?php echo $row->studiengang_kz ?>" <?php echo $selected ?>><?php echo $db->convert_html_chars($row->kuerzel).' ('.$db->convert_html_chars($row->bezeichnung) . ')' ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Stufe</td>
			<td>
				<select name='stufe'>
				<option value=''>-- keine Auswahl --</option>
					<?php for($i=1; $i<=3; $i++)
					{
						if($reihungstest->stufe==$i)
							$selected = 'selected="selected"';
						else
							$selected = ''; ?>

						<option value="<?php echo $i ?>" <?php echo $selected ?>><?php echo $i ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<td style="vertical-align: top">Ort</td>
			<?php 
			$arbeitsplaetze_sum = 0;
			if(!$neu)
			{
				$orte = new Reihungstest();
				$orte->getOrteReihungstest($reihungstest->reihungstest_id);
				echo '<td><table>';
				if ($rechte->isBerechtigt('lehre/reihungstestOrt', null, 'sui'))
				{
					echo '<tr><td colspan="2"><input id="ort" type="text" name="ort_kurzbz" placeholder="Ort eingeben" value="">';
					echo '&nbsp;<button type="submit" name="speichern"><img src="../../skin/images/list-add.png" alt="Ort hinzufügen" height="13px"></button>';
					echo '</td></tr>';
				}
				foreach ($orte->result AS $row)
				{
					//echo '<tr><td>&nbsp;</td><td>';
					//echo '<br>';
					$ort = new ort();
					$ort->load($row->ort_kurzbz);
					$arbeitsplaetze = $ort->arbeitsplaetze - (ceil(($ort->arbeitsplaetze/100)*REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND));
					$person = new Person();
					$person->getPersonFromBenutzer($row->uid);
					if ($row->uid != '')
						$anzeigename = $person->vorname.' '.$person->nachname.' ('.$row->uid.')';
					else 
						$anzeigename = '';
					//echo '<div style="border: 1px solid grey; padding: 2px; margin: 2px; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; width: content;">'.$row->ort_kurzbz;
					echo '<tr><td>'.$row->ort_kurzbz.' ('.$arbeitsplaetze.' Personen)</td><td>';
					//echo ' <input type="hidden" id="aufsicht_'.$row->ort_kurzbz.'" name="aufsicht['.$row->ort_kurzbz.']" value="'.$db->convert_html_chars($row->uid).'">';
					echo ' <input type="text" id="aufsicht_'.$row->ort_kurzbz.'" class="aufsicht_uid" name="aufsicht['.$row->ort_kurzbz.']" value="'.$anzeigename.'" placeholder="Aufsichtsperson" size="32">';
					if ($rechte->isBerechtigt('lehre/reihungstestOrt', null, 'suid'))
						echo '<button type="submit" name="delete_ort" value="'.$row->ort_kurzbz.'"><img src="../../skin/images/delete_x.png" alt="Ort hinzufügen" height="13px"></button>';
					//echo '</div></td></tr>';
					echo '</td></tr>';
					$arbeitsplaetze_sum = $arbeitsplaetze_sum + $arbeitsplaetze;
				}
				echo '</table></td>';
				//echo '<td><input id="ort" type="text" name="ort_kurzbz" placeholder="Ort eingeben" value="'.$db->convert_html_chars($reihungstest->ort_kurzbz).'"></td>';
			}
			else 
				echo '<td>Nach dem Anlegen eines Termins, können Sie Räume zuordnen</td>';
			?>
		</tr>
		<tr>
			<td>Anmerkung</td>
			<td><input type="text" size="64" maxlength="64" name="anmerkung" value="<?php echo $db->convert_html_chars($reihungstest->anmerkung) ?>"> (max. 64 Zeichen)</td>
		</tr>
		<tr>
			<td>Datum</td>
			<td><input class="datepicker_datum" type="text" name="datum" value="<?php echo $datum_obj->convertISODate($reihungstest->datum) ?>"></td>
		</tr>
		<tr>
			<td>Uhrzeit</td>
			<td><input type="text" class="timepicker" name="uhrzeit" value="<?php echo $db->convert_html_chars($datum_obj->formatDatum($reihungstest->uhrzeit,'H:i')) ?>" placeholder="HH:MM"> (Format: HH:MM)</td>
		</tr>
		<tr>
			<td>Anmeldefrist</td>
			<td><input class="datepicker_datum" type="text" name="anmeldefrist" value="<?php echo $datum_obj->convertISODate($reihungstest->anmeldefrist) ?>"></td>
		</tr>
		<tr>
			<td>Max TeilnehmerInnen</td>
			<td>
				<input type="number" name="max_teilnehmer" id="max_teilnehmer" value="<?php echo ($reihungstest->max_teilnehmer!=''?$reihungstest->max_teilnehmer:'') ?>">
				(optional; <?php echo $arbeitsplaetze_sum; ?> laut Raumkapazität)
			</td>
		</tr>
		<tr>
			<td>Öffentlich</td>
			<td>
				<input type="hidden" name="oeffentlich" value="0">
				<input type="checkbox" name="oeffentlich" value="1" <?php echo $reihungstest->oeffentlich ? 'checked="checked"' : '' ?>>
				(Für Bewerber sichtbar/auswählbar)
			</td>
		</tr>
		<tr>
			<td>Freigeschaltet</td>
			<td>
				<input type="checkbox" name="freigeschaltet"<?php echo $reihungstest->freigeschaltet ? 'checked="checked"' : '' ?>>
				(Kurz vor Testbeginn aktivieren)
			</td>
		</tr>
		<!--<tr>
			<td>Plätze</td>
			<td>
				<?php echo $arbeitsplaetze_sum ?> (inkl. Schwund)
			</td>
		</tr>-->
		<tr>
			<td>&nbsp;</td>
		</tr>
		<?php if(!$neu)
				$val = 'Änderung Speichern';
			else
				$val = 'Neu anlegen'; ?>
		<tr>
			<td></td>
			<td><button type="submit" name="speichern"><?php echo $val ?></button></td>
		</tr>
	</table>
</form>

<hr>
<?php
if($reihungstest_id!='')
{
	echo '<table width="100%"><tr><td>';
	echo "<a href='".$_SERVER['PHP_SELF']."?reihungstest_id=$reihungstest_id&excel=true'><img src='../../skin/images/xls_icon.png' alt='Excel Icon'> Excel Export</a>";
	echo '</td><td align="right">';
	echo "<a href='".$_SERVER['PHP_SELF']."?reihungstest_id=$reihungstest_id&type=saveallrtpunkte'>alle Punkte ins FAS &uuml;bertragen</a>";
	echo '</td></tr></table>';

	//Liste der Interessenten die zum Reihungstest angemeldet sind
	$qry = "SELECT 
 			prestudent_id,
 			person_id,
 			vorname,
 			nachname,
 			ort_kurzbz,
 			studiengang_kz,
 			gebdatum,
 			rt_punkte1
				,(
					SELECT kontakt
					FROM tbl_kontakt
					WHERE kontakttyp = 'email'
						AND person_id = tbl_prestudent.person_id
						AND zustellung = true LIMIT 1
					) AS email
				,(
					SELECT ausbildungssemester
					FROM public.tbl_prestudentstatus
					WHERE prestudent_id = tbl_prestudent.prestudent_id
						AND datum = (
							SELECT MAX(datum)
							FROM public.tbl_prestudentstatus
							WHERE prestudent_id = tbl_prestudent.prestudent_id
								AND status_kurzbz = 'Interessent'
							) LIMIT 1
					) AS ausbildungssemester
 				,(
					SELECT orgform_kurzbz
					FROM public.tbl_prestudentstatus
					WHERE prestudent_id = tbl_prestudent.prestudent_id
						AND datum = (
							SELECT MAX(datum)
							FROM public.tbl_prestudentstatus
							WHERE prestudent_id = tbl_prestudent.prestudent_id
								AND status_kurzbz = 'Interessent'
							) LIMIT 1
					) AS orgform_kurzbz
			FROM public.tbl_prestudent
			JOIN public.tbl_person USING (person_id)
 			LEFT JOIN public.tbl_rt_person USING (person_id)
			WHERE reihungstest_id = ".$db->db_add_param($reihungstest_id, FHC_INTEGER)."
			ORDER BY ort_kurzbz NULLS FIRST,nachname,vorname ";

	$mailto = '';
	$result_arr = array();
	if($result = $db->db_query($qry))
		while($row = $db->db_fetch_object($result))
			$result_arr[] = $row;
	//var_dump($result_arr);
	echo '<table><tr>';

	echo '<span style="font-size: 9pt">Anzahl: '.$db->db_num_rows($result).'/'.($reihungstest->max_teilnehmer!=''?$reihungstest->max_teilnehmer:$arbeitsplaetze_sum).'</span>';
	$pruefling = new pruefling();
	
	$orte = new Reihungstest();
	$orte->getOrteReihungstest($reihungstest->reihungstest_id);
	$cnt = 0;
	foreach ($orte->result AS $ort)
	{
		$cnt++;
		echo '<td style="vertical-align: top">';
		echo '<div align="center"><b>'.$ort->ort_kurzbz.'</b></div>';
		echo '<form id="raumzuteilung_form['.$ort->ort_kurzbz.']" method="POST" action="'.$_SERVER['PHP_SELF'].'">';
		echo '<input type="hidden" value="'.$reihungstest->reihungstest_id.'" name="reihungstest_id">';
		echo '<table class="tablesorter" id="t'.$cnt.'">
				<thead>
				<tr class="liste">
					<th>&nbsp;</th>
					<th title="PrestudentID">ID</th>
					<th>Vorname</th>
					<th>Nachname</th>
					<th>Studiengang</th>
 					<th>OrgForm</th>
					<th>Einstiegssemester</th>
					<th>Geburtsdatum</th>
					<th>EMail</th>
					<th>bereits absolvierte Verfahren</th>
					<th>Ergebnis</th>
					<th>FAS</th>
				</tr>
				</thead>
				<tbody>';

		foreach ($result_arr AS $row)
		{
			if(defined('FAS_REIHUNGSTEST_PUNKTE') && FAS_REIHUNGSTEST_PUNKTE)
				$rtergebnis = $pruefling->getReihungstestErgebnis($row->prestudent_id,true);
			else
				$rtergebnis = $pruefling->getReihungstestErgebnis($row->prestudent_id);
			$prestudent = new prestudent();
			$prestudent->getPrestudenten($row->person_id);
			$rt_in_anderen_stg='';
			foreach($prestudent->result as $item)
			{
				if($item->prestudent_id!=$row->prestudent_id)
				{
					if(defined('FAS_REIHUNGSTEST_PUNKTE') && FAS_REIHUNGSTEST_PUNKTE)
						$erg = $pruefling->getReihungstestErgebnis($item->prestudent_id, true);
					else
						$erg = $pruefling->getReihungstestErgebnis($item->prestudent_id);
					if($erg!=0)
					{
						$rt_in_anderen_stg.=number_format($erg,2).' Punkte im Studiengang '.$studiengang->kuerzel_arr[$item->studiengang_kz].'<br>';
					}

				}
			}
			if ($row->ort_kurzbz == $ort->ort_kurzbz)
			{
				echo '
					<tr>
						<td><input type="checkbox" id="checkbox_'.$row->person_id.'" name="checkbox['.$row->person_id.']"></td>
						<td>'.$db->convert_html_chars($row->prestudent_id).'</td>
						<td>'.$db->convert_html_chars($row->vorname).'</td>
						<td>'.$db->convert_html_chars($row->nachname).'</td>
						<td>'.$db->convert_html_chars($stg_arr[$row->studiengang_kz]).'</td>
						<td>'.$db->convert_html_chars($row->orgform_kurzbz).'</td>
						<td>'.$db->convert_html_chars($row->ausbildungssemester).'</td>
						<td>'.$db->convert_html_chars($datum_obj->convertISODate($row->gebdatum)).'</td>
						<td align="center"><a href="mailto:'.$db->convert_html_chars($row->email).'"><img src="../../skin/images/button_mail.gif" name="mail"></a></td>
						<td>'.$rt_in_anderen_stg.'</td>
						<td align="right">'.($rtergebnis==0?'-':number_format($rtergebnis,2,'.','')).'</td>
						<td align="right">'.($rtergebnis!=0 && $row->rt_punkte1==''?'<a href="'.$_SERVER['PHP_SELF'].'?reihungstest_id='.$reihungstest_id.'&stg_kz='.$stg_kz.'&type=savertpunkte&prestudent_id='.$row->prestudent_id.'&rtpunkte='.$rtergebnis.'" >&uuml;bertragen</a>':$row->rt_punkte1).'</td>
					</tr>';
	
				$mailto.= ($mailto!=''?',':'').$row->email;
			}
		}
		echo '</tbody></table>';
		
		echo '<select name="raumzuteilung">';
		echo '<option value="">-- keine Auswahl --</option>';
		
		foreach ($orte->result AS $item)
		{
			if($item->ort_kurzbz==$reihungstest->studiengang_kz)
				$selected = 'selected="selected"';
			else
				$selected = '';
			echo '<option value="'.$item->ort_kurzbz.'" '.$selected.'>'.$item->ort_kurzbz.'</option>';
		}
		echo '</select>';
		echo '<button type="submit" name="raumzuteilung_speichern">Speichern</button>';
		echo '</form>';
		echo '</td>';
	}
	
	
	
	echo '</tr></table>';
	echo "<span style='font-size: 9pt'><a href='mailto:?bcc=$mailto'>Mail an alle senden</a></span>";
} ?>

	</body>
</html>
