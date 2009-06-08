<?php	session_start(); ?>
<html>
<head>
<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
<title>Passwort ändern</title>
</head>
<body id="inhalt">

<?php


   	if (!session_is_registered('count'))
   	{
   		session_register('count');
		$count = 1;
   	}
   	else
   	{
		if ($count>3)
		{
			echo "<h4>Das Passwort Formular ist nach 3 Fehlern deaktiviert!</h4>";
			echo '<meta http-equiv="refresh" content="5; URL=https://cis.technikum-wien.at/profile/index.php">';
			exit;
		}
   	}
	$ds=@ldap_connect("pdc1.technikum-wien.at");

	if ($ds)
	{
		ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION,3);
		//if (!(@ldap_start_tls($ds))) { die("LDAP START_TLS failed"); }

		$oldpass=StripSlashes($oldpass);
		$newpass1=StripSlashes($newpass1);
		$newpass2=StripSlashes($newpass2);

		$r=@ldap_bind($ds,"uid=$username,ou=People,dc=technikum-wien,dc=at",$oldpass);

		if ($r == 1)
		{
			if ($newpass1 == $newpass2)
			{
				if ($newpass1 != $null)
				{

					$mySalt = substr(ereg_replace("[^a-zA-Z0-9./]","",crypt(rand(10000000,99999999), rand(10,99))),2, 2);
					$cryptPW = rtrim(crypt($newpass1,$mySalt));
					$info["userPassword"] = "{crypt}$cryptPW";
					$newpass1=escapeshellarg($newpass1);
					$info["sambalmPassword"] = rtrim(shell_exec("/usr/local/sbin/mkntpwd -L $newpass1"));
					$info["sambantPassword"] = rtrim(shell_exec("/usr/local/sbin/mkntpwd -N $newpass1"));

					$mod_r = ldap_mod_replace($ds,"uid=$username,ou=People,dc=technikum-wien,dc=at",$info);

					if ($mod_r)
					{
						echo "<h4>Das Passwort wurde erfolgreich geändert!</h4>";
						session_unregister('count');
						echo '<meta http-equiv="refresh" content="5; URL=https://cis.technikum-wien.at/profile/index.php">';
						exit;
					}
					else
					{
						echo "<h4>Beim Ändern des Passwortes ist ein Fehler aufgetreten!</h4>";
					}
				}
				else
				{
					echo "<h4>Das neue Passwort darf nicht leer sein!</h4>";
				}
			}
			else
			{
				echo "<h4>Die neuen Passwörter stimmen nicht überein!</h4>";
			}
		}
		else
		{
			echo "<h4>Passwort inkorrekt!</h4>";
		}
		ldap_close($ds);
	}
	else
	{
		echo "Der Technikum Wien LDAP Server ist zur Zeit nicht erreichbar!";
	}
	echo '<meta http-equiv="refresh" content="5; URL=https://cis.technikum-wien.at/profile/password.php">';
?>



</body>
</html>
