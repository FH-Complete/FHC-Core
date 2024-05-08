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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */

/**
 * Cronjob zur Versendung von Infomails wenn Coodle Umfragen Beendet sind 
 */

require_once(dirname(__FILE__).'/../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../include/coodle.class.php');
require_once(dirname(__FILE__).'/../include/phrasen.class.php');
require_once(dirname(__FILE__).'/../include/benutzer.class.php');
require_once(dirname(__FILE__).'/../include/mail.class.php');

$coodle = new coodle();
$coodle->getCoodleBeendet();
$p = new phrasen();

foreach($coodle->result as $row)
{
	$benutzer = new benutzer($row->ersteller_uid);
	$subject = 'Ablauf der Coodle Umfrage';
	$mailtext = '';
	$mailtexthtml = '';

	switch($benutzer->geschlecht)
	{
		case 'm':
			$mailtext.="Sehr geehrter Herr ".$benutzer->vorname.' '.$benutzer->nachname."!\n\n";
			$mailtexthtml.="Sehr geehrter Herr ".$benutzer->vorname.' '.$benutzer->nachname."!<br><br>";
			break;
		case 'w':
			$mailtext.="Sehr geehrte Frau ".$benutzer->vorname.' '.$benutzer->nachname."!\n\n";
			$mailtexthtml.="Sehr geehrte Frau ".$benutzer->vorname.' '.$benutzer->nachname."!<br><br>";
			break;
		default:
			$mailtext.="Sehr geehrte(r) Herr/Frau ".$benutzer->vorname.' '.$benutzer->nachname."!\n\n";
			$mailtexthtml.="Sehr geehrte(r) Herr/Frau ".$benutzer->vorname.' '.$benutzer->nachname."!<br><br>";
			break;
	}
	
	$mailtext .= "Ihre Terminumfrage zum Thema \"".$row->titel."\" ist beendet.\n";
	$mailtext .= "Bitte folgen sie dem Link um die Terminumfrage abzuschließen: ".CIS_ROOT."cis/public/coodle.php?coodle_id=".$row->coodle_id."\n\n";
	$mailtext .= $p->t('mail/signatur');
	
	$mailtexthtml .= "Ihre Terminumfrage zum Thema \"".$row->titel."\" ist beendet.<br>";
	$mailtexthtml .= "Bitte folgen sie dem Link um die Terminumfrage abzuschließen: <a href=\"".CIS_ROOT."cis/public/coodle.php?coodle_id=".
		$row->coodle_id."\">Link zur Umfrage</a><br><br>";
	$mailtexthtml .= nl2br($p->t('mail/signatur'));
	
	$mail = new mail($row->ersteller_uid.'@'.DOMAIN, 'no-reply@'.DOMAIN, $subject, $mailtext);
	$mail->setHTMLContent($mailtexthtml);

	if($mail->send())
		echo "Mail versandt an $row->ersteller_uid CoodleID $row->coodle_id\n";
	else
		echo "Fehler beim Mailversand an $row->ersteller_uid CoodleID $row->coodle_id\n";
}

?>

