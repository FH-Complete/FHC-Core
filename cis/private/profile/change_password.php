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
require_once('../../../include/Crypt_CHAP-1.5.0/CHAP.php');

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

echo $p->t('passwort/InfotextPolicy').'
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
		if(($errormsg = check_policy($passwort_neu))===true)
		{
			// Passwort aendern
			if(($msg = change_password($passwort_alt, $passwort_neu, $uid))===true)
			{
				echo '<span class="ok">'.$p->t('passwort/AenderungOK').'</span';
			}
			else
			{
				echo '<span class="error">'.$msg.'</span>';
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

/**
 * Prueft die Passwort Policy
 * @param $passwort_neu das neue Passwort
 * @return errormsg wenn Policy nicht erfuellt ist oder true wenn ok
 */
function check_policy($passwort_neu)
{
	global $p;

	// Prüfung des neuen Passwortes
	$errormsg='';
	$error=false;
	// Laenge mindestens 8 Zeichen
	if(mb_strlen($passwort_neu)<8)
	{
		$error=true;
		$errormsg .= $p->t('passwort/MinLaenge');
	}

	// Mindestens 1 Großbuchstabe
	if(!preg_match('/[A-Z]/', $passwort_neu))
	{
		$error=true;
		$errormsg .=$p->t('passwort/Grossbuchstabe');
	}
	// Mindestens 1 Kleinbuchstabe
	if(!preg_match('/[a-z]/', $passwort_neu))
	{
		$error=true;
		$errormsg .=$p->t('passwort/Kleinbuchstabe');
	}

	// Mindestens 1 Ziffer
	if(!preg_match('/[0-9]/', $passwort_neu))
	{
		$error=true;
		$errormsg .=$p->t('passwort/Ziffer');
	}

	// Keine Leerzeichen
	if(strstr($passwort_neu, ' '))
	{
		$error=true;
		$errormsg .=$p->t('passwort/Leerzeichen');
	}

	// keine Umlaute
	if(preg_match('/[ÄÖÜäöü]/', $passwort_neu))
	{
		$error=true;
		$errormsg .=$p->t('passwort/Umlaute');
	}

	// Sonderzeichen
	if(!preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z\-\$#\[\]\{\}!\(\)\.,\*:;_]{8,1024}$/', $passwort_neu))
	{
		$error=true;
		$errormsg.=$p->t('passwort/Sonderzeichen');
	}

	if($error)
		return $errormsg;
	else
		return true;
}

/**
 * Aendert das Passwort im LDAP
 * @param $passwort_alt Altes (aktuelles) Passwort
 * @param $passwort_neu neues Passwort
 * @param $uid UID
 * @return true wenn erfolgreich sonst false
 */
function change_password($passwort_alt, $passwort_neu, $uid)
{
	$ldap = new ldap();

	// Normalen Bind zum LDAP Server
	if($ldap->connect())
	{
		// DN des Users holen
		if($user_dn = $ldap->GetUserDN($uid))
		{
			$ldap->unbind();
			$ldap = new ldap();

			// Bind des User mit alten Passwort
			if($ldap->connect('starttls', LDAP_SERVER_MASTER, $user_dn, $passwort_alt))
			{
				// Passwort verschlüsseln
				//SSHA
				$salt = substr(pack('H*',hash('sha1',substr(pack('h*',hash('md5',mt_rand())),0,8).$passwort_neu)),0,4);
				$encrypted = base64_encode(pack('H*',hash('sha1',$passwort_neu.$salt)).$salt);
				$ssha_password = '{SSHA}'.$encrypted;

				// LM und NT 
				$hash = new Crypt_CHAP_MSv2();
				$hash->password = $passwort_neu;
				// $lm_password = strtoupper(bin2hex($hash->lmPasswordHash()));
				$nt_password = strtoupper(bin2hex($hash->ntPasswordHash()));

				// Neues Passwort setzen
				$data = array();
				$data['userPassword']=$ssha_password;
				// $data['sambaLMPassword']=$lm_password;
				$data['sambaNTPassword']=$nt_password;
				$data['sambaPwdLastSet']=time();
				$data['sambaPwdMustChange']=2147483647; // 2038-01-19 04:14:07

				if($ldap->Modify($user_dn, $data))
					return true;
				else
					return false;
			}
			else
			{
				return $ldap->errormsg;
			}
		}
		else
		{
			return $ldap->errormsg;
		}
	}
	else
	{
		return $ldap->errormsg;
	}
}
?>
