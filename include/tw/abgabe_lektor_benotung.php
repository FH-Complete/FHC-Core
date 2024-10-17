<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/*******************************************************************************************************
 *			abgabe_lektor_benotung
 *     abgabe_lektor_benotung ist die Benotungsoberflaeche des Abgabesystems 
 * 			fuer Diplom- und Bachelorarbeiten
 *******************************************************************************************************/
require_once('../../config/cis.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/projektarbeit.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/datum.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Fehler beim Herstellen der Datenbankverbindung');
 
// Pfad zu fpdf
define('FPDF_FONTPATH','../../include/pdf/font/');
// library einbinden
require_once('../../include/pdf/fpdf.php');
require_once('../../include/pdf.inc.php');

$getuid=get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($getuid);

if (isset($_GET['user']))
{
	if ($rechte->isBerechtigt('admin',null,'suid'))
		$getuid = $_GET['user'];
	else 
		$getuid=get_uid();
}
else 
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
$projekttyp_kurzbz='';

$projektarbeit_id='';
$uid='';
$matrikelnr='';
$titel='';
$beurteiler='';
$ende='';

function getmax($val1,$val2)
{
	return ($val1>$val2)?$val1:$val2;

}
$projektarbeit_obj = new projektarbeit();
if(!$projektarbeit_obj->load($_REQUEST['projektarbeit_id']))
	die('Projektarbeit konnte nicht geladen werden');

$titel = $projektarbeit_obj->titel;
$benutzer_autor = new benutzer();
if(!$benutzer_autor->load($projektarbeit_obj->student_uid))
	die('Studierender kann nicht geladen werden');
$nachname_clean = convertProblemChars($benutzer_autor->nachname);

if(!isset($_POST['projektarbeit_id']))
{
	$uid = (isset($_GET['uid'])?$_GET['uid']:'-1');
	$projektarbeit_id = (isset($_GET['projektarbeit_id'])?$_GET['projektarbeit_id']:'-1');
}
else 
{
	$projektarbeit_id=(isset($_POST['projektarbeit_id'])?$_POST['projektarbeit_id']:'-1');
	$uid=(isset($_POST['uid'])?$_POST['uid']:'-1');
	$matrikelnr=(isset($_POST['matrikelnr'])?$_POST['matrikelnr']:'-1');
	$studiengang=(isset($_POST['studiengang'])?$_POST['studiengang']:'');
	$stgtyp=(isset($_POST['stgtyp'])?$_POST['stgtyp']:'');
	$projekttyp_kurzbz=(isset($_POST['projekttyp_kurzbz'])?$_POST['projekttyp_kurzbz']:'');
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
	$summe2=(isset($_POST['summe2'])?$_POST['summe2']:'');
	$note=(isset($_POST['note'])?$_POST['note']:'');
	$ende=(isset($_POST['ende'])?$_POST['ende']:'');
	$titelpre=(isset($_POST['titelpre'])?$_POST['titelpre']:'');
	$titelpost=(isset($_POST['titelpost'])?$_POST['titelpost']:'');
	
	$qualitaet=mb_convert_encoding(trim($qualitaet),'ISO-8859-15','UTF-8');
	$form=mb_convert_encoding(trim($form),'ISO-8859-15','UTF-8');
	$hintergrund=mb_convert_encoding(trim($hintergrund),'ISO-8859-15','UTF-8');
	$autor=mb_convert_encoding(trim($autor),'ISO-8859-15','UTF-8');
	$titel=mb_convert_encoding(trim($titel),'ISO-8859-15','UTF-8');
	$titelpre=mb_convert_encoding(trim($titelpre),'ISO-8859-15','UTF-8');
	$titelpost=mb_convert_encoding(trim($titelpost),'ISO-8859-15','UTF-8');
	$studiengang=mb_convert_encoding(trim($studiengang),'ISO-8859-15','UTF-8');
	
	// Wenn der Titel zu lang ist fuer eine Zeile, dann wird der gesammte Block oberhalb des
	// Titels weiter nach oben geschoben, um Platz fuer den mehrzeiligen Titel zu schaffen
	// Hier wird berechnet, wie viele Zeilen fuer den Titel benoetigt werden
	$titel_len = mb_strlen($titel);
	$zeichenprozeile=80;
	$zeilen = round((($titel_len/$zeichenprozeile)+0.5),0);
	$zeilenhoehe=15;
	$titelabzug = ($zeilen*$zeilenhoehe);

	
	if($punkte1>100 || $punkte2>100 || $punkte3>100)
		die('<html><body><br><br>Die Punkteanzahl darf nicht groesser als 100 sein! <a href="javascript:history.back();">Zurueck</a></body></html>');
	
	if($projekttyp_kurzbz=='Bachelor')
	{
		//Bachelorausdruck generieren
		//PDF erzeugen
		$pdf = new PDF('P','pt');
		$pdf->Open();
		$pdf->AddPage();
		$pdf->AliasNbPages();
		
		$pdf->SetFillColor(111,111,111);
		$pdf->SetXY(30,30);
	
		//Logo
		$pdf->Image("../../skin/styles/".DEFAULT_STYLE."/logo.jpg","400","25","150","78","jpg","");

		$pdf->SetFont('Arial','',12);
		$pdf->SetFillColor(190,190,190);
		$pdf->SetXY(30,110-$titelabzug);
		$pdf->SetFont('Arial','',10);
		$pdf->MultiCell(0,15,'Studiengang: ');
		$pdf->SetXY(30,125-$titelabzug);
		$pdf->SetFont('Arial','',12);
		$pdf->MultiCell(0,15,$stgtyp.'studiengang '.$studiengang);
		$pdf->SetFont('Arial','',14);
		$pdf->SetXY(30,170-$titelabzug);
		
		$pdf->MultiCell(0,15,'Beurteilung Bachelorarbeit');
	
		$qry_beu="SELECT * FROM public.tbl_person JOIN public.tbl_benutzer using(person_id) WHERE uid=".$db->db_add_param($getuid).";";
		if(!$erg_beu=@$db->db_query($qry_beu))
		{
			die('Fehler beim Laden des Betreuernamens');
		}
		else
		{
			if($row_beu=$db->db_fetch_object($erg_beu))
			{
				// UTF-8 encoden
				while (list($key, $value) = each($row_beu)) 
				{
					if (!empty($value))
				    	$row_beu->$key=mb_convert_encoding(trim($value),'ISO-8859-15','UTF-8');
				}
				$beurteiler=trim($row_beu->titelpre.' '.$row_beu->vorname.' '.$row_beu->nachname.' '.$row_beu->titelpost);
			}
			else 
			{
				die('Betreuer nicht gefunden!');
			}
		}
		
		//Zeile Titel		 
		$pdf->SetFont('Arial','',10);
		$maxY=$pdf->GetY()+18;
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(80,15*$zeilen,'Titel',1,'L',0);
		$maxX +=80;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(450-$titelabzug,15,$titel,0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(450,15*$zeilen,'',1,'L',0);
		
		//Autor
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(80,18,'Autor',1,'L',0);
		$maxX +=80;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(291,18,trim($titelpre." ".$autor." ".$titelpost),1,'L',0);
		$maxX +=291;
		$pdf->SetXY($maxX,$maxY);
		$pdf->SetFont('Arial','',9);
		$pdf->MultiCell(159,18,trim('Personenkennzeichen: '),1,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,18,trim($perskz),0,'R',0);
		
		//Zeile Beurteilt von
		$maxY=$pdf->GetY();
		$pdf->SetFont('Arial','',10);
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(80,18,'Beurteilt von',1,'L',0);
		$maxX +=80;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(291,18,$beurteiler,1,'L',0);
		$maxX +=291;
		$pdf->SetFont('Arial','',9);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,18,'Datum: ',1,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,18,date('d.m.Y',mktime(0, 0, 0, date("m")  , date("d"), date("Y"))),1,'R',0);
		
		//Feld Beurteilung
		//Zeile Überschrift
		$maxY=$pdf->GetY()+14;
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,36,'',1,'L',0);
		$maxX +=159;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(212,36,'Kurze schriftliche Beurteilung',1,'C',0);
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
		$pdf->MultiCell(53,12,'    Punkte             x          Gewicht',0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,36,'',1,'L',0);
		
		//Zeile Qualität
		$pdf->SetFont('Arial','B',9);
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		//nur fettgedruckter Text
		$pdf->MultiCell(159,12,mb_convert_encoding("1.) Qualität des eigenen Beitrags\n\n  Angewandte Methodik, z.B.\n\n\n\n\n\n",'ISO-8859-15','UTF-8'),0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->SetFont('Arial','',9);
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,13,mb_convert_encoding("\n\n\n   - Wissenschaftlich fundierte,\n     systematische, ingenieurmäßige\n     Vorgangsweise\n   - Ist der eigene Beitrag deutlich\n     sichtbar?\n   - Qualität der Lösung",'ISO-8859-15','UTF-8'),0,'L',0);
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
		
		//Zeile Form
		$pdf->SetFont('Arial','B',9);
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		//nur fettgedruckter Text
		$pdf->MultiCell(159,12,mb_convert_encoding("2.) Form / Stil\n\n\n\n\n\n\n",'ISO-8859-15','UTF-8'),0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->SetFont('Arial','',9);
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,13,mb_convert_encoding("\n\n   - Hat die Bachelorarbeit eine klare\n     Struktur, entspricht der Vorgabe?\n   - Wird einwandfrei zitiert?\n   - Abbildungen?\n   - Sprache",'ISO-8859-15','UTF-8'),0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,116,'',1,'L',0);
		$maxX +=159;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(212,12,$form,0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(212,116,'',1,'L',0);
		$maxX +=212;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkte2,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,116,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,'0.40',0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,116,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkteges2,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,116,'',1,'L',0);

		//Zeile Hintergrundinfo
		$pdf->SetFont('Arial','B',9);
		//Zeile Gesamtpunkte
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(371,17,'Gesamtpunkte',1,'R',0);
		$pdf->SetFont('Arial','',9);
		$maxX +=371;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,17," ",1,'C',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,17,' ',1,'C',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,17,$summe2,1,'C',0);
		
		//Feld Umrechnung Punkte=>Note
		$maxY=620;
		$maxX=30;
		$pdf->SetFont('Arial','',10);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(80,12,'Kriterien:');
		$pdf->SetFont('Arial','',8);
		$maxX +=55;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(95,12,'< 50% = 5',1,'C',0);
		$maxX +=95;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(95,12,'>= 50% und < 63% = 4',1,'C',0);
		$maxX +=95;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(95,12,'>= 63% und < 75% = 3',1,'C',0);
		$maxX +=95;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(95,12,'>= 75% und < 88% = 2',1,'C',0);
		$maxX +=95;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(95,12,'>= 88% = 1',1,'C',0);
		$maxY=$pdf->GetY();
		$maxX=85;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(470,12,'Liegt die Punkteanzahl bei einem Kriterium unter 50%, ist die Bachelorarbeit insgesamt als negativ zu beurteilen.','LB','L',0);
		$maxX +=315;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(80,12,'','TB','C',0);
		$maxX +=80;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(80,12,'','RB','C',0);
		
		//Zeile Note und Unterschrift
		$pdf->SetFont('Arial','',11);
		$maxY+=25;
		$maxX=+30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(80,12,'Note: '.$note,0,'L',0);
		$maxX=+50;
		$maxY=$pdf->GetY();
		$pdf->SetXY($maxX,$maxY);
		$maxX +=300;
		$maxY +=80;
		$pdf->SetFont('Arial','',10);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(240,12,"_________________________________",0,'C',0);
		$maxY += 12;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(240,12,"Unterschrift",0,'C',0);
		$maxY=$pdf->GetY();
		$pdf->footerset[1]=1;
		$pdf->Output('Beurteilung_'.$nachname_clean.'.pdf','D');
		
	}
	else //diplomarbeit
	{	
		$pdf = new PDF('P','pt');
		$pdf->Open();
		$pdf->AddPage();
		$pdf->AliasNbPages();
		
		$pdf->SetFillColor(111,111,111);
		$pdf->SetXY(30,30);
	
		//Logo
		$pdf->Image("../../skin/styles/".DEFAULT_STYLE."/logo.jpg","400","25","150","78","jpg","");
		$pdf->SetFont('Arial','',12);
		$pdf->SetFillColor(190,190,190);
		$pdf->SetXY(30,110-$titelabzug);
		$pdf->SetFont('Arial','',10);
		$pdf->MultiCell(0,15,'Studiengang: ');
		$pdf->SetXY(30,125-$titelabzug);
		$pdf->SetFont('Arial','',12);
		$pdf->MultiCell(0,15,$stgtyp.'studiengang '.$studiengang);
		$pdf->SetFont('Arial','',14);
		$pdf->SetXY(30,150-$titelabzug);
		$pdf->MultiCell(0,15,'Beurteilung Masterarbeit - 1. BegutachterIn');

		$qry_beu="SELECT * FROM public.tbl_person JOIN public.tbl_benutzer using(person_id) WHERE uid=".$db->db_add_param($getuid).";";
		if(!$erg_beu=@$db->db_query($qry_beu))
		{
			die('Fehler beim Laden des Betreuernamens');
		}
		else
		{
			if($row_beu=$db->db_fetch_object($erg_beu))
			{
				// UTF-8 encoden
				while (list($key, $value) = each($row_beu)) 
				{
					if (!empty($value))
				    	$row_beu->$key=mb_convert_encoding(trim($value),'ISO-8859-15','UTF-8');
				}
				$beurteiler=trim($row_beu->titelpre.' '.$row_beu->vorname.' '.$row_beu->nachname.' '.$row_beu->titelpost);
			}
			else 
			{
				die('Betreuer nicht gefunden!');
			}
		}
		//Zeile Titel
		 
		$pdf->SetFont('Arial','',10);
		$maxY=$pdf->GetY()+4;
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(80,15*$zeilen,'Titel',1,'L',0);
		$maxX +=80;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(450-$titelabzug,15,$titel,0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(450,15*$zeilen,'',1,'L',0);
		
		//Autor
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(80,18,'Autor',1,'L',0);
		$maxX +=80;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(291,18,trim($titelpre." ".$autor." ".$titelpost),1,'L',0);
		$maxX +=291;
		$pdf->SetXY($maxX,$maxY);
		$pdf->SetFont('Arial','',9);
		$pdf->MultiCell(159,18,trim('Personenkennzeichen: '),1,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,18,trim($perskz),0,'R',0);
		
		//Zeile Beurteilt von
		$maxY=$pdf->GetY();
		$pdf->SetFont('Arial','',10);
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(80,18,'Beurteilt von',1,'L',0);
		$maxX +=80;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(291,18,$beurteiler,1,'L',0);
		$maxX +=291;
		$pdf->SetFont('Arial','',9);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,18,'Datum (dd.mm.yyyy): ',1,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(159,18,date('d.m.Y',mktime(0, 0, 0, date("m")  , date("d"), date("Y"))),1,'R',0);
		
		//Feld Beurteilung
		//Zeile Überschrift
		$maxY=$pdf->GetY()+3;
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(170,36,'',1,'L',0);
		$maxX +=170;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(201,36,'Kurze schriftliche Beurteilung',1,'C',0);
		$maxX +=201;
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
		$pdf->MultiCell(53,12,'    Punkte             x          Gewicht',0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,36,'',1,'L',0);
		
		//Zeile Qualität
		$pdf->SetFont('Arial','B',9);
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		//nur fettgedruckter Text
		$pdf->MultiCell(170,12,mb_convert_encoding("Qualität des eigenen Beitrags\nAngewandte Methodik, z.B.\n\n\n\n\n\n Art der Problemlösung \n\n\n\n\n",'ISO-8859-15','UTF-8'),0,'L',0);
		$pdf->SetFont('Arial','',9);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(170,12,mb_convert_encoding("\n\n - Projektmäßige Vorgangsweise\n - Wissenschaftlich - systematische\n   Methoden in der Analyse bzw.\n   Lösungsfindung\n\n\n - Wurde das Problem tatsächlich gelöst?\n - Eigenständigkeit & Kreativität der\n   Lösung \n - Ist der eigene Beitrag deutlich sichtbar\n - Technische Qualität der Lösung",'ISO-8859-15','UTF-8'),0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(170,160,'',1,'L',0);
		$maxX +=170;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(201,12,$qualitaet,0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(201,160,'',1,'L',0);
		$maxX +=201;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkte1,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,160,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,'0.55',0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,160,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkteges1,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,160,'',1,'L',0);	
		
		//Zeile Form
		$pdf->SetFont('Arial','B',9);
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		//nur fettgedruckter Text
		$pdf->MultiCell(170,12,mb_convert_encoding("Form / Stil\n\n\n\n\n\n\n",'ISO-8859-15','UTF-8'),0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->SetFont('Arial','',9);
		$pdf->MultiCell(170,12,mb_convert_encoding("\n - Hat die Masterarbeit eine klare\n   Struktur, entspricht der Vorgabe\n - Wird einwandfrei zitiert?\n - Abbildungen?\n - Sprache: benötigte Überarbeitung\n   seitens seitens des Betreuers/ der\n   Betreuerin",'ISO-8859-15','UTF-8'),0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(170,100,'',1,'L',0);
		$maxX +=170;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(201,12,$form,0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(201,100,'',1,'L',0);
		$maxX +=201;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkte2,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,100,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,'0.20',0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,100,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkteges2,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,100,'',1,'L',0);

		//Zeile Hintergrundinfo
		$pdf->SetFont('Arial','B',9	);	
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		//nur fettgedruckter Text
		$pdf->MultiCell(170,12,mb_convert_encoding("Qualität der Hintergrundinformation\n\n\n\n\n\n\n\n\n\n\n",'ISO-8859-15','UTF-8'),0,'L',0);
		$pdf->SetFont('Arial','',9	);	
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(170,12,mb_convert_encoding("\n - werden Gesamtzusammenhänge\n   erkannt, wird Bedeutung und Gewicht\n   der Einflussfaktoren /Daten/\n   Informationen richtig bewertet?\n - Intelligente Darstellung des relevanten\n   Stands der Technik und des\n   Firmenumfelds\n - Aufdecken und Darstellen von\n   größeren (z.B. wirtschaftlichen oder\n   sozialen) Zusammenhängen und\n   entsprechende Diskussion",'ISO-8859-15','UTF-8'),0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(170,150,'',1,'L',0);
		$maxX +=170;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(201,12,$hintergrund,0,'L',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(201,150,'',1,'L',0);
		$maxX +=201;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkte3,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,150,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,'0.25',0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,150,'',1,'L',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,12,$punkteges3,0,'C',0);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,150,'',1,'L',0);

		//Zeile Gesamtpunkte
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->SetFont('Arial','B',9	);
		$pdf->MultiCell(371,17,'Gesamtpunkte',1,'R',0);
		$pdf->SetFont('Arial','',9	);
		$maxX +=371;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,17," ",1,'C',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,17,' ',1,'C',0);
		$maxX +=53;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(53,17,$summe2,1,'C',0);
		
		//Feld Umrechnung Punkte=>Note
		$maxY=697;
		$maxX=30;
		$pdf->SetFont('Arial','',10);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(80,12,'Kriterien:');
		$pdf->SetFont('Arial','',8);
		$maxX +=55;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(95,12,'< 50% = 5',1,'C',0);
		$maxX +=95;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(95,12,'>= 50% und < 63% = 4',1,'C',0);
		$maxX +=95;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(95,12,'>= 63% und < 75% = 3',1,'C',0);
		$maxX +=95;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(95,12,'>= 75% und < 88% = 2',1,'C',0);
		$maxX +=95;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(95,12,'>= 88% = 1',1,'C',0);
		$maxY=$pdf->GetY();
		$maxX=85;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(470,12,'Liegt die Punkteanzahl bei einem Kriterium unter 50%, ist die Masterarbeit insgesamt als negativ zu beurteilen.','LB','L',0);	
		$maxX +=315;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(80,12,'','TB','C',0);
		$maxX +=80;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(80,12,'','RB','C',0);
		
		//Zeile Note und Unterschrift
		$pdf->SetFont('Arial','',10);
		$maxY+=20;
		$maxX = 30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(450,12,'Das Gutachten des/der 2. Gutachters/Gutachterin liegt vor und ist in die Benotung miteinbezogen.','0','L',0);
		$maxY+=17;
		$maxX=+30;
		$pdf->SetFont('Arial','',11);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(80,12,'Note: '.$note,0,'L',0);
		$maxX=+40;
		$maxY=$pdf->GetY();
		$pdf->SetXY($maxX,$maxY);
		$maxX +=300;
		$maxY +=4;
		$pdf->SetFont('Arial','',10);
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(240,12,"_________________________________",0,'C',0);
		$maxY += 11;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(240,12,"Unterschrift",0,'C',0);
		$maxY=$pdf->GetY();
		$pdf->footerset[1]=1;
		$pdf->Output('Beurteilung_'.$nachname_clean.'.pdf','D');
	}
	exit();
}



$sql_query = "SELECT *,(SELECT abgabedatum FROM campus.tbl_paabgabe WHERE projektarbeit_id=".$db->db_add_param($projektarbeit_id, FHC_INTEGER)." AND abgabedatum is NOT NULL ORDER BY abgabedatum DESC LIMIT 1) as abgabedatum FROM (SELECT DISTINCT ON(tbl_projektarbeit.projektarbeit_id) tbl_studiengang.bezeichnung as stgbezeichnung, tbl_studiengang.typ as stgtyp, * 
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
							AND public.tbl_benutzer.uid=".$db->db_add_param($getuid).")
	AND lehre.tbl_projektarbeit.note IS NULL 
	AND lehre.tbl_projektarbeit.projektarbeit_id=".$db->db_add_param($projektarbeit_id, FHC_INTEGER)."
	ORDER BY tbl_projektarbeit.projektarbeit_id, betreuerart_kurzbz desc) as xy 
	ORDER BY nachname";

if(!$erg=$db->db_query($sql_query))
{
	die('Fehler beim Laden der Betreuungen');
}
else
{
	if($row=$db->db_fetch_object($erg))
	{
			// UTF-8 encoden

		echo '
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
		<html>
		<head>
			<title>DA/BA-Benotung</title>
			<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
			<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
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
				if(document.getElementById("punkte1").value>100 ||
				   document.getElementById("punkte2").value>100 ||
				   document.getElementById("punkte3").value>100)
				{
					alert("Es duerfen pro Kategorie maximal 100 Punkte vergeben werden!");
					return false;
				}
				if(document.getElementById("summe2").value=="NaN")
				{
					alert("Eingabe ung&uuml;ltig! Bitte nur Ziffern eingeben.");
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
					ergebnis=parseFloat(document.getElementById("punkte1").value)+parseFloat(document.getElementById("punkte2").value)+parseFloat(document.getElementById("punkte3").value);';
					//echo 'document.getElementById("summe1").value=ergebnis.toFixed(1);';
					echo 'ergebnis=parseFloat(document.getElementById("punkteges1").value)+parseFloat(document.getElementById("punkteges2").value)+parseFloat(document.getElementById("punkteges3").value);
					document.getElementById("summe2").value=ergebnis.toFixed(2);
					if(document.getElementById("punkte1").value<50 || document.getElementById("punkte2").value<50 || document.getElementById("punkte3").value<50)
					{
						ergebnis=5;
					}
					else
					{
						if(document.getElementById("summe2").value<50)
						{
							ergebnis=5;
						}
						else if(document.getElementById("summe2").value<63)
						{
							ergebnis=4;
						}
						else if(document.getElementById("summe2").value<75)
						{
							ergebnis=3;
						}
						else if(document.getElementById("summe2").value<88)
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
					ergebnis=parseFloat(document.getElementById("punkte1").value)+parseFloat(document.getElementById("punkte2").value);';
					//echo 'document.getElementById("summe1").value=ergebnis.toFixed(1);';
					echo 'ergebnis=parseFloat(document.getElementById("punkteges1").value)+parseFloat(document.getElementById("punkteges2").value);
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
						else if(document.getElementById("summe2").value<63)
						{
							ergebnis=4;
						}
						else if(document.getElementById("summe2").value<75)
						{
							ergebnis=3;
						}
						else if(document.getElementById("summe2").value<88)
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
		$htmlstr .= '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="POST" onsubmit="return inputcheck()">';
		$htmlstr .= "<tr><td style='font-size:16px' colspan='5'>Student: <b>".$row->matrikelnr.", ".trim($row->titelpre." ".$row->vorname." ".$row->nachname." ".$row->titelpost)."</b></td>";
		$htmlstr .= "<tr><td style='font-size:16px' colspan='5'>Titel: <b>".$db->convert_html_chars($titel)."</b>";
		$htmlstr .= '<input type="hidden" name="projektarbeit_id" value="'.$db->convert_html_chars($projektarbeit_id).'">';
		$htmlstr .= '<input type="hidden" name="uid" value="'.$db->convert_html_chars($uid).'">';
		$htmlstr .= '<input type="hidden" name="matrikelnr" value="'.$db->convert_html_chars($matrikelnr).'">';
		$htmlstr .= '<input type="hidden" name="titel" value="'.$db->convert_html_chars($titel).'">';
		$htmlstr .= '<input type="hidden" name="perskz" value="'.$db->convert_html_chars($row->matrikelnr).'">';
		$htmlstr .= '<input type="hidden" name="studiengang" value="'.$db->convert_html_chars($row->stgbezeichnung).'">';
		$htmlstr .= '<input type="hidden" name="titelpre" value="'.$db->convert_html_chars($row->titelpre).'">';
		$htmlstr .= '<input type="hidden" name="titelpost" value="'.$db->convert_html_chars($row->titelpost).'">';
		$htmlstr .= '<input type="hidden" name="autor" value="'.$db->convert_html_chars($row->vorname." ".$row->nachname).'">';
		$htmlstr .= '<input type="hidden" name="ende" value="'.$db->convert_html_chars($row->abgabedatum).'">';

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
		$htmlstr .= '<input type="hidden" name="stgtyp" value="'.$db->convert_html_chars($stgtyp).'">';
		$htmlstr .= '<input type="hidden" name="projekttyp_kurzbz" value="'.$db->convert_html_chars($row->projekttyp_kurzbz).'">';
		$htmlstr .= '</td></tr>';
		$htmlstr .= '<td>&nbsp;</td><td align="center">Kurze schriftliche Beurteilung</td><td align="center">Punkte (0-100)</td><td align="center">Gewicht</td><td align="center">Punkte x Gewicht</td>';
		$htmlstr .= '<tr>';
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
		$htmlstr .= '<td width="30%"><textarea name="qualitaet" value="'.$db->convert_html_chars($qualitaet).'" cols="50" rows="10" 
		onKeyDown="txtcount(this.form.qualitaet,this.form.remLen,500);" onKeyUp="txtcount(this.form.qualitaet,this.form.remLen,500);"></textarea>
		<br>Buchstaben noch zur Verf&uuml;gung<input readonly disabled type=text name=remLen size=3 maxlength=3 value="500" style="text-align:right"> </td>
		<td width="10%" align="center"><input type="hidden" name="weight" id="weight1" value="'.$db->convert_html_chars($weight1).'">
		<input type="text" name="punkte1" value="'.$db->convert_html_chars($punkte1).'" size="5" maxlength="5" id="punkte1" onkeyup="berechne()" style="text-align:right"></td>';
		if($row->projekttyp_kurzbz!='Bachelor')
		{
			$htmlstr.='<td width="10%" align="center">0.55</td>';
		}
		else 
		{
			$htmlstr.="<td width='10%' align='center'>0.60</td>";
		}
		$htmlstr .='<td width="10%" align="center"><input type="text" name="punkteges1" value="'.$db->convert_html_chars($punkteges1).'" id="punkteges1" style="text-align:right" size="5" maxlength="5" readonly></td></tr>';
		if($row->projekttyp_kurzbz!='Bachelor')
		{
			$htmlstr .= '<td width="40%"><b>Form / Stil</b><br>
			Hat die Masterarbeit eine klare Stuktur, entspricht der Vorgabe?<br>
			Wird einwandfrei zitiert?<br>
			Abbildungen<br>
			Sprache: ben&ouml;tigte &Uuml;berarbeitungen seitens der Betreuerin / des Betreuers</td>
			<td width="30%"><textarea name="form" value="'.$db->convert_html_chars($form).'" cols="50" rows="10" 
			onKeyDown="txtcount(this.form.form,this.form.remLen2,500);" onKeyUp="txtcount(this.form.form,this.form.remLen2,500);"></textarea>
			<br>Buchstaben noch zur Verf&uuml;gung<input readonly disabled type=text name=remLen2 size=3 maxlength=3 value="500" style="text-align:right"></td>';
			$weight2='0.2';
		}
		else 
		{
			$htmlstr .= '<td width="40%"><b>Form / Stil</b><br>
			Hat die Bachelorarbeit eine klare Stuktur, entspricht der Vorgabe?<br>
			Wird einwandfrei zitiert?<br>
			Abbildungen<br>
			Sprache</td>
			<td width="30%"><textarea name="form" value="'.$db->convert_html_chars($form).'" cols="50" rows="10"
			onKeyDown="txtcount(this.form.form,this.form.remLen2,500);" onKeyUp="txtcount(this.form.form,this.form.remLen2,500);"></textarea>
			<br>Buchstaben noch zur Verf&uuml;gung<input readonly disabled type=text name=remLen2 size=3 maxlength=3 value="500" style="text-align:right"></td>';
			$weight2='0.4';
		}
		$htmlstr .= '<td width="10%" align="center"><input type="hidden" name="weight" id="weight2" value="'.$db->convert_html_chars($weight2).'">
		<input  type="text" name="punkte2" value="'.$db->convert_html_chars($punkte2).'" size="5" maxlength="5" id="punkte2" onkeyup="berechne()" style="text-align:right"></td>';
		if($row->projekttyp_kurzbz!='Bachelor')
		{
			$htmlstr .="<td width='10%' align='center'>0.20</td>";
		}
		else
		{
			$htmlstr .="<td width='10%' align='center'>0.40</td>";
		}
		$htmlstr .='<td width="10%" align="center"><input type="text" name="punkteges2" value="'.$db->convert_html_chars($punkteges2).'" style="text-align:right" size="5" maxlegnth="3" id="punkteges2" readonly></td></tr>';
		if($row->projekttyp_kurzbz!='Bachelor')
		{
			$htmlstr .= '<td width="40%"><b>Qualit&auml;t der Hintergrundinformation</b><br>
			Werden Gesamtzusammenhänge erkannt, wird Bedeutung und Gewicht der Einflussfaktoren / Daten / Informationen richtig bewertet?<br>
			Intelligente Darstellung des relevanten Stands der Technik und des Firmenumfelds<br>
			Aufdecken und Darstellen von gr&ouml;&szlig;eren (z.B. wirtschaftlichen und sozialen) Zusammenh&auml;ngen und entsprechende Diskussion</td>
			<td width="30%"><textarea name="hintergrund" value="'.$db->convert_html_chars($hintergrund).'" cols="50" rows="10" 
			onKeyDown="txtcount(this.form.hintergrund,this.form.remLen3,500);" onKeyUp="txtcount(this.form.hintergrund,this.form.remLen3,500);"></textarea>
			<br>Buchstaben noch zur Verf&uuml;gung<input readonly disabled type=text name=remLen3 size=3 maxlength=3 value="500" style="text-align:right"></td>
			<td width="10%" align="center"><input type="hidden" name="weight" id="weight3" value="0.25">
			<input  type="text" name="punkte3" value="'.$db->convert_html_chars($punkte3).'" size="5" maxlength="5" id="punkte3" style="text-align:right" onkeyup="berechne()"></td>
			<td width="10%" align="center">0.25</td>
			<td width="10%" align="center"><input type="text" name="punkteges3" value="'.$db->convert_html_chars($punkteges3).'" id="punkteges3" style="text-align:right" size="5" maxlength="5" readonly></td></tr>';
		}
		else 
		{
			$htmlstr .= "
			<input  type='hidden' name='punkte3' value='0' id='punkte3'></td>\n
			<input  type='hidden' name='punkteges3' value='0' id='punkteges3'></td>\n
			<input  type='hidden' name='weight3' value='0' id='weight3'></td>\n";
		}
		$htmlstr .= "<td colspan='2'>Gesamtpunkte</td>";
		//$htmlstr .="<td align='center'><input  type='text' name='summe1' value='".$summe1."' id='summe1' style='text-align:right' size='5' maxlength='5' readonly ></td>
		$htmlstr .="<td align='center'>&nbsp;</td>
			<td align='center'>&nbsp;</td>";
		$htmlstr .= '<td align="center"><input type="text" name="summe2" value="'.$db->convert_html_chars($summe2).'" id="summe2" style="text-align:right" size="5" maxlength="5" readonly></td><tr>';
		$htmlstr .= '<td colspan="4">Note</td><td align="center"><input type="text" name="note" value="'.$db->convert_html_chars($note).'" id="note" style="text-align:right" size="5" maxlength="5" readonly></td></tr>';
		$htmlstr .="</table>";
				
		$htmlstr .= "<br><table border='1' align='center' width='70%'>";
		$htmlstr .= "<tr><td>&lt; 50% <b>Nicht genügend</b></td><td>&gt;= 50% und &lt;63% <b>Genügend</b></td><td>&gt;= 63% und &lt; 75% <b>Befriedigend</b></td><td>&gt;= 75% und &lt; 88% <b>Gut</b></td><td>&gt;= 88% <b>Sehr Gut</b></td></tr>";
		if($row->projekttyp_kurzbz!='Bachelor')
		{
			$htmlstr .= "<tr><td colspan='5'>Liegt die Punkteanzahl bei einem Kriterium unter 50%, ist die Masterarbeit insgesamt als negativ zu beurteilen.</td></tr>";
		}
		else 
		{
			$htmlstr .= "<tr><td colspan='5'>Liegt die Punkteanzahl bei einem Kriterium unter 50%, ist die Bachelorarbeit insgesamt als negativ zu beurteilen.</td></tr>";
		}
		$htmlstr .= "</table>";
		if($row->projekttyp_kurzbz!='Bachelor')
		{
			$htmlstr.="<br><center>Das Gutachten des/der 2. Gutachters/Gutachterin liegt vor und ist in die Benotung miteinbezogen</center><br>";
		}
		 
		//$htmlstr .= "<br><input type='submit' name='drucken' value='Formular ausdrucken' id='drucken' onclick='this.disabled=true;'>";
		$htmlstr .= "<br><center><input type='submit' name='drucken' value='Formular ausdrucken' id='drucken'></center>";
		$htmlstr.="<br>Bitte klicken Sie auf den Button 'Formular ausdrucken' um das Benotungsformular zu erstellen. Das ausgedruckte, unterschriebene Formular ist im jeweiligen Sekretariat abzugeben.";
		$htmlstr .="</form><br><br><br><br><br><br>";
		$htmlstr .="</body></html>";
		echo $htmlstr;
		echo '<script type="text/javascript">
		<!--
			initInputHighlightScript();
		//-->
		</script>';
	}
	else 
	{
		die('Betreuung nicht gefunden!');
	}	
}
?>
