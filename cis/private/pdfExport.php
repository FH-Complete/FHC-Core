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
 *		  Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *		  Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/* Erstellt einen Lehrauftrag im PDF Format
 *
 * Erstellt ein XML File Transformiert dieses mit
 * Hilfe der XSL-FO Vorlage aus der DB und generiert
 * daraus ein PDF
 */
require_once('../../config/cis.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/akte.class.php');
require_once('../../include/konto.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/vorlage.class.php');
require_once('../../include/addon.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/student.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/dokument_export.class.php');
require_once('../../include/person.class.php');
require_once('../../include/webservicelog.class.php');
require_once('../../include/projektarbeit.class.php');

if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

$user = get_uid();
loadVariables($user);

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

//Parameter holen
if (isset($_GET['xml']))
	$xml = $_GET['xml'];
else
	die('Fehlerhafte Parameteruebergabe');
if (isset($_GET['xsl']))
	$xsl = $_GET['xsl'];
else
	die('Fehlerhafte Parameteruebergabe');

// Studiengang ermitteln dessen Vorlage verwendet werden soll
$xsl_stg_kz = 0;

$sign = false;

// Direkte uebergabe des Studienganges dessen Vorlage verwendet werden soll
if (isset($_GET['xsl_stg_kz']))
	$xsl_stg_kz = $_GET['xsl_stg_kz'];
else
{
	// Wenn eine Studiengangskennzahl uebergeben wird, wird die Vorlage dieses Studiengangs verwendet
	if (isset($_GET['stg_kz']))
		$xsl_stg_kz = $_GET['stg_kz'];
	else
	{
		// Werden UIDs oder Prestudent_IDs uebergeben, wird die Vorlage des Studiengangs genommen
		// in dem der 1. Studierende in der Liste ist
		if (isset($_GET['uid']) && $_GET['uid'] != '')
		{
			if (strstr($_GET['uid'],';'))
				$uids = explode(';',$_GET['uid']);
			else
				$uids[1] = $_GET['uid'];

			$student_obj = new student();
			if ($student_obj->load($uids[1]))
			{
				$xsl_stg_kz = $student_obj->studiengang_kz;
			}
		}
		elseif (isset($_GET['prestudent_id']) && $_GET['prestudent_id'] != '')
		{
			if (strstr($_GET['prestudent_id'],';'))
				$prestudent_ids = explode(';',$_GET['prestudent_id']);
			else
				$prestudent_ids[1] = $_GET['prestudent_id'];

			$prestudent_obj = new prestudent();
			if ($prestudent_obj->load($prestudent_ids[1]))
			{
				$xsl_stg_kz = $prestudent_obj->studiengang_kz;
			}
		}
	}
}

if (isset($_GET['version']) && is_numeric($_GET['version']))
	$version = $_GET['version'];
else
	$version = null;

if (isset($_GET['xsl_oe_kurzbz']))
	$xsl_oe_kurzbz = $_GET['xsl_oe_kurzbz'];
else
	$xsl_oe_kurzbz = '';

//Parameter setzen
$params = 'xmlformat=xml';

//Admins duerfen Dokumente anderer Personen drucken
if ($rechte->isBerechtigt('admin'))
	$user = isset($_GET['uid']) ? $_GET['uid'] : $user;

$params .= '&uid='.$user;
if (isset($_GET['person_id']))
	$params .= '&person_id='.$_GET['person_id'];
if (isset($_GET['buchungsnummern']))
	$params .= '&buchungsnummern='.$_GET['buchungsnummern'];
if (isset($_GET['stg_kz']))
	$params .= '&stg_kz='.$_GET['stg_kz'];
if (isset($_GET['ss']))
	$params .= '&ss='.$_GET['ss'];
if (isset($_GET['abschlusspruefung_id']))
	$params .= '&abschlusspruefung_id='.$_GET['abschlusspruefung_id'];
if (isset($_GET['typ']))
	$params .= '&typ='.$_GET['typ'];
if (isset($_GET['all']))
	$params .= '&all=1';
if (isset($_GET['xsl_oe_kurzbz']))
	$params .= '&xsl_oe_kurzbz='. $_GET['xsl_oe_kurzbz'];
if (isset($_GET['projektarbeit_id']))
	$params .= '&projektarbeit_id='. $_GET['projektarbeit_id'];
if (isset($_GET['betreuerart_kurzbz']))
	$params .= '&betreuerart_kurzbz='. $_GET['betreuerart_kurzbz'];


// Logeintrag bei Download von Zahlungsbestaetigungen
if (isset($_GET['xsl']) && $_GET['xsl'] == 'Zahlung')
{
	$requestdata = $_SERVER['QUERY_STRING'];
	
	$log = new Webservicelog();
	$log->webservicetyp_kurzbz = 'content';
	$log->request_id = isset($_GET['buchungsnummern']) && !empty($_GET['buchungsnummern']) ? $_GET['buchungsnummern'] : NULL;
	$log->beschreibung = 'Zahlungsbestaetigungsdownload';
	$log->request_data = $requestdata;
	$log->execute_user = get_uid();
	
	$log->save(true);
}

//OE fuer Output ermitteln

if ($xsl_oe_kurzbz != '')
{
	$oe_kurzbz = $xsl_oe_kurzbz;
}
else
{
	if ($xsl_stg_kz == '')
		$xsl_stg_kz = '0';
	$oe = new studiengang();
	$oe->load($xsl_stg_kz);
	$oe_kurzbz = $oe->oe_kurzbz;
}

//Darf der User Dokumente in einem NICHT-PDF-Format exportieren?
if (isset($_GET['output']) && $_GET['output'] != 'pdf')
{
	if (!$rechte->isBerechtigt('system/change_outputformat', $oe_kurzbz))
	{
		$output = 'pdf';
	}
	else
		$output = $_GET['output'];
}
else
	$output = 'pdf';

if (isset($_GET['xsl']) && ($_GET['xsl'] === 'Projektbeurteilung'))
{
	if (!isset($_GET['betreuerart_kurzbz']) || !isset($_GET['person_id']) || !isset($_GET['projektarbeit_id']))
		die('Fehlerhafte Parameteruebergabe');

	$projektarbeit = new projektarbeit();
	$projektarbeit->load($_GET['projektarbeit_id']);

	$betreuer = new person();
	$betreuer->getPersonFromBenutzer($user);

	//Überprüft ob es der Betreuer oder der Student ist
	if ($betreuer->person_id !== $_GET['person_id'] && $projektarbeit->student_uid !== $user && !$rechte->isBerechtigt('assistenz'))
		die("<html><body><h3>Sie haben keine Berechtigung für diese Aktion.</h3></body></html>");

	switch ($_GET['betreuerart_kurzbz'])
	{
		case 'Begutachter' :
			$xsl = 'ProjektBeurteilungBA';
			break;
		case 'Erstbegutachter' :
			$xsl = 'ProjektBeurteilungMAErst';
			break;
		case 'Zweitbegutachter' :
			$xsl = 'ProjektBeurteilungMAZweit';
			break;
	}

	$allowed = true;
}


$konto = new konto();
if ((((isset($_GET["uid"]) && $user == $_GET["uid"])) || $rechte->isBerechtigt('admin')) || (isset($allowed) && $allowed === true))
{
	$buchungstypen = array();
	if (defined("CIS_DOKUMENTE_STUDIENBEITRAG_TYPEN"))
	{
		$buchungstypen = unserialize (CIS_DOKUMENTE_STUDIENBEITRAG_TYPEN);
	}

	if (isset($_GET['ss']))
		$stsem_zahlung = $konto->getLastStSemBuchungstypen($user, $buchungstypen, $_GET['ss']);

	if ((($xsl=='Inskription') || ($xsl == 'Studienblatt')) && ($_GET["ss"] != $stsem_zahlung))
	{
		die('Der Studienbeitrag wurde noch nicht bezahlt');
	}
	if (isset($_GET['buchungsnummern']))
	{
		//Beim Drucken von Buchungsbestaetigungen pruefen ob diese Buchungen auch zu diesem Benutzer gehoeren
		$buchungsnr = explode(';',$_GET['buchungsnummern']);
		$user_obj = new benutzer();
		$user_obj->load($user);
		foreach($buchungsnr as $bnr)
		{
			if ($bnr != '')
			{
				$konto->load($bnr);
				if ($konto->person_id!=$user_obj->person_id)
					die('Sie haben keine Berechtigung fuer diese Buchung');
				if ($konto->getDifferenz($bnr)>0)
					die('Diese Zahlung wurde noch nicht beglichen');
			}
		}
	}
	$xml_url = XML_ROOT.$xml.$params;

	if ($xsl_oe_kurzbz == '')
	{
		if ($xsl_stg_kz == '')
			$xsl_stg_kz = '0';
		$stg_obj = new studiengang();
		if (!$stg_obj->load($xsl_stg_kz))
			die($stg_obj->errormsg);
		$xsl_oe_kurzbz = $stg_obj->oe_kurzbz;
	}

	$dokument = new dokument_export($xsl, $xsl_oe_kurzbz, $version);
	$dokument->addDataURL($xml, $params);

	switch($xsl)
	{
		case 'LV_Informationen':
			$studiengang = new studiengang($_GET['stg_kz']);
			$studiensemester = new studiensemester($_GET['ss']);
			$filename = $filename.'_'.$studiengang->kurzbzlang.'_'.$studiensemester->studiensemester_kurzbz;
			break;
		case 'Honorarvertrag':
			$filename = $filename.'_'.$benutzer_obj->nachname.'_'.$benutzer_obj->vorname;
			break;
		case 'Studienordnung':
			$studienordnung = new studienordnung();
			$studienordnung->loadStudienordnung($_GET['studienordnung_id']);
			$filename = 'Studienordnung-Studienplan-'.
			$filename .= sprintf("%'.04d",$studienordnung->studiengang_kz).
			$filename .= '-'.$studienordnung->studiengangkurzbzlang;
			break;
		default:
			$person = new Person();
			$person->getPersonFromBenutzer($user);
			$filename = $xsl. '_'. $person->nachname;
	}

	$dokument->setFilename($filename);

	if (!$dokument->create($output))
		die($dokument->errormsg);

	if ($sign === true)
	{
		if ($dokument->sign($user))
		{
			$dokument->output();
		}
		else
		{
			echo $dokument->errormsg;
		}
	}
	else
		$dokument->output();
	$dokument->close();
}
else
{
	// keine berechtigung
	echo "<html><body><h3>Sie haben keine Berechtigung zum Anzeigen dieser Seite</h3></body></html>";
}
?>
