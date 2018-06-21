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
			if($datum->DateDiff(date('Y-m-d'), $insert_date) >= -7)
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
		};	
	};
	
	if ($new)
	{
		$new_ampel_user_arr[] =
		array(
				'ampel_id' => $ampel->ampel_id,
				'ampel_bezeichnung' => $kurzbz,
				'user' => $new_user_arr,
				'deadline' => date('d.m.Y', $deadline)
		);
	}
	
	if ($overdue)
	{
		$overdue_ampel_user_arr[] =
		array(
			'ampel_id' => $ampel->ampel_id,
			'ampel_bezeichnung' => $kurzbz,
			'user' => $overdue_user_arr,
			'deadline' => date('d.m.Y', $deadline)
		);
	}
};

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
	$link = APP_ROOT. "cis/index.php?sprache=German&content_id=&menu=". 
		APP_ROOT. "cis/menu.php?content_id=&content=". 
		APP_ROOT. "cis/private/tools/ampelverwaltung.php";

	//eMail data
	$to = $receiver['user']. '@'. DOMAIN;
	$from = 'noreply@'. DOMAIN;				
	$subject = 'Sie haben eine neue Ampel!';
	$headerImg = "sancho_header_neue_nachrichten_in_ampelsystem.jpg";
	$content = "<p align=\"center\">Hallo ". $firstName. ",</p><br>";
	$content .= "<p>es gibt neue Ampeln für Sie:</p><br>";
	
	for ($i = 0; $i < count($receiver) - 1; $i++)
	{
		$receiver[$i]['ampel_id'];
		$content .= "<p><strong>". strtoupper($receiver[$i]['ampel_bezeichnung']). "</strong></p></br>";
	}
	
	$content .= "<p>Sie können sie jetzt gleich in Ihrem Ampelsystem bestätigen:</p>";
	$content .= "<a style=\"color: #bfc130;\" href=". $link. ">Zu meinem Ampelsystem</a></br>";
	$content .= "<p align=\"center\"><br>Schönen Tag noch,</br>";
	$content .= "Sancho</p><br>";
	
	//send eMail
	sendMail($to, $from, $subject, $content, $headerImg);
}

//send eMail for overdue notifications
foreach ($overdue_ampel_user_arr as $receiver)
{
	//get data about sender
	$person = new person();
	$person->getPersonFromBenutzer($receiver['user']);
	$firstName = $person->vorname;
	
	//link to notifications system site
	$link = APP_ROOT. "cis/index.php?sprache=German&content_id=&menu=". 
		APP_ROOT. "cis/menu.php?content_id=&content=". 
		APP_ROOT. "cis/private/tools/ampelverwaltung.php";

	//eMail data
	$to = $receiver['user']  . '@' . DOMAIN;
	$from = 'noreply@'.DOMAIN;			
	$subject = 'Bestätigen Sie bitte Ihre Ampel!';
	$headerImg = "sancho_header_deadline_ampel_overdue.jpg";
	$content = "<p align=\"center\">Hallo " . $firstName . ",</p><br>";
	$content .= "<p>diese Ampeln müssen von Ihnen noch bestätigt werden:</p><br>";
	
	for ($i = 0; $i < count($receiver) - 1; $i++)
	{
		$receiver[$i]['ampel_id'];
		$content .= "<p><strong>" . strtoupper($receiver[$i]['ampel_bezeichnung']) . "</strong><br>";
		$content .= "<small><i style=\"color: #65696E;\">Die Deadline für die Bestätigung war am <span style=\"color: #FF0000;\">" . $receiver[$i]['deadline'] . "</span></i></small></p><br>";
	}
	
	$content .= "<p>Sie können sie jetzt gleich in Ihrem Ampelsystem bestätigen:</p>";
	$content .= "<a style=\"color: #bfc130;\" href=\"$link\">Zu meinem Ampelsystem</a><br>";
	$content .= "<p align=\"center\"><br>Schönen Tag noch!<br>";
	$content .= "Sancho</p><br>";
	
	//send eMail
	sendMail($to, $from, $subject, $content, $headerImg);
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
function sendMail($to, $from, $subject, $html_content, $headerImg = 'sancho_header_sie_haben_neue_nachrichten.jpg')
{
	$sanchoHeader_img = APP_ROOT . "skin/images/sancho/" . $headerImg;
	$sanchoFooter_img = APP_ROOT . "skin/images/sancho/sancho_footer.jpg";
	
	//mail content as plain text (fallback if html not activated)
	$plain_text = "Hallo,\n\n";
	if (!empty ($title))
		$plain_text .= strip_tags($title) . "\n\n";	
	$plain_text .= "Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.";
	
	//mail content as html text
	/*
	 * no css styles in html-head (email clients picky about that)
	 * tables inside tables for correct styles in different email clients
	 * border-collapse and mso-table: this corrects strange behavior of outlook 2013 adding unwished extra space
	 */
	$html_text = '
		<html>
			<head>	
				<title>Sancho Ampelmail</title>
			</head>
			<body><center>
				<table cellpadding="0" cellspacing="0" style="border: 2px solid #000000; padding: 0px; max-width: 850px; 
				border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;">  
					<tr>
						<td align="center">
							<table cellpadding="0" cellspacing="0" width="100%" border="0">
								<tr>
									<td>
										<img src="cid:SanchoHeader" alt="sancho_header" width="100%">
									</td>
								</tr>
							</table>
						</td>
					</tr>
					 <tr>
						<td align="center">
							<table cellpadding="0" cellspacing="0" width="100%" style="font-family: courier, verdana, sans-serif; font-size: 0.95em; border-bottom: 2px solid #000000;">
								<tr>
									<td style="padding-left: 8%; padding-right: 8%; padding-top: 5%; padding-bottom: 5%;">
										<br>' . $html_content . '</br>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td align="center">
							<table cellpadding="0" cellspacing="0" width="100%">
								<tr>
									<td>
										<img src="cid:SanchoFooter" alt="sancho_footer" width="100%">
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
	$mail->addEmbeddedImage($sanchoFooter_img, "image/jpg", "", "SanchoFooter");
	$mail->addEmbeddedImage($sanchoHeader_img, "image/jpg", "", "SanchoHeader");

	if(!$mail->send())
		echo $p->t('global/emailNichtVersendet') . ' an ' . $to . "<br>";
}
 