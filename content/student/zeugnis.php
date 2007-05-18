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
/* Erstellt eine Zeugnis im PDF Format
 *
 * Erstellt ein XML File Transformiert dieses mit
 * Hilfe der XSL-FO Vorlage aus der DB und generiert
 * daraus ein PDF (xslfo2pdf)
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/xslfo2pdf/xslfo2pdf.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/vorlage.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$user = get_uid();
loadVariables($conn, $user);
//Parameter holen
if(isset($_GET['uid']))
	$uid = $_GET['uid'];
else 
	$uid = null;
	
if(isset($_GET['studiengang_kz']))
	$studiengang_kz = $_GET['studiengang_kz'];
else 
	die('Studiengang_kz muss uebergeben werden');
	
if(isset($_GET['semester']))
	$semester = $_GET['semester'];
else 	
	$semester = '';

//Berechtigung pruefen
$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('admin',$studiengang_kz))
	die("Keine Berechtigung");

// GENERATE XML
$lehrveranstaltung = new lehrveranstaltung($conn);
$xml = $lehrveranstaltung->generateZeugnisXML($uid);

// END GENERATE XML

// Load the XML source
$xml_doc = new DOMDocument;
if(!$xml_doc->loadXML($xml))
	die('unable to load xml');

//XSL aus der DB holen

$vorlage = new vorlage($conn);
$vorlage->getAktuelleVorlage($studiengang_kz, 'Zeugnis');

// Load the XSL source
$xsl = new DOMDocument;
//if(!$xsl->load('../../../../xsl/collection.xsl'))
if(!$xsl->loadXML($vorlage->text))
	die('unable to load xsl');
//echo $vorlage->text;

// Configure the transformer
$proc = new XSLTProcessor;
if(!$proc->importStyleSheet($xsl)) // attach the xsl rules
	echo "Failed to import Style";

$buffer = $proc->transformToXml($xml_doc);
//in $buffer steht nun das xsl-fo file mit den daten

$buffer = '<?xml version="1.0" encoding="ISO-8859-15" ?>'.substr($buffer, strpos($buffer,"\n"),strlen($buffer));
$buffer = html_entity_decode($buffer);
//echo "buffer: $buffer";

//Pdf erstellen
$fo2pdf = new XslFo2Pdf(); 
if (!$fo2pdf->generatePdf($buffer, 'filename', "D")) 
{
    echo('Failed to generate PDF');
}

?>