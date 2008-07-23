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
require_once('../vilesci/config.inc.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Connecten zur DB');

echo '
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
		<html>
		<head>
		<title>Check</title>
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<meta http-equiv="content-type" content="text/html; charset=ISO-8859-9" />
		</head>
		<body class="Background_main">
		<h2>Check</h2>';
$text='';

//Information an Bibliothek wenn ein Account deaktiviert wurde
$qry = "SELECT uid, (SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid=uid) as mitarbeiter, titelpre, vorname, nachname, titelpost FROM public.tbl_benutzer JOIN public.tbl_person USING(person_id) WHERE tbl_benutzer.aktiv=false AND updateaktivam=CURRENT_DATE- interval '3 days'";

if($result = pg_query($conn, $qry))
{
	if(pg_num_rows($result)>0)
	{
		$message = "Dies ist eine automatische Mail!\n";
		$message .= "Folgende Studenten/Mitarbeiter wurden im FAS deaktiviert: \n\n";
		while($row = pg_fetch_object($result))
		{
			$message .= " - $row->titelpre $row->vorname $row->nachname $row->titelpost ( $row->uid )\n";
		}

		$message .= "\nMit freundlichen Grüßen,\n";
		$message .= "FACHHOCHSCHULE TECHNIKUM WIEN\n";
		$message .= "Höchstädtplatz 5\n";
		$message .= "A-1200 Wien \n";

		//$to = 'oesi@technikum-wien.at';
		$to = 'bibliothek@'.DOMAIN;
		mail($to,'Account Deaktivierung ', $message, 'From: vilesci@'.DOMAIN);
		$text.= "Warnung fuer Bibliothek wurde an $to verschickt\n";
	}
}

//Alle die vor einer Woche inaktiv gesetzt wurden darueber informieren
$qry = "SELECT uid, (SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid=uid) as mitarbeiter FROM public.tbl_benutzer WHERE aktiv=false AND updateaktivam=CURRENT_DATE- interval '1 week'";

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$message = "Dies ist eine automatische Mail!\n";
		$message .= "Ihr Benutzerdatensatz wurde von einem unserer Mitarbeiter deaktiviert. Was bedeutet das nun für Sie?\n\n";
		$message .= "Vorerst werden Sie aus allen Mail-Verteilern gelöscht.\n";
		$message .= "Wenn der Datensatz in den nächsten Wochen/Monaten nicht mehr aktiviert wird, führt das System automatisch folgende Aktionen durch:\n";
		$message .= "- Ihr Account wird gelöscht.\n";
		$message .= "- Ihre Mailbox mit sämtlichen Mails wird gelöscht.\n";
		$message .= "- Ihr Home-Verzeichnis mit allen enthaltenen Dateien wird gelöscht.\n\n";
		$message .= "Folgende Fristen gelten derzeit an der FH:\n";
		$message .= "- Mitarbeiter: 12 Monate nach Deaktivierung.\n";
		$message .= "- Student:      6 Monate nach Deaktivierung.\n";
		$message .= "- Abbrecher:    3 Wochen nach Deaktivierung.\n\n";
		if($row->mitarbeiter!='')
		{
			//Mitarbeiter
			$message .= "Sollte es sich hierbei um einen Irrtum handeln, wenden sie sich bitte an die Mitarbeiter unserer Personalabteillung.\n";
			$message .= "Adelheit Schaaf  - schaaf@technikum-wien.at\n";
			$message .= "Orestis Kazamias - kazamias@technikum-wien.at\n\n";
		}
		else
		{
			//Student
			$message .= "Sollte es sich hierbei um einen Irrtum handeln, wenden sie sich bitte an ihre Studiengangsassistenz.\n";
		}
		$message .= "Mit freundlichen Grüßen,\n";
		$message .= "FACHHOCHSCHULE TECHNIKUM WIEN\n";
		$message .= "Höchstädtplatz 5\n";
		$message .= "A-1200 Wien \n";

		//$to = 'oesi@technikum-wien.at';
		$to = $row->uid.'@'.DOMAIN;
		mail($to,'Ihr Datensatz wurde deaktiviert! '.$row->uid, $message, 'From: vilesci@'.DOMAIN);
		$text.= "Warnung zur Accountloeschung wurde an $row->uid verschickt\n";
	}
}

//Letzte Warnung vor Accountloeschung verschicken
//Abbrecher
$qry = "SELECT uid FROM public.tbl_benutzer JOIN public.tbl_student ON(uid=student_uid) WHERE
		aktiv=false AND updateaktivam=CURRENT_DATE- interval '".DEL_ABBRECHER_WEEKS." week'
		AND get_rolle_prestudent (prestudent_id, NULL)='Abbrecher'";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$message = "Dies ist eine automatische Mail!\n";
		$message .= "Ihr Benutzerdatensatz wurde von einem unserer Mitarbeiter deaktiviert. Was bedeutet das nun für Sie?\n\n";
		$message .= "Vorerst werden Sie aus allen Mail-Verteilern gelöscht.\n";
		$message .= "Wenn der Datensatz in den nächsten Tagen nicht mehr aktiviert wird, führt das System automatisch folgende Aktionen durch:\n";
		$message .= "- Ihr Account wird gelöscht.\n";
		$message .= "- Ihre Mailbox mit sämtlichen Mails wird gelöscht.\n";
		$message .= "- Ihr Home-Verzeichnis mit allen enthaltenen Dateien wird gelöscht.\n\n";
		$message .= "Sollte es sich hierbei um einen Irrtum handeln, wenden sie sich bitte an ihre Studiengangsassistenz.\n";
		$message .= "Mit freundlichen Grüßen,\n";
		$message .= "FACHHOCHSCHULE TECHNIKUM WIEN\n";
		$message .= "Höchstädtplatz 5\n";
		$message .= "A-1200 Wien \n";

		//$to = 'oesi@technikum-wien.at';
		$to = $row->uid.'@'.DOMAIN;
		mail($to,'Ihr Datensatz wurde deaktiviert! Letzte Warnung '.$row->uid, $message, 'From: vilesci@'.DOMAIN);
		$text.= "Letzte Warnung zur Accountloeschung wurde an $row->uid verschickt\n";
	}
}

//Studenten
$qry = "SELECT uid FROM public.tbl_benutzer JOIN public.tbl_student ON(uid=student_uid) WHERE
		aktiv=false AND updateaktivam=CURRENT_DATE- interval '".DEL_STUDENT_WEEKS." week'
		AND get_rolle_prestudent (prestudent_id, NULL)<>'Abbrecher'";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$message = "Dies ist eine automatische Mail!\n";
		$message .= "Ihr Benutzerdatensatz wurde von einem unserer Mitarbeiter deaktiviert. Was bedeutet das nun für Sie?\n\n";
		$message .= "Vorerst werden Sie aus allen Mail-Verteilern gelöscht.\n";
		$message .= "Wenn der Datensatz in den nächsten Tagen nicht mehr aktiviert wird, führt das System automatisch folgende Aktionen durch:\n";
		$message .= "- Ihr Account wird gelöscht.\n";
		$message .= "- Ihre Mailbox mit sämtlichen Mails wird gelöscht.\n";
		$message .= "- Ihr Home-Verzeichnis mit allen enthaltenen Dateien wird gelöscht.\n\n";
		$message .= "Sollte es sich hierbei um einen Irrtum handeln, wenden sie sich bitte an ihre Studiengangsassistenz.\n";
		$message .= "Mit freundlichen Grüßen,\n";
		$message .= "FACHHOCHSCHULE TECHNIKUM WIEN\n";
		$message .= "Höchstädtplatz 5\n";
		$message .= "A-1200 Wien \n";

		//$to = 'oesi@technikum-wien.at';
		$to = $row->uid.'@'.DOMAIN;
		mail($to,'Ihr Datensatz wurde deaktiviert! Letzte Warnung '.$row->uid, $message, 'From: vilesci@'.DOMAIN);
		$text.= "Letzte Warnung zur Accountloeschung wurde an $row->uid verschickt\n";
	}
}

//Mitarbeiter
$qry = "SELECT uid FROM public.tbl_benutzer JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid) WHERE
		aktiv=false AND updateaktivam=CURRENT_DATE- interval '".DEL_MITARBEITER_WEEKS." week'";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$message = "Dies ist eine automatische Mail!\n";
		$message .= "Ihr Benutzerdatensatz wurde von einem unserer Mitarbeiter deaktiviert. Was bedeutet das nun für Sie?\n\n";
		$message .= "Vorerst werden Sie aus allen Mail-Verteilern gelöscht.\n";
		$message .= "Wenn der Datensatz in den nächsten Tagen nicht mehr aktiviert wird, führt das System automatisch folgende Aktionen durch:\n";
		$message .= "- Ihr Account wird gelöscht.\n";
		$message .= "- Ihre Mailbox mit sämtlichen Mails wird gelöscht.\n";
		$message .= "- Ihr Home-Verzeichnis mit allen enthaltenen Dateien wird gelöscht.\n\n";
		$message .= "Sollte es sich hierbei um einen Irrtum handeln, wenden sie sich bitte an die Mitarbeiter unserer Personalabteillung.\n";
		$message .= "Adelheit Schaaf  - schaaf@technikum-wien.at\n";
		$message .= "Orestis Kazamias - kazamias@technikum-wien.at\n\n";
		$message .= "Mit freundlichen Grüßen,\n";
		$message .= "FACHHOCHSCHULE TECHNIKUM WIEN\n";
		$message .= "Höchstädtplatz 5\n";
		$message .= "A-1200 Wien \n";

		//$to = 'oesi@technikum-wien.at';
		$to = $row->uid.'@'.DOMAIN;
		mail($to,'Ihr Datensatz wurde deaktiviert! Letzte Warnung '.$row->uid, $message, 'From: vilesci@'.DOMAIN);
		$text.= "Letzte Warnung zur Accountloeschung wurde an $row->uid verschickt\n";
	}
}

echo nl2br($text);
if($text!='')
{
	mail(MAIL_IT.', tw_ht@technikum-wien.at, schmuderm@technikum-wien.at, vilesci@technikum-wien.at' , 'Account Deaktivierung', "Dies ist eine automatische Mail!\nFolgende Warnungen zur Accountloeschung wurden versandt:\n\n".$text, 'From: vilesci@'.DOMAIN);
}

echo '</body></html>';
?>