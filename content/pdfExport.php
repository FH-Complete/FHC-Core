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
session_cache_limiter('none'); //muss gesetzt werden sonst funktioniert der Download mit IE8 nicht
session_start();
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/xslfo2pdf/xslfo2pdf.php');
require_once('../include/fop.class.php');
require_once('../include/akte.class.php');
require_once('../include/vorlage.class.php');
require_once('../include/student.class.php');
require_once('../include/prestudent.class.php');

$user = get_uid();
$db = new basis_db();
loadVariables($user);

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
    return mb_ereg_replace("[^a-zA-Z0-9]", "", $string);
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

$xsl_stg_kz=0;
if(isset($_GET['xsl_stg_kz']))
	$xsl_stg_kz=$_GET['xsl_stg_kz'];
else
	if(isset($_GET['stg_kz']))
		$xsl_stg_kz=$_GET['stg_kz'];
	else
		if(isset($_GET['uid']) && $_GET['uid']!='')
		{
			if(strstr(';',$_GET['uid']))
				$uids = explode(';',$_GET['uid']);
			else 
				$uids = $_GET['uid'];
			//var_dump($uids);
			$qry = "SELECT student_uid, studiengang_kz FROM public.tbl_student WHERE student_uid='".addslashes($uids[1])."'";
			if($result_std = $db->db_query($qry))
				if($db->db_num_rows($result_std)==1)
				{
					$row_std = $db->db_fetch_object($result_std);
					$xsl_stg_kz=$row_std->studiengang_kz;
				}
		}

if(isset($_GET['xsl_oe_kurzbz']))
	$xsl_oe_kurzbz=$_GET['xsl_oe_kurzbz'];
else
	$xsl_oe_kurzbz='';

//Parameter setzen
$params='?xmlformat=xml';
if(isset($_GET['uid']))
	$params.='&uid='.$_GET['uid'];
if(isseT($_GET['stg_kz']))
	$params.='&stg_kz='.$_GET['stg_kz']; 
if(isset($_GET['person_id']))
	$params.='&person_id='.$_GET['person_id'];
if(isset($_GET['id']))
	$params.='&id='.$_GET['id'];
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
if(isset($_GET['preoutgoing_id']))
    $params.='&preoutgoing_id='.$_GET['preoutgoing_id'];
if(isset($_GET["lvid"]))
	$params.='&lvid='.$_GET["lvid"];
if(isset($_GET['projekt_kurzbz']))
	$params.='&projekt_kurzbz='.$_GET['projekt_kurzbz'];
if(isset($_GET['version']) && is_numeric($_GET['version']))
	$version = $_GET['version'];
else 
	$version ='';

$output = (isset($_GET['output'])?$_GET['output']:'odt');

if($xsl=='AccountInfo')
{
	$isberechtigt = false;
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	$uids = explode(';',$_GET['uid']);
	foreach ($uids as $uid)
	{
		//Berechtigung fuer das Drucken des Accountinfoblattes pruefen
		$qry = "SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid='".addslashes($uid)."'";
		if($result_ma = $db->db_query($qry))
		{
			if($db->db_num_rows($result_ma)==1)
			{
				//Mitarbeiterrechte erforderlich
				if($rechte->isBerechtigt('admin', 0, 'suid') || $rechte->isBerechtigt('mitarbeiter', 0, 'suid'))
				{
					$isberechtigt=true;
				}
			}
		}

		$qry = "SELECT student_uid, studiengang_kz FROM public.tbl_student WHERE student_uid='".addslashes($uid)."'";
		if($result_std = $db->db_query($qry))
		{
			if($db->db_num_rows($result_std)==1)
			{
				$row_std = $db->db_fetch_object($result_std);
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
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$xml_url=XML_ROOT.$xml.$params;

// Load the XML source
$xml_doc = new DOMDocument;

if(!$xml_doc->load($xml_url))
	die('unable to load xml: '.$xml_url);

//XSL aus der DB holen
$vorlage = new vorlage();
if($xsl_oe_kurzbz!='')
{
	$vorlage->getAktuelleVorlage($xsl_oe_kurzbz, $xsl, $version);
}
else
{
	if($xsl_stg_kz=='')
		$xsl_stg_kz='0';

	$vorlage->getAktuelleVorlage($xsl_stg_kz, $xsl, $version);
}
//$qry = "SELECT text FROM public.tbl_vorlagestudiengang WHERE (studiengang_kz=0";
//if($xsl_stg_kz!='')
//$qry.=" OR studiengang_kz='".addslashes($xsl_stg_kz)."'";
//$qry.=") AND vorlage_kurzbz='$xsl'";
//if(isset($version) && $version!='')
//	$qry.=" AND version='$version'"; 
//$qry.=" ORDER BY studiengang_kz DESC, version DESC LIMIT 1";
//echo $qry;
//if(!$result = $db->db_query($qry))
//	die('Fehler beim Laden der Vorlage'.$db->db_last_error());
//if(!$row = $db->db_fetch_object($result))
//	die('Vorlage wurde nicht gefunden'.$qry);

$xsl_content = $vorlage->text;

//Pdf erstellen

//wenn uid gefunden wird, dann den Nachnamen zum Dateinamen dazuhaengen
$nachname='';
if(isset($_GET['uid']) && $_GET['uid']!='')
{
	$uid = str_replace(';','',$_GET['uid']);
	$qry = "SELECT nachname FROM campus.vw_benutzer WHERE uid='".addslashes($uid)."'";

	if($result = $db->db_query($qry))
	{
		if($row = $db->db_fetch_object($result))
		{
			$nachname = '_'.clean_string($row->nachname);
		}
	}
}
$filename=$xsl.$nachname;

if (!isset($_REQUEST["archive"]))
{ 
	if(mb_strstr($vorlage->mimetype, 'application/vnd.oasis.opendocument'))
	{
		switch($vorlage->mimetype)
		{
			case 'application/vnd.oasis.opendocument.text':
					$endung = 'odt';
					break; 
			case 'application/vnd.oasis.opendocument.spreadsheet':
					$endung = 'ods'; 
					break;               
			default:
					$endung = 'pdf'; 
		}

		// Load the XSL source
		$xsl_doc = new DOMDocument;
		if(!$xsl_doc->loadXML($xsl_content))
			die('unable to load xsl');
		
		// Configure the transformer
		$proc = new XSLTProcessor;
		$proc->importStyleSheet($xsl_doc); // attach the xsl rules
		
		$buffer = $proc->transformToXml($xml_doc);
		//echo $buffer;
		//exit;
		$tempfolder = '/tmp/'.uniqid();
		mkdir($tempfolder);
		chdir($tempfolder);
		file_put_contents('content.xml', $buffer);
        
		$zipfile = DOC_ROOT.'system/vorlage_zip/'.$vorlage->vorlage_kurzbz.'.'.$endung;
		$tempname_zip = 'out.zip';
		if(copy($zipfile, $tempname_zip))
		{
			exec("zip $tempname_zip content.xml");
			clearstatcache(); 
            if($output == 'pdf')
            {
                $tempPdfName = $vorlage->vorlage_kurzbz.'.pdf';
                exec("unoconv -e IsSkipEmptyPages=false --stdout -f pdf $tempname_zip > $tempPdfName");
                
                $fsize = filesize($tempPdfName); 
                $handle = fopen($tempPdfName,'r');
                header('Content-type: application/pdf');
                header('Content-Disposition: attachment; filename="'.$tempPdfName.'"');
                header('Content-Length: '.$fsize); 
            }
            else if($output =='odt')
            {
                $fsize = filesize($tempname_zip);
                $handle = fopen($tempname_zip,'r');
                header('Content-type: '.$vorlage->mimetype);
                header('Content-Disposition: attachment; filename="'.$vorlage->vorlage_kurzbz.'.'.$endung.'"');
                header('Content-Length: '.$fsize); 
           } 
            
		    while (!feof($handle)) 
		    {
			  	echo fread($handle, 8192);
			}
			fclose($handle);

			unlink('content.xml');
			unlink($tempname_zip);
            if($output=='pdf')
                unlink($tempPdfName);
			rmdir($tempfolder);
		}
	}
	else 
	{
		if(PDF_CREATE_FUNCTION=='FOP')
		{
			$fop = new fop();
			$xml = $xml_doc->saveXML();
			//$xml = '<personen></personen>';
			//$xsl='foobar';
			$fop->generatePdf($xml, $xsl_content, $filename, "D");
		}
		else 
		{
			$fo2pdf = new XslFo2Pdf();
				
			// Load the XSL source
			$xsl_doc = new DOMDocument;
			
			if(!$xsl_doc->loadXML($xsl_content))
				die('unable to load xsl');
			
			// Configure the transformer
			$proc = new XSLTProcessor;
			$proc->importStyleSheet($xsl_doc); // attach the xsl rules
			
			$buffer = $proc->transformToXml($xml_doc);
			if (!$fo2pdf->generatePdf($buffer, $filename, "D"))
			{
				echo('Failed to generate PDF');
			}
		}
	}
}
else
{
	$uid = $_REQUEST["uid"];
	$ss = $_REQUEST["ss"];
	$heute = date('Y-m-d');
	
	$student=new student();
	$student->load($uid);
	$prestudent=new prestudent();
	$prestudent->getLastStatus($student->prestudent_id,$ss);
	$semester=$prestudent->ausbildungssemester;
	
	$query = "SELECT 
				tbl_studiengang.studiengang_kz, tbl_studentlehrverband.semester, tbl_studiengang.typ, 
				tbl_studiengang.kurzbz, tbl_person.person_id FROM tbl_person, tbl_benutzer, 
				tbl_studentlehrverband, tbl_studiengang 
			WHERE 
				tbl_studentlehrverband.student_uid = tbl_benutzer.uid 
				AND tbl_benutzer.person_id = tbl_person.person_id 
				AND tbl_studentlehrverband.studiengang_kz = tbl_studiengang.studiengang_kz 
				AND tbl_studentlehrverband.student_uid = '".addslashes($uid)."' 
				AND tbl_studentlehrverband.studiensemester_kurzbz = '".addslashes($ss)."'";
/*	$query = "SELECT 
				tbl_studiengang.studiengang_kz, tbl_prestudentstatus.ausbildungssemester as semester, tbl_studiengang.typ, 
				tbl_studiengang.kurzbz, tbl_person.person_id FROM tbl_person, tbl_benutzer, 
				tbl_studentlehrverband, tbl_prestudentstatus, tbl_studiengang 
			WHERE 
				tbl_prestutendstatus.prestudent_id = 
				tbl_studentlehrverband.student_uid = tbl_benutzer.uid 
				AND tbl_benutzer.person_id = tbl_person.person_id 
				AND tbl_studentlehrverband.studiengang_kz = tbl_studiengang.studiengang_kz 
				AND tbl_studentlehrverband.student_uid = '".addslashes($uid)."' 
				AND tbl_studentlehrverband.studiensemester_kurzbz = '".addslashes($ss)."'"; */

	if($result = $db->db_query($query))
	{
		if($row = $db->db_fetch_object($result))
		{
			$person_id = $row->person_id;
			$titel = $xsl."_".strtoupper($row->typ).strtoupper($row->kurzbz)."_".$semester;
			$bezeichnung = $xsl." ".strtoupper($row->typ).strtoupper($row->kurzbz)." ".$semester.". Semester";
			$studiengang_kz = $row->studiengang_kz;
		}
		else
		{
			$echo = 'Datensatz wurde nicht gefunden';
		}
	}

	if($rechte->isBerechtigt('admin', $studiengang_kz, 'suid') || $rechte->isBerechtigt('assistenz', $studiengang_kz, 'suid'))
	{
		if(PDF_CREATE_FUNCTION=='FOP')
		{
			$fop = new fop();
			$file = $fop->generatePdf($xml_doc->saveXML(), $xsl_content, $filename, "F");
		}
		else 
		{
			$filename = $user;
			$fo2pdf = new XslFo2Pdf();
			
			// Load the XSL source
			$xsl_doc = new DOMDocument;
			
			if(!$xsl_doc->loadXML($xsl_content))
				die('unable to load xsl');
				
			// Configure the transformer
			$proc = new XSLTProcessor;
			$proc->importStyleSheet($xsl_doc); // attach the xsl rules
			
			$buffer = $proc->transformToXml($xml_doc);
			
			if (!$fo2pdf->generatePdf($buffer, $filename, 'F'))
			{
				echo('Failed to generate PDF');
			}
			$file = "/tmp/".$filename.".pdf";
		}
		
		$handle = fopen($file, "rb");
		$string = fread($handle, filesize($file));
		fclose($handle);
		//$string = file_get_contents($file);
		unlink($file);
		
		$hex="";
		//for ($i=0;$i<mb_strlen($string);$i++)
		//	$hex.=(mb_strlen(dechex(ord(mb_substr($string,$i,1)))<2)? "0".dechex(ord(mb_substr($string,$i,1))): dechex(ord(mb_substr($string,$i,1))));

		$hex = base64_encode($string);
		$akte = new akte();
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
		{
			echo 'Erstellen Fehlgeschlagen: '.$akte->errormsg;
			return false;
		}
		else
		{
			return true;
		}
	}
	else
		echo 'Keine Berechtigung zum Speichern';
}
?>
