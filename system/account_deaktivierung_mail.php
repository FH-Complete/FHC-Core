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
/*
 * - Dieses Script versendet automatisch Mails an Accounts die Deaktiviert wurden.
 *   und informiert die Benutzer ueber die Folgen der Deaktivierung
 *
 * - Accounts die laenger als 3 Tage deaktiviert sind, werden per Mail an die
 *   Bibliothek gemeldet.
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/mail.class.php');

$db = new basis_db();
$text='';

echo '
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
		<html>
		<head>
		<title>Check</title>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		</head>
		<body class="Background_main">
		<h2>Check</h2>';
		


//Information an Bibliothek wenn ein Account deaktiviert wurde
$qry = "SELECT uid, (SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid=uid) as mitarbeiter, titelpre, vorname, nachname, titelpost FROM public.tbl_benutzer JOIN public.tbl_person USING(person_id) WHERE tbl_benutzer.aktiv=false AND updateaktivam=CURRENT_DATE- interval '3 days'";

if($result = $db->db_query($qry))
{
	if($db->db_num_rows($result)>0)
	{
		//$message = "Dies ist eine automatische Mail!\n";
		$message = "Dies ist eine automatische Nachricht!\n\n";
		$message .= "Folgende Studenten/Mitarbeiter wurden im FAS deaktiviert: \n\n";
		while($row = $db->db_fetch_object($result))
		{
			$message .= " - $row->titelpre $row->vorname $row->nachname $row->titelpost ( $row->uid )\n";
		}
		$message = "\n";
		$message = "Mit freundlichen Grüßen\n";
		$message = "Fachhochschule Technikum Wien\n";
		$message = "Höchstädtplatz 5\n";
		$message = "1200 Wien\n";				

		$to = 'bibliothek@'.DOMAIN;
		//$to = 'oesi@technikum-wien.at';
		//$to = 'sequens@technikum-wien.at';

		$mail = new mail($to, 'vilesci@'.DOMAIN, 'Account Deaktivierung', $message);
		$mail->send();
		$text.= "Warnung für Bibliothek wurde an $to verschickt\n";
	}
}


/*

	-----               Alle die vor einer Woche inaktiv gesetzt wurden darueber informieren
	
*/	
$qry = "SELECT uid, (SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid=uid) as mitarbeiter FROM public.tbl_benutzer WHERE aktiv=false AND updateaktivam=CURRENT_DATE- interval '1 week'";
##$qry = "SELECT uid, (SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid=uid) as mitarbeiter FROM public.tbl_benutzer WHERE aktiv=false limit 120";
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
/*	
		$message = "Dies ist eine automatische Nachricht!\n";
		$message .= "Ihr Benutzerdatensatz wurde von einem unserer Mitarbeiter deaktiviert. Was bedeutet das nun für Sie?\n\n";
		$message .= "Vorerst werden Sie aus allen Mail-Verteilern gelöscht.\n";
		$message .= "Wenn der Datensatz in den nächsten Wochen/Monaten nicht mehr aktiviert wird, führt das System automatisch folgende Aktionen durch:\n";
		$message .= "- Ihr Account wird gelöscht.\n";
		$message .= "- Ihre Mailbox mit sämtlichen Mails wird gelöscht.\n";
		$message .= "- Ihr Home-Verzeichnis mit allen enthaltenen Dateien wird gelöscht.\n\n";
		$message .= "Folgende Fristen gelten derzeit an der FH:\n";
		$message .= "- Mitarbeiter: 12 Monate nach Deaktivierung.\n";
		$message .= "- Student:      6 Monate nach Deaktivierung.\n";
		$message .= "- Abbrecher:    3 Wochen nach Deaktivierung.\n";
		if($row->mitarbeiter!='')
		{
			//Mitarbeiter
			$message .= "Sollte es sich hierbei um einen Irrtum handeln, wenden sie sich bitte an die Mitarbeiter unserer Personalabteillung.\n";
			$message .= "Nicole Sagmeister  - sagmeister@technikum-wien.at\n";
			$message .= "Orestis Kazamias - kazamias@technikum-wien.at\n";
		}
		else
		{
			//Student
			$message .= "Sollte es sich hierbei um einen Irrtum handeln, wenden Sie sich bitte an ihre Studiengangsassistenz.\n";
		}

		$message .= "\n";
		$message .= "Mit freundlichen Grüßen\n";
		$message .= "Fachhochschule Technikum Wien\n";
		$message .= "Höchstädtplatz 5\n";
		$message .= "1200 Wien\n";		
*/				

		if($row->mitarbeiter!='')
		{
			//Mitarbeiter
			$message = "Dies ist eine automatische Nachricht!\n";
			$message .= "\n";
			$message .= "Ihr Benutzerdatensatz wurde deaktiviert! Damit wurden Sie auch aus allen Email-Verteilern gelöscht. Wenn innerhalb einer Frist von 12 Monaten nach Deaktivierung keine neuerliche Aktivierung Ihres Benutzerdatensatzes erfolgt, dann werden zudem folgende Aktionen automatisch durchgeführt werden:\n";
			$message .= "-	Ihr Account wird gelöscht werden\n";
			$message .= "-	Ihre Mailbox mit sämtlichen Mails wird gelöscht werden\n";
			$message .= "-	Ihr Home-Verzeichnis mit allen enthaltenen Dateien wird gelöscht werden\n";
			$message .= "\n";
			$message .= "Sollte es sich bei der Deaktivierung um einen Irrtum handeln, wenden Sie sich bitte an die KollegInnen in der Personalabteilung:\n";
			$message .= "Nicole Sagmeister, nicole.sagmeister@technikum-wien.at\n";
			$message .= "Mag. Orestis Kazamias, orestis.kazamias@technikum-wien.at\n";
			$message .= "\n";
			$message .= "Mit freundlichen Grüßen\n";
			$message .= "\n";
			$message .= "Fachhochschule Technikum Wien\n";
			$message .= "Höchstädtplatz 5\n";
			$message .= "1200 Wien \n";
			$message .= "\n";
			$message .= "Falls Sie weiterhin über Neuigkeiten an der FH Technikum Wien informiert werden wollen, können Sie unter www.technikum-wien.at/newsletter den kostenlosen Newsletter abonnieren.";
		}
		else
		{
			//Student
			$message = "Dies ist eine automatische Nachricht!\n";
			$message .= "\n";
			$message .= "Ihr Benutzerdatensatz wurde deaktiviert! Damit wurden Sie auch aus allen Email-Verteilern gelöscht. Wenn innerhalb einer Frist von 6 Monaten (für Studierende) bzw. 3 Wochen (für AbbrecherInnen) nach Deaktivierung keine neuerliche Aktivierung Ihres Benutzerdatensatzes erfolgt, dann werden zudem folgende Aktionen automatisch durchgeführt werden:\n";
			$message .= "-	Ihr Account wird gelöscht werden\n";
			$message .= "-	Ihre Mailbox mit sämtlichen Mails wird gelöscht werden\n";
			$message .= "-	Ihr Home-Verzeichnis mit allen enthaltenen Dateien wird gelöscht werden\n";
			$message .= "\n";
			$message .= "Sollte es sich bei der Deaktivierung um einen Irrtum handeln, wenden Sie sich bitte umgehend an Ihre Studiengangsassistenz.\n";
			$message .= "\n";
			$message .= "Mit freundlichen Grüßen\n";
			$message .= "\n";
			$message .= "Fachhochschule Technikum Wien\n";
			$message .= "Höchstädtplatz 5\n";
			$message .= "1200 Wien\n";
			$message .= "\n";
			$message .= "Falls Sie weiterhin über Neuigkeiten an der FH Technikum Wien informiert werden wollen, können Sie unter www.technikum-wien.at/newsletter den kostenlosen Newsletter abonnieren.\n";
		}

		
		$to = $row->uid.'@'.DOMAIN;
		//$to = 'oesi@technikum-wien.at';
		//$to = 'sequens@technikum-wien.at';
		
		$mail = new mail($to,'vilesci@'.DOMAIN,'Ihr Datensatz wurde deaktiviert! '.$row->uid, $message);
		$mail->send();
		$text.= "Warnung zur Accountloeschung wurde an $row->uid verschickt\n";
	}
}

/*

	-----               Letzte Warnung vor Accountloeschung verschicken
	
*/	



//**************** Abbrecher
$qry = "SELECT uid FROM public.tbl_benutzer JOIN public.tbl_student ON(uid=student_uid) WHERE
		aktiv=false AND updateaktivam=CURRENT_DATE- interval '".DEL_ABBRECHER_WEEKS." week'
		AND get_rolle_prestudent (prestudent_id, NULL)='Abbrecher'  ";
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
/*	
		$message = "Dies ist eine automatische Nachricht!\n\n";
		$message .= "Ihr Benutzerdatensatz wurde von einem unserer Mitarbeiter deaktiviert. Was bedeutet das nun für Sie?\n\n";
		$message .= "Vorerst werden Sie aus allen Mail-Verteilern gelöscht.\n";
		$message .= "Wenn der Datensatz in den nächsten Tagen nicht mehr aktiviert wird, führt das System automatisch folgende Aktionen durch:\n";
		$message .= "- Ihr Account wird gelöscht.\n";
		$message .= "- Ihre Mailbox mit sämtlichen Mails wird gelöscht.\n";
		$message .= "- Ihr Home-Verzeichnis mit allen enthaltenen Dateien wird gelöscht.\n\n";
		$message .= "Sollte es sich hierbei um einen Irrtum handeln, wenden sie sich bitte an ihre Studiengangsassistenz.\n";
		$message .= "\n";
		$message .= "Mit freundlichen Grüßen\n";
		$message .= "Fachhochschule Technikum Wien\n";
		$message .= "Höchstädtplatz 5\n";
		$message .= "1200 Wien\n";		
*/
		$message = "Dies ist eine automatische Nachricht!\n";
		$message .= "\n";
		$message .= "ACHTUNG: Ihr Benutzerdatensatz".(DEL_ABBRECHER_WEEKS > 1?" wird in den nächsten ".DEL_ABBRECHER_WEEKS." Wochen ":" wird nach einer Woche ")."deaktiviert werden! Damit werden Sie auch aus allen Email-Verteilern gelöscht werden. Wenn innerhalb einer Frist von 6 Monaten (für Studierende) bzw. 3 Wochen (für AbbrecherInnen) nach Deaktivierung keine neuerliche Aktivierung Ihres Benutzerdatensatzes erfolgt, dann werden zudem folgende Aktionen automatisch durchgeführt werden:\n";
		$message .= "-	Ihr Account wird gelöscht werden\n";
		$message .= "-	Ihre Mailbox mit sämtlichen Mails wird gelöscht werden\n";
		$message .= "-	Ihr Home-Verzeichnis mit allen enthaltenen Dateien wird gelöscht werden\n";
		$message .= "\n";
		$message .= "Sollte es sich bei der bevorstehenden Deaktivierung um einen Irrtum handeln, wenden Sie sich bitte an Ihre Studiengangsassistenz.\n";
		$message .= "\n";
		$message .= "Mit freundlichen Grüßen\n";
		$message .= "\n";
		$message .= "Fachhochschule Technikum Wien\n";
		$message .= "Höchstädtplatz 5\n";
		$message .= "1200 Wien \n";
		$message .= "\n";
		$message .= "Falls Sie weiterhin über Neuigkeiten an der FH Technikum Wien informiert werden wollen, können Sie unter www.technikum-wien.at/newsletter den kostenlosen Newsletter abonnieren.\n";
		
		
		$to = $row->uid.'@'.DOMAIN;
		//$to = 'oesi@technikum-wien.at';
		//$to = 'sequens@technikum-wien.at';
		
		$mail = new mail($to,'vilesci@'.DOMAIN,'Ihr Datensatz wurde deaktiviert! Letzte Warnung '.$row->uid, $message);
		$mail->send();
		$text.= "Letzte Warnung zur Accountloeschung wurde an $row->uid verschickt\n";
	}
}

//**************** Studenten
$qry = "SELECT uid FROM public.tbl_benutzer JOIN public.tbl_student ON(uid=student_uid) WHERE
		aktiv=false AND updateaktivam=CURRENT_DATE- interval '".DEL_STUDENT_WEEKS." week'
		AND get_rolle_prestudent (prestudent_id, NULL)<>'Abbrecher'";
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
/*	
		$message = "Dies ist eine automatische Nachricht!\n\n";
		$message .= "Ihr Benutzerdatensatz wurde von einem unserer Mitarbeiter deaktiviert. Was bedeutet das nun für Sie?\n\n";
		$message .= "Vorerst werden Sie aus allen Mail-Verteilern gelöscht.\n";
		$message .= "Wenn der Datensatz in den nächsten Tagen nicht mehr aktiviert wird, führt das System automatisch folgende Aktionen durch:\n";
		$message .= "- Ihr Account wird gelöscht.\n";
		$message .= "- Ihre Mailbox mit sämtlichen Mails wird gelöscht.\n";
		$message .= "- Ihr Home-Verzeichnis mit allen enthaltenen Dateien wird gelöscht.\n\n";
		$message .= "Sollte es sich hierbei um einen Irrtum handeln, wenden sie sich bitte an ihre Studiengangsassistenz.\n";
		$message .= "\n";
		$message .= "Mit freundlichen Grüßen\n";
		$message .= "Fachhochschule Technikum Wien\n";
		$message .= "Höchstädtplatz 5\n";
		$message .= "1200 Wien\n";		
*/
		$message = "Dies ist eine automatische Nachricht!\n";
		$message .= "\n";
		$message .= "ACHTUNG: Ihr Benutzerdatensatz".(DEL_STUDENT_WEEKS > 1?" wird in den nächsten ".DEL_STUDENT_WEEKS." Wochen ":" wird nach einer Woche ")."deaktiviert werden! Damit werden Sie auch aus allen Email-Verteilern gelöscht werden. Wenn innerhalb einer Frist von 6 Monaten (für Studierende) bzw. 3 Wochen (für AbbrecherInnen) nach Deaktivierung keine neuerliche Aktivierung Ihres Benutzerdatensatzes erfolgt, dann werden zudem folgende Aktionen automatisch durchgeführt werden:\n";
		$message .= "-	Ihr Account wird gelöscht werden\n";
		$message .= "-	Ihre Mailbox mit sämtlichen Mails wird gelöscht werden\n";
		$message .= "-	Ihr Home-Verzeichnis mit allen enthaltenen Dateien wird gelöscht werden\n";
		$message .= "\n";
		$message .= "Sollte es sich bei der bevorstehenden Deaktivierung um einen Irrtum handeln, wenden Sie sich bitte an Ihre Studiengangsassistenz.\n";
		$message .= "\n";
		$message .= "Mit freundlichen Grüßen\n";
		$message .= "\n";
		$message .= "Fachhochschule Technikum Wien\n";
		$message .= "Höchstädtplatz 5\n";
		$message .= "1200 Wien \n";
		$message .= "\n";
		$message .= "Falls Sie weiterhin über Neuigkeiten an der FH Technikum Wien informiert werden wollen, können Sie unter www.technikum-wien.at/newsletter den kostenlosen Newsletter abonnieren.\n";
		
		$to = $row->uid.'@'.DOMAIN;
		//$to = 'oesi@technikum-wien.at';
		//$to = 'sequens@technikum-wien.at';


		$mail = new mail($to,'vilesci@'.DOMAIN,'Ihr Datensatz wurde deaktiviert! Letzte Warnung '.$row->uid, $message);
		$mail->send();
		$text.= "Letzte Warnung zur Accountloeschung wurde an $row->uid verschickt\n";
	}
}

//**************** Mitarbeiter
$qry = "SELECT uid FROM public.tbl_benutzer JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid) WHERE
		aktiv=false AND updateaktivam=CURRENT_DATE- interval '".DEL_MITARBEITER_WEEKS." week' ";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
/*	
		$message = "Dies ist eine automatische Nachricht!\n\n";
		$message .= "Ihr Benutzerdatensatz wurde von einem unserer Mitarbeiter deaktiviert. Was bedeutet das nun für Sie?\n\n";
		$message .= "Vorerst werden Sie aus allen Mail-Verteilern gelöscht.\n";
		$message .= "Wenn der Datensatz in den nächsten Tagen nicht mehr aktiviert wird, führt das System automatisch folgende Aktionen durch:\n";
		$message .= "- Ihr Account wird gelöscht.\n";
		$message .= "- Ihre Mailbox mit sämtlichen Mails wird gelöscht.\n";
		$message .= "- Ihr Home-Verzeichnis mit allen enthaltenen Dateien wird gelöscht.\n\n";
		$message .= "Sollte es sich hierbei um einen Irrtum handeln, wenden sie sich bitte an die Mitarbeiter unserer Personalabteillung.\n";
		$message .= "Nicole Sagmeister - sagmeister@technikum-wien.at\n";
		$message .= "Orestis Kazamias - kazamias@technikum-wien.at\n\n";
		$message .= "\n";
		$message .= "Mit freundlichen Grüßen\n";
		$message .= "Fachhochschule Technikum Wien\n";
		$message .= "Höchstädtplatz 5\n";
		$message .= "1200 Wien\n";		
*/
		$message = "Dies ist eine automatische Nachricht!\n";
		$message .= "\n";
		$message .= "ACHTUNG: Ihr Benutzerdatensatz".(DEL_MITARBEITER_WEEKS > 1?" wird in den nächsten ".DEL_MITARBEITER_WEEKS." Wochen ":" wird nach einer Woche ")."deaktiviert werden! Damit werden Sie auch aus allen Email-Verteilern gelöscht werden. Wenn innerhalb einer Frist von 12 Monaten nach Deaktivierung keine neuerliche Aktivierung Ihres Benutzerdatensatzes erfolgt, dann werden zudem folgende Aktionen automatisch durchgeführt werden:\n";
		$message .= "-	Ihr Account wird gelöscht werden\n";
		$message .= "-	Ihre Mailbox mit sämtlichen Mails wird gelöscht werden\n";
		$message .= "-	Ihr Home-Verzeichnis mit allen enthaltenen Dateien wird gelöscht werden\n";
		$message .= "\n";
		$message .= "Sollte es sich bei der bevorstehenden Deaktivierung um einen Irrtum handeln, wenden Sie sich bitte umgehend an die KollegInnen in der Personalabteilung:\n";
		$message .= "Nicole Sagmeister, nicole.sagmeister@technikum-wien.at\n";
		$message .= "Mag. Orestis Kazamias, orestis.kazamias@technikum-wien.at\n";
		$message .= "\n";
		$message .= "Mit freundlichen Grüßen\n";
		$message .= "\n";
		$message .= "Fachhochschule Technikum Wien\n";
		$message .= "Höchstädtplatz 5\n";
		$message .= "1200 Wien \n";
		$message .= "\n";
		$message .= "Falls Sie weiterhin über Neuigkeiten an der FH Technikum Wien informiert werden wollen, können Sie unter www.technikum-wien.at/newsletter den kostenlosen Newsletter abonnieren.\n";
		
		$to = $row->uid.'@'.DOMAIN;
		//$to = 'oesi@technikum-wien.at';
		//$to = 'sequens@technikum-wien.at';


		$mail = new mail($to,'vilesci@'.DOMAIN, 'Ihr Datensatz wurde deaktiviert! Letzte Warnung '.$row->uid, $message);
		$mail->send();
		$text.= "Letzte Warnung zur Accountloeschung wurde an $row->uid verschickt\n";
	}
}
echo nl2br($text);
		//$text='';
if($text!='')
{
	$mail = new mail(MAIL_IT.', tw_ht@technikum-wien.at, schmuderm@technikum-wien.at, vilesci@technikum-wien.at', 'vilesci@'.DOMAIN, 'Account Deaktivierung', "Dies ist eine automatische Mail!\nFolgende Warnungen zur Accountloeschung wurden versandt:\n\n".$text);
	$mail->send();
}

echo '</body></html>';
?>