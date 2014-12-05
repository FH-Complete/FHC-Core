<?php
/* Copyright (C) 2013 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 */ 
/**
 * Dieses Programm listet nach einem Suchbegriff bestehender Benutzer auf. 
 * Fuer jede UserID wird geprueft ob dieser bereits einen Moodle ID besitzt.
 * Bestehende Moodle IDs werden angezeigt, fuer alle anderen wird die Moeglichkeit
 * der Neuanlage geboten.
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/moodle24_user.class.php');
require_once('../../include/benutzerberechtigung.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/moodle'))
	die('Sie haben keine Berechtigung für diese Seite');

if (!$db = new basis_db())
	die('Fehler bei der Datenbankverbindung');

$uid = (isset($_REQUEST['uid'])?trim($_REQUEST['uid']):'');
$mdl_user_id = (isset($_REQUEST['mdl_user_id'])?trim($_REQUEST['mdl_user_id']):'');
$searchstr = (isset($_REQUEST['searchstr'])?trim($_REQUEST['searchstr']):'');
$content='';
$moodle = new moodle24_user();	

if($uid!='')
{
	// Check ob User nicht bereits angelegt ist
	if (!$moodle->loaduser($uid))
	{
		//  User ist noch nicht in Moodle angelegt => Neuanlage
		if (!$moodle->createUser($uid))
				$content.=$moodle->errormsg;
	}	
}

$content.='
	<form name="search" method="GET" action="'.$_SERVER["PHP_SELF"].'" target="_self">
  		Bitte Suchbegriff eingeben: 
  		<input type="text" name="searchstr" size="30" value="'.$db->convert_html_chars($searchstr).'">
  		<input type="submit" value="Suchen">
  	</form>	
	<hr>';

if($searchstr!='' && $searchstr!='?'  && $searchstr!='*')
{
	// SQL Select-String
	$qry = "SELECT 
				distinct tbl_person.person_id,tbl_person.nachname,tbl_person.vorname,
				tbl_person.aktiv,tbl_benutzer.uid
			FROM 
				public.tbl_person 
				JOIN public.tbl_benutzer USING(person_id)
			WHERE  
				tbl_person.nachname ~* ".$db->db_add_param($searchstr)." OR 
				tbl_person.vorname ~* ".$db->db_add_param($searchstr)." OR
				tbl_benutzer.alias ~* ".$db->db_add_param($searchstr)." OR
				tbl_person.nachname || ' ' || tbl_person.vorname = ".$db->db_add_param($searchstr)." OR 
				tbl_person.vorname || ' ' || tbl_person.nachname = ".$db->db_add_param($searchstr)." OR 
				tbl_benutzer.uid ~* ".$db->db_add_param($searchstr)."
			ORDER BY nachname, vorname;";

		if($result = $db->db_query($qry))
		{	
			// Header Top mit Anzahl der gelisteten Kurse		
			$content.= $db->db_num_rows($result).' Person(en) gefunden';	
			
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
				$content.= '<td '.$showCSS.'><a href="../personen/personen_details.php?person_id='.$db->convert_html_chars($row->person_id).'">'.$db->convert_html_chars($row->nachname).'</a></td>';
				$content.= '<td '.$showCSS.'>'.$db->convert_html_chars($row->vorname).'</td>';
				$content.= '<td '.$showCSS.'>'.$db->convert_html_chars($row->uid).'</td>';
				$content.= '<td '.$showCSS.'>'.(!empty($row->aktiv) && mb_strtoupper($row->aktiv)!='F' && mb_strtoupper($row->aktiv)!='FALSE' ?'aktiv':'deaktiviert').'</td>';

				if (!$moodle->loaduser($row->uid))
					$moodle->mdl_user_id='';
				
				// Es gibt noch keinen Moodle User - Anlage ermoeglichen
				if (!isset($moodle->mdl_user_id) || empty($moodle->mdl_user_id))
				{
					$content.= '<td style="vertical-align:bottom;cursor: pointer;" onclick="document.work'.$iTmpCounter.'.submit();">';
					$content.='<form style="display: inline;border:0px;" name="work'.$iTmpCounter.'" method="GET" target="_self" action="'.$_SERVER["PHP_SELF"].'">';
				  	$content.= '<input style="display:none" type="text" name="uid" value="'.$db->convert_html_chars($row->uid).'" />';
				  	$content.= '<input style="display:none" type="text" name="searchstr" value="'.$db->convert_html_chars($searchstr).'" />';
					$content.= '<img height="12" src="../../skin/images/table_row_insert.png" border="0" title="MoodleUser anlegen" alt="table_row_insert.png" />';					
					$content.= 'anlegen';					
					$content.='</form>';
					$content.= '</td>';
				}
				else // Anzeige bestehende Moodle User ID
				{
					$content.= '<td '.$showCSS.'>'.((isset($moodle->mdl_user_id) && !empty($moodle->mdl_user_id))?$moodle->mdl_user_id:'').'</td>';
				}
				$content.= '</tr>';
			}
			$content.= '</table>';
		}
}
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Moodle 2.4 - Accountverwaltung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>
<h2>Moodle 2.4 - Accountverwaltung</h2>
'.$content.'
</body>
	</html>';
?>
