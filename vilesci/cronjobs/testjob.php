<?php
/* Copyright (C) 2010 Technikum-Wien
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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/**
 * Testjob - schickt ein Mail wenn das Script aufgerufen wird
 * Dient zur Pruefung ob die Cronjobs richtig funktionieren
 * 
 * als erster Parameter kann die UID uebergeben werden. Wenn diese übergeben wird, dann wird das
 * Mail an diese Adresse gesandt (es sei denn MAIL_DEBUG ist gesetzt)
 * Ansonsten wird das Mail an MAIL_AMDIN geschickt.
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/mail.class.php');
require_once('../../include/cronjob.class.php');

//ID des Cronjobs holen
$cj = new cronjob();
$id = $cj->getCronjobID();

//Variableninitialisierung
if($cj->isInitialCall())
{
	if($cj->load($id))
	{
		$variablen['StSem']='SS2010';
		$variablen['stg_kz']=array('255','256','257','258');
		$cj->variablen = json_encode($variablen);
		if($cj->save())
			echo 'Variablen initialisiert';
		else 
			echo 'Fehler beim Initialisieren:'.$cj->errormsg;
	}
	else 
		echo 'Fehler beim Laden der ID '.$id;
	exit;
}

//UID als Kommandozeilenparameter
if(isset($_SERVER['argv']) && isset($_SERVER['argv'][1]) && !strstr($_SERVER['argv'][1],'='))
{
	$user = $_SERVER['argv'][1].'@'.DOMAIN;
}

//UID als GET-Parameter
if(isset($_GET['user']))
	$user = $_GET['user'].'@'.DOMAIN;

//Default Empfaenger
if(!isset($user))
	$user = MAIL_ADMIN;

//Variablen des Jobs laden
$cj->load($id);
$vars = json_decode($cj->variablen, true);

$text = "Dies ist ein Test-Cronjob!\n\n";
$text.="Der Cronjob \"$cj->titel\" hat die ID $id\n";
$text.="Er wurde am ".date('d.m.Y')." um ".date('H:i:s')." Uhr auf dem Server ".SERVER_NAME." gestartet.\n";
$text.="Dieses Mail wurde an $user geschickt";
$text.="\n\nVariablen zu diesem Job Dekodiert:\n".print_r($vars,true);
$text.="\n\nVariablen zu diesem Job Plain:\n".$cj->variablen;

$mail = new mail($user, 'no-reply@'.DOMAIN,'CronJob - Testjob',$text);

if(!$mail->send())
	die('Fehler beim Senden des Mails!');
else 
	echo 'Mail verschickt an: '.$user;

?>