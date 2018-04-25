<?php

/* Copyright (C) 2018 fhcomplete.org
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
 * Authors: Cristina Hainberger <hainberg@technikum-wien.at> */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/mail.class.php');
require_once('../../include/ampel.class.php');
require_once('../../include/person.class.php');
require_once('../../include/phrasen.class.php');

$db = new basis_db();
$datum = new datum();
$now = $datum->mktime_fromdate(date('Y-m-d'));

$sprache = getSprache();
$p = new phrasen($sprache);

//get all notifications
$ampel_obj = new ampel();
$ampel_obj->getAll();		
$ampel_arr = $ampel_obj->result;

//filter only notifications that are not expired, not before vorlaufzeit AND email is true
$ampel_arr = filterAmpeln($ampel_arr);
	
//get user of notifications, ampel_id, description and deadline
$new_ampel_user_arr = array();				//user with new notifications that are not confirmed
$overdue_ampel_user_arr = array();			//user with overdue notifications that are not confirmed
foreach($ampel_arr as $ampel)
{
	$deadline = $datum->mktime_fromdate($ampel->deadline);
	$insert_date = $datum->formatDatum($ampel->insertamum, 'Y-m-d');
	$qry_all_ampel_user = $ampel->benutzer_select;
	$kurzbz = $ampel->kurzbz;
	
	$new = false;
	$overdue = false;
	$new_user_arr = array();
	$overdue_user_arr = array();

	if($result = $db->db_query($qry_all_ampel_user))
	{		
		while($row = $db->db_fetch_object($result))
		{			
			$user = $row->uid;

			//break if almost confirmed
			if ($ampel_obj->isBestaetigt($user, $ampel->ampel_id))
				break;
			
			//check if notification is new (within last week, as cronjob will run every week)
			if ($datum->DateDiff (date('Y-m-d'), $insert_date) >= -7)
			{
				$new = true;
				$new_user_arr[] = $user;
			}
			
			//check if notification is overdue
			if ($now > $deadline)
			{
				$overdue = true;
				$overdue_user_arr[] = $user;
			}			 
		}			
	}
	
	if ($new)
	{
	$new_ampel_user_arr[] = array(
							'ampel_id' => $ampel->ampel_id, 
							'ampel_bezeichnung' => $kurzbz,
							'user' => $new_user_arr,
							'deadline' => date('d.m.Y', $deadline));
	} 
	
	if ($overdue)
	{
	$overdue_ampel_user_arr[] = array(
								'ampel_id' => $ampel->ampel_id, 
								'ampel_bezeichnung' => $kurzbz,
								'user' => $overdue_user_arr,
								'deadline' => date('d.m.Y', $deadline));
	}
}

//rearrange arrays as needed to send in eMails
$new_ampel_user_arr = organizeAmpelnForMail($new_ampel_user_arr);
$overdue_ampel_user_arr = organizeAmpelnForMail($overdue_ampel_user_arr);

//send eMail for new notifications
foreach ($new_ampel_user_arr as $receiver)
{
	//get data about sender
	$person = new person();
	$person->getPersonFromBenutzer($receiver['user']);
	$firstName = $person->vorname;
	
	//link to notifications system site
	$link = APP_ROOT . "cis/index.php?sprache=German&content_id=&menu=" . APP_ROOT . "cis/menu.php?content_id=&content=" . APP_ROOT . "cis/private/tools/ampelverwaltung.php";

	//eMail data
	$to = $receiver['user']  . '@' . DOMAIN;
	$from = 'noreply@'.DOMAIN;				
	$subject = 'Sie haben eine neue Ampel!';
	$title = "Sie haben neue Nachrichten in Ihrem Ampelsystem!";
	$content = "<p>Hallo " . $firstName . ",</p>";
	$content .= "<p>es gibt neue Ampeln für Sie:</p><br>";
	
	for ($i = 0; $i < count($receiver) - 1; $i++)
	{
		$receiver[$i]['ampel_id'];
		$content .= "<p><i>NEU:</i>&nbsp&nbsp<strong>" . $receiver[$i]['ampel_bezeichnung'] . "</strong></p>";
	}
	
	$content .= "<br><p>Sie können sie jetzt gleich in Ihrem Ampelsystem bestätigen:</p>";
	$content .= "<br><a style=\"color: #74ba24;\" href=" . $link . ">Zu meinem Ampelsystem</a></br>";
	$content .= "<p><br>Schönen Tag noch,</p>";
	$content .= "<p>Sancho</p>";
	
	//send eMail
	sendMail($to, $from, $subject, $content, $title);
}

//send eMail for overdue notifications
foreach ($overdue_ampel_user_arr as $receiver)
{
	//get data about sender
	$person = new person();
	$person->getPersonFromBenutzer($receiver['user']);
	$firstName = $person->vorname;
	
	//link to notifications system site
	$link = APP_ROOT . "cis/index.php?sprache=German&content_id=&menu=" . APP_ROOT . "cis/menu.php?content_id=&content=" . APP_ROOT . "cis/private/tools/ampelverwaltung.php";

	//eMail data
	$to = $receiver['user']  . '@' . DOMAIN;
	$from = 'noreply@'.DOMAIN;			
	$subject = 'Bestätigen Sie bitte Ihre Ampel!';
	$title = "Die Deadline für Ihre Ampel ist überschritten!";
	$content = "<p>Hallo " . $firstName . ",</p>";
	$content .= "<p>es gibt Ampeln, die von Ihnen noch bestätigt werden müssen:</p><br>";
	
	for ($i = 0; $i < count($receiver) - 1; $i++)
	{
		$receiver[$i]['ampel_id'];
		$content .= "<p><i>BESTÄTIGUNG FEHLT:</i>&nbsp&nbsp<strong>" . $receiver[$i]['ampel_bezeichnung'] . "</strong></p>";
		$content .= "<p><small><i style=\"color: #65696E;\">Die Deadline für die Bestätigung war am <span style=\"color: #FF0000;\">" . $receiver[$i]['deadline'] . "</span></i></small></p><br>";
	}
	
	$content .= "<br><p>Sie können sie jetzt gleich in Ihrem Ampelsystem bestätigen:</p>";
	$content .= "<br><a style=\"color: #74ba24;\" href=" . $link . ">Zu meinem Ampelsystem</a></br>";
	$content .= "<p><br>Schönen Tag noch,</p>";
	$content .= "<p>Sancho</p>";
	
	//send eMail
	sendMail($to, $from, $subject, $content, $title);
}


//*************************************************************		FUNCTIONS
function filterAmpeln($ampel_arr)
{
	$datum = new datum();
	$now = $datum->mktime_fromdate(date('Y-m-d'));
	$arr = array();
	
	foreach ($ampel_arr as $ampel)
	{
		$deadline = $datum->mktime_fromdate($ampel->deadline);
		$vorlaufzeit = $ampel->vorlaufzeit;
		$verfallszeit = $ampel->verfallszeit;

		$datum_liegt_vor_vorlaufzeit = false;
		$datum_liegt_nach_verfallszeit = false;

		if (!is_null($vorlaufzeit))
			$datum_liegt_vor_vorlaufzeit = $now < strtotime('-' .  $vorlaufzeit . ' day', $deadline); 

		if (!is_null($verfallszeit))
			$datum_liegt_nach_verfallszeit = $now > strtotime('+' . $verfallszeit . ' day', $deadline);
		
		if (!$datum_liegt_vor_vorlaufzeit && !$datum_liegt_nach_verfallszeit  && $ampel->email)
		{
			$arr[] = $ampel;
		}
	}	
	return $arr;
}
function organizeAmpelnForMail ($ampel_user_arr)
{
	$helper_arr = array();
	$unique_user_arr = array();
	foreach ($ampel_user_arr as $ampel_user)
	{
		foreach ($ampel_user['user'] as $user)
		{
			if(!in_array($user, $helper_arr))
			{
				$helper_arr[] = $user;
				$unique_user_arr[] = array(
							'user' => $user,
							array(
							'ampel_id' => $ampel_user['ampel_id'],
							'ampel_bezeichnung' => $ampel_user['ampel_bezeichnung'],
							'deadline' => $ampel_user['deadline']));
			}
			else
			{
				$index = array_search($user, array_column($unique_user_arr, 'user'));
				$unique_user_arr[$index][] =
							array(
							'ampel_id' => $ampel_user['ampel_id'],
							'ampel_bezeichnung' => $ampel_user['ampel_bezeichnung'],
							'deadline' => $ampel_user['deadline']);
			}
		}
	}
	return $unique_user_arr;
}
function sendMail($to, $from, $subject, $html_content, $title = 'Sancho hat neue Nachrichten für Sie!')
{
	$sancho_img = APP_ROOT . "skin/images/sancho_round_right_blue.png";
	$logo_img = APP_ROOT . "skin/images/fh_logo.png";
	
	//mail content as plain text (fallback if html not activated)
	$plain_text = "Hallo,\n\n";
	if (!empty ($title))
		$plain_text .= strip_tags($title) . "\n\n";	
	$plain_text .= "Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.";
	
	//mail content as html text
	/*
	 * no css styles in html-head (email clients picky about that)
	 * tables inside tables for correct styles in different email clients
	 */
	$html_text = '
		<html>
			<head>	
				<title>Sancho Ampelmail</title>
			</head>
			<body><center>
				<table width="650px" cellpadding="0" cellspacing="0" style="border: 2px solid #65696e; border-spacing: 0px; margin-top: 40px;">  
					<tr>
						<td align="left">
							<table cellpadding="0" width="100%" cellspacing="0" style="padding:0; border-bottom: 2px solid #A0A0A0; margin: 0; font-family: arial, verdana, sans-serif; font-size: 0.8em; border-spacing: 0px;">
								<tr>
									<td width="25%" style="padding: 25px;">
										<img src="cid:SanchoFace" align="left" alt="sancho_face" width="105" height="105">
									</td>
									<td width="75%" style="padding: 25px;">
										<h4 align="center" style="padding-top: 20">' . $title . '</h4>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					 <tr>
						<td align="left">
							<table cellpadding="0" width="100%" cellspacing="0" style="padding:0; border-bottom: 2px solid #A0A0A0; margin: 0; font-family: arial, verdana, sans-serif; font-size: 0.8em; border-spacing: 0px;">
								<tr>
									<td style="padding: 25px;">
										<br>' . $html_content . '</br>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td align="left">
							<table cellpadding="0" width="100%" cellspacing="0" style="padding:0; margin: 0; font-family: arial, verdana, sans-serif; font-size: 0.8em; border-spacing: 0px;">
								<tr>
									<td width="75%" style="padding: 25px;">
										<span style="color: #0a629c; padding: 20 0 0 20;">So spannend kann Technik sein!</span>
									</td>
									<td width="25%" style="padding: 25px;">
										<a href="https://www.technikum-wien.at"><img src="cid:Logo" align="right" alt="logo" width="120" height="70">
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</center></body>
		</html>';
	
	$mail = new mail($to, $from, $subject, $plain_text);
	$mail->setHtmlContent($html_text);
	$mail->addEmbeddedImage($sancho_img, "image/png", "", "SanchoFace");
	$mail->addEmbeddedImage($logo_img, "image/png", "", "Logo");

	if(!$mail->send())
		echo $p->t('global/emailNichtVersendet') . ' an ' . $to . "<br>";
}





