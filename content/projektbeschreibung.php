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
 * Authors: Karl Burkhart <burkhart@technikum-wien.at>
 * 
 */

session_cache_limiter('none'); //muss gesetzt werden sonst funktioniert der Download mit IE8 nicht
session_start();
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/vorlage.class.php');
require_once('../include/gantt.class.php');
require_once('../include/benutzerberechtigung.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('planner'))
{
	die('Sie haben keine Berechtigung fuer diese Seite');
}

// Parameter holen
if(isset($_GET['xml']))
	$xml=$_GET['xml'];
else
	die('Fehlerhafte Parameteruebergabe');

if(isset($_GET['xsl']))
	$xsl=$_GET['xsl'];
else
	die('Fehlerhafte Parameteruebergabe');

if(isset($_GET['xsl_oe_kurzbz']))
	$xsl_oe_kurzbz=$_GET['xsl_oe_kurzbz'];
else
	$xsl_oe_kurzbz='0';

if(isset($_GET['version']) && is_numeric($_GET['version']))
	$version = $_GET['version'];
else 
	$version ='';

$output = (isset($_GET['output'])?$_GET['output']:'odt');

//Parameter setzen
$params='?xmlformat=xml';
if(isset($_GET['uid']))
	$params.='&uid='.$_GET['uid'];
if(isset($_GET['projekt_kurzbz']))
	$params.='&projekt_kurzbz='.$_GET['projekt_kurzbz'];


$xml_url=XML_ROOT.$xml.$params;

// Load the XML source
$xml_doc = new DOMDocument;

if(!$xml_doc->load($xml_url))
	die('unable to load xml: '.$xml_url);

//XSL aus der DB holen
$vorlage = new vorlage();
$vorlage->getAktuelleVorlage($xsl_oe_kurzbz, $xsl);

$xsl_content = $vorlage->text;

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

    $tempfolder = '/tmp/'.uniqid();
    mkdir($tempfolder);
    chdir($tempfolder);
    mkdir('Pictures');

    file_put_contents('content.xml', $buffer);
    
    // aktuelles Jahr
    $timestamp = time(); 
    $year = date('Y',$timestamp);
    
    $gantt = new gantt(); 
    $gantSvg = $gantt->getBeschreibungGantt($_GET['projekt_kurzbz'], $year-1, 'studienjahr');

    // Bild im Temp Ordner zwischenspeichern
    file_put_contents($tempfolder.'/Pictures/20000001000071B00000242C6CF7933F.svg',$gantSvg);

    $zipfile = DOC_ROOT.'system/vorlage_zip/'.$vorlage->vorlage_kurzbz.'.'.$endung;
    $tempname_zip = 'out.zip';
    if(copy($zipfile, $tempname_zip))
    {
        exec("zip $tempname_zip content.xml");
        // Bilder zum ZIP-File hinzufuegen
        exec("zip $tempname_zip Pictures/*");

        clearstatcache(); 
        if($output == 'pdf')
        {
            $tempPdfName = $vorlage->vorlage_kurzbz.'_'.$_GET['projekt_kurzbz'].'.pdf';
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
            header('Content-Disposition: attachment; filename="'.$vorlage->vorlage_kurzbz.'_'.$_GET['projekt_kurzbz'].'.'.$endung.'"');
            header('Content-Length: '.$fsize); 
        } 

        while (!feof($handle)) 
        {
            echo fread($handle, 8192);
        }
        fclose($handle);

        unlink('content.xml');
		unlink('styles.xml');
		unlink('Pictures/20000001000071B00000242C6CF7933F.svg');
		rmdir('Pictures');
        unlink($tempname_zip);
        if($output=='pdf')
            unlink($tempPdfName);
        rmdir($tempfolder);
    }
}
?>
