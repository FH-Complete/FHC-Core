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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Funktionen zum Pruefen der Passwort Policy und setzen des Passworts
 */
require_once(dirname(__FILE__).'/../addon.class.php');

// die aktiven Addons werden durchsucht, ob eines davon eine eigene UID Generierung vorsieht
// falls ja, wird die Version des Addons genommen, ansonsten die Default Generierung
$passwort_addon_found=false;
$passwort_addons = new addon();

foreach($passwort_addons->aktive_addons as $addon)
{
	$passwort_addon_filename = dirname(__FILE__).'/../../addons/'.$addon.'/vilesci/passwort.inc.php';

	if(file_exists($passwort_addon_filename))
	{
		include($passwort_addon_filename);
		$passwort_addon_found=true;
		break;
	}
}

if(!$passwort_addon_found)
{

	/**
	 * Prueft die Passwort Policy
	 * Das Passwort muss zumindest 8 Zeichen enthalten, davon mindestens 1 Großbuchstabe,
     * 1 Kleinbuchstabe und eine Ziffer!
     * Das Passwort darf keine Leerzeichen und Umlaute enthalten!
     * Erlaubte Sonderzeichen sind: -$#[]{}!().,*:;_
	 *
	 * @param $passwort_neu das neue Passwort
	 * @param $p Phrasen Objekt - Wenn nicht uebergeben werden die Fehler in der Default Langauge angezeigt
	 * @param $passwort_alt Altes Passwort bei Passwortaenderung
	 * @return errormsg wenn Policy nicht erfuellt ist oder true wenn ok
	 */
	function check_policy($passwort_neu, $p=null, $passwort_alt=null)
	{
		if(is_null($p))
			$p = new phrase(DEFAULT_LANGUAGE);

		// Prüfung des neuen Passwortes
		$errormsg='';
		$error=false;
		// Laenge mindestens 8 Zeichen
		if(mb_strlen($passwort_neu)<8)
		{
			$error=true;
			$errormsg .= $p->t('passwort/MinLaenge');
		}

		// Altes Passwort darf nicht gleich dem neuen sein
		if(!is_null($passwort_alt) && $passwort_alt!='')
		{
			if($passwort_neu == $passwort_alt)
			{
				$error=true;
				$errormsg .= $p->t('passwort/nichtGleich');
			}
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
				if($ldap->connect(LDAP_SERVER_MASTER,LDAP_PORT,$user_dn, $passwort_alt, LDAP_STARTTLS))
				{
					// Passwort verschlüsseln
					//SSHA
					$salt = substr(pack('H*',hash('sha1',substr(pack('h*',hash('md5',mt_rand())),0,8).$passwort_neu)),0,4);
					$encrypted = base64_encode(pack('H*',hash('sha1',$passwort_neu.$salt)).$salt);
					$ssha_password = '{SSHA}'.$encrypted;

					// LM und NT
					//$hash = new Crypt_CHAP_MSv2();
					//$hash->password = $passwort_neu;
					// $lm_password = strtoupper(bin2hex($hash->lmPasswordHash()));
					//$nt_password = strtoupper(bin2hex($hash->ntPasswordHash()));

					// Neues Passwort setzen
					$data = array();
					$data['userPassword']=$ssha_password;
					// $data['sambaLMPassword']=$lm_password;
					//$data['sambaNTPassword']=$nt_password;
					//$data['sambaPwdLastSet']=time();
					//$data['sambaPwdMustChange']=2147483647; // 2038-01-19 04:14:07

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
}
?>
