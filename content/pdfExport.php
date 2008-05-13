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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/* Erstellt einen Lehrauftrag im PDF Format
 *
 * Erstellt ein XML File Transformiert dieses mit
 * Hilfe der XSL-FO Vorlage aus der DB und generiert
 * daraus ein PDF (xslfo2pdf)
 */
require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/xslfo2pdf/xslfo2pdf.php');
require_once('../include/akte.class.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$user = get_uid();
loadVariables($conn, $user);

function clean_string($string)
 {
 	$trans = array("ä" => "ae",
 				   "Ä" => "Ae",
 				   "ö" => "oe",
 				   "Ö" => "Oe",
 				   "ü" => "ue",
 				   "Ü" => "Ue",
 				   "á" => "a",
 				   "à" => "a",
 				   "é" => "e",
 				   "è" => "e",
 				   "ó" => "o",
 				   "ò" => "o",
 				   "í" => "i",
 				   "ì" => "i",
 				   "ù" => "u",
 				   "ú" => "u",
 				   "ß" => "ss");
	$string = strtr($string, $trans);
    return ereg_replace("[^a-zA-Z0-9]", "", $string);
    //[:space:]
 }

//Parameter holen
if(isset($_GET['xml']))
	$xml=$_GET['xml'];
else
	die('Fehlerhafte Parameteruebergabe');
if(isset($_GET['xsl']))
	$xsl=$_GET['xsl'];
else
	die('Fehlerhafte Parameteruebergabe');
if(isset($_GET['xsl_stg_kz']))
	$xsl_stg_kz=$_GET['xsl_stg_kz'];
else
	$xsl_stg_kz=0;

//Parameter setzen
$params='?xmlformat=xml';
if(isset($_GET['uid']))
	$params.='&uid='.$_GET['uid'];
if(isset($_GET['person_id']))
	$params.='&person_id='.$_GET['person_id'];
if(isset($_GET['prestudent_id']))
	$params.='&prestudent_id='.$_GET['prestudent_id'];
if(isset($_GET['buchungsnummern']))
	$params.='&buchungsnummern='.$_GET['buchungsnummern'];
if(isset($_GET['stg_kz']))
	$params.='&stg_kz='.$_GET['stg_kz'];
if(isset($_GET['ss']))
	$params.='&ss='.$_GET['ss'];
if(isset($_GET['abschlusspruefung_id']))
	$params.='&abschlusspruefung_id='.$_GET['abschlusspruefung_id'];
if(isset($_GET['typ']))
	$params.='&typ='.$_GET['typ'];
if(isset($_GET['all']))
	$params.='&all='.$_GET['all'];
if(isset($_GET["lvid"]))
	$params.='&lvid='.$_GET["lvid"];

if($xsl=='AccountInfo')
{
	$isberechtigt = false;
	$rechte = new benutzerberechtigung($conn);
	$rechte->getBerechtigungen($user);

	$uids = explode(';',$_GET['uid']);
	foreach ($uids as $uid)
	{
		//Berechtigung fuer das Drucken des Accountinfoblattes pruefen
		$qry = "SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid='".$uid."'";
		if($result_ma = pg_query($conn, $qry))
		{
			if(pg_num_rows($result_ma)==1)
			{
				//Mitarbeiterrechte erforderlich
				if($rechte->isBerechtigt('admin', 0, 'suid') || $rechte->isBerechtigt('mitarbeiter', 0, 'suid'))
				{
					$isberechtigt=true;
				}
			}
		}

		$qry = "SELECT student_uid, studiengang_kz FROM public.tbl_student WHERE student_uid='".$uid."'";
		if($result_std = pg_query($conn, $qry))
		{
			if(pg_num_rows($result_std)==1)
			{
				$row_std = pg_fetch_object($result_std);
				//Rechte pruefen
				if($rechte->isBerechtigt('admin', $row_std->studiengang_kz, 'suid') ||
				   $rechte->isBerechtigt('admin', 0, 'suid') ||
				   $rechte->isBerechtigt('assistenz', $row_std->studiengang_kz, 'suid') ||
				   $rechte->isBerechtigt('assistenz', 0, 'suid') ||
				   $rechte->isBerechtigt('support', 0, 'suid'))
				{
					$isberechtigt=true;
				}
			}
		}
	}

	if(!$isberechtigt)
	{
		echo 'Sie haben keine Berechtigung um dieses AccountInfoBlatt zu drucken';
		exit;
	}
}

//Berechtigung pruefen
$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

//if(!$rechte->isBerechtigt('admin',$stg_kz))
//	die("Keine Berechtigung");

$xml_url=XML_ROOT.$xml.$params;
//echo $xml_url;
// Load the XML source
$xml_doc = new DOMDocument;

if(!$xml_doc->load($xml_url))
	die('unable to load xml');
//echo 'XML:'.$xml_doc->saveXML().':';

//XSL aus der DB holen
$qry = "SELECT text FROM public.tbl_vorlagestudiengang WHERE (studiengang_kz=0 OR studiengang_kz='".addslashes($xsl_stg_kz)."') AND vorlage_kurzbz='$xsl' ORDER BY studiengang_kz DESC, version DESC LIMIT 1";
//echo $qry;
if(!$result = pg_query($conn, $qry))
	die('Fehler beim Laden der Vorlage'.pg_errormessage($conn));
if(!$row = pg_fetch_object($result))
	die('Vorlage wurde nicht gefunden'.$qry);

// Load the XSL source
$xsl_doc = new DOMDocument;
//if(!$xsl_doc->load('../../../../xsl/collection.xsl'))
if(!$xsl_doc->loadXML($row->text))
	die('unable to load xsl');
//echo 'XSL:'.$xsl_doc->saveXML().':';


// Configure the transformer
$proc = new XSLTProcessor;
$proc->importStyleSheet($xsl_doc); // attach the xsl rules

$buffer = $proc->transformToXml($xml_doc);
//in $buffer steht nun das xsl-fo file mit den daten
/*$buffer = '<?xml version="1.0" encoding="ISO-8859-15" ?>'.substr($buffer, strpos($buffer,"\n"),strlen($buffer));*/
//$buffer = html_entity_decode($buffer);
//echo "XSL-FO: $buffer";

//Pdf erstellen
$fo2pdf = new XslFo2Pdf();

//wenn uid gefunden wird, dann den Nachnamen zum Dateinamen dazuhaengen
$nachname='';
if(isset($_GET['uid']) && $_GET['uid']!='')
{
	$uid = str_replace(';','',$_GET['uid']);
	$qry = "SELECT nachname FROM campus.vw_benutzer WHERE uid='".addslashes($uid)."'";

	if($result = pg_query($conn, $qry))
	{
		if($row = pg_fetch_object($result))
		{
			$nachname = '_'.clean_string($row->nachname);
		}
	}
}
$filename=$xsl.$nachname;

if (!isset($_REQUEST["archive"]))
{
 if (!$fo2pdf->generatePdf($buffer, $filename, "D"))
 {
     echo('Failed to generate PDF');
 }
}
else
{

	$uid = $_REQUEST["uid"];
	$ss = $_REQUEST["ss"];
	$heute = date('Y-m-d');
	$query = "SELECT tbl_studiengang.studiengang_kz, tbl_studentlehrverband.semester, tbl_studiengang.typ, tbl_studiengang.kurzbz, tbl_person.person_id FROM tbl_person, tbl_benutzer, tbl_studentlehrverband, tbl_studiengang where tbl_studentlehrverband.student_uid = tbl_benutzer.uid and tbl_benutzer.person_id = tbl_person.person_id and tbl_studentlehrverband.studiengang_kz = tbl_studiengang.studiengang_kz and tbl_studentlehrverband.student_uid = '".$uid."' and tbl_studentlehrverband.studiensemester_kurzbz = '".$ss."'";

	if($result = pg_query($conn, $query))
	{
		if($row = pg_fetch_object($result))
		{
			$person_id = $row->person_id;
			$titel = "Zeugnis_".strtoupper($row->typ).strtoupper($row->kurzbz)."_".$row->semester;
			$bezeichnung = "Zeugnis ".strtoupper($row->typ).strtoupper($row->kurzbz)." ".$row->semester.". Semester";
			$studiengang_kz = $row->studiengang_kz;
		}
		else
		{
			$echo = 'Datensatz wurde nicht gefunden';

		}
	}

	if($rechte->isBerechtigt('admin', $studiengang_kz, 'suid') || $rechte->isBerechtigt('assistenz', $studiengang_kz, 'suid'))
	{

		$filename = $user;
		if (!$fo2pdf->generatePdf($buffer, $filename, 'F'))
		{
			echo('Failed to generate PDF');
		}
		$file = "/tmp/".$filename.".pdf";
		$handle = fopen($file, "rb");
		$string = fread($handle, filesize($file));
		fclose($handle);
		unlink($file);

		$hex="";
		for ($i=0;$i<strlen($string);$i++)
			$hex.=(strlen(dechex(ord($string[$i])))<2)? "0".dechex(ord($string[$i])): dechex(ord($string[$i]));

		$akte = new akte($conn);
		$akte->person_id = $person_id;
	  	$akte->dokument_kurzbz = "Zeugnis";
	  	$akte->inhalt = $hex;
	  	$akte->mimetype = "application/octet-stream";
	  	$akte->erstelltam = $heute;
	  	$akte->gedruckt = true;
	  	$akte->titel = $titel.".pdf";
	  	$akte->bezeichnung = $bezeichnung;
	  	$akte->updateamum = "";
	  	$akte->updatevon = "";
		$akte->insertamum = date('Y-m-d h:m:s');
		$akte->insertvon = $user;
	  	$akte->ext_id = "";
	  	$akte->uid = $uid;
		$akte->new = true;
		if (!$akte->save())
			return true;
		else
			return false;
	}
	else
		echo 'Keine Berechtigung zum Speichern';
}
?>