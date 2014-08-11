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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
require_once('../../config/cis.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/phrasen.class.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/gruppe.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);
$uid = get_uid();
$db = new basis_db();
if(!isset($_REQUEST['grp']))
	die('Falsche Parameter');
	
if(mb_strlen($_REQUEST['grp'])>32)
	die('Grp ungueltig');
	
//Pruefen ob es eine gueltige Gruppe ist
$gruppe = new gruppe();
if(!$gruppe->exists($_REQUEST['grp']))
{
	//Wenn es keine Gruppe in der DB ist, kann es
	//noch ein Studierendenverteiler sein
	//bif_std
	if(!preg_match('/^\D\D\D_std$/', $_REQUEST['grp']))
	{
		die('Ungueltige Gruppe');
	}
}
function mail_id_generator()
{
	mt_srand((double)microtime()*1000000);

	/* Laenge des Passwortes dem Zufall ueberlassen */
	$length = 6; //mt_rand(6, 6);
	$fix_similar = '';
	$valid_charset = "";

    /* Stelle ein Charset zusammen */
    if (!$valid_charset)
    {
    	// deactivated, regarding an case sensitive issue
	    //$valid_charset .= 'ABCDEFGHIJKLMNOPQRSTUVXYZ';
    	$valid_charset .= 'abcdefghijklmnopqrstuvxyz';
 	   	$valid_charset .= '0123456789';
    	//$valid_charset .= '!@_-';
    }

    $charset_length = mb_strlen($valid_charset);

    if ($charset_length == 0) return false;

    /* Initialisieren - Auswahl von chars bis definierte Anzahl erreicht */
    $mail_id = "";
    while(strlen($mail_id) < $length)
    {
    	/* Waehle einen zufaelligen char aus */
	    $char = $valid_charset[mt_rand(0, ($charset_length-1))];

	    /* Abgleich von gleich aussehenden chars */
	    if (($fix_similar && !strpos('O01lI5S', $char)) || !$fix_similar) $mail_id .= $char;
	}
    return $mail_id;
}

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
	<title>'.$p->t('mailverteiler/oeffnenEinesVerteilers').'</title>
</head>
<body>';

if(isset($_REQUEST['token']) && isset($_REQUEST['grp']))
{
	echo '
	<table class="tabcontent">
 		<tr>
 	        	<td class="ContentHeader"><font class="ContentHeader">'.$p->t('mailverteiler/mailverteiler').'</font></td>
 	        	<td class="ContentHeader"><font class="ContentHeader">'.$p->t('mailverteiler/status').'</font></td>
 	      	</tr>';
 	
	/* Generate an random String  */
	$mail_id=mail_id_generator();

	/* call the shellpart at polyxena */
	$command = "ssh -p 22022 polyxena sudo /root/bin/mlistin.sh " . $_REQUEST['grp'] . " " . $mail_id . " 2>&1";
	exec($command);

	/* ffe, 20051020 - do a little logging */
	$message= date("F j G:i:s") . " mailgroup: [" . $_REQUEST['grp'] . "] (using " . $mail_id . ") requested by [" . $uid . "]\n";

	$filet = fopen(LOG_PATH.'.htmlistopen.log', "a");
   	fwrite($filet, $message, mb_strlen($message));
    fclose($filet);

	// for the users
	echo "
	<tr>
		<td><a href='mailto:".$_REQUEST['grp'].$mail_id."@".DOMAIN."'>".$db->convert_html_chars($_REQUEST['desc'])."</a></td>
		<td>".$p->t('mailverteiler/geoeffnet')." (Code: ".$mail_id.")</td>
	</tr>
	<tr>
	<td colspan='2'>
	<p>".$p->t('mailverteiler/klickenZumSchicken')."</p>

	<p>".$p->t('mailverteiler/infoBenutzung',array($_REQUEST['grp'].$mail_id."@".DOMAIN))."</p>
	</td>
	</tr>
	</table>
	";
}
else
{
	if($_REQUEST['grp']=="")
	{
		exit();
	}
	else
	{
		echo $p->t('mailverteiler/bestaetigeOeffnen',array($_REQUEST['grp']))." : <a href=\"".$_SERVER['SCRIPT_NAME']."?grp=".$_REQUEST['grp']."&desc=".$db->convert_html_chars($_REQUEST['desc'])."&token=1\">".$p->t('mailverteiler/bestaetige')."</a>";
	}
}
 
echo '</body>
</html>';
?>
