<?php
/* Copyright (C) 2006 fhcomplete.org
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
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

$user = get_uid();
$db = new basis_db();

//Parameter holen
if(isset($_GET['xml']))
	$xml=$_GET['xml'];
else
	die('Fehlerhafte1 Parameteruebergabe');


if(isset($_GET['xsl']))
	$xsl=$_GET['xsl'];
else
	die('Fehlerhafte2 Parameteruebergabe');

$xsl_stg_kz=0;
if(isset($_GET['xsl_stg_kz']))
	$xsl_stg_kz=$_GET['xsl_stg_kz'];
else
	if(isset($_GET['stg_kz']))
		$xsl_stg_kz=$_GET['stg_kz'];
	else
		if(isset($_GET['uid']) && $_GET['uid']!='')
		{
			if(strstr($_GET['uid'],';'))
				$uids = explode(';',$_GET['uid']);
			else 
				$uids = $_GET['uid'];

			$qry = "SELECT uid, studiengang_kz FROM public.tbl_prestudent WHERE uid=".$db->db_add_param($uids[1]);
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
if(isset($_GET['version']) && is_numeric($_GET['version']))
	$version = $_GET['version'];
else 
	$version ='';

$output = (isset($_GET['output'])?$_GET['output']:'odt');

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

$xsl_content = $vorlage->text;
loadVariables($user);

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
        

        $vorlage->getAktuelleVorlage($xsl_stg_kz, 'AusbildStatus', $version);
        $xsl_content = $vorlage->text;
        
        
        $xsl_doc = new DOMDocument;
		if(!$xsl_doc->loadXML($xsl_content))
			die('unable to load xsl');
        
		// Configure the transformer
		$proc = new XSLTProcessor;
		$proc->importStyleSheet($xsl_doc); // attach the xsl rules
		
		$buffer1 = $proc->transformToXml($xml_doc);
		//echo $buffer;
		//exit;
		chdir($tempfolder);
		file_put_contents('styles.xml', $buffer1);
        
        

		$zipfile = DOC_ROOT.'system/vorlage_zip/Ausbildungsver.'.$endung;
		$tempname_zip = 'out.zip';
		if(copy($zipfile, $tempname_zip))
		{
			exec("zip $tempname_zip content.xml");
            exec("zip $tempname_zip styles.xml");
            clearstatcache(); 
            
            if($output == 'pdf')
            {
                $tempPdfName = 'Ausbildungsver.pdf';
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
                header('Content-Disposition: attachment; filename="Ausbildungsver.odt"');
                header('Content-Length: '.$fsize); 
           } 
            
		    while (!feof($handle)) 
		    {
			  	echo fread($handle, 8192);
			}
			fclose($handle);

			unlink('content.xml');
			unlink('styles.xml');
			unlink($tempname_zip);
            if($output=='pdf')
                unlink($tempPdfName);
			rmdir($tempfolder);
		}
	}

}
?>
