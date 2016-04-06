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
require_once('../../config/cis.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/xslfo2pdf/xslfo2pdf.php');
require_once('../../include/akte.class.php');
require_once('../../include/konto.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/vorlage.class.php');
require_once('../../include/addon.class.php');
require_once('../../include/studiengang.class.php');

if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

$user = get_uid();
loadVariables($user);

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

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

if(isset($_GET['version']) && is_numeric($_GET['version']))
	$version = $_GET['version'];
else
	$version ='';

if(isset($_GET['xsl_oe_kurzbz']))
	$xsl_oe_kurzbz=$_GET['xsl_oe_kurzbz'];
else
	$xsl_oe_kurzbz='';

//Parameter setzen
$params='?xmlformat=xml';
//if(isset($_GET['uid']))
//	$params.='&uid='.$_GET['uid'];

//Admins duerfen Dokumente anderer Personen drucken
if($rechte->isBerechtigt('admin'))
	$user = $_GET['uid'];

$params.='&uid='.$user;
if(isset($_GET['person_id']))
	$params.='&person_id='.$_GET['person_id'];
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
    $params.='&all=1';

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


$konto = new konto();
if (($user == $_GET["uid"]) || $rechte->isBerechtigt('admin'))
{
    $buchungstypen = array();
    if(defined("CIS_DOKUMENTE_STUDIENBEITRAG_TYPEN"))
    {
	$buchungstypen = unserialize (CIS_DOKUMENTE_STUDIENBEITRAG_TYPEN);
    }

	if(isset($_GET['ss']))
	    $stsem_zahlung = $konto->getLastStSemBuchungstypen($user, $buchungstypen, $_GET['ss']);

	if((($xsl=='Inskription') || ($xsl == 'Studienblatt')) && ($_GET["ss"] != $stsem_zahlung))
	{
	    die('Der Studienbeitrag wurde noch nicht bezahlt');
	}
	if(isset($_GET['buchungsnummern']))
	{
	    //Beim Drucken von Buchungsbestaetigungen pruefen ob diese Buchungen auch zu diesem Benutzer gehoeren
	    $buchungsnr = explode(';',$_GET['buchungsnummern']);
	    $user_obj = new benutzer();
	    $user_obj->load($user);
	    foreach($buchungsnr as $bnr)
	    {
		if($bnr!='')
		{
		    $konto->load($bnr);
		    if($konto->person_id!=$user_obj->person_id)
			die('Sie haben keine Berechtigung fuer diese Buchung');
		    if($konto->getDifferenz($bnr)!=0)
			die('Diese Zahlung wurde noch nicht beglichen');
		}
	    }
	}
	$xml_url=XML_ROOT.$xml.$params;
	//echo $xml_url;
	// Load the XML source
	$xml_doc = new DOMDocument;

	if(!$xml_doc->load($xml_url))
		die('unable to load xml');
	//echo ':'.$xml_doc->saveXML().':';

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

		if(!$xsl_doc->loadXML($vorlage->text))
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
		    $style_xsl=$_GET['style_xsl'];
		    $style_vorlage = new vorlage();
		    $style_vorlage->getAktuelleVorlage($xsl_stg_kz, $style_xsl, $version);
		    $style_xsl_doc = new DOMDocument;
		    if(!$style_xsl_doc->loadXML($style_vorlage->text))
			    die('unable to load xsl');

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
		    $zipfile = DOC_ROOT.'/addons/'.$addon.'/system/vorlage_zip/'.$vorlage->vorlage_kurzbz.'.'.$endung;

		    if(file_exists($zipfile))
		    {
			$vorlage_found=true;
			break;
		    }
		}
		if(!$vorlage_found)
		    $zipfile = DOC_ROOT.'/system/vorlage_zip/'.$vorlage->vorlage_kurzbz.'.'.$endung;


		$tempname_zip = 'out.zip';
		if(copy($zipfile, $tempname_zip))
		{
		    exec("zip $tempname_zip content.xml");
		    if(isset($_GET['style_xsl']))
			    exec("zip $tempname_zip styles.xml");

		    clearstatcache();
		    if($vorlage->bezeichnung!='')
			    $filename = $vorlage->bezeichnung;
		    else
			    $filename = $vorlage->vorlage_kurzbz;
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
					$filename = $filename.'_'.$studienordnung->studiengangkurzbzlang.'.pdf';
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
				    $filename = $filename.'_'.$studienordnung->studiengangkurzbzlang;
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
				    $filename = $filename.'_'.$studienordnung->studiengangkurzbzlang.'.doc';
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
	    if(isset($_GET['style_xsl']))
		    unlink('styles.xml');
	    unlink($tempname_zip);
	    if($output=='pdf' || $output=='doc')
	    	unlink($tempPdfName);
		rmdir($tempfolder);
	    }
	}
	else
	{
	    // Load the XSL source
	    $xsl_doc = new DOMDocument;

	    if(!$xsl_doc->loadXML($vorlage->text))
		    die('unable to load xsl');

	    // Configure the transformer
	    $proc = new XSLTProcessor;
	    $proc->importStyleSheet($xsl_doc); // attach the xsl rules

	    $buffer = $proc->transformToXml($xml_doc);
	    //in $buffer steht nun das xsl-fo file mit den daten
	    $buffer = '<?xml version="1.0" encoding="utf-8" ?>'.substr($buffer, strpos($buffer,"\n"),strlen($buffer));

	    //Pdf erstellen
	    $fo2pdf = new XslFo2Pdf();

	    //wenn uid gefunden wird, dann den Nachnamen zum Dateinamen dazuhaengen
	    $nachname='';


	    if(isset($_GET['uid']) && $_GET['uid']!='')
	    {
		$uid = str_replace(';','',$_GET['uid']);
		$qry = "SELECT nachname FROM campus.vw_benutzer WHERE uid=".$db->db_add_param($uid);

		if($result = $db->db_query($qry))
		{
		    if($row = $db->db_fetch_object($result))
		    {
			$nachname = '_'.$row->nachname;
		    }
		}
	    }
	    $filename=$xsl.$nachname;

	    if (!$fo2pdf->generatePdf($buffer, $filename, "D"))
	    {
		echo('Failed to generate PDF');
	    }
	}
}
else
{
	// kein berechtigung
	echo "<html><body><h3>Sie haben keine Berechtigung zum Anzeigen dieser Seite</h3></body></html>";
}
?>
