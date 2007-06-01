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


// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$user = get_uid();
loadVariables($conn, $user);

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
if(isset($_GET['buchungsnr']))
	$params.='&buchungsnr='.$_GET['buchungsnr'];
if(isset($_GET['stg_kz']))
	$params.='&stg_kz='.$_GET['stg_kz'];
if(isset($_GET['ss']))
	$params.='&ss='.$_GET['ss'];
//Berechtigung pruefen
$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

//if(!$rechte->isBerechtigt('admin',$stg_kz))
//	die("Keine Berechtigung");

$xml_url=APP_ROOT.'rdf/'.$xml.$params;
//echo $xml_url;
// Load the XML source
$xml_doc = new DOMDocument;
if(!$xml_doc->load($xml_url))
	die('unable to load xml');

//XSL aus der DB holen
$qry = "SELECT text FROM public.tbl_vorlagestudiengang WHERE (studiengang_kz=0 OR studiengang_kz='".addslashes($xsl_stg_kz)."') AND vorlage_kurzbz='$xsl' ORDER BY studiengang_kz DESC, version DESC LIMIT 1";

if(!$result = pg_query($conn, $qry))
	die('Fehler beim laden der Vorlage'.pg_errormessage($conn));
if(!$row = pg_fetch_object($result))
	die('Vorlage wurde nicht gefunden'.$qry);

// Load the XSL source
$xsl = new DOMDocument;
//if(!$xsl->load('../../../../xsl/collection.xsl'))
if(!$xsl->loadXML($row->text))
	die('unable to load xsl');

// Configure the transformer
$proc = new XSLTProcessor;
$proc->importStyleSheet($xsl); // attach the xsl rules

$buffer = $proc->transformToXml($xml_doc);
//in $buffer steht nun das xsl-fo file mit den daten
$buffer = '<?xml version="1.0" encoding="ISO-8859-15" ?>'.substr($buffer, strpos($buffer,"\n"),strlen($buffer));
//$buffer = html_entity_decode($buffer);
//echo "buffer: $buffer";

//Pdf erstellen
$fo2pdf = new XslFo2Pdf();
if (!$fo2pdf->generatePdf($buffer, 'filename', "D"))
{
    echo('Failed to generate PDF');
}

?>