<?php
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/*
 * Synchronisiert die Lektoren und Studenten der aktuellen MoodleKurse
 * wenn kein aktuelles Studiensemester vorhanden ist, wird NICHT Synchronisiert
 */
require_once('../vilesci/config.inc.php');
require_once('../include/moodle_course.class.php');
require_once('../include/moodle_user.class.php');
require_once('../include/studiensemester.class.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Connecten zur DB');

if(!$conn_moodle = pg_pconnect(CONN_STRING_MOODLE))
	die('Fehler beim Connecten zur DB');
	
$sync_lektoren_gesamt=0;
$sync_studenten_gesamt=0;
$fehler=0;
$message='';

//nur Synchronisieren wenn ein aktuelles Studiensemester existiert damit keine 
//Probleme durch die Vorrueckung entstehen
$stsem = new studiensemester($conn);
if($stsem_kurzbz=$stsem->getakt())
{
	//nur die Eintraege des aktuellen Studiensemesters syncen
	$qry = "SELECT distinct mdl_course_id FROM lehre.tbl_moodle WHERE studiensemester_kurzbz='$stsem_kurzbz'";
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			//Lektoren
			$mdluser = new moodle_user($conn, $conn_moodle);
			if($mdluser->sync_lektoren($row->mdl_course_id))
			{
				$sync_lektoren_gesamt+=$mdluser->sync_create;
				if($mdluser->sync_create>0)
				{
					$message.="\nCourse $row->mdl_course_id:\n".$mdluser->log."\n";
				}
			}
			else 
			{
				$message.="\nFehler: $mdluser->errormsg";
				$fehler++;
			}
			
			//Studenten
			$mdluser = new moodle_user($conn, $conn_moodle);
			if($mdluser->sync_studenten($row->mdl_course_id))
			{
				$sync_studenten_gesamt+=$mdluser->sync_create;
				if($mdluser->sync_create>0)
				{
					$message.="\nCourse $row->mdl_course_id:\n".$mdluser->log."\n";
				}
			}
			else
			{
				$message.="\nFehler: $mdluser->errormsg";
				$fehler++;
			}		
		}
		
		if($sync_lektoren_gesamt>0 || $sync_studenten_gesamt>0 || $fehler>0)
		{
			$header = "Dies ist eine automatische Mail!\n";
			$header.= "Folgende Syncros mit den MoodleKursen wurde durchgef�hrt:\n\n";
			$header.= "Anzahl der aktualisierten Lektoren: $sync_lektoren_gesamt\n";
			$header.= "Anzahl der aktualisierten Studenten: $sync_studenten_gesamt\n";
			$header.= "Anzahl der Fehler: $fehler\n";
			
			$to = MAIL_ADMIN;
			//$to = 'oesi@technikum-wien.at';
			
			if(mail($to,'Moodle Syncro', $header.$message, 'From: vilesci@'.DOMAIN))
				echo "Mail wurde an $to versandt:<br>".nl2br($header.$message);
			else 
				echo "Fehler beim Senden des Mails an $to:<br>".nl2br($header.$message);
		}
		else 
		{
			echo 'Alle Zuteilungen sind auf dem neuesten Stand';
		}
	}
	else 
	{
		echo 'Fehler bei Select:'.$qry;
	}
}
else 
	echo "Kein aktuelles Studiensemester vorhanden->kein Syncro";
?>