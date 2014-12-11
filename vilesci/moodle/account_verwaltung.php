<?php
//@version $Id$
/* Copyright (C) 2008 Technikum-Wien
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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
 
/*
	Dieses Programm listet nach einem Suchbegriff bestehender Benutzer auf. 
	Fuer jede UserID wird geprueft ob dieser bereits einen Moodle ID besitzt.
	Bestehende Moodle IDs werden angezeigt, fuer alle anderen wird die moeglichkeit
	der neuanlage geboten.
*/

// ---------------- Standart Include Dateien einbinden
require_once('../../config/vilesci.config.inc.php');	
require_once('../../include/basis_db.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/globals.inc.php');
require_once('../../include/moodle19_user.class.php');
require_once('../../include/benutzerberechtigung.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/moodle'))
	die('Sie haben keine Berechtigung für diese Seite');

if (!$db = new basis_db())
	die('<div style="text-align:center;"><br />MOODLE Datenbank zurzeit NICHT Online.<br />Bitte etwas Geduld.<br />Danke</div>');
// ***********************************************************************************************	
// Variable Initialisieren
// ***********************************************************************************************
// AusgabeStream
	$content='';

// ***********************************************************************************************
// POST oder GET Parameter einlesen 
// ***********************************************************************************************

// $cUID UserID fuer Moodelaccount anlage
	$cUID = (isset($_REQUEST['uid'])?trim($_REQUEST['uid']):'');
// @$cMdl_user_id Moodleaccount zum loeschen
	$cMdl_user_id = (isset($_REQUEST['mdl_user_id'])?trim($_REQUEST['mdl_user_id']):'');
// @cSearchstr Suchtext in Tabelle Benutzer 
	$cSearchstr = (isset($_REQUEST['searchstr'])?trim($_REQUEST['searchstr']):'');
// ***********************************************************************************************
//	Datenbankverbindungen zu Moodle und Vilesci und Classen
// ***********************************************************************************************
	// Classen Instanzen
	$objMoodle = new moodle19_user();	
// ***********************************************************************************************
//	Verarbeitung einer Moodle-Account Anlageaktion
// ***********************************************************************************************
	if ($cUID!='') // Bearbeiten User UID Anfrage
	{
		// Check ob User nicht bereits angelegt ist
		if (!$bStatus=$objMoodle->loaduser($cUID))
		{
			$objMoodle->errormsg='';
		//  User ist noch nicht in Moodle angelegt => Neuanlage
			if (!$bStatus=$objMoodle->createUser($cUID))
				$content.=$objMoodle->errormsg;
		}	
	}	
// ***********************************************************************************************
//	HTML Suchfeld (Teil 1)
// ***********************************************************************************************
	$content.='
		<form accept-charset="UTF-8" name="search" method="GET" action="'.$_SERVER["PHP_SELF"].'" target="_self">
	  		Bitte Suchbegriff eingeben: 
	  		<input type="text" name="searchstr" size="30" value="'.$cSearchstr.'">
	  		<input type="submit" value=" suchen ">
	  	</form>	
		<hr>';
// ***********************************************************************************************
//	HTML Listenanzeige (Teil 2)
// ***********************************************************************************************
	if($cSearchstr!='' && $cSearchstr!='?'  && $cSearchstr!='*')
	{
		// SQL Select-String
		$qry = "SELECT distinct tbl_person.person_id,tbl_person.nachname,tbl_person.vorname,tbl_person.aktiv,tbl_benutzer.uid
			FROM public.tbl_person ,public.tbl_benutzer
			
			WHERE  tbl_benutzer.person_id=tbl_person.person_id 
			and (		
			tbl_person.nachname ~* '".addslashes($cSearchstr)."' OR 
			tbl_person.vorname ~* '".addslashes($cSearchstr)."' OR
			tbl_benutzer.alias ~* '".addslashes($cSearchstr)."' OR
			tbl_person.nachname || ' ' || tbl_person.vorname = '".addslashes($cSearchstr)."' OR 
			tbl_person.vorname || ' ' || tbl_person.nachname = '".addslashes($cSearchstr)."' OR 
			tbl_benutzer.uid ~* '".addslashes($cSearchstr)."'
			) 
			ORDER BY nachname, vorname;";
#			and tbl_benutzer.uid >'' 
#			and tbl_benutzer.uid IS NOT NULL 

			if($result = $db->db_query($qry))
			{	
				// Header Top mit Anzahl der gelisteten Kurse		
				$content.= '<a name="top">'. $db->db_num_rows($result).' Person(en) gefunden</a>';	
				
				$content.='<table  style="border: 1px outset #F7F7F7;">';

				// Header Teil Information der Funktion	
					$content.='<tr class="liste" align="center">';
						$content.='<td colspan="6"><b>Benutzer</b></td>';
					$content.='</tr>';
					
				// Headerinformation der Tabellenfelder 
					$content.='<tr class="liste" align="center">';
						$content.='<th>&nbsp;Nachname&nbsp;</th>';
						$content.='<th>&nbsp;Vorname&nbsp;</th>';
						$content.='<th>&nbsp;UserID&nbsp;</th>';
						$content.='<th>&nbsp;Status&nbsp;</th>';
						$content.='<th>&nbsp;MoodleAccount&nbsp;</th>';
#						$content.='<th>&nbsp;Bearbeitung&nbsp;</th>';
					$content.='</tr>';
				
					// Alle gefundenen User in einer Schleife anzeigen.
					$iTmpCounter=0;
					while($row = $db->db_fetch_object($result))
					{
						// ZeilenCSS (gerade/ungerade) zur besseren Ansicht
						$iTmpCounter++;
						if ($iTmpCounter%2)
							$showCSS=' style="text-align: left;border: 1px outset #F7F7F7;padding: 1px 5px 1px 5px; background:#FEFFEC" ';
						else
							$showCSS=' style="text-align: left;border: 1px outset #F7F7F7;padding: 1px 5px 1px 5px; background:#FCFCFC"  ';			

						// Listenzeile
						$content.= '<tr '.$showCSS.'>';
							$content.= '<td '.$showCSS.'><a href="../personen/personen_details.php?person_id='.$row->person_id.'">'.$row->nachname.'</a></td>';
							$content.= '<td '.$showCSS.'>'.$row->vorname.'</td>';
							$content.= '<td '.$showCSS.'>'.$row->uid.'</td>';
							$content.= '<td '.$showCSS.'>'.(!empty($row->aktiv) && mb_strtoupper($row->aktiv)!='F' && mb_strtoupper($row->aktiv)!='FALSE' ?'aktiv':'deaktiviert').'</td>';
							$arrMoodleUser=array();	
							$objMoodle->errormsg='';
							$objMoodle->mdl_user_id='';
							if (!empty($row->uid))
							{
								if (!$boolReadMoodle=$objMoodle->loaduser($row->uid))
									$objMoodle->mdl_user_id='';
							}
							// Es gibt noch keinen Moodle User - Anlage ermoeglichen
							if (!isset($objMoodle->mdl_user_id) || empty($objMoodle->mdl_user_id))
							{
								$content.= '<td style="vertical-align:bottom;cursor: pointer;" onclick="document.work'.$iTmpCounter.'.submit();">';
								$content.='<form style="display: inline;border:0px;" name="work'.$iTmpCounter.'" method="GET" target="_self" action="'.$_SERVER["PHP_SELF"].'">';
								  	$content.= '<input style="display:none" type="text" name="uid" value="'.$row->uid.'" />';
								  	$content.= '<input style="display:none" type="text" name="searchstr" value="'.$cSearchstr.'" />';
									$content.= '<img height="12" src="../../skin/images/table_row_insert.png" border="0" title="MoodleUser anlegen" alt="table_row_insert.png" />';					
									$content.= '<input onclick="this.checked=false;" onblur="this.checked=false;" type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" name="check_va_detail_kal'.$iTmpCounter.'" />';
									$content.= 'anlegen';					
								$content.='</form>';
								$content.= '</td>';
							}
							else // Anzeige bestehende Moodle User ID
							{
								$content.= '<td '.$showCSS.'>'.((isset($objMoodle->mdl_user_id) && !empty($objMoodle->mdl_user_id))?$objMoodle->mdl_user_id:'').'</td>';
							}
							// Tastatureingabe ermoeglichen
						$content.= '</tr>';
					} // Ende Schleife der gefundenen User
					$content.= '</table>';
					$content.= '<a href="#top">zum Anfang</a>';
			}	// 	Ende SQL Result abfrage
	} // Ende ob Suchanfrage gestellt (Submit) wurde
	$content='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
		<title>Moodle - Accountverwaltung</title>
		<base target="main">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	</head>
	<body class="background_main">
	<h2>Moodle - Accountverwaltung</h2>
	<!-- MoodleAccount Content Start -->
		'.$content.'
	<!-- MoodleAccount Content Ende -->
	</body>
		</html>';
	exit($content);
?>
