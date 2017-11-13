<?php
/*
 * Copyright 2013 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 *
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 *			Alexander Nimmervoll <alexander.nimmervoll@technikum-wien.at>
 *
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../addons/ldap/vilesci/ldap.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/'.EXT_FKT_PATH.'/passwort.inc.php');

$uid = get_uid();
$db = new basis_db();
$p = new phrasen(getSprache());

echo '<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>'.$p->t('passwort/Title').'</title>
	<link rel="stylesheet" href="../../../skin/fhcomplete.css" />
	<link rel="stylesheet" href="../../../skin/style.css.php" />
</head>
<body>
';

$benutzer = new benutzer();
if(!$benutzer->load($uid))
	die('Benutzer nicht gefunden');

echo '<h1>'.$p->t('passwort/PasswortAenderFuer',array($db->convert_html_chars($benutzer->vorname),$db->convert_html_chars($benutzer->nachname),$db->convert_html_chars($benutzer->uid))).'</h1>';

if(!isset($_SERVER['HTTPS']) || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='off'))
{

	$httpspath = str_replace('http://','https://',APP_ROOT).'cis/private/profile/change_password.php';
	echo '<div style="border: 2px solid red; text-align:center">'.$p->t('passwort/NoHttps').'<br>
	<a href="'.$httpspath.'">'.$p->t('passwort/ZuHttpsWechseln').'</a></div><br>';
}
if(isset($_GET['requiredtochange']))
{
	echo '<span class="error">'.$p->t('passwort/RequiredToChangeInfo').'</span><br><br>';
}
echo $p->t('passwort/InfotextPolicy');

if($p->t('dms_link/passwortpolicy')!='')
{
	echo '<br><br>'.$p->t('passwort/weitereInfos',array($p->t('dms_link/passwortpolicy')));
}
echo '
<br>
<br>
<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
<table>
	<tr>
		<td>'.$p->t('passwort/AltesPasswort').'</td>
		<td><input type="password" name="passwort_alt"></td>
	</tr>
	<tr>
		<td>'.$p->t('passwort/NeuesPasswort').'</td>
		<td><input type="password" name="passwort_neu"></td>
	</tr>
	<tr>
		<td>'.$p->t('passwort/PasswortWiederholung').'</td>
		<td><input type="password" name="passwort_neu_check"></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="change" value="'.$p->t('passwort/PasswortAendern').'" /></td>
	</tr>
</table>
</form>';

if(isset($_POST['change']))
{
	if(!isset($_POST['passwort_alt'])
	|| !isset($_POST['passwort_neu'])
	|| !isset($_POST['passwort_neu_check']))
	{
		die('Fehlerhafte Parameteruebergabe');
	}

	$passwort_alt = $_POST['passwort_alt'];
	$passwort_neu = $_POST['passwort_neu'];
	$passwort_neu_check = $_POST['passwort_neu_check'];

	// Pruefen ob das neue Passwort uebereinstimmt
	if($passwort_neu==$passwort_neu_check)
	{
		// Passwort Policy pruefen
		if(($errormsg = check_policy($passwort_neu, $p, $passwort_alt))===true)
		{
			// Passwort aendern
			if(($msg = change_password($passwort_alt, $passwort_neu, $uid))===true)
			{
				echo '<span class="ok">'.$p->t('passwort/AenderungOK').'</span';
			}
			else
			{
				echo '<span class="error">ERR:'.$msg.'</span>';
			}
		}
		else
		{
			echo '<span class="error">'.$p->t('passwort/AenderungFehler', array($errormsg)).'</span';
		}

	}
	else
	{
		echo '<span class="error">'.$p->t('passwort/NichtUebereinstimmend').'</span>';
	}
}
echo '</body>
</html>';

?>
