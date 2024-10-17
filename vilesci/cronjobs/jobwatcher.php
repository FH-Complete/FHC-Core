<?php
/* Copyright (C) 2020 Technikum-Wien
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
 * Prueft ob aktuelle Jobs sich im Status running befinden.
 * Falls dies der Fall ist wird ein Warn Mail an den Admin verschickt.
 * Dies soll verhindern dass Jobs die stecken geblieben sind nicht mehr gestartet werden da diese im
 * Running Status hängen und daher nicht erneut gestartet werden.
 */
require_once(dirname(__FILE__).'/../../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../../include/mail.class.php');
require_once(dirname(__FILE__).'/../../include/cronjob.class.php');

//ID des Cronjobs holen
$cj = new cronjob();
if($cj->isJobRunning())
{
	$cj->getAll();

	$text = '';
	$found = false;

	foreach($cj->result as $row)
	{
		if($row->running && (mb_strstr($row->file, 'jobwatcher.php')===false))
		{
			$found = true;
			$text.="Der Cronjob \"$row->titel\" mit ID $row->cronjob_id ist im Status RUNNING\n";
			$text.="Er wurde zuletzt am \"$row->last_execute\" Uhr gestartet.\n";
		}
	}

	if ($found)
	{
		$text.="Der Status muss gegebenenfalls manuell zurückgesetzt werden da der Job sonst nicht erneut startet\n";

		$mail = new mail(MAIL_ADMIN, 'no-reply@'.DOMAIN,'CronJob - RUNNING ALERT',$text);

		if(!$mail->send())
			die('Fehler beim Senden des Mails!');
		else
			echo 'Cronjob Running Alert verschickt';
	}
}
?>
