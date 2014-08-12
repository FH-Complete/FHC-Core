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
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/mail.class.php');

$db = new basis_db();
$text='';
$wochen_zum_entfernen=1;
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Account Deaktivierung - Infomails</title>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	</head>
	<body class="Background_main">
		<h2>Account Deaktivierung - Infomails</h2>';

// Alle die vor einer Woche inaktiv gesetzt wurden darueber informieren
$qry = "SELECT uid, (SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid=uid) as mitarbeiter FROM public.tbl_benutzer WHERE aktiv=false AND updateaktivam=CURRENT_DATE- interval '".$wochen_zum_entfernen." week'";
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if($row->mitarbeiter!='')
		{
			//Mitarbeiter
			$message = "Dies ist eine automatische Nachricht!\n";
			$message .= "\n";
			$message .= "Wir möchten Sie darauf aufmerksam machen, dass Ihr Benutzerdatensatz deaktiviert wurde. Durch diese Deaktivierung wurden Sie auch aus allen Email-Verteilern gelöscht. \n\n";
			$message .= "Sollte innerhalb von 12 Monaten nach der Deaktivierung keine neuerliche Aktivierung Ihres Benutzerdatensatzes erfolgen, dann werden automatisch auch\n";
			$message .= "	- Ihr Account, \n";
			$message .= "	- Ihre Mailbox (inkl. aller E-Mails) und\n";
			$message .= "	- Ihr Home-Verzeichnis (inkl. aller Dateien) gelöscht werden.\n";
			$message .= "\n";
			$message .= "Falls es sich bei der Deaktivierung um einen Irrtum handelt, würden wir Sie bitten, sich umgehend mit den KollegInnen in der Personalabteilung in Verbindung zu setzen: ";
			$message .= "Frau Maria Meyer-Mölleringhof, meyermoe@technikum-wien.at\n";
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
			$message .= "Wir möchten Sie darauf aufmerksam machen, dass Ihr Benutzerdatensatz deaktiviert wurde. Durch diese Deaktivierung wurden Sie auch aus allen Email-Verteilern gelöscht.\n";
			$message .= "\n";
			$message .= "Sollte innerhalb von 6 Monaten (für Studierende) bzw. 3 Wochen (für AbbrecherInnen) nach der Deaktivierung keine neuerliche Aktivierung Ihres Benutzerdatensatzes erfolgen, dann werden automatisch auch\n";
			$message .= "	- Ihr Account,\n";
			$message .= "	- Ihre Mailbox (inkl. aller E-Mails) und\n";
			$message .= "	- Ihr Home-Verzeichnis (inkl. aller Dateien) gelöscht werden.\n";
			$message .= "\n";
			$message .= "Falls es sich bei der Deaktivierung um einen Irrtum handelt, würden wir Sie bitten, sich umgehend mit Ihrer Studiengangsassistenz in Verbindung zu setzen.\n";
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
		
		$mail = new mail($to,'no-reply@'.DOMAIN,'Ihr Datensatz wurde deaktiviert! '.$row->uid, $message);
		$mail->send();
		$text.= "Warnung zur Accountloeschung wurde an $row->uid verschickt\n";
	}
}

// Letzte Warnung vor Accountloeschung verschicken

// Abbrecher
$qry = "SELECT uid FROM public.tbl_benutzer JOIN public.tbl_student ON(uid=student_uid) WHERE
		aktiv=false AND updateaktivam=CURRENT_DATE- interval '".DEL_ABBRECHER_WEEKS." week'
		AND get_rolle_prestudent (prestudent_id, NULL)='Abbrecher'  ";
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$message = "Dies ist eine automatische Nachricht!\n";
		$message .= "\n";
		$message .= "ACHTUNG: Ihr Benutzerdatensatz wurde vor ".(DEL_ABBRECHER_WEEKS > 1?DEL_ABBRECHER_WEEKS." Wochen ":"einer Woche ")."deaktiviert! Sollte innerhalb der nächsten Tage keine neuerliche Aktivierung Ihres Benutzerdatensatzes erfolgen, dann werden automatisch auch\n";
		$message .= "	- Ihr Account,\n";
		$message .= "	- Ihre Mailbox (inkl. aller E-Mails) und\n";
		$message .= "	- Ihr Home-Verzeichnis (inkl. aller Dateien) gelöscht werden.\n";
		$message .= "\n";
		$message .= "Falls es sich bei der Deaktivierung um einen Irrtum handelt, würden wir Sie bitten, sich umgehend mit Ihrer Studiengangsassistenz in Verbindung zu setzen.\n";
		$message .= "\n";
		$message .= "Mit freundlichen Grüßen\n";
		$message .= "\n";
		$message .= "Fachhochschule Technikum Wien\n";
		$message .= "Höchstädtplatz 5\n";
		$message .= "1200 Wien \n";
		$message .= "\n";
		$message .= "Falls Sie weiterhin über Neuigkeiten an der FH Technikum Wien informiert werden wollen, können Sie unter www.technikum-wien.at/newsletter den kostenlosen Newsletter abonnieren.\n";

		$to = $row->uid.'@'.DOMAIN;

		$mail = new mail($to,'no-reply@'.DOMAIN,'Ihr Datensatz wurde deaktiviert! Letzte Warnung '.$row->uid, $message);
		$mail->send();
		$text.= "Letzte Warnung zur Accountloeschung wurde an $row->uid verschickt\n";
	}
}


// Abbrecher an Bibliothek melden wenn diese inaktiv gesetzt wurden
$qry = "SELECT uid, vorname, nachname, titelpre, titelpost FROM public.tbl_benutzer JOIN public.tbl_student ON(uid=student_uid) JOIN public.tbl_person USING(person_id) WHERE
		tbl_benutzer.aktiv=false AND tbl_benutzer.updateaktivam=(CURRENT_DATE - '1 day'::interval)::date
		AND get_rolle_prestudent (prestudent_id, NULL)='Abbrecher'  ";
if($result = $db->db_query($qry))
{
	if($db->db_num_rows($result)>0)
	{
		$message = "Dies ist eine automatische Nachricht!\n\n";
		$message.= "Die folgenden Studierenden wurden als Abbrecher eingetragen:\n\n";
		while($row = $db->db_fetch_object($result))
		{
			$message.=trim($row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost).' ( '.$row->uid.'@'.DOMAIN." )\n";
		}
		$message .= "\nMit freundlichen Grüßen\n";
		$message .= "\n";
		$message .= "Fachhochschule Technikum Wien\n";
		$message .= "Höchstädtplatz 5\n";
		$message .= "1200 Wien \n";
		$to = 'wienerro@technikum-wien.at, astfaell@technikum-wien.at, ganzera@technikum-wien.at';
		$mail = new mail($to,'no-reply@'.DOMAIN,'Abbrecher Information', $message);
		if($mail->send())
			$text.="Abbrecher Infomail an $to verschickt\n";
		else
			$text.="Fehler beim Versenden des Abbrecher Infomails an $to !\n";  
	}
}

// Studenten
$qry = "SELECT uid FROM public.tbl_benutzer JOIN public.tbl_student ON(uid=student_uid) WHERE
		aktiv=false AND updateaktivam=CURRENT_DATE- interval '".DEL_STUDENT_WEEKS." week'
		AND get_rolle_prestudent (prestudent_id, NULL)<>'Abbrecher'";
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$message = "Dies ist eine automatische Nachricht!\n";
		$message .= "\n";
		$message .= "ACHTUNG: Ihr Benutzerdatensatz  wurde vor ".(DEL_STUDENT_WEEKS > 1?DEL_STUDENT_WEEKS." Wochen ":"einer Woche ")."deaktiviert! Sollte innerhalb der nächsten Tage keine neuerliche Aktivierung Ihres Benutzerdatensatzes erfolgen, dann werden automatisch auch\n";
		$message .= "	- Ihr Account,\n";
		$message .= "	- Ihre Mailbox (inkl. aller E-Mails) und\n";
		$message .= "	- Ihr Home-Verzeichnis (inkl. aller Dateien) gelöscht werden\n";
		$message .= "\n";
		$message .= "Falls es sich bei der Deaktivierung um einen Irrtum handelt, würden wir Sie bitten, sich umgehend mit Ihrer Studiengangsassistenz in Verbindung zu setzen.\n";
		$message .= "\n";
		$message .= "Mit freundlichen Grüßen\n";
		$message .= "\n";
		$message .= "Fachhochschule Technikum Wien\n";
		$message .= "Höchstädtplatz 5\n";
		$message .= "1200 Wien \n";
		$message .= "\n";
		$message .= "Falls Sie weiterhin über Neuigkeiten an der FH Technikum Wien informiert werden wollen, können Sie unter www.technikum-wien.at/newsletter den kostenlosen Newsletter abonnieren.\n";
		
		$to = $row->uid.'@'.DOMAIN;

		$mail = new mail($to,'no-reply@'.DOMAIN,'Ihr Datensatz wurde deaktiviert! Letzte Warnung '.$row->uid, $message);
		$mail->send();
		$text.= "Letzte Warnung zur Accountloeschung wurde an $row->uid verschickt\n";
	}
}

// Mitarbeiter
$qry = "SELECT uid FROM public.tbl_benutzer JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid) WHERE
		aktiv=false AND updateaktivam=CURRENT_DATE- interval '".DEL_MITARBEITER_WEEKS." week' ";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$message = "Dies ist eine automatische Nachricht!\n";
		$message .= "\n";
		$message .= "ACHTUNG: Ihr Benutzerdatensatz  wurde vor ".(DEL_MITARBEITER_WEEKS > 1?DEL_MITARBEITER_WEEKS." Wochen ":"einer Woche ")."deaktiviert! Sollte innerhalb der nächsten Tage keine neuerliche Aktivierung Ihres Benutzerdatensatzes erfolgen, dann werden automatisch auch\n";
		$message .= "	- Ihr Account,\n";
		$message .= "	- Ihre Mailbox (inkl. aller E-Mails) und\n";
		$message .= "	- Ihr Home-Verzeichnis (inkl. aller Dateien) gelöscht werden\n";
		$message .= "\n";
		$message .= "Falls es sich bei der Deaktivierung um einen Irrtum handelt, würden wir Sie bitten, sich umgehend mit den KollegInnen in der Personalabteilung in Verbindung zu setzen: ";
		$message .= "Frau Maria Meyer-Mölleringhof, meyermoe@technikum-wien.at\n";
		$message .= "\n";
		$message .= "Mit freundlichen Grüßen\n";
		$message .= "\n";
		$message .= "Fachhochschule Technikum Wien\n";
		$message .= "Höchstädtplatz 5\n";
		$message .= "1200 Wien \n";
		$message .= "\n";
		$message .= "Falls Sie weiterhin über Neuigkeiten an der FH Technikum Wien informiert werden wollen, können Sie unter www.technikum-wien.at/newsletter den kostenlosen Newsletter abonnieren.\n";
		
		$to = $row->uid.'@'.DOMAIN;
		
		$mail = new mail($to,'no-reply@'.DOMAIN, 'Ihr Datensatz wurde deaktiviert! Letzte Warnung '.$row->uid, $message);
		$mail->send();
		$text.= "Letzte Warnung zur Accountloeschung wurde an $row->uid verschickt\n";
	}
}
echo nl2br($text);

if($text!='')
{
	$mail = new mail(MAIL_IT.', vilesci@technikum-wien.at', 'vilesci@'.DOMAIN, 'Account Deaktivierung', "Dies ist eine automatische Mail!\nFolgende Warnungen zur Accountloeschung wurden versandt:\n\n".$text);
	$mail->send();
}

echo '</body></html>';
?>