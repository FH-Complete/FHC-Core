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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *		  Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *		  Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
require_once('../../config/cis.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/phrasen.class.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/gruppe.class.php');
require_once('../../include/student.class.php');
require_once('../../include/benutzerfunktion.class.php');
require_once('../../include/studiengang.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);
$uid = get_uid();

$is_lector=check_lektor($uid);
$is_stdv=false;
$std_obj = new student($uid); 

//Studien- und Hochschulvertreter duerfen den Verteiler tw_std oeffnen
if(!$is_lector)
{
	$fkt = new benutzerfunktion();
	if($fkt->benutzerfunktion_exists($uid, 'stdv', true)) // Studienvertretung
		$is_stdv=true;
	elseif($fkt->benutzerfunktion_exists($uid, 'hsv', true)) // Hochschulvertretung
		$is_stdv=true;
}

$db = new basis_db();
if (!isset($_REQUEST['grp']))
	die('Parameter "grp" wurde nicht uebergeben');

if (mb_strlen($_REQUEST['grp'])>32)
	die('Grp ungueltig');

//Pruefen ob es eine gueltige Gruppe ist
$gruppe = new gruppe();
if (!$gruppe->exists($_REQUEST['grp']))
{
	//Wenn es keine Gruppe in der DB ist, kann es
	//noch ein Studierendenverteiler sein
	//zb bif_std oder tw_std
	if (!preg_match('/^\D\D\D_std$/', $_REQUEST['grp']))
	{
		die('Ungueltige Gruppe');
	}
	else
	{
		// Kürzel aus Gruppe auslesen
		$studiengang_kuerzel = substr($_REQUEST['grp'], 0, strpos($_REQUEST['grp'], '_'));
		$studiengang = new studiengang();
		$studiengang->getStudiengangFromOe($studiengang_kuerzel);

		// Lektoren oder Studierende dieses Studiengangs dürfen
		if (!$is_lector && $std_obj->studiengang_kz != $studiengang->studiengang_kz)
			die('Sie haben keine Berechtigung zum öffnen dieses Mailverteilers');
	}
}
elseif (!$is_lector && $_REQUEST['grp'] == 'tw_std')
{
	//Studien- und Hochschulvertreter duerfen den Verteiler tw_std oeffnen
	if ($is_stdv === false)
		die('!$is_lector Sie haben keine Berechtigung zum öffnen dieses Mailverteilers');
}

function mail_id_generator()
{
	mt_srand((double)microtime()*1000000);

	/* Laenge des Passwortes dem Zufall ueberlassen */
	$length = 6;
	$fix_similar = '';
	$valid_charset = "";

	/* Stelle ein Charset zusammen */
	if (!$valid_charset)
	{
		$valid_charset .= 'abcdefghijklmnopqrstuvxyz';
		$valid_charset .= '0123456789';
	}

	$charset_length = mb_strlen($valid_charset);

	if ($charset_length == 0) return false;

	/* Initialisieren - Auswahl von chars bis definierte Anzahl erreicht */
	$mail_id = '';
	while (strlen($mail_id) < $length)
	{
		/* Waehle einen zufaelligen char aus */
		$char = $valid_charset[mt_rand(0, ($charset_length-1))];

		/* Abgleich von gleich aussehenden chars */
		if (($fix_similar && !strpos('O01lI5S', $char)) || !$fix_similar) $mail_id .= $char;
	}
	return $mail_id;
}

echo '<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
	<title>'.$p->t('mailverteiler/oeffnenEinesVerteilers').'</title>
</head>
<style>
body
{
	width: 95%;
	margin: 10px;
	overflow: hidden;
}
.button_oeffnen
{
	display: block;
	color: #fff;
	background-color: #337ab7;
	padding: 6px 12px;
	margin-bottom: 0;
	margin-top: 10px;
	text-align: center;
	white-space: nowrap;
	vertical-align: middle;
	cursor: pointer;
	background-image: none;
	border-radius: 4px;
	border: 1px solid transparent;
	overflow: visible;
	box-sizing: border-box;
	text-transform: none;
}
.button_oeffnen:hover
{
	display: block;
	color: #fff;
	background-color: #286090;
	padding: 6px 12px;
	margin-bottom: 0;
	margin-top: 10px;
	text-align: center;
	white-space: nowrap;
	vertical-align: middle;
	cursor: pointer;
	background-image: none;
	border-radius: 4px;
	border: 1px solid transparent;
	overflow: visible;
	box-sizing: border-box;
	text-transform: none;
}
a, a:hover
{
	text-decoration: none;
}
</style>
<body>';

if (isset($_REQUEST['token']) && isset($_REQUEST['grp']))
{
	echo '
	<h1>'.$p->t('mailverteiler/oeffnenEinesVerteilers').'</h1>';

	/* Generate an random String  */
	$mail_id = mail_id_generator();

	/* Command to unlock Mailgroup */
	$command = "ssh -i /etc/apache2/id_mail_provisioning mailadmins@bifrost2 ".$_REQUEST['grp'] . " " . $mail_id;

	$output = array();
	exec($command, $output, $retval);

	if ($retval === 0)
	{
		// Add Log Message
		$message= date("F j G:i:s") . " mailgroup: [" . $_REQUEST['grp'] . "] (using " . $mail_id . ") requested by [" . $uid . "]\n";

		$filet = fopen(LOG_PATH.'.htmlistopen.log', "a");
		fwrite($filet, $message, mb_strlen($message));
		fclose($filet);

		echo '
		<p>'.$p->t('mailverteiler/geoeffnet',$db->convert_html_chars($_REQUEST['desc'])).'</p>
		<p><b>Code</b>: '.$mail_id.'<br>
		<b>Adresse</b>: <a href="mailto:?bcc='.$_REQUEST["grp"].$mail_id.'@'.DOMAIN.'">'.$_REQUEST["grp"].$mail_id.'@'.DOMAIN.'</a></p>
		<p>'.$p->t('mailverteiler/klickenZumSchicken').'</p>
		<p>'.$p->t('mailverteiler/infoBenutzung').'</p>';
	}
	else
	{
		echo '
		<p class="error">'.$p->t('mailverteiler/oeffnenFehlgeschlagen').'</p>';
	}
}
else
{
	if ($_REQUEST['grp'] == '')
	{
		exit();
	}
	else
	{
		echo '<h1>'.$p->t('mailverteiler/oeffnenEinesVerteilers').'</h1>';
		echo $p->t('mailverteiler/bestaetigeOeffnen').':';
		echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?grp='.$_REQUEST['grp'].'&desc='.$db->convert_html_chars($_REQUEST['desc']).'&token=1"><button class="button_oeffnen">'.$p->t('mailverteiler/verteilerGenerieren',array($_REQUEST['grp'])).'</button></a>';
	}
}

echo '</body>
</html>';
?>
