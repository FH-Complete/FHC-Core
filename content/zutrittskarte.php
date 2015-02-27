<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 */
/* Erstellt ein Dokument zum Drucken der Zutrittskarten
 *
 * Parameter:
 * data ... Liste der UIDs mit Strichpunkt getrennt
 * type ... normal | datum - wenn datum übergeben wird, wird nur das Gueltigkeitsdatum erstellt, 
 *                           sonst alle Kartendaten
 *
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/akte.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/vorlage.class.php');
require_once('../include/datum.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/konto.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/student.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/benutzerfunktion.class.php');
require_once('../include/organisationseinheit.class.php');

$user = get_uid();
$db = new basis_db();

$datum_obj = new datum();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('mitarbeiter/stammdaten') && !$rechte->isBerechtigt('student/stammdaten'))
{
	die('Sie haben keine Berechtigung fuer diese Seite');
}

//UIDs werden entweder als data Parameter mit ";" getrennt übergeben
$uid = isset($_REQUEST['data'])?$_REQUEST['data']:'';
//ODER als POST Array über den Parameter users
$users = isset($_REQUEST['users'])?$_REQUEST['users']:'';
$type = isset($_REQUEST['type'])?$_REQUEST['type']:'normal';
$output = isset($_REQUEST['output'])?$_REQUEST['output']:'pdf';
if($uid=='' && $users=='')
	die('Parameter data is missing');
	
if($users!='')
	$uid_arr=$users;
else
	$uid_arr = explode(';',$uid);

// Tempordner fuer das erstellen des ODT anlegen
$tempfolder = '/tmp/'.uniqid();
mkdir($tempfolder);
chdir($tempfolder);

// Unterordner fuer die Bilder erstellen
mkdir('Pictures');

// Vorlage der Zutrittskarte laden
$vorlage = new vorlage();
if(!$vorlage->getAktuelleVorlage('0', 'Zutrittskarte'))
	die($vorlage->errormsg);
$xsl_content = $vorlage->text;

// Vorlage ODT in den Temp Ordner kopieren
$zipfile = DOC_ROOT.'system/vorlage_zip/'.$vorlage->vorlage_kurzbz.'.odt';
$tempname_zip = 'out.zip';
if(copy($zipfile, $tempname_zip))
{
	// XML mit den Personendaten erstellen
	$xml ="<?xml version='1.0' encoding='UTF-8' standalone='yes'?>
	<zutrittskarte>";
		
	foreach($uid_arr as $uid)
	{
		$bn = new benutzer();
		if($bn->load($uid))
		{
			$gueltigbis = '';
			
			// Bild der Person holen
			$bild = $qry = "SELECT inhalt as foto FROM public.tbl_akte WHERE dokument_kurzbz='Lichtbil' AND person_id=".$db->db_add_param($bn->person_id, FHC_INTEGER);

			$cTmpHEX='/9j/4AAQSkZJRgABAQEASABIAAD/4QAWRXhpZgAATU0AKgAAAAgAAAAAAAD//gAXQ3JlYXRlZCB3aXRoIFRoZSBHSU1Q/9sAQwAFAwQEBAMFBAQEBQUFBgcMCAcHBwcPCwsJDBEPEhIRDxERExYcFxMUGhURERghGBodHR8fHxMXIiQiHiQcHh8e/9sAQwEFBQUHBgcOCAgOHhQRFB4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4e/8AAEQgAAQABAwEiAAIRAQMRAf/EABUAAQEAAAAAAAAAAAAAAAAAAAAI/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/EABQBAQAAAAAAAAAAAAAAAAAAAAD/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCywAf/2Q==';			
			if($result = $db->db_query($qry))
			{
				if($row = $db->db_fetch_object($result))	
					$cTmpHEX=$row->foto;
			}

			// Bild in den Temp Ordner zwischenspeichern
			file_put_contents($tempfolder.'/Pictures/'.$bn->uid.'.jpg',base64_decode($cTmpHEX));
			
			// Bild zum Manifest-File des ODTs hinzufuegen
			addImageToManifest($tempname_zip, 'Pictures/'.$bn->uid.'.jpg', $contenttype='image/jpeg');
			
			if(check_lektor($uid))
			{
				$ma = new mitarbeiter();
				$ma->load($uid);
				$benutzerfunktion = new benutzerfunktion();
				$benutzerfunktion->getBenutzerFunktionByUid($uid, NULL, date("Y-m-d"), date("Y-m-d"));
				if(!empty($benutzerfunktion->result[0]))
				{
				    $oe = new organisationseinheit($benutzerfunktion->result[0]->oe_kurzbz);
				}
				else
				{
				    $oe = new organisationseinheit();
				}
				$xml.="
				<mitarbeiter>
					<uid><![CDATA[".$bn->uid."]]></uid>
					<vorname><![CDATA[".$bn->vorname."]]></vorname>
					<nachname><![CDATA[".$bn->nachname."]]></nachname>
					<titelpre><![CDATA[".$bn->titelpre."]]></titelpre>
					<titelpost><![CDATA[".$bn->titelpost."]]></titelpost>
					<personalnummer><![CDATA[".$ma->personalnummer."]]></personalnummer>
					<ausstellungsdatum><![CDATA[".date('d.m.Y')."]]></ausstellungsdatum>
					<gebdatum><![CDATA[".$datum_obj->formatDatum($ma->gebdatum,'d.m.Y')."]]></gebdatum>
					<organisationseinheit><![CDATA[".$oe->bezeichnung."]]></organisationseinheit>
				</mitarbeiter>";
			}
			else
			{
				$student = new student();
				$student->load($bn->uid);
				$konto = new konto();
				$studiengang = new studiengang();
				$studiengang->load($student->studiengang_kz);
				
				$stsem_obj = new studiensemester();
				$stsem = $stsem_obj->getaktorNext();
				$stsem_obj->load($stsem);
				
				if($konto->checkStudienbeitrag($bn->uid, $stsem_obj->studiensemester_kurzbz))
				{
					$gueltigbis=$stsem_obj->ende;
				}
				else
				{
					// Studiengebuehr noch nicht bezahlt
					$gueltigbis=$stsem_obj->ende;
				}
				
				if($type=='datum')
				{
					//Nur der Datumsstempel wird erstellt
					$xml.="
					<datum>
						<gueltigbis><![CDATA[".$datum_obj->formatDatum($gueltigbis,'d/m/Y')."]]></gueltigbis>
					</datum>";
				}
				else
				{					
					//Student
					$xml.="
					<student>
						<uid><![CDATA[".$bn->uid."]]></uid>
						<vorname><![CDATA[".$bn->vorname."]]></vorname>
						<nachname><![CDATA[".$bn->nachname."]]></nachname>
						<titelpre><![CDATA[".$bn->titelpre."]]></titelpre>
						<titelpost><![CDATA[".$bn->titelpost."]]></titelpost>
						<studiengang><![CDATA[".$studiengang->kurzbzlang."]]></studiengang>
						<gebdatum><![CDATA[".$datum_obj->formatDatum($bn->gebdatum,'d.m.Y')."]]></gebdatum>
						<matrikelnummer><![CDATA[".$student->matrikelnr."]]></matrikelnummer>
						<ausstellungsdatum><![CDATA[".date('M.Y')."]]></ausstellungsdatum>
						<gueltigbis><![CDATA[".$datum_obj->formatDatum($gueltigbis,'d.m.Y')."]]></gueltigbis>
					</student>";
				}
			}
		}
	}
	$xml.="</zutrittskarte>";
	
	// XSL-Vorlage von content.xml laden
	$xsl_doc = new DOMDocument;
	if(!$xsl_doc->loadXML($xsl_content))
		die('Fehler beim Laden der XSL Vorlage von content.xml.');
		
	// XML Dokument in ein DOM Objekt laden
	$xml_doc = new DOMDocument;
	if(!$xml_doc->loadXML($xml))
		die('Fehler beim Laden des XML');
		
	// XSL File in den Processor laden
	$proc = new XSLTProcessor;
	$proc->importStyleSheet($xsl_doc);
	
	// XSL-Transformation starten
	$buffer = $proc->transformToXml($xml_doc);

	// Konvertierte content.xml ins Filesystem schreiben
	file_put_contents('content.xml', $buffer);
	
	//Debugging XML
	//file_put_contents('Pictures/out.xml', $xml);

	// Bilder zum ZIP-File hinzufuegen
	exec("zip $tempname_zip Pictures/*");
	
	// content.xml ins ZIP-File hinzufuegen
	exec("zip $tempname_zip content.xml");
	
	if($output=='pdf')
	{
		exec("unoconv --stdout -f pdf $tempname_zip > out.pdf");
		$tempname_zip='out.pdf';
	}
	
	//Ausgeben des Dokuments
	clearstatcache(); 
    $fsize = filesize($tempname_zip);
    $handle = fopen($tempname_zip,'r');
    if($output=='pdf')
    {
    	header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="'.$vorlage->vorlage_kurzbz.'.pdf"');
    }
    else
    {
	    header('Content-type: '.$vorlage->mimetype);
		header('Content-Disposition: attachment; filename="'.$vorlage->vorlage_kurzbz.'.odt"');
    }
    header('Content-Length: '.$fsize); 
    while (!feof($handle)) 
    {
	  	echo fread($handle, 8192);
	}
	fclose($handle);
	
	//Loeschen der Temporaeren Dateien 
	//unlink('content.xml');
	//Unlinking Pictures ?
	//unlink($tempname_zip);
	//rmdir($tempfolder);
	exec('rm -r '.$tempfolder);
}

/**
 * Fuegt ein Bild zur Manifest-Datei eines ODT Files hinzu
 *  
 * @param $zip Zip Pfad
 * @param $image Bild Pfad
 * @param $contenttype Content-Type des Bildes
 */
function addImageToManifest($zip, $image, $contenttype='image/png')
{
	// Manifest Datei holen
	exec('unzip '.$zip.' META-INF/manifest.xml');
	// Bild zur Manifest Datei hinzufuegen
	$manifest = file_get_contents('META-INF/manifest.xml');
	$xml_doc = new DOMDocument;
	if(!$xml_doc->loadXML($manifest))
		die('Manifest File ungueltig');
	//root-node holen
	$root = $xml_doc->getElementsByTagName('manifest')->item(0);

	//Neues Element unterhalb des Root Nodes anlegen
	$node = $xml_doc->createElement("manifest:file-entry");
	$node->setAttribute("manifest:media-type",$contenttype);
	$node->setAttribute("manifest:full-path",$image);
	$root->appendChild($node);
	$out = $xml_doc->saveXML();

	//geaenderte Manifest Datei speichern und wieder ins Zip packen
	file_put_contents('META-INF/manifest.xml', $out);
	exec('zip '.$zip.' META-INF/*');
}
?>
