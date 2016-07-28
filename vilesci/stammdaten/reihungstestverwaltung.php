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
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/studienplan.class.php');


define('REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND', '5');
define('REIHUNGSTEST_ERGEBNISSE_BERECHNEN', false);

if (!$db = new basis_db())
{
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
}

$stsem_akt = new studiensemester();
$stsem_akt = $stsem_akt->getakt();

$user = get_uid();
$datum_obj = new datum();
$stg_kz = (isset($_GET['stg_kz']) ? $_GET['stg_kz'] : '');
$reihungstest_id = (isset($_GET['reihungstest_id']) ? $_GET['reihungstest_id'] : '');
$studiensemester_kurzbz = (isset($_GET['studiensemester_kurzbz']) ? $_GET['studiensemester_kurzbz'] : '');
$prestudent_id = (isset($_GET['prestudent_id']) ? $_GET['prestudent_id'] : '');
$rtpunkte = (isset($_GET['rtpunkte']) ? $_GET['rtpunkte'] : '');
$neu = (isset($_GET['neu']) ? true : false);
$stg_arr = array();
$error = false;

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if ($studiensemester_kurzbz == '' && ($reihungstest_id != '' || isset($_POST['reihungstest_id'])))
{
	if ($reihungstest_id != '')
	{
		$rt = new Reihungstest();
		$rt->load($reihungstest_id);
		$studiensemester_kurzbz = $rt->studiensemester_kurzbz;
	}
	elseif (isset($_POST['reihungstest_id']))
	{
		$rt = new Reihungstest();
		$rt->load($_POST['reihungstest_id']);
		$studiensemester_kurzbz = $rt->studiensemester_kurzbz;
	}
	else
		$studiensemester_kurzbz = $stsem_akt;
}

if ($stg_kz == '' && ($reihungstest_id != '' || isset($_POST['reihungstest_id'])))
{
	if ($reihungstest_id != '')
	{
		$rt = new Reihungstest();
		$rt->load($reihungstest_id);
		$stg_kz = $rt->studiengang_kz;
	}
	elseif (isset($_POST['reihungstest_id']))
	{
		$rt = new Reihungstest();
		$rt->load($_POST['reihungstest_id']);
		$stg_kz = $rt->studiengang_kz;
	}
	else
		$stg_kz = '-1';
}

if(!$rechte->isBerechtigt('lehre/reihungstest'))
{
	die($rechte->errormsg);
}
	
$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz', false);

$studiensemester = new Studiensemester();
$studiensemester->getAll('DESC');


//Studierende als Excel Exportieren
if(isset($_GET['excel']))
{
	$reihungstest = new reihungstest();
	if($reihungstest->load($_GET['reihungstest_id']))
	{
		$qry = "SELECT 
	 			prestudent_id,
	 			person_id,
	 			vorname,
	 			nachname,
	 			ort_kurzbz,
	 			studiengang_kz,
	 			gebdatum,
	 			geschlecht,
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
				WHERE reihungstest_id = ".$db->db_add_param($reihungstest->reihungstest_id, FHC_INTEGER)."
				ORDER BY ort_kurzbz NULLS FIRST,nachname,vorname";

		// Creating a workbook
		$workbook = new Spreadsheet_Excel_Writer();
		$workbook->setVersion(8);
		// sending HTTP headers
		$workbook->send("Anwesenheitsliste_Reihungstest_".$reihungstest->datum.".xls");
		
		//Formate Definieren
		$format_bold =& $workbook->addFormat();
		$format_bold->setBold();
		
		$format_border =& $workbook->addFormat();
		$format_border->setBorder(1);
		
		if($result = $db->db_query($qry))
		{
			$ort_kurzbz = '0';
			while($row = $db->db_fetch_object($result))
			{
				if ($ort_kurzbz == '0' || $ort_kurzbz != $row->ort_kurzbz)
				{
					// Creating a worksheet
					if ($row->ort_kurzbz=='')
						$worksheet =& $workbook->addWorksheet("Ohne Raumzuteilung");
					else 
						$worksheet =& $workbook->addWorksheet("Raum ".$row->ort_kurzbz);
					$worksheet->setInputEncoding('utf-8');
					//$worksheet->setZoom (85);
					$worksheet->hideScreenGridlines();
					$worksheet->hideGridlines();
					$worksheet->setLandscape();
					$worksheet->centerHorizontally(1);
					$worksheet->fitToPages ( 1, 1);
					$worksheet->setMargins_LR (0.4);
					$worksheet->setMarginTop (0.79);
					$worksheet->setMarginBottom (0.59);
					
					
					$worksheet->write(0,0,'Anwesenheitsliste Reihungstest '.$datum_obj->convertISODate($reihungstest->datum).' '.$reihungstest->uhrzeit.' Uhr, '.$reihungstest->anmerkung.', erstellt am '.date('d.m.Y'), $format_bold);
					//Ueberschriften
					$col=0;
					$worksheet->write(2,$col,"Vorname", $format_bold);
					$maxlength[$col] = 7;
					$worksheet->write(2,++$col,"Nachname", $format_bold);
					$maxlength[$col] = 8;
					$worksheet->write(2,++$col,"Geschlecht", $format_bold);
					$maxlength[$col] = 8;
					$worksheet->write(2,++$col,"Geburtsdatum", $format_bold);
					$maxlength[$col] = 12;
					$worksheet->write(2,++$col,"Studiengang", $format_bold);
					$maxlength[$col] = 11;
					$worksheet->write(2,++$col,"Bereits absolvierte RTs", $format_bold);
					$maxlength[$col] = 18;
					$worksheet->write(2,++$col,"EMail", $format_bold);
					$maxlength[$col] = 5;
					$worksheet->write(2,++$col,"Einstiegssemester", $format_bold);
					$maxlength[$col] = 15;
					$worksheet->write(2,++$col,"Strasse", $format_bold);
					$maxlength[$col] = 6;
					$worksheet->write(2,++$col,"PLZ", $format_bold);
					$maxlength[$col] = 3;
					$worksheet->write(2,++$col,"Ort", $format_bold);
					$maxlength[$col] = 3;
					$worksheet->write(2,++$col,"Unterschrift", $format_bold);
					$maxlength[$col] = 30;
					
					$ort_kurzbz = $row->ort_kurzbz;
					$zeile=3;
				}
				
				$pruefling = new pruefling();

				$prestudent = new prestudent();
				$prestudent->getPrestudenten($row->person_id);
				$rt_in_anderen_stg='';
				$erg = '';
				if(defined('REIHUNGSTEST_ERGEBNISSE_BERECHNEN') && REIHUNGSTEST_ERGEBNISSE_BERECHNEN)
				{
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
				}
				$col=0;
				$worksheet->write($zeile,$col, $row->vorname, $format_border);
				if(strlen($row->vorname)>$maxlength[$col])
					$maxlength[$col] = mb_strlen($row->vorname);

				$worksheet->write($zeile,++$col,$row->nachname, $format_border);
				if(strlen($row->nachname)>$maxlength[$col])
					$maxlength[$col] = mb_strlen($row->nachname);
				
				$worksheet->write($zeile,++$col, $row->geschlecht, $format_border);
				if(strlen($row->geschlecht)>$maxlength[$col])
					$maxlength[$col] = mb_strlen($row->geschlecht);

				$worksheet->write($zeile,++$col,$datum_obj->convertISODate($row->gebdatum), $format_border);
				if(strlen($row->gebdatum)>$maxlength[$col])
					$maxlength[$col] = mb_strlen($row->gebdatum);

				$worksheet->write($zeile,++$col,$studiengang->kuerzel_arr[$row->studiengang_kz], $format_border);
				if(strlen($studiengang->kuerzel_arr[$row->studiengang_kz])>$maxlength[$col])
					$maxlength[$col] = mb_strlen($studiengang->kuerzel_arr[$row->studiengang_kz]);

				$worksheet->write($zeile,++$col,$rt_in_anderen_stg, $format_border);
				if(strlen($rt_in_anderen_stg)>$maxlength[$col])
					$maxlength[$col] = mb_strlen($rt_in_anderen_stg);

				$worksheet->write($zeile,++$col,$row->email, $format_border);
				if(strlen($row->email)>$maxlength[$col])
					$maxlength[$col] = mb_strlen($row->email);

				$worksheet->write($zeile,++$col,$row->ausbildungssemester, $format_border);
				if(strlen($row->ausbildungssemester)>$maxlength[$col])
					$maxlength[$col] = mb_strlen($row->ausbildungssemester);

 				$adresse = new adresse();
				$adresse->loadZustellAdresse($row->person_id);

				$worksheet->write($zeile,++$col,$adresse->strasse, $format_border);
				if(strlen($adresse->strasse)>$maxlength[$col])
					$maxlength[$col] = mb_strlen($adresse->strasse);

				$worksheet->write($zeile,++$col,$adresse->plz, $format_border);
				if(strlen($adresse->plz)>$maxlength[$col])
					$maxlength[$col] = mb_strlen($adresse->plz);

				$worksheet->write($zeile,++$col,$adresse->ort, $format_border);
				if(strlen($adresse->ort)>$maxlength[$col])
					$maxlength[$col] = mb_strlen($adresse->ort);
				
				$worksheet->write($zeile,++$col,'', $format_border);
				
				$worksheet->setRow($zeile, 35);
			
				//Die Breite der Spalten setzen
				foreach($maxlength as $col=>$breite)
					$worksheet->setColumn($col, $col, $breite+2);

				$zeile++;
			}
		}
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
		<script src="../../include/js/jquery.checkboxes-1.0.7.min.js" type="text/javascript"></script>
		
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

				if (typeof(Storage) !== 'undefined') 
				{
					if (localStorage.getItem('clm_id') != null) 
					{
						$('.clm_id').css('display', localStorage.getItem('clm_id'));
						if (localStorage.getItem('clm_id') == 'none')
							document.getElementById('clm_id').className = 'inactive';
						else
							document.getElementById('clm_id').className = 'active';
					}
					if (localStorage.getItem('clm_geschlecht') != null) 
					{
						$('.clm_geschlecht').css('display', localStorage.getItem('clm_geschlecht'));
						if (localStorage.getItem('clm_geschlecht') == 'none')
							document.getElementById('clm_geschlecht').className = 'inactive';
						else
							document.getElementById('clm_geschlecht').className = 'active';
					}
					if (localStorage.getItem('clm_studiengang') != null) 
					{
						$('.clm_studiengang').css('display', localStorage.getItem('clm_studiengang'));
						if (localStorage.getItem('clm_studiengang') == 'none')
							document.getElementById('clm_studiengang').className = 'inactive';
						else
							document.getElementById('clm_studiengang').className = 'active';
					}
					if (localStorage.getItem('clm_orgform') != null) 
					{
						$('.clm_orgform').css('display', localStorage.getItem('clm_orgform'));
						if (localStorage.getItem('clm_orgform') == 'none')
							document.getElementById('clm_orgform').className = 'inactive';
						else
							document.getElementById('clm_orgform').className = 'active';
					}
					if (localStorage.getItem('clm_einstiegssemester') != null) 
					{
						$('.clm_einstiegssemester').css('display', localStorage.getItem('clm_einstiegssemester'));
						if (localStorage.getItem('clm_einstiegssemester') == 'none')
							document.getElementById('clm_einstiegssemester').className = 'inactive';
						else
							document.getElementById('clm_einstiegssemester').className = 'active';
					}
					if (localStorage.getItem('clm_geburtsdatum') != null) 
					{
						$('.clm_geburtsdatum').css('display', localStorage.getItem('clm_geburtsdatum'));
						if (localStorage.getItem('clm_geburtsdatum') == 'none')
							document.getElementById('clm_geburtsdatum').className = 'inactive';
						else
							document.getElementById('clm_geburtsdatum').className = 'active';
					}
					if (localStorage.getItem('clm_email') != null) 
					{
						$('.clm_email').css('display', localStorage.getItem('clm_email'));
						if (localStorage.getItem('clm_email') == 'none')
							document.getElementById('clm_email').className = 'inactive';
						else
							document.getElementById('clm_email').className = 'active';
					}
					if (localStorage.getItem('clm_absolviert') != null) 
					{
						$('.clm_absolviert').css('display', localStorage.getItem('clm_absolviert'));
						if (localStorage.getItem('clm_absolviert') == 'none')
							document.getElementById('clm_absolviert').className = 'inactive';
						else
							document.getElementById('clm_absolviert').className = 'active';
					}
					if (localStorage.getItem('clm_ergebnis') != null) 
					{
						$('.clm_ergebnis').css('display', localStorage.getItem('clm_ergebnis'));
						if (localStorage.getItem('clm_ergebnis') == 'none')
							document.getElementById('clm_ergebnis').className = 'inactive';
						else
							document.getElementById('clm_ergebnis').className = 'active';
					}
					if (localStorage.getItem('clm_fas') != null) 
					{
						$('.clm_fas').css('display', localStorage.getItem('clm_fas'));
						if (localStorage.getItem('clm_fas') == 'none')
							document.getElementById('clm_fas').className = 'inactive';
						else
							document.getElementById('clm_fas').className = 'active';
					}
				} 
				else 
				{
					alert('Local Storage nicht unterstuetzt');
				}

				$(".tablesorter").each(function(i,v)
				{
					$("#"+v.id).tablesorter(
					{
						widgets: ["zebra"],
						sortList: [[2,0],[3,0]],
						headers: {0: { sorter: false}}
					});
					
					$("#toggle_"+v.id).on('click', function(e) {
						$("#"+v.id).checkboxes('toggle');
						e.preventDefault();
					});

					$("#uncheck_"+v.id).on('click', function(e) {
						$("#"+v.id).checkboxes('uncheck');
						e.preventDefault();
					});
					
					$("#"+v.id).checkboxes('range', true);
				});
			});

			function hideColumn(column)
			{
				if ($('.'+column).css('display') == 'table-cell')
				{
					$('.'+column).css('display', 'none');
					localStorage.setItem(column, 'none');
					if (localStorage.getItem(column) == 'none')
						document.getElementById(column).className = 'inactive';
					else
						document.getElementById(column).className = 'active';
				}
				else
				{
					$('.'+column).css('display', 'table-cell');
					localStorage.setItem(column, 'table-cell');
					if (localStorage.getItem(column) == 'none')
						document.getElementById(column).className = 'inactive';
					else
						document.getElementById(column).className = 'active';
				}
			}
		</script>
		<style type="text/css">
		.active
		{
			cursor: pointer; 
			color: #000000; 
			margin-right: 5px; 
			text-decoration: none; 
			border-radius: 3px; 
			-webkit-border-radius: 3px; 
			-moz-border-radius: 3px; 
			background-color: #8dbdd8; 
			border-top: 3px solid #8dbdd8; 
			border-bottom: 3px solid #8dbdd8; 
			border-right: 8px solid #8dbdd8; 
			border-left: 8px solid #8dbdd8; 
			display: inline-block;
		}
		.inactive
		{
			cursor: pointer; 
			color: #000000; 
			margin-right: 5px; 
			text-decoration: none; 
			border-radius: 3px; 
			-webkit-border-radius: 3px; 
			-moz-border-radius: 3px; 
			background-color: #DCE4EF; 
			border-top: 3px solid #DCE4EF; 
			border-bottom: 3px solid #DCE4EF; 
			border-right: 8px solid #DCE4EF; 
			border-left: 8px solid #DCE4EF; 
			display: inline-block;
		}
		.buttongreen, a.buttongreen
		{
			cursor: pointer; 
			color: #FFFFFF; 
			margin: 0 5px 5px 0; 
			text-decoration: none; 
			border-radius: 3px; 
			-webkit-border-radius: 3px; 
			-moz-border-radius: 3px; 
			background-color: #5cb85c; 
			border-top: 3px solid #5cb85c; 
			border-bottom: 3px solid #5cb85c; 
			border-right: 8px solid #5cb85c; 
			border-left: 8px solid #5cb85c; 
			display: inline-block;
			vertical-align: middle;
		}
		.buttonorange, a.buttonorange
		{
			cursor: pointer; 
			color: #FFFFFF; 
			margin: 0 5px 5px 0; 
			text-decoration: none; 
			border-radius: 3px; 
			-webkit-border-radius: 3px; 
			-moz-border-radius: 3px; 
			background-color: #EC971F; 
			border-top: 3px solid #EC971F; 
			border-bottom: 3px solid #EC971F; 
			border-right: 8px solid #EC971F; 
			border-left: 8px solid #EC971F; 
			display: inline-block;
			vertical-align: middle;
		}

		</style>
	</head>
	<body class="Background_main">
		<h2>Reihungstest - Verwaltung</h2>
<?php

// Speichern eines Termines
if(isset($_POST['speichern']) || isset($_POST['kopieren']))
{

	if(!$rechte->isBerechtigt('lehre/reihungstest', null, 'sui'))
	{
		die($rechte->errormsg);
	}

	$reihungstest = new reihungstest();

	if(isset($_POST['reihungstest_id']) && $_POST['reihungstest_id']!='' && !isset($_POST['kopieren']))
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
		if (isset($_POST['kopieren']))
		{
			
			$reihungstest->freigeschaltet = false;
			$reihungstest->max_teilnehmer = '';
			$reihungstest->oeffentlich = false;
			$reihungstest->stufe = filter_input(INPUT_POST, 'stufe', FILTER_VALIDATE_INT);
			$reihungstest->anmeldefrist = $datum_obj->formatDatum($_POST['anmeldefrist']);
			$reihungstest->updateamum = date('Y-m-d H:i:s');
			$reihungstest->updatevon = $user;
		}
		else
		{
			$reihungstest->freigeschaltet = isset($_POST['freigeschaltet']);
			$reihungstest->max_teilnehmer = filter_input(INPUT_POST, 'max_teilnehmer', FILTER_VALIDATE_INT);
			$reihungstest->oeffentlich = filter_input(INPUT_POST, 'oeffentlich', FILTER_VALIDATE_BOOLEAN);
			$reihungstest->stufe = filter_input(INPUT_POST, 'stufe', FILTER_VALIDATE_INT);
			$reihungstest->anmeldefrist = $datum_obj->formatDatum($_POST['anmeldefrist']);
			$reihungstest->updateamum = date('Y-m-d H:i:s');
			$reihungstest->updatevon = $user;
		}
		$reihungstest->studiengang_kz = $_POST['studiengang_kz'];
		//$reihungstest->ort_kurzbz = $_POST['ort_kurzbz'];
		$reihungstest->studiensemester_kurzbz = filter_input(INPUT_POST, 'studiensemester_kurzbz');
		$reihungstest->anmerkung = $_POST['anmerkung'];
		$reihungstest->datum = $datum_obj->formatDatum($_POST['datum']);
		$reihungstest->uhrzeit = $_POST['uhrzeit'];
		

		if($reihungstest->save())
		{
			if (isset($_POST['ort_kurzbz']) && $_POST['ort_kurzbz']!='')
			{
				$ort = new ort();
				
				if (!$ort->load($_POST['ort_kurzbz']))
					echo '<span class="input_error">Die Bezeichnung des Ortes ist ungueltig oder wurde nicht gefunden</span>';
				else 
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
			}
			$reihungstest_id = $reihungstest->reihungstest_id;
			$stg_kz = $reihungstest->studiengang_kz;
			$studiensemester_kurzbz = $reihungstest->studiensemester_kurzbz;
		}
		else
		{
			echo '<span class="input_error">Fehler beim Speichern der Daten: '.$db->convert_html_chars($reihungstest->errormsg).'</span>';
		}
	}
	$neu=false;
}

if ($reihungstest_id != '' || isset($_POST['reihungstest_id']))
{
	$orte = new Reihungstest();
	$orte->getOrteReihungstest($reihungstest_id != ''?$reihungstest_id:$_POST['reihungstest_id']);
	$orte_array = array();
	foreach ($orte->result AS $row)
	{
		// Wenn Arbeitsplaetze in DB gepflegt sind, Schwund herausrechnen (wenn gesetzt) sonst max_person verwenden und Schwund herausrechnen (wenn gesetzt)
		$raum = new Ort();
		$raum->load($row->ort_kurzbz);
		if ($raum->arbeitsplaetze != '')
		{
			if(defined('REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND') || REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND > 0)
				$orte_array[$row->ort_kurzbz] = $raum->arbeitsplaetze - ceil(($raum->arbeitsplaetze/100)*REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND);
				else
					$orte_array[$row->ort_kurzbz] = $raum->arbeitsplaetze;
		}
		else
		{
			if(defined('REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND') || REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND > 0)
				$orte_array[$row->ort_kurzbz] = $raum->max_person - ceil(($raum->max_person/100)*REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND);
				else
					$orte_array[$row->ort_kurzbz] = $raum->max_person;
		}
	}
	$arbeitsplaetze_gesamt = array_sum($orte_array);
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
		if (isset($_POST['checkbox']))
		{
			$person_ids = $_POST['checkbox'];
	
			foreach ($person_ids AS $key=>$value)
			{
				//Pruefen ob Person schon Zuteilung in tbl_rt_person hat @todo: Kann weggelassen werden, wenn sichergestellt ist, dass das schon uebers FAS passiert
				$checkperson = new Reihungstest();
				$checkperson->getReihungstestPerson($key);
				if ($checkperson->result)
					$raumzuteilung->new = false;
				else 
				{
					$raumzuteilung->new = true;
				}
				
				$raumzuteilung->anmeldedatum = date('Y-m-d H:i:s'); // @todo: Anmeldedatum, teilgenommen und Punkte nicht immer auf defaultwerte setzen sondern nur, wenn tatsächlich neu
				$raumzuteilung->teilgenommen = false;
				$raumzuteilung->punkte = 0;
				
				$raumzuteilung->rt_id = $_POST['reihungstest_id'];
				$raumzuteilung->rt_id_old = $_POST['reihungstest_id'];
				$raumzuteilung->person_id = $key;
				$raumzuteilung->ort_kurzbz = $_POST['raumzuteilung'];
				
				if (!$raumzuteilung->savePersonReihungstest())
				{
					echo '<span class="input_error">Fehler beim Speichern der Daten: '.$db->convert_html_chars($reihungstest->errormsg).'</span>';
				}
			}
		}
		$reihungstest_id = $_POST['reihungstest_id'];
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

// Verteilt alle BewerberInnen gleichmaessig auf die Raeume
if(isset($_GET['type']) && $_GET['type']=='verteilen')
{
	if(!$rechte->isBerechtigt('lehre/reihungstest', null, 'sui'))
	{
		die($rechte->errormsg);
	}
	
	if($reihungstest_id!='')
	{
		$errormsg='';
		$qry = "SELECT 
				person_id,
				vorname,
				nachname,
				ort_kurzbz
				FROM public.tbl_prestudent
				JOIN public.tbl_person USING (person_id)
				LEFT JOIN public.tbl_rt_person USING (person_id)
				WHERE reihungstest_id = ".$db->db_add_param($reihungstest_id, FHC_INTEGER)."
				/*AND tbl_rt_person.ort_kurzbz IS NULL*/
				ORDER BY nachname,vorname ";
		
		$raumzuteilung = new reihungstest();
		if($result = $db->db_query($qry))
		{
			$anz_personen = $db->db_num_rows($result);
			
			$multiplikator = $anz_personen/$arbeitsplaetze_gesamt;
			foreach ($orte->result AS $ort)
			{
				$counter = 0;
				
				$anz_zugeteilte = new Reihungstest();
				$anz_zugeteilte->getPersonReihungstestOrt($reihungstest_id, $ort->ort_kurzbz);
				$anz_zugeteilte = count($anz_zugeteilte->result);
				
				$anteil = round(($orte_array[$ort->ort_kurzbz] * $multiplikator))-$anz_zugeteilte;
				
				//if ($orte_array[$ort->ort_kurzbz] == 0 || ($orte_array[$ort->ort_kurzbz]-$anz_zugeteilte)<=0)
				if ($orte_array[$ort->ort_kurzbz] == 0 || ($anteil - $anz_zugeteilte)<=0)
					continue;

				while($row = $db->db_fetch_object($result))
				{
					//Nur Personen ohne Raumzuteilung verteilen
					if ($row->ort_kurzbz == '')
					{
						//Pruefen ob Person schon Zuteilung in tbl_rt_person hat @todo: Kann weggelassen werden, wenn sichergestellt ist, dass das schon uebers FAS passiert
						$checkperson = new Reihungstest();
						$checkperson->getReihungstestPerson($row->person_id);
						if ($checkperson->result)
							$raumzuteilung->new = false;
						else 
						{
							$raumzuteilung->new = true;
						}
						$raumzuteilung->anmeldedatum = date('Y-m-d H:i:s');
						$raumzuteilung->teilgenommen = false;
						$raumzuteilung->punkte = 0;
						
						$raumzuteilung->rt_id = $reihungstest_id;
						$raumzuteilung->rt_id_old = $reihungstest_id;
						$raumzuteilung->person_id = $row->person_id;
						$raumzuteilung->ort_kurzbz = $ort->ort_kurzbz;
						if (!$raumzuteilung->savePersonReihungstest())
						{
							echo '<span class="input_error">Fehler beim Speichern der Daten: '.$db->convert_html_chars($raumzuteilung->errormsg).'</span>';
						}
						$counter++;
						
						//Wenn 0 Arbeitsplaetze vorhanden sind oder die max. Arbeitsplatzanzahl erreicht ist
						if ($orte_array[$ort->ort_kurzbz] == 0 || ($anteil - $counter)<=0)
							break;
						
						/*if ($counter==$pers_pro_raum || $counter==$arbeitsplaetze)
							break;*/
					}
				}
			}
		}
		
	}
	$neu=false;
}

// Fuellt die Raeume aufsteigend mit BewerberInnen an
if(isset($_GET['type']) && $_GET['type']=='auffuellen')
{
	if(!$rechte->isBerechtigt('lehre/reihungstest', null, 'sui'))
	{
		die($rechte->errormsg);
	}
	
	if($reihungstest_id!='')
	{
		$orte = new Reihungstest();
		$orte->getOrteReihungstest($reihungstest_id);
	
		$errormsg='';
		$qry = "SELECT 
				person_id,
				vorname,
				nachname,
				ort_kurzbz
				FROM public.tbl_prestudent
				JOIN public.tbl_person USING (person_id)
				LEFT JOIN public.tbl_rt_person USING (person_id)
				WHERE reihungstest_id = ".$db->db_add_param($reihungstest_id, FHC_INTEGER)."
				AND tbl_rt_person.ort_kurzbz IS NULL
				ORDER BY nachname,vorname ";
		
		$raumzuteilung = new reihungstest();
		if($result = $db->db_query($qry))
		{
			//$anz_personen = $db->db_num_rows($result);
			//$pers_pro_raum = ceil($anz_personen/$anz_orte);
			foreach ($orte->result AS $ort)
			{
				$counter = 0;
				
				$anz_zugeteilte = new Reihungstest();
				$anz_zugeteilte->getPersonReihungstestOrt($reihungstest_id, $ort->ort_kurzbz);
				$anz_zugeteilte = count($anz_zugeteilte->result);				
				
				if ($orte_array[$ort->ort_kurzbz] == 0 || ($orte_array[$ort->ort_kurzbz]-$anz_zugeteilte)<=0)
					continue;
				
				while($row = $db->db_fetch_object($result))
				{
					//Pruefen ob Person schon Zuteilung in tbl_rt_person hat @todo: Kann weggelassen werden, wenn sichergestellt ist, dass das schon uebers FAS passiert
					$checkperson = new Reihungstest();
					$checkperson->getReihungstestPerson($row->person_id);
					if ($checkperson->result)
						$raumzuteilung->new = false;
					else 
					{
						$raumzuteilung->new = true;
					}
					$raumzuteilung->anmeldedatum = date('Y-m-d H:i:s');
					$raumzuteilung->teilgenommen = false;
					$raumzuteilung->punkte = 0;
					
					$raumzuteilung->rt_id = $reihungstest_id;
					$raumzuteilung->rt_id_old = $reihungstest_id;
					$raumzuteilung->person_id = $row->person_id;
					$raumzuteilung->ort_kurzbz = $ort->ort_kurzbz;
					if (!$raumzuteilung->savePersonReihungstest())
					{
						echo '<span class="input_error">Fehler beim Speichern der Daten: '.$db->convert_html_chars($raumzuteilung->errormsg).'</span>';
					}
					$counter++;
					
					//Wenn 0 Arbeitsplaetze vorhanden sind oder die max. Arbeitsplatzanzahl erreicht ist
					if ($orte_array[$ort->ort_kurzbz] == 0 || ($orte_array[$ort->ort_kurzbz]-($anz_zugeteilte+$counter))<=0)
						break;
				}
			}
		}
		
	}
	$neu=false;
}

if(isset($_POST['aufsicht']) && $_POST['aufsicht']!='' && !isset($_POST['kopieren']))
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
			
			$benutzer = new benutzer();			
			if ($uid!='' && !$benutzer->load($uid))
				echo '<span class="input_error">Die UID '.$value.' konnte nicht gefunden werden</span>';
			else 
			{
				$save_aufsicht->new = false;
				$save_aufsicht->rt_id = $_POST['reihungstest_id'];
				$save_aufsicht->ort_kurzbz = $key;
				$save_aufsicht->uid = $uid;
				if (!$save_aufsicht->saveOrtReihungstest())
				{
					echo '<span class="input_error">Fehler beim Speichern der Daten: '.$db->convert_html_chars($reihungstest->errormsg).'</span>';
				}
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
		$delete_ort->getPersonReihungstestOrt($_POST['reihungstest_id'], $_POST['delete_ort']);
		
		if (count($delete_ort->result) == 0)
		{
			if (!$delete_ort->deleteOrtReihungstest($_POST['reihungstest_id'], $_POST['delete_ort']))
				echo '<span class="input_error">Fehler beim löschen der Raumzuordnung: '.$db->convert_html_chars($reihungstest->errormsg).'</span>';
		}
		else 
			echo '<span class="input_error">Dem Raum '.$_POST['delete_ort'].' sind noch '.count($delete_ort->result).' Personen zugeteilt. Bitte entfernen Sie zuerst diese Zuteilungen</span>';
		
		
		
		$reihungstest_id = $_POST['reihungstest_id'];
		$studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
	}
	$neu=false;
}
//var_dump($_POST);

echo '<table width="100%"><tr><td>';

// Studiengang DropDown
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

	echo "<OPTION value='".$_SERVER['PHP_SELF']."?stg_kz=$row->studiengang_kz&studiensemester_kurzbz=$studiensemester_kurzbz' $selected>".$db->convert_html_chars($row->kuerzel)."</OPTION>"."\n";
}
echo "</SELECT>";

// Studiensemester DropDown
echo "<SELECT name='studiensemester' onchange='window.location.href=this.value'>";
/*if($stg_kz==-1)
	$selected='selected';
else
	$selected='';*/

echo "<OPTION value='".$_SERVER['PHP_SELF']."?studiensemester_kurzbz='>Alle Studiensemester</OPTION>";
foreach ($studiensemester->studiensemester as $row)
{
	if($row->studiensemester_kurzbz == $studiensemester_kurzbz)
		$selected='selected';
	else
		$selected='';

	echo '<OPTION value="'.$_SERVER['PHP_SELF'].'?studiensemester_kurzbz='.$row->studiensemester_kurzbz.'&stg_kz='.$stg_kz.'&reihungstest_id='.$reihungstest_id.'" '.$selected.'>'.$db->convert_html_chars($row->studiensemester_kurzbz).'</OPTION>'.'\n';
}
echo "</SELECT>";

//Reihungstest DropDown
$reihungstest = new reihungstest();
if($stg_kz==-1)
	$reihungstest->getAll(date('Y').'-01-01'); //Alle Reihungstests ab diesem Jahr laden
else
	$reihungstest->getReihungstest($stg_kz,'datum DESC,uhrzeit DESC',$studiensemester_kurzbz);

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

	echo '<OPTION value="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&reihungstest_id='.$row->reihungstest_id.'&studiensemester_kurzbz='.$studiensemester_kurzbz.'" '.$selected.'>'.$db->convert_html_chars($row->datum.' '.$datum_obj->formatDatum($row->uhrzeit,'H:i').' '.$studiengang->kuerzel_arr[$row->studiengang_kz].' '.$row->ort_kurzbz.' '.$row->anmerkung).'</OPTION>';
	echo "\n";
}
echo '</SELECT>';
echo "<INPUT type='button' value='Anzeigen' onclick='window.location.href=document.getElementById(\"reihungstest\").value;'>";
echo '&nbsp;&nbsp;<input type="button" value="Neuen Termin anlegen" onclick="window.location.href=\''.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&neu=true\'" >';
echo "</td></tr></table>";

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
<hr>
<form id='rt_form' method='POST' action='<?php echo $_SERVER['PHP_SELF'] ?>'>
<input type='hidden' value='<?php echo $reihungstest->reihungstest_id ?>' name='reihungstest_id' />

	<table>
		<tr>
		<td style="vertical-align: top">
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
				&nbsp;&nbsp;Studiensemester
				<select name='studiensemester_kurzbz'>
				<option value=''>-- keine Auswahl --</option>
					<?php 
						foreach ($studiensemester->studiensemester as $row)
						{
							if($row->studiensemester_kurzbz == $studiensemester_kurzbz)
								$selected='selected';
							else
								$selected='';
						
							echo '<OPTION value="'.$row->studiensemester_kurzbz.'" '.$selected.'>'.$db->convert_html_chars($row->studiensemester_kurzbz).'</OPTION>'.'\n';
						}
					?>
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
					$person = new Person();
					$person->getPersonFromBenutzer($row->uid);
					if ($row->uid != '')
						$anzeigename = $person->vorname.' '.$person->nachname.' ('.$row->uid.')';
					else 
						$anzeigename = '';
					//echo '<div style="border: 1px solid grey; padding: 2px; margin: 2px; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; width: content;">'.$row->ort_kurzbz;
					echo '<tr><td>'.$row->ort_kurzbz.' ('.$orte_array[$row->ort_kurzbz].' Personen)</td><td>';
					//echo ' <input type="hidden" id="aufsicht_'.$row->ort_kurzbz.'" name="aufsicht['.$row->ort_kurzbz.']" value="'.$db->convert_html_chars($row->uid).'">';
					echo ' <input type="text" id="aufsicht_'.$row->ort_kurzbz.'" class="aufsicht_uid" name="aufsicht['.$row->ort_kurzbz.']" value="'.$anzeigename.'" placeholder="Aufsichtsperson" size="32">';
					if ($rechte->isBerechtigt('lehre/reihungstestOrt', null, 'suid'))
						echo '<button type="submit" name="delete_ort" value="'.$row->ort_kurzbz.'"><img src="../../skin/images/delete_x.png" alt="Ort hinzufügen" height="13px"></button>';
					//echo '</div></td></tr>';
					echo '</td></tr>';
					$arbeitsplaetze_sum = $arbeitsplaetze_sum + $orte_array[$row->ort_kurzbz];
				}
				echo '</table></td>';
				//echo '<td><input id="ort" type="text" name="ort_kurzbz" placeholder="Ort eingeben" value="'.$db->convert_html_chars($reihungstest->ort_kurzbz).'"></td>';
			}
			else 
				echo '<td>Nach dem Anlegen eines Termins, können Sie Räume zuordnen</td>';
			?>
		</tr>
		</table>
		</td>
		<td style="padding-left: 20px; vertical-align: top">
		<table>
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
			<td>
				<button type="submit" name="speichern"><?php echo $val ?></button>
				<?php if(!$neu)
					echo '<button type="submit" name="kopieren" onclick="return confirm (\'Eine Kopie dieses Tests (ohne Raumzuordnung) erstellen?\')">Kopie erstellen</button>'; ?>
			</td>
		</tr>
		</table>
		</td>
		</tr>
	</table>
</form>

<hr>
<?php
if($reihungstest_id!='')
{
//Liste der Interessenten die zum Reihungstest angemeldet sind
	$qry = "SELECT 
			prestudent_id,
			person_id,
			vorname,
			nachname,
			ort_kurzbz,
			studiengang_kz,
			gebdatum,
			geschlecht,
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
//echo $qry;
$mailto = '';
$result_arr = array();

$orte = new Reihungstest();
$orte->getOrteReihungstest($reihungstest_id);
$orte_zuteilung_array = array();
$orte_zuteilung_array['ohne'] = 0;
foreach ($orte->result AS $row)
	$orte_zuteilung_array[$row->ort_kurzbz] = 0;

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$result_arr[] = $row;
		
		if (is_null($row->ort_kurzbz))
			$orte_zuteilung_array['ohne']++;
		else 
			$orte_zuteilung_array[$row->ort_kurzbz]++;
	}
}

	echo '<span style="font-size: 9pt">Anzahl: '.$db->db_num_rows($result).'/'.($reihungstest->max_teilnehmer!=''?$reihungstest->max_teilnehmer:$arbeitsplaetze_sum).'</span>';
	echo '<table width="100%"><tr><td>';
	echo '<a class="buttongreen" href="'.$_SERVER['PHP_SELF'].'?reihungstest_id='.$reihungstest_id.'&excel=true">Excel Export</a>';
	echo '<a class="buttongreen" href="'.$_SERVER['PHP_SELF'].'?reihungstest_id='.$reihungstest_id.'&type=saveallrtpunkte">Punkte ins FAS &uuml;bertragen</a>';
	echo '<a class="buttongreen" href="../../cis/testtool/admin/auswertung.php?'.($reihungstest_id!=''?"reihungstest=$reihungstest_id":'').'" target="_blank">Auswertung</a>';
	if($rechte->isBerechtigt('basis/testtool', null, 'suid'))
	{
		echo '<a class="buttonorange" href="reihungstest_administration.php">Administration</a><br>';
	}
	echo '</td></tr><tr><td>';
	echo '<div id="clm_id" class="active" onclick="hideColumn(\'clm_id\')">ID</div>';
	echo '<div id="clm_geschlecht" class="active" onclick="hideColumn(\'clm_geschlecht\')">Geschlecht</div>';
	echo '<div id="clm_studiengang" class="active" onclick="hideColumn(\'clm_studiengang\')">Studiengang</div>';
	echo '<div id="clm_orgform" class="active" onclick="hideColumn(\'clm_orgform\')">OrgForm</div>';
	echo '<div id="clm_einstiegssemester" class="active" onclick="hideColumn(\'clm_einstiegssemester\')">Einstiegssemester</div>';
	echo '<div id="clm_geburtsdatum" class="active" onclick="hideColumn(\'clm_geburtsdatum\')">Geburtsdatum</div>';
	echo '<div id="clm_email" class="active" onclick="hideColumn(\'clm_email\')">EMail</div>';
	echo '<div id="clm_absolviert" class="active" onclick="hideColumn(\'clm_absolviert\')">Absolvierte Tests</div>';
	echo '<div id="clm_ergebnis" class="active" onclick="hideColumn(\'clm_ergebnis\')">Ergebnis</div>';
	echo '<div id="clm_fas" class="active" onclick="hideColumn(\'clm_fas\')">FAS</div>';
	echo '</td></tr></table>';
	echo '<br>';
	echo '<table><tr>';

	
	$pruefling = new pruefling();
	
	$cnt = 0;
	if ($orte_zuteilung_array['ohne']>0)
	{
		echo '<td style="vertical-align: top">';
		echo '<div style="text-align: center; padding: 0 0 5px 0"><b>Ohne Raumzuteilung ('.$orte_zuteilung_array['ohne'].')</b></div>';
		echo '<div align="center"><a class="buttonorange" href="'.$_SERVER['PHP_SELF'].'?reihungstest_id='.$reihungstest_id.'&type=verteilen" onclick="return confirm(\'BewerberInnen gleichmaeßig auf alle Raeume verteilen?\');">Gleichmäßig verteilen</a>';
		echo '<a class="buttonorange" href="'.$_SERVER['PHP_SELF'].'?reihungstest_id='.$reihungstest_id.'&type=auffuellen" onclick="return confirm(\'Die Räume werden ansteigend mit BewerbeInnen aufgefuellt\');">Auffüllen</a></div>';
		echo '<form id="raumzuteilung_form[ohne]" method="POST" action="'.$_SERVER['PHP_SELF'].'">';
		echo '<input type="hidden" value="'.$reihungstest->reihungstest_id.'" name="reihungstest_id">';
		echo '<table class="tablesorter" id="t'.$cnt.'">
				<thead>
				<tr class="liste">
					<th style="text-align: center">
					<nobr>
						<a href="#" data-toggle="checkboxes" data-action="toggle" id="toggle_t'.$cnt.'"><img src="../../skin/images/checkbox_toggle.png" name="toggle"></a>
						<a href="#" data-toggle="checkboxes" data-action="uncheck" id="uncheck_t'.$cnt.'"><img src="../../skin/images/checkbox_uncheck.png" name="toggle"></a>
					</nobr>
					</th>
					<th style="display: table-cell" class="clm_id" title="PrestudentID">ID</th>
					<th>Nachname</th>
					<th>Vorname</th>
					<th style="display: table-cell" class="clm_geschlecht">Geschlecht</th>
					<th style="display: table-cell" class="clm_studiengang">Studiengang</th>
 					<th style="display: table-cell" class="clm_orgform">OrgForm</th>
					<th style="display: table-cell" class="clm_einstiegssemester">Einstiegssemester</th>
					<th style="display: table-cell" class="clm_geburtsdatum">Geburtsdatum</th>
					<th style="display: table-cell" class="clm_email">EMail</th>
					<th style="display: table-cell" class="clm_absolviert">bereits absolvierte Verfahren</th>
					<th style="display: table-cell" class="clm_ergebnis">Ergebnis</th>
					<th style="display: table-cell" class="clm_fas">FAS</th>
				</tr>
				</thead>
				<tbody>';
		foreach ($result_arr AS $row)
		{
			$rt_in_anderen_stg='';
			$rtergebnis = '';
			if(defined('REIHUNGSTEST_ERGEBNISSE_BERECHNEN') && REIHUNGSTEST_ERGEBNISSE_BERECHNEN)
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
			}
			if ($row->ort_kurzbz == '')
			{
				echo '
					<tr>
						<td style="text-align: center"><input type="checkbox" class="chkbox" id="checkbox_'.$row->person_id.'" name="checkbox['.$row->person_id.']"></td>
						<td style="display: table-cell" class="clm_id">'.$db->convert_html_chars($row->prestudent_id).'</td>
						<td>'.$db->convert_html_chars($row->nachname).'</td>
						<td>'.$db->convert_html_chars($row->vorname).'</td>
						<td style="display: table-cell" class="clm_geschlecht">'.$db->convert_html_chars($row->geschlecht).'</td>
						<td style="display: table-cell" class="clm_studiengang">'.$db->convert_html_chars($stg_arr[$row->studiengang_kz]).'</td>
						<td style="display: table-cell" class="clm_orgform">'.$db->convert_html_chars($row->orgform_kurzbz!=''?$row->orgform_kurzbz:' ').'</td>
						<td style="display: table-cell" class="clm_einstiegssemester">'.$db->convert_html_chars($row->ausbildungssemester).'</td>
						<td style="display: table-cell" class="clm_geburtsdatum">'.$db->convert_html_chars($row->gebdatum!=''?$datum_obj->convertISODate($row->gebdatum):' ').'</td>
						<td style="display: table-cell; text-align: center" class="clm_email"><a href="mailto:'.$db->convert_html_chars($row->email).'"><img src="../../skin/images/button_mail.gif" name="mail"></a></td>
						<td style="display: table-cell" class="clm_absolviert">'.$rt_in_anderen_stg.'</td>
						<td style="display: table-cell; align: right" class="clm_ergebnis"">'.($rtergebnis==0?'-':number_format($rtergebnis,2,'.','')).'</td>
						<td style="display: table-cell; align: right" class="clm_fas">'.($rtergebnis!=0 && $row->rt_punkte1==''?'<a href="'.$_SERVER['PHP_SELF'].'?reihungstest_id='.$reihungstest_id.'&stg_kz='.$stg_kz.'&type=savertpunkte&prestudent_id='.$row->prestudent_id.'&rtpunkte='.$rtergebnis.'" >&uuml;bertragen</a>':$row->rt_punkte1).'</td>
					</tr>';

				$mailto.= ($mailto!=''?',':'').$row->email;
			}
		}
		echo '</tbody></table>';
	
		echo '<select name="raumzuteilung">';
		echo '<option value="">Ohne Zuteilung</option>';
	
		foreach ($orte->result AS $item)
		{
			if($item->ort_kurzbz=='')
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
	foreach ($orte->result AS $ort)
	{
		$cnt++;
		
		echo '<td style="vertical-align: top">';
		if ($orte_array[$ort->ort_kurzbz] - $orte_zuteilung_array[$ort->ort_kurzbz] < 0)
			$style = 'text-align: center; margin: 0 5px 0 5px; color: red';
		else 
			$style = 'text-align: center; margin: 0 5px 0 5px;';
		echo '<div style="'.$style.'"><b>'.$ort->ort_kurzbz.' ('.$orte_zuteilung_array[$ort->ort_kurzbz].'/'.$orte_array[$ort->ort_kurzbz].')</b></div>';
		
		if ($orte_zuteilung_array[$ort->ort_kurzbz]>0)
		{
			echo '<form id="raumzuteilung_form['.$ort->ort_kurzbz.']" method="POST" action="'.$_SERVER['PHP_SELF'].'">';
			echo '<input type="hidden" value="'.$reihungstest->reihungstest_id.'" name="reihungstest_id">';
			echo '<table class="tablesorter" id="t'.$cnt.'">
					<thead>
					<tr class="liste">
						<th style="text-align: center">
						<nobr>
							<a href="#" data-toggle="checkboxes" data-action="toggle" id="toggle_t'.$cnt.'"><img src="../../skin/images/checkbox_toggle.png" name="toggle"></a>
							<a href="#" data-toggle="checkboxes" data-action="uncheck" id="uncheck_t'.$cnt.'"><img src="../../skin/images/checkbox_uncheck.png" name="toggle"></a>
						</nobr>
						</th>
						<th style="display: table-cell" class="clm_id" title="PrestudentID">ID</th>
						<th>Nachname</th>
						<th>Vorname</th>
						<th style="display: table-cell" class="clm_geschlecht">Geschlecht</th>
						<th style="display: table-cell" class="clm_studiengang">Studiengang</th>
	 					<th style="display: table-cell" class="clm_orgform">OrgForm</th>
						<th style="display: table-cell" class="clm_einstiegssemester">Einstiegssemester</th>
						<th style="display: table-cell" class="clm_geburtsdatum">Geburtsdatum</th>
						<th style="display: table-cell" class="clm_email">EMail</th>
						<th style="display: table-cell" class="clm_absolviert">bereits absolvierte Verfahren</th>
						<th style="display: table-cell" class="clm_ergebnis">Ergebnis</th>
						<th style="display: table-cell" class="clm_fas">FAS</th>
					</tr>
					</thead>
					<tbody>';
			$cnt_personen = 0;
			foreach ($result_arr AS $row)
			{
				$rt_in_anderen_stg='';
				$rtergebnis = '';
				if(defined('REIHUNGSTEST_ERGEBNISSE_BERECHNEN') && REIHUNGSTEST_ERGEBNISSE_BERECHNEN)
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
				}
				if ($row->ort_kurzbz == $ort->ort_kurzbz)
				{
					$cnt_personen++;
					echo '
						<tr>
							<td style="text-align: center"><input class="chkbox" type="checkbox" id="checkbox_'.$row->person_id.'" name="checkbox['.$row->person_id.']"></td>
							<td style="display: table-cell" class="clm_id">'.$db->convert_html_chars($row->prestudent_id).'</td>
							<td>'.$db->convert_html_chars($row->nachname).'</td>
							<td>'.$db->convert_html_chars($row->vorname).'</td>
							<td style="display: table-cell" class="clm_geschlecht">'.$db->convert_html_chars($row->geschlecht).'</td>
							<td style="display: table-cell" class="clm_studiengang">'.$db->convert_html_chars($stg_arr[$row->studiengang_kz]).'</td>
							<td style="display: table-cell" class="clm_orgform">'.$db->convert_html_chars($row->orgform_kurzbz!=''?$row->orgform_kurzbz:' ').'</td>
							<td style="display: table-cell" class="clm_einstiegssemester">'.$db->convert_html_chars($row->ausbildungssemester).'</td>
							<td style="display: table-cell" class="clm_geburtsdatum">'.$db->convert_html_chars($row->gebdatum!=''?$datum_obj->convertISODate($row->gebdatum):' ').'</td>
							<td style="display: table-cell; text-align: center" class="clm_email"><a href="mailto:'.$db->convert_html_chars($row->email).'"><img src="../../skin/images/button_mail.gif" name="mail"></a></td>
							<td style="display: table-cell" class="clm_absolviert">'.$rt_in_anderen_stg.'</td>
							<td style="display: table-cell; align: right" class="clm_ergebnis"">'.($rtergebnis==0?'-':number_format($rtergebnis,2,'.','')).'</td>
							<td style="display: table-cell; align: right" class="clm_fas">'.($rtergebnis!=0 && $row->rt_punkte1==''?'<a href="'.$_SERVER['PHP_SELF'].'?reihungstest_id='.$reihungstest_id.'&stg_kz='.$stg_kz.'&type=savertpunkte&prestudent_id='.$row->prestudent_id.'&rtpunkte='.$rtergebnis.'" >&uuml;bertragen</a>':$row->rt_punkte1).'</td>
						</tr>';
		
					$mailto.= ($mailto!=''?',':'').$row->email;
				}
			}
			if (1==0)
			{
				echo '
					<tr>
						<td style="text-align: center">-</td>
						<td style="display: table-cell" class="clm_id">-</td>
						<td>-</td>
						<td>-</td>
						<td style="display: table-cell" class="clm_geschlecht">-</td>
						<td style="display: table-cell" class="clm_studiengang">-</td>
						<td style="display: table-cell" class="clm_orgform">-</td>
						<td style="display: table-cell" class="clm_einstiegssemester">-</td>
						<td style="display: table-cell" class="clm_geburtsdatum">-</td>
						<td style="display: table-cell; align: center" class="clm_email">-</td>
						<td style="display: table-cell" class="clm_absolviert">-</td>
						<td style="display: table-cell; align: right" class="clm_ergebnis"">-</td>
						<td style="display: table-cell; align: right" class="clm_fas">-</td>
					</tr>';
			}
			echo '</tbody></table>';
			
			echo '<select name="raumzuteilung">';
			echo '<option value="">Zuteilung entfernen</option>';
			
			foreach ($orte->result AS $item)
			{
				if($item->ort_kurzbz==$ort->ort_kurzbz)
					$selected = 'selected="selected"';
				else
					$selected = '';
				echo '<option value="'.$item->ort_kurzbz.'" '.$selected.'>'.$item->ort_kurzbz.'</option>';
			}
			echo '</select>';
			echo '<button type="submit" name="raumzuteilung_speichern">Speichern</button>';
			echo '</form>';
		}
		else 
			echo '<table style="width: 100%; margin: 10px 0pt 15px;"><tr><td style="text-align: center; background: #DCE4EF; border: 1px solid white; padding: 4px; font-size: 8pt"><b>Leer</b></td></tr></table>';
		
		echo '</td>';
	}
	
	
	
	echo '</tr></table>';
	echo "<span style='font-size: 9pt'><a href='mailto:?bcc=$mailto'>Mail an alle senden</a></span>";
} ?>

	</body>
</html>
