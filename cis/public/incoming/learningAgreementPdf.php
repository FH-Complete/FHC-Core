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
require_once('../../../config/vilesci.config.inc.php');
require_once 'auth.php';
require_once('../../../include/functions.inc.php');
require_once('../../../include/xslfo2pdf/xslfo2pdf.php');
require_once('../../../include/akte.class.php');
require_once('../../../include/vorlage.class.php');
require_once('../../../include/organisationseinheit.class.php');
require_once('../../../include/studiengang.class.php');

$db = new basis_db();

//Parameter setzen
$params='?xmlformat=xml';
if(isset($_GET['id']))
	$params.='&id='.$_GET['id'];
$xml='learningagreement.rdf.php';

$xml_url=XML_ROOT.$xml.$params;

// Load the XML source
$xml_doc = new DOMDocument;

if(!$xml_doc->load($xml_url))
	die('unable to load xml: '.$xml_url);

//XSL aus der DB holen
$vorlage = new vorlage();
$vorlage->getAktuelleVorlage('0', 'LearningAgree');

$xsl_content = $vorlage->text;
//Pdf erstellen

$filename='LearningAgreement';

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
?>
