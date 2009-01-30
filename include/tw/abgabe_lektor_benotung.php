<?php
/* Copyright (C) 2008 Technikum-Wien
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
/*******************************************************************************************************
 *			abgabe_lektor_benotung
 *     abgabe_lektor_benotung ist die Benotungsoberfläche des Abgabesystems 
 * 			für Diplom- und Bachelorarbeiten
 *******************************************************************************************************/

// Pfad zu fpdf
define('FPDF_FONTPATH','../../include/pdf/font/');
// library einbinden
require_once('../../include/pdf/fpdf.php');

require_once('../../cis/config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/datum.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/mitarbeiter.class.php');

require_once('../../include/pdf.inc.php');

error_reporting(E_ALL);
ini_set('display_errors','1');

//DB Verbindung herstellen
if (!$conn = @pg_pconnect(CONN_STRING))
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$getuid=get_uid();
$datum_obj = new datum();
$htmlstr = "";
$qualitaet='';
$form='';
$hintergrund='';
$punkte1=0;
$punkteges1='';
$punkte2=0;
$punkteges2='';
$punkte3=0;
$punkteges3='';
$summe1='';
$summe2='';
$note='';
$weight1='';
$weight2='';
$weight3='';	
$stgbezeichnung='';
$stgtyp='';

$projektarbeit_id='';
$uid='';
$titel='';
$beurteiler='';
$ende='';


function getmax($val1,$val2)
{
	return ($val1>$val2)?$val1:$val2;

}

if(!isset($_POST['projektarbeit_id']))
{
	$uid = (isset($_GET['uid'])?$_GET['uid']:'-1');
	$projektarbeit_id = (isset($_GET['projektarbeit_id'])?$_GET['projektarbeit_id']:'-1');
	$titel = (isset($_GET['titel'])?$_GET['titel']:'-1');
}
else 
{
	$projektarbeit_id=(isset($_POST['projektarbeit_id'])?$_POST['projektarbeit_id']:'-1');
	$uid=(isset($_POST['uid'])?$_POST['uid']:'-1');
	$titel=(isset($_POST['titel'])?$_POST['titel']:'');
	$studiengang=(isset($_POST['studiengang'])?$_POST['studiengang']:'');
	$stgtyp=(isset($_POST['stgtyp'])?$_POST['stgtyp']:'');
	$autor=(isset($_POST['autor'])?$_POST['autor']:'');
	$perskz=(isset($_POST['perskz'])?$_POST['perskz']:'');
	
	$qualitaet=(isset($_POST['qualitaet'])?$_POST['qualitaet']:'');
	$form=(isset($_POST['form'])?$_POST['form']:'');
	$hintergrund=(isset($_POST['hintergrund'])?$_POST['hintergrund']:'');
	$punkte1=(isset($_POST['punkte1'])?$_POST['punkte1']:'');
	$punkteges1=(isset($_POST['punkteges1'])?$_POST['punkteges1']:'');
	$punkte2=(isset($_POST['punkte2'])?$_POST['punkte2']:'');
	$punkteges2=(isset($_POST['punkteges2'])?$_POST['punkteges2']:'');
	$punkte3=(isset($_POST['punkte3'])?$_POST['punkte3']:'');
	$punkteges3=(isset($_POST['punkteges3'])?$_POST['punkteges3']:'');
	$summe1=(isset($_POST['summe1'])?$_POST['summe1']:'');
	$summe2=(isset($_POST['summe2'])?$_POST['summe2']:'');
	$note=(isset($_POST['note'])?$_POST['note']:'');
	$ende=(isset($_POST['ende'])?$_POST['ende']:'');
	
	//Ausdruck generieren
	//PDF erzeugen
	$pdf = new PDF('P','pt');
	$pdf->Open();
	$pdf->AddPage();
	$pdf->AliasNbPages();
	
	$pdf->SetFillColor(111,111,111);
	$pdf->SetXY(30,30);

	//Logo
	$pdf->Image("../../skin/images/logo.jpg","400","25","160","54","jpg","");
	
	$pdf->SetFont('Arial','',12);
	$pdf->SetFillColor(190,190,190);
	$pdf->SetXY(40,80);
	$pdf->SetFont('Arial','',10);
	$pdf->MultiCell(0,15,'Studiengang: ');
	$pdf->SetXY(40,95);
	$pdf->SetFont('Arial','',12);
	$pdf->MultiCell(0,15,$stgtyp.'studiengang '.$studiengang);
	$pdf->SetFont('Arial','',14);
	$pdf->SetXY(40,115);
	if($stgtyp=='Bachelor')
	{
		$pdf->MultiCell(0,15,'Beurteilung Bachelorarbeit');
	}
	else
	{
		$pdf->MultiCell(0,15,'Beurteilung Diplomarbeit');
	}
	$qry_beu="SELECT * FROM public.tbl_person JOIN public.tbl_benutzer using(person_id) WHERE uid='".$getuid."';";
	if(!$erg_beu=pg_query($conn, $qry_beu))
	{
		die('Fehler beim Laden des Betreuernamens');
	}
	else
	{
		if($row_beu=@pg_fetch_object($erg_beu))
		{
			$beurteiler=trim($row_beu->titelpre.' '.$row_beu->vorname.' '.$row_beu->nachname.' '.$row_beu->titelpost);
		}
		else 
		{
			die('Betreuer nicht gefunden!');
		}
	}
	//Zeile Titel
	$pdf->SetFont('Arial','',12);
	$maxY=$pdf->GetY()+5;
	$maxX=30;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(80,42,'Titel',1,'L',0);
	$maxX +=80;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(450,14,$titel,0,'L',0);
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(450,42,'',1,'L',0);
	//Autor
	$maxY=$pdf->GetY();
	$maxX=30;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(80,14,'Autor',1,'L',0);
	$maxX +=80;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(291,14,$autor,1,'L',0);
	$maxX +=291;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(159,14,trim('Personenkz.: '),1,'L',0);
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(159,14,trim($perskz),0,'R',0);
	//Zeile Beurteilt von
	$maxY=$pdf->GetY();
	$maxX=30;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(80,14,'Beurteilt von',1,'L',0);
	$maxX +=80;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(291,14,$beurteiler,1,'L',0);
	$maxX +=291;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(159,14,'Datum.: ',1,'L',0);
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(159,14,date('d.m.Y',mktime(0, 0, 0, date("m")  , date("d"), date("Y"))),1,'R',0);
	
	//Feld Beurteilung
	//Zeile Überschrift
	$pdf->SetFont('Arial','',9);
	$maxY=$pdf->GetY()+2;
	$maxX=30;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(159,36,'',1,'L',0);
	$maxX +=159;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(212,36,'Kurze verbale Beurteilung',1,'C',0);
	$maxX +=212;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(53,12,'Punkte (0-100)',0,'C',0);
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(53,36,'',1,'L',0);
	$maxX +=53;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(53,12,'Gewicht',0,'C',0);
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(53,36,'',1,'L',0);
	$maxX +=53;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(53,12,'    Punkte             *          Gewicht',0,'C',0);
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(53,36,'',1,'L',0);
	//Zeile Qualität
	$pdf->SetFont('Arial','',9);
	if($stgtyp=='Bachelor')
	{
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,12,"Qualität des eigenen Beitrags\nAngewandte Methodik, z.B.\n   - wissenschaftlich fundierte,\n     systematische, ingenieurmäßige\n     Vorgangsweise\n   - Ist der eigene Beitrag\n     deutlich sichtbar?\n   - Qualität der Lösung",0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,144,'',1,'L',0);
		$maxX +=159;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(212,12,$qualitaet,0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(212,144,'',1,'L',0);
		$maxX +=212;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkte1,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,144,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,'0.60',0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,144,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkteges1,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,144,'',1,'L',0);
	}
	else 
	{
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,12,"Qualität des eigenen Beitrags\nAngewandte Methodik, z.B.\n   - Projektmäßige Vorgehensweise\n   - Wissenschaftlich - systematische\n     Methoden in der Analyse\n     bzw. Lösungsfindung",0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,144,'',1,'L',0);
		$maxX +=159;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(212,12,$qualitaet,0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(212,144,'',1,'L',0);
		$maxX +=212;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkte1,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,144,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,'0.55',0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,144,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkteges1,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,144,'',1,'L',0);
	}
	//Zeile Form
	$pdf->SetFont('Arial','',9);
	if($stgtyp=='Bachelor')
	{
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,12,"Form / Stil\n  - Hat die Bachelorarbeit eine\n     klare Struktur, entspricht der\n     Vorgabe\n   - Wird einwandfrei zitiert\n   - Abbildungen\n   - Sprache",0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,144,'',1,'L',0);
		$maxX +=159;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(212,12,$form,0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(212,144,'',1,'L',0);
		$maxX +=212;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkte2,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,144,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,'0.40',0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,144,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkteges2,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,144,'',1,'L',0);
	}
	else 
	{
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,12,"Form / Stil\n   - Hat die Diplomarbeit eine\n     klare Struktur, entspricht der\n     Vorgabe\n   - Wird einwandfrei zitiert\n   - Abbildungen\n   - Sprache: benötigte Überarbeitung\n     seitens der Betreuerin / \n     des Betreuers",0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,144,'',1,'L',0);
		$maxX +=159;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(212,12,$form,0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(212,144,'',1,'L',0);
		$maxX +=212;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkte2,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,144,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,'0.20',0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,144,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkteges2,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,144,'',1,'L',0);
	}
	//Zeile Hintergrundinfo
	$pdf->SetFont('Arial','',9);
	if($stgtyp=='Bachelor')
	{
	}
	else 
	{
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,12,"Qualität der Hintergrundinformation\n   - werden Gesamtzusammenhänge\n     erkannt, wird Bedeutung\n     und Gewicht der Einflussfaktoren\n     /Daten/Informationen richtig\n     bewertet\n   - Intelligente Darstellung des\n     relevanten Stands der Technik\n     und des Firmenumfelds\n   - Aufdecken und Darstellen von\n     größeren (z.B. wirtschaftlichen\n     oder sozialen) Zusammenhängen\n     und entsprechende Diskussion",0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,156,'',1,'L',0);
		$maxX +=159;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(212,12,$hintergrund,0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(212,156,'',1,'L',0);
		$maxX +=212;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkte3,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,156,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,'0.25',0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,156,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkteges3,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,156,'',1,'L',0);
	}
	//Zeile Gesamtpunkte
	$maxY=$pdf->GetY();
	$maxX=30;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(371,12,'Gesamtpunkte',1,'L',0);
	$maxX +=371;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(53,12,$summe1,1,'C',0);
	$maxX +=53;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(53,12,'1',1,'C',0);
	$maxX +=53;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(53,12,$summe2,1,'C',0);
	//Feld Umrechnung Punkte=>Note
	$pdf->SetFont('Arial','',7);
	if($stgtyp=='Bachelor')
	{
		$maxY=680;
	}
	else 
	{
		$maxY=705;
	}
	$maxX=160;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(80,12,'0 - 50 Punkte = 5',1,'C',0);
	$maxX +=80;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(80,12,'51 - 64 Punkte = 4',1,'C',0);
	$maxX +=80;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(80,12,'65 - 77 Punkte = 3',1,'C',0);
	$maxX +=80;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(80,12,'78 - 90 Punkte = 2',1,'C',0);
	$maxX +=80;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(80,12,'91 - 100 Punkte = 1',1,'C',0);
	$maxY=$pdf->GetY();
	$maxX=160;
	$pdf->SetXY($maxX,$maxY);
	if($stgtyp=='Bachelor')
	{
		$pdf->MultiCell(240,12,'1 Gruppe < 50 Punkte => Bachelorarbeit gesamt negativ',1,'L',0);
	}
	else 
	{
		$pdf->MultiCell(240,12,'1 Gruppe < 50 Punkte => Diplomarbeit gesamt negativ',1,'L',0);
	}
	$maxX +=240;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(80,12,'',1,'C',0);
	$maxX +=80;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(80,12,'',1,'C',0);
	//Zeile Note undUnterschrift
	$pdf->SetFont('Arial','',10);
	$maxY=760;
	$maxX=+40;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(80,12,'Note: '.$note,0,'L',0);
	$maxX=+40;
	$maxY=$pdf->GetY();
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(160,12,'Letzter Abgabetermin: '.$datum_obj->formatDatum($ende,'d.m.Y'),0,'L',0);
	$maxX +=300;
	$pdf->SetXY($maxX,$maxY);
	$pdf->MultiCell(240,12,"Unterschrift:__________________________",0,'C',0);
	$maxY=$pdf->GetY();
	$pdf->footerset[1]=1;
	$pdf->Output('Beurteilung.pdf','I');
}



$sql_query = "SELECT * FROM (SELECT DISTINCT ON(tbl_projektarbeit.projektarbeit_id) tbl_studiengang.bezeichnung as stgbezeichnung, tbl_studiengang.typ as stgtyp, * 
	FROM lehre.tbl_projektarbeit LEFT JOIN lehre.tbl_projektbetreuer using(projektarbeit_id) 
	LEFT JOIN public.tbl_benutzer on(uid=student_uid) 
	LEFT JOIN public.tbl_student on(tbl_benutzer.uid=tbl_student.student_uid) 
	LEFT JOIN public.tbl_person on(tbl_benutzer.person_id=tbl_person.person_id)
	LEFT JOIN lehre.tbl_lehreinheit using(lehreinheit_id) 
	LEFT JOIN lehre.tbl_lehrveranstaltung using(lehrveranstaltung_id) 
	LEFT JOIN public.tbl_studiengang on(tbl_lehrveranstaltung.studiengang_kz=tbl_studiengang.studiengang_kz)
	WHERE (projekttyp_kurzbz='Bachelor' OR projekttyp_kurzbz='Diplom')
	AND tbl_projektbetreuer.person_id IN (SELECT person_id FROM public.tbl_benutzer 
							WHERE public.tbl_benutzer.person_id=lehre.tbl_projektbetreuer.person_id 
							AND public.tbl_benutzer.uid='$getuid')
	AND lehre.tbl_projektarbeit.note IS NULL 
	AND lehre.tbl_projektarbeit.projektarbeit_id=".$projektarbeit_id."
	ORDER BY tbl_projektarbeit.projektarbeit_id, betreuerart_kurzbz desc) as xy 
	ORDER BY nachname";

if(!$erg=pg_query($conn, $sql_query))
{
	die('Fehler beim Laden der Betreuungen');
}
else
{
	if($row=@pg_fetch_object($erg))
	{
		echo '
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
		<html>
		<head>
		<title>DA/BA-Benotung</title>
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<meta http-equiv="content-type" content="text/html; charset=ISO-8859-9" />
		<style type="text/css">			
			.textInput,textarea 
			{
				background-color: #FFFFFF;
			}
			
			.inputHighlighted 
			{
				background-color: #EEFFEE;
				color: #000;
			}

		</style>

		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
		<script language="Javascript">
			var currentlyActiveInputRef = false;
			var currentlyActiveInputClassName = false;
			
			function highlightActiveInput() 
			{
				if(currentlyActiveInputRef) 
				{
					currentlyActiveInputRef.className = currentlyActiveInputClassName;
				}
				currentlyActiveInputClassName = this.className;
				this.className = "inputHighlighted";
				currentlyActiveInputRef = this;
			}
				
			function blurActiveInput() 
			{
				this.className = currentlyActiveInputClassName;
			}
			
			function initInputHighlightScript() 
			{
				var tags = ["INPUT","TEXTAREA"];
				for(tagCounter=0;tagCounter<tags.length;tagCounter++)
				{
					var inputs = document.getElementsByTagName(tags[tagCounter]);
					for(var no=0;no<inputs.length;no++)
					{
						if(inputs[no].className && inputs[no].className=="doNotHighlightThisInput")
						{
							continue;
						}
						if((inputs[no].tagName.toLowerCase()=="textarea" || (inputs[no].tagName.toLowerCase()=="input" && inputs[no].type.toLowerCase()=="text"))&&inputs[no].readOnly==false)
						{
							inputs[no].onfocus = highlightActiveInput;
							inputs[no].onblur = blurActiveInput;
						}
					}
				}
			}
			
			function inputcheck()
			{
				if(document.getElementById("summe2").value=="NaN")
				{
					alert("Eingabe ungültig! Bitte nur Ziffern eingeben.");
					document.getElementById("drucken").disabled=false;
					return false;
				}
				else
				{
					return true;
				}
			}
			
			function berechne() 
			{
				ergebnis=document.getElementById("punkte1").value*document.getElementById("weight1").value;
				document.getElementById("punkteges1").value=ergebnis.toFixed(2);
				ergebnis=document.getElementById("punkte2").value*document.getElementById("weight2").value;
				document.getElementById("punkteges2").value=ergebnis.toFixed(2);';
				if($row->projekttyp_kurzbz=='Diplom')
				{
					echo 'ergebnis=document.getElementById("punkte3").value*document.getElementById("weight3").value;
					document.getElementById("punkteges3").value=ergebnis.toFixed(2);
					ergebnis=parseFloat(document.getElementById("punkte1").value)+parseFloat(document.getElementById("punkte2").value)+parseFloat(document.getElementById("punkte3").value);
					document.getElementById("summe1").value=ergebnis.toFixed(1);
					ergebnis=parseFloat(document.getElementById("punkteges1").value)+parseFloat(document.getElementById("punkteges2").value)+parseFloat(document.getElementById("punkteges3").value);
					document.getElementById("summe2").value=ergebnis.toFixed(2);
					if(document.getElementById("punkte1").value<50 || document.getElementById("punkte2").value<50 || document.getElementById("punkte3").value<50)
					{
						ergebnis=5;
					}
					else
					{
						if(document.getElementById("summe2").value<=50)
						{
							ergebnis=5;
						}
						else if(document.getElementById("summe2").value<65)
						{
							ergebnis=4;
						}
						else if(document.getElementById("summe2").value<78)
						{
							ergebnis=3;
						}
						else if(document.getElementById("summe2").value<91)
						{
							ergebnis=2;
						}
						else
						{
							ergebnis=1;
						}
					}
					document.getElementById("note").value=ergebnis.toFixed(0);';
				}
				else
				{
					echo '
					ergebnis=parseFloat(document.getElementById("punkte1").value)+parseFloat(document.getElementById("punkte2").value);
					document.getElementById("summe1").value=ergebnis.toFixed(1);
					ergebnis=parseFloat(document.getElementById("punkteges1").value)+parseFloat(document.getElementById("punkteges2").value);
					document.getElementById("summe2").value=ergebnis.toFixed(2);
					if(document.getElementById("punkte1").value<50 || document.getElementById("punkte2").value<50)
					{
						ergebnis=5;
					}
					else
					{
						if(document.getElementById("summe2").value<=50)
						{
							ergebnis=5;
						}
						else if(document.getElementById("summe2").value<65)
						{
							ergebnis=4;
						}
						else if(document.getElementById("summe2").value<78)
						{
							ergebnis=3;
						}
						else if(document.getElementById("summe2").value<91)
						{
							ergebnis=2;
						}
						else
						{
							ergebnis=1;
						}
					}
					document.getElementById("note").value=ergebnis.toFixed(0);';
				}
			echo '}
			function txtcount(field, countfield, maxlimit)
			{
				if(field.value.length>maxlimit)
				{
					field.value=field.value.substring(0,maxlimit);
				}
				else
				{
					countfield.value=maxlimit-field.value.length;
				}
			}

		</script>
		</head>
		<body class="Background_main"  style="background-color:#eeeeee;" onload="berechne()">';
				
		$htmlstr = "<br>";
		$htmlstr .= "<table border='1'  class='detail'>\n";
		$htmlstr .= "<form action='$PHP_SELF' method='POST' name='note' onsubmit='return inputcheck()'>";
		$htmlstr .= "<tr><td style='font-size:16px' colspan='5'>Student: <b>".$uid.", ".$row->vorname." ".$row->nachname."</b></td>";
		$htmlstr .= "<tr><td style='font-size:16px' colspan='5'>Titel: <b>".$titel."</b>";
		$htmlstr .= "<input type='hidden' name='projektarbeit_id' value='".$projektarbeit_id."'>\n";
		$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>\n";
		$htmlstr .= "<input type='hidden' name='titel' value='".$titel."'>\n";
		$htmlstr .= "<input type='hidden' name='perskz' value='".$row->matrikelnr."'>\n";
		$htmlstr .= "<input type='hidden' name='studiengang' value='".$row->stgbezeichnung."'>\n";
		$htmlstr .= "<input type='hidden' name='autor' value='".$row->vorname." ".$row->nachname."'>\n";
		$htmlstr .= "<input type='hidden' name='ende' value='".$row->ende."'>\n";
		if($row->stgtyp=='b')
		{
			$stgtyp='Bachelor';
		}
		elseif($row->stgtyp=='m')
		{
			$stgtyp='Master';
		}
		elseif($row->stgtyp=='d')
		{
			$stgtyp='Diplom';
		}
		else
		{
			$stgtyp='';
		}
		$htmlstr .= "<input type='hidden' name='stgtyp' value='".$stgtyp."'>\n";
		
		$htmlstr .= "</td></tr>";
		$htmlstr .= "<td>&nbsp;</td><td align='center'>Kurze verbale Beurteilung</td><td align='center'>Punkte (0-100)</td><td align='center'>Gewicht</td><td align='center'>Punkte * Gewicht</td>\n";
		$htmlstr .= "<tr>\n";
		if($row->projekttyp_kurzbz!='Bachelor')
		{
			$htmlstr .= "<td width='40%'><b>Qualit&auml;t des eigenen Beitrags<br>
			Angewandte Methodik, z.B.</b><br>Projektm&auml;&szlig;ige Vorgangsweise<br>
			Wissenschaftlich - systematische Methoden in der Analyse bzw. L&ouml;sungsfindung<br>
			<b>Art der Probleml&ouml;sung</b><br>
			Wurde das Problem tats&auml;chlich gel&ouml;st?<br>
			Eigenst&auml;ndigkeit und Kreativit&auml;t der L&ouml;sung<br>
			Ist der eigene Beitrag deutlich sichtbar?<br>
			Technische Qualit&auml;t der L&ouml;sung</td>";
			$weight1='0.55';
		}
		else 
		{
			$htmlstr .= "<td width='40%'><b>Qualit&auml;t des eigenen Beitrags<br>
			Angewandte Methodik, z.B.</b><br>wissenschaftlich fundierte, systematische, ingenieurm&auml;&szlig;ige Vorgangsweise<br>
			Ist der eigene Beitrag deutlich sichtbar?<br>
			Eigenst&auml;ndigkeit und Kreativit&auml;t der L&ouml;sung<br>
			Ist der eigene Beitrag deutlich sichtbar?<br>
			Qualit&auml;t der L&ouml;sung</td>";
			$weight1='0.6';
		}
		$htmlstr .= "<td width='30%'><textarea name='qualitaet' value='".$qualitaet."' cols='50'  rows='10' 
		onKeyDown='txtcount(this.form.qualitaet,this.form.remLen,500);' onKeyUp='txtcount(this.form.qualitaet,this.form.remLen,500);'></textarea>
		<br>Buchstaben noch zur Verf&uuml;gung<input readonly type=text name=remLen size=3 maxlength=3 value='500' style='text-align:right'> </td>\n
		<td width='10%' align='center'><input type='hidden' name='weight' id='weight1' value='".$weight1."'>
		<input  type='text' name='punkte1' value='".$punkte1."' size='5' maxlength='5' id='punkte1' onkeyup='berechne()' style='text-align:right'></td>\n";
		if($row->projekttyp_kurzbz!='Bachelor')
		{
			$htmlstr.="<td width='10%' align='center'>0.55</td>";
		}
		else 
		{
			$htmlstr.="<td width='10%' align='center'>0.60</td>";
		}
		$htmlstr .="<td width='10%' align='center'><input  type='text' name='punkteges1' value='".$punkteges1."' id='punkteges1' style='text-align:right' size='5' maxlength='5' readonly></td></tr>\n";
		if($row->projekttyp_kurzbz!='Bachelor')
		{
			$htmlstr .= "<td width='40%'><b>Form / Stil</b><br>
			Hat die Diplomarbeit eine klare Stuktur, entspricht der Vorgabe?<br>
			Wird einwandfrei zitiert?<br>
			Abbildungen<br>
			Sprache: benötigte &Uuml;berarbeitungen seitens der Betreuerin / des Betreuers</td>
			<td width='30%'><textarea name='form' value='".$form."' cols='50'  rows='10' 
			onKeyDown='txtcount(this.form.form,this.form.remLen2,500);' onKeyUp='txtcount(this.form.form,this.form.remLen2,500);'></textarea>
			<br>Buchstaben noch zur Verf&uuml;gung<input readonly type=text name=remLen2 size=3 maxlength=3 value='500' style='text-align:right'></td>\n";
			$weight2='0.2';
		}
		else 
		{
			$htmlstr .= "<td width='40%'><b>Form / Stil</b><br>
			Hat die Bachelorarbeit eine klare Stuktur, entspricht der Vorgabe?<br>
			Wird einwandfrei zitiert?<br>
			Abbildungen<br>
			Sprache</td>
			<td width='30%'><textarea name='form' value='".$form."' cols='50'  rows='10' 
			onKeyDown='txtcount(this.form.form,this.form.remLen2,500);' onKeyUp='txtcount(this.form.form,this.form.remLen2,500);'></textarea>
			<br>Buchstaben noch zur Verf&uuml;gung<input readonly type=text name=remLen2 size=3 maxlength=3 value='500' style='text-align:right'></td>\n";
			$weight2='0.4';
		}
		$htmlstr .= "<td width='10%' align='center'><input type='hidden' name='weight' id='weight2' value='".$weight2."'>
		<input  type='text' name='punkte2' value='".$punkte2."' size='5' maxlength='5' id='punkte2' onkeyup='berechne()' style='text-align:right'></td>\n";
		if($row->projekttyp_kurzbz!='Bachelor')
		{
			$htmlstr .="<td width='10%' align='center'>0.20</td>";
		}
		else
		{
			$htmlstr .="<td width='10%' align='center'>0.40</td>";
		}
		$htmlstr .="<td width='10%' align='center'><input  type='text' name='punkteges2' value='".$punkteges2."' style='text-align:right' size='5' maxlegnth='3' id='punkteges2' readonly></td></tr>\n";
		if($row->projekttyp_kurzbz!='Bachelor')
		{
			$htmlstr .= "<td width='40%'><b>Qualit&auml;t der Hintergrundinformation</b><br>
			Werden Gesamtzusammenhänge erkannt, wird Bedeutung und Gewicht der Einflussfaktoren / Daten / Informationen richtig bewertet?<br>
			Intelligente Darstellung des relevanten Stands der Technik und des Firmenumfelds<br>
			Aufdecken und Darstellen von gr&ouml;&szlig;eren (z.B. wirtschaftlichen und sozialen) Zusammenh&auml;ngen und entsprechende Diskussion</td>
			<td width='30%'><textarea name='hintergrund' value='".$hintergrund."' cols='50'  rows='10' 
			onKeyDown='txtcount(this.form.hintergrund,this.form.remLen3,500);' onKeyUp='txtcount(this.form.hintergrund,this.form.remLen3,500);'></textarea>
			<br>Buchstaben noch zur Verf&uuml;gung<input readonly type=text name=remLen3 size=3 maxlength=3 value='500' style='text-align:right'></td>\n
			<td width='10%' align='center'><input type='hidden' name='weight' id='weight3' value='0.25'>
			<input  type='text' name='punkte3' value='".$punkte3."' size='5' maxlength='5' id='punkte3' style='text-align:right' onkeyup='berechne()'></td>\n
			<td width='10%' align='center'>0.25</td>
			<td width='10%' align='center'><input  type='text' name='punkteges3' value='".$punkteges3."' id='punkteges3' style='text-align:right' size='5' maxlength='5' readonly></td></tr>";
		}
		else 
		{
			$htmlstr .= "
			<input  type='hidden' name='punkte3' value='0' id='punkte3'></td>\n
			<input  type='hidden' name='punkteges3' value='0' id='punkteges3'></td>\n
			<input  type='hidden' name='weight3' value='0' id='weight3'></td>\n";
		}
		$htmlstr .= "<td colspan='2'>Gesamtpunkte</td>";
		$htmlstr .="<td align='center'><input  type='text' name='summe1' value='".$summe1."' id='summe1' style='text-align:right' size='5' maxlength='5' readonly></td>
			<td align='center'>1</td>
			<td align='center'><input  type='text' name='summe2' value='".$summe2."' id='summe2' style='text-align:right' size='5' maxlength='5' readonly></td><tr>";
		$htmlstr .= "<td colspan='4'>Note</td><td align='center'><input  type='text' name='note' value='".$note."' id='note' style='text-align:right' size='5' maxlength='5' readonly></td></tr>";
		$htmlstr .="</table>";
		$htmlstr .= "<br><table border='1' align='center' width='60%'>";
		$htmlstr .= "<tr><td>0 - 50 Punkte = 5</td><td>51 - 64 Punkte = 4</td><td>65 - 77 Punkte = 3</td><td>78 - 90 Punkte = 2</td><td>91 - 100 Punkte = 1</td></tr>";
		if($row->projekttyp_kurzbz!='Bachelor')
		{
			$htmlstr .= "<tr><td colspan='5'>Ein Kriterium mit weniger als 50 Punkten &rArr; Diplomarbeit gesamt negativ</td></tr>";
		}
		else 
		{
			$htmlstr .= "<tr><td colspan='5'>Ein Kriterium mit weniger als 50 Punkten &rArr; Bachelorarbeit gesamt negativ</td></tr>";
		}
		$htmlstr .= "</table>";
		$htmlstr .= "<br><input type='submit' name='drucken' value='Formular ausdrucken' id='drucken' onclick='this.disabled=true;'>";
		$htmlstr .="</form>";
		$htmlstr .="</body></html>";
		echo $htmlstr;
	}
	else 
	{
		die('Betreuung nicht gefunden!');
	}	
}
echo '<script type="text/javascript">
<!--
	initInputHighlightScript();
//-->
</script>';

?>