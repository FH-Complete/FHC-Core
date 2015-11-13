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
/* Erstellt diverse Dokumente
 *
 * Erstellt ein XML File Transformiert dieses mit
 * Hilfe der XSL-FO Vorlage aus der DB und generiert
 * daraus ein PDF mittels xslfo2pdf bzw unoconv
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
require_once('../include/variable.class.php');
require_once('../include/addon.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/studienordnung.class.php');

$user = get_uid();
$db = new basis_db();

$variable_obj = new variable();
$variable_obj->loadVariables($user);

//Parameter holen
if(isset($_GET['xml']))
	$xml=$_GET['xml'];
else
	die('Fehlerhafte Parameteruebergabe');
if(isset($_GET['xsl']))
	$xsl=$_GET['xsl'];
else
	die('Fehlerhafte Parameteruebergabe');

// Studiengang ermitteln dessen Vorlage verwendet werden soll
$xsl_stg_kz=0;
// Direkte uebergabe des Studienganges dessen Vorlage verwendet werden soll
if(isset($_GET['xsl_stg_kz']))
	$xsl_stg_kz=$_GET['xsl_stg_kz'];
else
{
	// Wenn eine Studiengangskennzahl uebergeben wird, wird die Vorlage dieses Studiengangs verwendet
	if(isset($_GET['stg_kz']))
		$xsl_stg_kz=$_GET['stg_kz'];
	else
	{
		// Werden UIDs uebergeben, wird die Vorlage des Studiengangs genommen
		// in dem der 1. Studierende in der Liste ist
		if(isset($_GET['uid']) && $_GET['uid']!='')
		{
			if(strstr($_GET['uid'],';'))
				$uids = explode(';',$_GET['uid']);
			else
				$uids[1] = $_GET['uid'];

			$student_obj = new student();
			if($student_obj->load($uids[1]))
			{
				$xsl_stg_kz=$student_obj->studiengang_kz;
			}
		}
	}
}
if(isset($_GET['xsl_oe_kurzbz']))
	$xsl_oe_kurzbz=$_GET['xsl_oe_kurzbz'];
else
	$xsl_oe_kurzbz='';

//Parameter setzen
$params='?xmlformat=xml';
if(isset($_GET['uid']))
	$params.='&uid='.urlencode($_GET['uid']);
if(isset($_GET['stg_kz']))
	$params.='&stg_kz='.urlencode($_GET['stg_kz']);
if(isset($_GET['person_id']))
	$params.='&person_id='.urlencode($_GET['person_id']);
if(isset($_GET['id']))
	$params.='&id='.urlencode($_GET['id']);
if(isset($_GET['prestudent_id']))
	$params.='&prestudent_id='.urlencode($_GET['prestudent_id']);
if(isset($_GET['buchungsnummern']))
	$params.='&buchungsnummern='.urlencode($_GET['buchungsnummern']);
if(isset($_GET['ss']))
	$params.='&ss='.urlencode($_GET['ss']);
if(isset($_GET['abschlusspruefung_id']))
	$params.='&abschlusspruefung_id='.urlencode($_GET['abschlusspruefung_id']);
if(isset($_GET['typ']))
	$params.='&typ='.urlencode($_GET['typ']);
if(isset($_GET['all']))
	$params.='&all='.urlencode($_GET['all']);
if(isset($_GET['preoutgoing_id']))
    $params.='&preoutgoing_id='.urlencode($_GET['preoutgoing_id']);
if(isset($_GET["lvid"]))
	$params.='&lvid='.urlencode($_GET["lvid"]);
if(isset($_GET['projekt_kurzbz']))
	$params.='&projekt_kurzbz='.urlencode($_GET['projekt_kurzbz']);
if(isset($_GET['version']) && is_numeric($_GET['version']))
	$version = $_GET['version'];
else
	$version ='';
if(isset($_GET['von']))
	$params.='&von='.urlencode($_GET['von']);
if(isset($_GET['bis']))
	$params.='&bis='.urlencode($_GET['bis']);
if(isset($_GET['stundevon']))
	$params.='&stundevon='.urlencode($_GET['stundevon']);
if(isset($_GET['stundebis']))
	$params.='&stundebis='.urlencode($_GET['stundebis']);
if(isset($_GET['sem']))
	$params.='&sem='.urlencode($_GET['sem']);
if(isset($_GET['lehreinheit']))
	$params.='&lehreinheit='.urlencode($_GET['lehreinheit']);
if(isset($_GET['mitarbeiter_uid']))
	$params.='&mitarbeiter_uid='.urlencode($_GET['mitarbeiter_uid']);
if(isset($_GET['vertrag_id']))
{
    foreach($_GET['vertrag_id'] as $id)
    {
	$params.='&vertrag_id[]='.urlencode($id);
    }
}
if(isset($_GET['studienordnung_id']))
	$params.='&studienordnung_id='.urlencode($_GET['studienordnung_id']);
if(isset($_GET['fixangestellt']))
	$params.='&fixangestellt='.urlencode($_GET['fixangestellt']);
if(isset($_GET['standort']))
	$params.='&standort='.urlencode($_GET['standort']);
if(isset($_GET['abrechnungsmonat']))
	$params.='&abrechnungsmonat='.urlencode($_GET['abrechnungsmonat']);
if(isset($_GET['form']))
	$params.='&form='.urlencode($_GET['form']);
$output = (isset($_GET['output'])?$_GET['output']:'odt');

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

//OE fuer Output ermitteln

if ($xsl_oe_kurzbz!='')
{
	$oe_kurzbz = $xsl_oe_kurzbz;
}
else
{
	if($xsl_stg_kz=='')
		$xsl_stg_kz='0';
	$oe = new studiengang();
	$oe->load($xsl_stg_kz);
	$oe_kurzbz = $oe->oe_kurzbz;
}

//Darf der User Dokumente in einem NICHT-PDF-Format exportieren?
if (isset($_GET['output']) && $_GET['output']!='pdf')
{
	if (!$rechte->isBerechtigt('system/change_outputformat',$oe_kurzbz))
	{
		$output = 'pdf';
	}
	else
		$output = $_GET['output'];
}
else 
	$output = 'pdf';


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

$xsl_content = $vorlage->text;

if($xsl_content=='')
	die('Für diese Organisationseinheit ist keine Vorlage im System hinterlegt');

//Berechtigung pruefen
if($xsl=='AccountInfo')
{
	$isberechtigt = false;

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
elseif(in_array($xsl,array('Lehrveranstaltungszeugnis','Zertifikat','Diplomurkunde','Diplomzeugnis','Bakkurkunde','BakkurkundeEng','Bakkzeugnis',
'PrProtokollBakk','PrProtokollDipl','Lehrauftrag','DiplomurkundeEng','Zeugnis','ZeugnisEng','StudienerfolgEng',
'Sammelzeugnis','PrProtDiplEng','PrProtBakkEng','BakkzeugnisEng','DiplomzeugnisEng','statusbericht',
'DiplSupplement','Zutrittskarte','Projektbeschr','Ausbildungsver','AusbildStatus','PrProtBA','PrProtMA',
'PrProtBAEng','PrProtMAEng','Studienordnung','Erfolgsnachweis','ErfolgsnwHead','Studienblatt','LV_Informationen',
'LVZeugnis','AnwListBarcode','Honorarvertrag','AusbVerEng','AusbVerEngHead','Zeugnis','ErfolgsnachweisE','ErfolgsnwHeadE','Magisterurkunde','Masterurkunde',
'Defensiourkunde','Magisterzeugnis','Laufzettel','StudienblattEng','Zahlung1','Terminliste','Studienbuchblatt','Veranstaltungen')))
{
	if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('assistenz'))
	{
		echo 'Sie haben keine Berechtigung dieses Dokument zu erstellen';
		exit;
	}
}
elseif(in_array($xsl,array('Ressource')))
{
	if(!$rechte->isBerechtigt('lehre/lvplan'))
	{
		echo 'Sie haben keine Berechtigung dieses Dokument zu erstellen';
		exit;
	}
}
elseif(in_array($xsl,array('Inskription','Studienerfolg','OutgoingLearning','OutgoingChangeL','LearningAgree','Zahlung')))
{
	if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('assistenz'))
	{
		echo 'Sie haben keine Berechtigung dieses Dokument zu erstellen';
		exit;
	}
}
elseif($xsl=='Uebernahme')
{
	if(!$rechte->isBerechtigt('wawi/inventar') && !$rechte->isBerechtigt('assistenz'))
	{
		echo 'Sie haben keine Berechtigung dieses Dokument zu erstellen';
		exit;
	}
}
elseif($xsl=='Bestellung')
{
	if(!$rechte->isBerechtigt('wawi/bestellung'))
	{
		echo 'Sie haben keine Berechtigung dieses Dokument zu erstellen';
		exit;
	}
}
else
{
	// Wenn Berechtigung direkt beim der Vorlage angegeben ist
	if(count($vorlage->berechtigung)>0)
	{
		$allowed=false;
		foreach($vorlage->berechtigung as $berechtigung_kurzbz)
		{
			if($rechte->isBerechtigt($berechtigung_kurzbz))
				$allowed=true;
		}
		if(!$allowed)
		{
			echo 'unbekanntes Dokument oder keine Berechtigung';
			exit;
		}
	}
	else
	{
		echo 'unbekanntes Dokument oder keine Berechtigung';
		exit;
	}
}


$xml_found = false;
$addons = new addon();

foreach($addons->aktive_addons as $addon)
{
	$xmlfile = DOC_ROOT.'addons/'.$addon.'/rdf/'.$xml;
	if(file_exists($xmlfile))
	{
		$xml_found = true;
		$xml_url = XML_ROOT.'../addons/'.$addon.'/rdf/'.$xml.$params;
		break;
	}
}
if(!$xml_found)
	$xml_url=XML_ROOT.$xml.$params;


// Load the XML source
$xml_doc = new DOMDocument;

if(!$xml_doc->load($xml_url))
	die('unable to load xml: '.$xml_url);

//Pdf erstellen

//wenn uid gefunden wird, dann den Nachnamen zum Dateinamen dazuhaengen
$nachname='';
if(isset($_GET['uid']) && $_GET['uid']!='')
{
	$uid = str_replace(';','',$_GET['uid']);
	$benutzer_obj = new benutzer();
	if($benutzer_obj->load($uid))
		$nachname = '_'.convertProblemChars($benutzer_obj->nachname);

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

		// Wenn ein Style XSL uebergeben wurde wird ein zweites XML File erstellt mit den
		// Styleanweisungen und ebenfalls zum Zip hinzugefuegt
		if(isset($_GET['style_xsl']) || $vorlage->style!='')
		{
			//Wenn die Spalte style in der DB befuellt ist, wird dieses verwendet
			if($vorlage->style!='')
			{
				$style_xsl_doc = new DOMDocument;
				if(!$style_xsl_doc->loadXML($vorlage->style))
					die('unable to load xsl from tbl_vorlagestudiengang');
			}
			else
			{
				$style_xsl=$_GET['style_xsl'];
				$style_vorlage = new vorlage();
				$style_vorlage->getAktuelleVorlage($xsl_stg_kz, $style_xsl, $version);
		        $style_xsl_doc = new DOMDocument;
				if(!$style_xsl_doc->loadXML($style_vorlage->text))
					die('unable to load xsl');
			}

			// Configure the transformer
			$style_proc = new XSLTProcessor;
			$style_proc->importStyleSheet($style_xsl_doc); // attach the xsl rules

			$stylebuffer = $style_proc->transformToXml($xml_doc);

			file_put_contents('styles.xml', $stylebuffer);
		}

		$vorlage_found=false;
		$addons = new addon();

		foreach($addons->aktive_addons as $addon)
		{
			$zipfile = DOC_ROOT.'addons/'.$addon.'/system/vorlage_zip/'.$vorlage->vorlage_kurzbz.'.'.$endung;

			if(file_exists($zipfile))
			{
				$vorlage_found=true;
				break;
			}
		}
		if(!$vorlage_found)
			$zipfile = DOC_ROOT.'system/vorlage_zip/'.$vorlage->vorlage_kurzbz.'.'.$endung;


		$tempname_zip = 'out.zip';
		if(copy($zipfile, $tempname_zip))
		{
			exec("zip $tempname_zip content.xml");
			if(isset($_GET['style_xsl']) || $vorlage->style!='')
				exec("zip $tempname_zip styles.xml");

			clearstatcache();
			if($vorlage->bezeichnung!='')
				$filename = $vorlage->bezeichnung.$nachname;
			else
				$filename = $vorlage->vorlage_kurzbz.$nachname;
            if($output == 'pdf')
            {
				if($xsl == 'LV_Informationen')
				{
				    $studiengang = new studiengang($_GET['stg_kz']);
				    $studiensemester = new studiensemester($_GET['ss']);
				    $tempPdfName = $vorlage->vorlage_kurzbz.'_'.$studiengang->kurzbzlang.'_'.$studiensemester->studiensemester_kurzbz.'.pdf';
				    $filename = $filename.'_'.$studiengang->kurzbzlang.'_'.$studiensemester->studiensemester_kurzbz.'.pdf';
				}
				elseif($xsl == "Honorarvertrag")
				{
				    $tempPdfName = $vorlage->vorlage_kurzbz.'_'.$benutzer_obj->nachname.'_'.$benutzer_obj->vorname.'.pdf';
				    $filename = $filename.'_'.$benutzer_obj->nachname.'_'.$benutzer_obj->vorname.'.pdf';
				}
            	elseif($xsl == "Studienordnung")
				{
				    $studienordnung = new studienordnung();
					$studienordnung->loadStudienordnung($_GET['studienordnung_id']);
					$filename = 'Studienordnung-Studienplan-'. sprintf("%'.04d",$studienordnung->studiengang_kz).'-'.$studienordnung->studiengangkurzbzlang;
					$tempPdfName = $vorlage->vorlage_kurzbz.'.pdf';
				}
				else
				{
				    $tempPdfName = $vorlage->vorlage_kurzbz.'.pdf';
				    $filename = $filename.'.pdf';
				}
                exec("unoconv -e IsSkipEmptyPages=false --stdout -f pdf $tempname_zip > $tempPdfName");

                $fsize = filesize($tempPdfName);
                $handle = fopen($tempPdfName,'r');
                header('Content-type: application/pdf');
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                header('Content-Length: '.$fsize);
            }
            else if($output =='odt')
            {
            	if($xsl == "Studienordnung")
				{
				    $studienordnung = new studienordnung();
					$studienordnung->loadStudienordnung($_GET['studienordnung_id']);
					$filename = 'Studienordnung-Studienplan-'. sprintf("%'.04d",$studienordnung->studiengang_kz).'-'.$studienordnung->studiengangkurzbzlang;
				}

            	$fsize = filesize($tempname_zip);
                $handle = fopen($tempname_zip,'r');
                header('Content-type: '.$vorlage->mimetype);
                header('Content-Disposition: attachment; filename="'.$filename.'.'.$endung.'"');
                header('Content-Length: '.$fsize);
           }
           else if($output =='doc')
           {
                $tempPdfName = $vorlage->vorlage_kurzbz.'.doc';
				if($xsl == "Studienordnung")
				{
				    $studienordnung = new studienordnung();
					$studienordnung->loadStudienordnung($_GET['studienordnung_id']);
					$filename = 'Studienordnung-Studienplan-'. sprintf("%'.04d",$studienordnung->studiengang_kz).'-'.$studienordnung->studiengangkurzbzlang.'.doc';
				}
				else
				{
					$filename = $filename.'.doc';
				}
                exec("unoconv -e IsSkipEmptyPages=false --stdout -f doc $tempname_zip > $tempPdfName");

                $fsize = filesize($tempPdfName);
                $handle = fopen($tempPdfName,'r');
                header('Content-type: application/vnd.ms-word');
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                header('Content-Length: '.$fsize);
           }
		    while (!feof($handle))
		    {
			  	echo fread($handle, 8192);
			}
			fclose($handle);

			unlink('content.xml');
			if(isset($_GET['style_xsl']) || $vorlage->style!='')
				unlink('styles.xml');
			unlink($tempname_zip);
            if($output=='pdf' || $output=='doc')
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
	// Archivieren von Dokumenten
	$uid = $_REQUEST["uid"];
	$heute = date('Y-m-d');

	$student=new student();
	$student->load($uid);

	if(isset($_REQUEST['ss']))
	{
		$ss = $_REQUEST["ss"];

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
					AND tbl_studentlehrverband.student_uid = ".$db->db_add_param($uid)."
					AND tbl_studentlehrverband.studiensemester_kurzbz = ".$db->db_add_param($ss);

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
	}
	else
	{
		$studiengang = new studiengang();
		$studiengang->load($student->studiengang_kz);
		$studiengang_kz=$student->studiengang_kz;
		$person_id = $student->person_id;
		$titel = $vorlage->bezeichnung.'_'.$studiengang->kuerzel;
		$bezeichnung = $vorlage->bezeichnung.'_'.$studiengang->kuerzel;
	}

	if($rechte->isBerechtigt('admin', $studiengang_kz, 'suid') || $rechte->isBerechtigt('assistenz', $studiengang_kz, 'suid'))
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

			// Wenn ein Style XSL uebergeben wurde wird ein zweites XML File erstellt mit den
			// Styleanweisungen und ebenfalls zum Zip hinzugefuegt
			if(isset($_GET['style_xsl']))
			{
				//Wenn die Spalte style in der DB befuellt ist, wird dieses verwendet
				if($vorlage->style!='')
				{
					$style_xsl_doc = new DOMDocument;
					if(!$style_xsl_doc->loadXML($vorlage->style))
						die('unable to load xsl from tbl_vorlagestudiengang');
				}
				else
				{
					$style_xsl=$_GET['style_xsl'];
					$style_vorlage = new vorlage();
					$style_vorlage->getAktuelleVorlage($xsl_stg_kz, $style_xsl, $version);
			        $style_xsl_doc = new DOMDocument;
					if(!$style_xsl_doc->loadXML($style_vorlage->text))
						die('unable to load xsl');
				}

				// Configure the transformer
				$style_proc = new XSLTProcessor;
				$style_proc->importStyleSheet($style_xsl_doc); // attach the xsl rules

				$stylebuffer = $style_proc->transformToXml($xml_doc);

				file_put_contents('styles.xml', $stylebuffer);
			}

			$vorlage_found=false;
			$addons = new addon();

			foreach($addons->aktive_addons as $addon)
			{
				$zipfile = DOC_ROOT.'addons/'.$addon.'/system/vorlage_zip/'.$vorlage->vorlage_kurzbz.'.'.$endung;

				if(file_exists($zipfile))
				{
					$vorlage_found=true;
					break;
				}
			}
			if(!$vorlage_found)
				$zipfile = DOC_ROOT.'system/vorlage_zip/'.$vorlage->vorlage_kurzbz.'.'.$endung;


			$tempname_zip = 'out.zip';
			if(copy($zipfile, $tempname_zip))
			{
				exec("zip $tempname_zip content.xml");
				if(isset($_GET['style_xsl']) || $vorlage->style!='')
					exec("zip $tempname_zip styles.xml");

				clearstatcache();

				$tempPdfName = $vorlage->vorlage_kurzbz.'.pdf';
	            exec("unoconv -e IsSkipEmptyPages=false --stdout -f pdf $tempname_zip > $tempPdfName");
			}
			$file = $tempfolder.'/'.$tempPdfName;
		}
		else
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
