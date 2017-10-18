<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Andreas Österreicher <oesi@technikum-wien.at>
 */
require_once('../../config/cis.config.inc.php');
require_once('../../include/phrasen.class.php');
require_once('../../include/sprache.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/securimage/securimage.php');
require_once('../../include/'.EXT_FKT_PATH.'/passwort.inc.php');

if(isset($_GET['sprache']))
{
	$sprache = new sprache();
	if($sprache->load($_GET['sprache']))
	{
		setSprache($_GET['sprache']);
	}
	else
		setSprache(DEFAULT_LANGUAGE);
}

$erfolgreichaktiviert=false;
$sprache = getSprache();

$p = new phrasen($sprache);
$securimage = new Securimage();
$errormsg='';
$db = new basis_db();

if(isset($_REQUEST['username']))
	$username = $_REQUEST['username'];
else
	$username='';

if(isset($_REQUEST['code']))
	$code = $_REQUEST['code'];
else
	$code ='';


if(isset($_POST['submit']))
{
	// Captcha Pruefen
	if ($securimage->check($_POST['captcha_code']) == true)
	{
		// Benutzer laden
		$benutzer = new benutzer();
		if($benutzer->load($username))
		{
			// Aktivierungscode pruefen
			if($benutzer->aktivierungscode==$code && $code!='')
			{
				$passwort = $_POST['passwort'];
				$passwort2 = $_POST['passwort2'];

				// Vergleichen ob beide Passwoerter gleich sind
				if($passwort==$passwort2)
				{
					// Passwort Policy pruefen
					if(($errormsg = check_policy($passwort, $p))===true)
					{
						// Passwort setzen
						if(($errormsg = change_password(ACCOUNT_ACTIVATION_PASSWORD, $passwort, $username))===true)
						{
							// Code entfernen
							$benutzer = new benutzer();
							$benutzer->DeleteAktivierungscode($username);

							// Account aktiviert
							$erfolgreichaktiviert=true;
						}
					}
				}
				else
				{
					$errormsg = $p->t('passwort/NichtUebereinstimmend');
				}
			}
			else
			{
				$errormsg = $p->t('passwort/CodeOderUsernameFalsch');
			}
		}
		else
		{
			$errormsg = $p->t('passwort/CodeOderUsernameFalsch');
		}
	}
	else
	{
    	$errormsg= $p->t('passwort/CaptchaCodeFalsch');
	}
}

echo '<!doctype html>
<html>
	<head>
		<title>Account Aktivierung</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta name="robots" content="noindex">
		<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">

		<script type="text/javascript">
		function changeSprache(sprache)
		{
			window.location.href="accountactivation.php?sprache="+sprache;
		}
		</script>
	</head>
<body>
<table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
<td class="rand"></td>
<td class="boxshadow" align="center" valign="top"><br><br>';


if($erfolgreichaktiviert)
{
	echo '<br><br><h1>'.$p->t('passwort/AccountErfolgreichAktiviert').'</h1><br><br>
	<a href="'.APP_ROOT.'">&gt;&gt; '.$p->t('passwort/WeiterZumLogin').'</a>';
}
else
{
	echo '		<table width="100%" border="0">
		<tr>
		    <td align="left"></td>
		    <td align="right" width="10px">
			<select style="text-align: right; color: #0086CC; border: 0;" name="select" onchange="changeSprache(this.options[this.selectedIndex].value);">';
		        $sprache2 = new sprache();
				$sprache2->getAll(true);
				foreach($sprache2->result as $row)
				{
					echo ' <option value="'.$row->sprache.'" '.($row->sprache==$sprache?'selected':'').'>'.($row->bezeichnung_arr[getSprache()]).'&nbsp;&nbsp;</option>';
				}
	echo '	</select></td>
		</tr>
	</table>';

	echo '
	<h1>'.$p->t('passwort/AccountAktivierung').'</h1>
	'.$p->t('passwort/PasswortWaehlen').'<br>'.
	$p->t('passwort/InfotextPolicy').'
	<br><br>';
	if(!isset($_SERVER['HTTPS']) || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='off'))
	{

		$httpspath = str_replace('http://','https://',APP_ROOT).'cis/public/accountactivation.php';
		echo '<div style="border: 2px solid red; text-align:center">'.$p->t('passwort/NoHttps').'<br>
		<a href="'.$httpspath.'">'.$p->t('passwort/ZuHttpsWechseln').'</a></div><br>';
	}

	echo '<br>
	<span class="error">'.$errormsg.'</span>
	<br>
	<form method="POST">
	<table>
	<tr>
		<td>Username</td>
		<td><input type="text" name="username" value="'.$db->convert_html_chars($username).'"/></td>
	</tr>
	<tr>
		<td>Code</td>
		<td><input type="text" size="32" name="code" value="'.$db->convert_html_chars($code).'"/></td>
	</tr>
	<tr>
		<td>'.$p->t('passwort/NeuesPasswort').'</td>
		<td><input type="password" name="passwort" /></td>
	</tr>
	<tr>
		<td>'.$p->t('passwort/PasswortWiederholung').'</td>
		<td><input type="password" name="passwort2" /></td>
	</tr>
	<tr>
		<td></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td valign="top">
			'.$p->t('passwort/CaptchaEingabe').'
			<br>
			<a href="#" onclick="document.getElementById(\'captcha\').src = \'../../include/securimage/securimage_show.php?\'+Math.random(); return false">'.$p->t('passwort/ReloadCaptcha').'</a>
		</td>
		<td>
			<img id="captcha" src="../../include/securimage/securimage_show.php" alt="CAPTCHA Image" style="border:1px solid;" />
			<br>
			<input type="text" name="captcha_code" size="10" maxlength="6" />
		</td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" name="submit" value="'.$p->t('global/abschicken').'" /></td>
	</tr>
	</table>
	</form>';
}

echo '
</td>
<td class="rand">
</td>
</tr>
</table>

</body>
</html>
';
?>
