<?php
/**
 * Script zur Erstellung einer Statistik über die Häufigkeit der Passwortänderungen
 *
 * Prueft wie viele User gestern das Passwort geaendert haben
 * und schickt eine Liste mit den md5 Hashes per Mail
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/mail.class.php');

if($connect=@ldap_connect(LDAP_SERVER))
{
    // bind to ldap connection
    if(($bind=@ldap_bind($connect)) == false)
    {
		print "bind:__FAILED__<br>\n";
		return false;
    }

	$ts = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
    // search for user
    if (($res_id = ldap_search( $connect, LDAP_BASE_DN, "uid=*")) == false)
    {
		print "failure: search in LDAP-tree failed<br>";
		return false;
    }
	
	$entries = ldap_get_entries($connect, $res_id);
	$notset=0;
	$anzahl_geaendert=0;
	$message = "Dies ist ein automatisches Mail.\n\n";
	$message.= "Folgende Personen haben heute ihr Passwort geaendert:\n\n";

	for ($i=0; $i < $entries["count"]; $i++)
	{
		if(!isset($entries[$i]["sambapwdlastset"]))
		{
			$notset++;
		}
		else
		{
			$lastset = $entries[$i]["sambapwdlastset"][0];
			if($lastset>$ts)
			{
				$anzahl_geaendert++;
				$message.= "\n".md5($entries[$i]["uid"][0]);
				//var_dump($entries);
				//echo $entries[$i]["cn"][0]."<br />";
				//echo ."<br />";
			}
		}
	}

	$message.="\n\nEintraege Gesamt:".$entries["count"];
	$message.="\nNie geaendert/nicht gesetzt:".$notset;
	$message.="\nHeute geaendert:".$anzahl_geaendert;

	$to = MAIL_ADMIN;
	$from = 'no-reply@'.DOMAIN;
	$subject = 'Passwort Aenderung Statistik';

	$mail = new mail($to, $from, $subject, $message);
	if(!$mail->send())
		echo 'Mail schicken fehlgeschlagen<br>';
	else
		echo 'Mail erfolgreich verschickt<br>';

	echo nl2br($message);
    @ldap_close($connect);
}
else
{
	// no conection to ldap server
	echo "no connection to '$ldap_server'<br>\n";
}
?>
