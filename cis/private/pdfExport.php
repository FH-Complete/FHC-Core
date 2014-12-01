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


$konto = new konto();
if (($user == $_GET["uid"]) || $rechte->isBerechtigt('admin'))
{
	if($xsl=='Inskription' && (!$konto->checkStudienbeitrag($user, $_GET["ss"])))
		die('Der Studienbeitrag wurde noch nicht bezahlt');		
	
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
	$qry = "SELECT text FROM public.tbl_vorlagestudiengang WHERE (studiengang_kz=0 OR studiengang_kz=".$db->db_add_param($xsl_stg_kz).") AND vorlage_kurzbz=".$db->db_add_param($xsl)." ORDER BY studiengang_kz DESC, version DESC LIMIT 1";
	
	if(!$result = $db->db_query($qry))
		die('Fehler beim laden der Vorlage'.$db->db_last_error());
	if(!$row = $db->db_fetch_object($result))
		die('Vorlage wurde nicht gefunden'.$qry);
	
	// Load the XSL source
	$xsl_doc = new DOMDocument;
	
	if(!$xsl_doc->loadXML($row->text))
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
else
{
	// kein berechtigung
	echo "<html><body><h3>Sie haben keine Berechtigung zum Anzeigen dieser Seite</h3></body></html>";
}
?>
