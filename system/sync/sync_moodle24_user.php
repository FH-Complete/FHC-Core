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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 */
/*
 * Synchronisiert die Lektoren und Studenten der aktuellen MoodleKurse
 * wenn kein aktuelles Studiensemester vorhanden ist, wird NICHT Synchronisiert
 */
require_once(dirname(__FILE__).'/../../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../../config/global.config.inc.php');
require_once(dirname(__FILE__).'/../../include/moodle24_course.class.php');
require_once(dirname(__FILE__).'/../../include/moodle24_user.class.php');
require_once(dirname(__FILE__).'/../../include/studiensemester.class.php');
require_once(dirname(__FILE__).'/../../include/studiengang.class.php');
require_once(dirname(__FILE__).'/../../include/mail.class.php');

$db = new basis_db();
$sync_lektoren_gesamt=0;
$sync_studenten_gesamt=0;
$group_updates=0;
$fehler=0;
$message='';
$message_lkt='';
$lektoren=array();

//ini_set('soap.wsdl_cache_enabled',0);
//ini_set('soap.wsdl_cache_ttl',0);

echo "-- Start ".date('Y-m-d H:i:s')."--";

//nur Synchronisieren wenn ein aktuelles Studiensemester existiert damit keine
//Probleme durch die Vorrueckung entstehen
$stsem = new studiensemester();
if($stsem_kurzbz=$stsem->getakt())
{
	//nur die Eintraege des aktuellen Studiensemesters syncen
	$qry = "SELECT distinct mdl_course_id FROM lehre.tbl_moodle
			WHERE studiensemester_kurzbz=".$db->db_add_param($stsem_kurzbz)."
			AND moodle_version='2.4'";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			echo "<br>\nSync fuer Kurs $row->mdl_course_id";
			flush();

			$course = new moodle24_course();
			if($course->load($row->mdl_course_id))
			{
				$message_lkt='';
				//Lektoren
				$mdluser = new moodle24_user();
				$mitarbeiter = $mdluser->getMitarbeiter($row->mdl_course_id);

				echo "<br>\n-- Mitarbeiter --";
				flush();
				if($mdluser->sync_lektoren($row->mdl_course_id))
				{
					$sync_lektoren_gesamt+=$mdluser->sync_create;
					$group_updates+=$mdluser->group_update;
					if($mdluser->sync_create>0 || $mdluser->group_update>0)
					{
						$message.="\nKurs: $course->mdl_fullname ($course->mdl_shortname) $course->mdl_course_id:\n".$mdluser->log."\n";
						$message_lkt.="\nKurs: $course->mdl_fullname ($course->mdl_shortname) $course->mdl_course_id:\n".$mdluser->log_public."\n";
					}
				}
				else
				{
					$message.="\nFehler: $mdluser->errormsg";
					$fehler++;
				}
				echo $mdluser->log;
				//Lektoren
				$mdluser = new moodle24_user();
				$mitarbeiter = $mdluser->getMitarbeiter($row->mdl_course_id);

				if(defined('MOODLE_SYNC_FACHBEREICHSLEITUNG') && MOODLE_SYNC_FACHBEREICHSLEITUNG)
				{
					echo "<br>\n-- Fachbereichsleitung --";
					flush();
					if($mdluser->sync_fachbereichsleitung($row->mdl_course_id))
					{
						$sync_lektoren_gesamt+=$mdluser->sync_create;
						$group_updates+=$mdluser->group_update;
						if($mdluser->sync_create>0 || $mdluser->group_update>0)
						{
							$message.="\nKurs: $course->mdl_fullname ($course->mdl_shortname) $course->mdl_course_id:\n".$mdluser->log."\n";
							$message_lkt.="\nKurs: $course->mdl_fullname ($course->mdl_shortname) $course->mdl_course_id:\n".$mdluser->log_public."\n";
						}
					}
					else
					{
						$message.="\nFehler: $mdluser->errormsg";
						$fehler++;
					}
					echo $mdluser->log;
				}
				echo "<br>\n-- Studenten --";
				flush();

				//Studenten
				$mdluser = new moodle24_user();
				if($mdluser->sync_studenten($row->mdl_course_id))
				{
					$sync_studenten_gesamt+=$mdluser->sync_create;
					$group_updates+=$mdluser->group_update;
					if($mdluser->sync_create>0 || $mdluser->group_update>0)
					{
						$message.="\nKurs: $course->mdl_fullname ($course->mdl_shortname):\n".$mdluser->log."\n";
						$message_lkt.="\nKurs: $course->mdl_fullname ($course->mdl_shortname):\n".$mdluser->log_public."\n";
					}
				}
				else
				{
					$message.="\nFehler: $mdluser->errormsg";
					$fehler++;
				}

				echo $mdluser->log;
				flush();
				foreach ($mitarbeiter as $uid)
				{
					if(!isset($lektoren[$uid]))
						$lektoren[$uid]='';
					$lektoren[$uid].=$message_lkt;
				}
			}
			else
			{
				$message.="\nFehler: in der Tabelle lehre.tbl_moodle wird auf den Kurs $row->mdl_course_id verwiesen, dieser existiert jedoch nicht im Moodle!";
				$fehler++;
			}
		}

		if($sync_lektoren_gesamt>0 || $sync_studenten_gesamt>0 || $fehler>0 || $group_updates>0)
		{
			//Mail an die Lektoren
			foreach ($lektoren as $uid=>$message_lkt)
			{
				if($message_lkt!='')
				{
					$header = "Dies ist eine automatische Mail!\n";
					$header.= "Es wurden folgende Aktualisierungen an Ihren Moodle-Kursen durchgeführt:\n\n";

					$to = "$uid@".DOMAIN;
					//$to = 'oesi@technikum-wien.at';

					$mail = new mail($to, 'vilesci@'.DOMAIN,'Moodle - Aktualisierungen',$header.$message_lkt);
					if($mail->send())
						echo "Mail wurde an $to versandt<br>";
					else
						echo "Fehler beim Senden des Mails an $to<br>";
				}
			}
			//Mail an Admin
			$header = "Dies ist eine automatische Mail!\n";
			$header.= "Folgende Syncros mit den MoodleKursen wurde durchgeführt:\n\n";
			$header.= "Anzahl der aktualisierten Lektoren: $sync_lektoren_gesamt\n";
			$header.= "Anzahl der aktualisierten Studenten: $sync_studenten_gesamt\n";
			$header.= "Anzahl der Fehler: $fehler\n";

			$to = MAIL_ADMIN;
			//$to = 'oesi@technikum-wien.at';

			$mail = new mail($to, 'vilesci@'.DOMAIN,'Moodle Syncro',$header.$message);
			if($mail->send())
				echo "Mail wurde an $to versandt:<br>".nl2br($header.$message);
			else
				echo "Fehler beim Senden des Mails an $to:<br>".nl2br($header.$message);
		}
		else
		{
			echo "\nAlle Zuteilungen sind auf dem neuesten Stand";
		}
	}
	else
	{
		echo 'Fehler bei Select:'.$qry;
	}
}
else
	echo "Kein aktuelles Studiensemester vorhanden->kein Syncro";
echo "<br>\n-- Ende ".date('Y-m-d H:i:s')." --\n";
?>
