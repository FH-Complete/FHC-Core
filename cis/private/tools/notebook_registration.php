<?php
	/*
	 * author: maximilian schremser
	 * email: max@technikum-wien.at
	 * date-created: ?? 2003
	 * date-modified: ?? by ??
	 *		  15.9.2004  by max schremser
	 *
	 * manual: es wurden keine neuen einträge in der dhcp.dat eingetragen
	 *	   seit der letzten änderung, ich wurde von ferdinand esberger
	 * 	   gebeten dies wieder in ordnung zu bringen
	 */
	require_once('../../../config/cis.config.inc.php');
  require_once('../../../include/basis_db.class.php');
  if (!$db = new basis_db())
      die('Fehler beim Oeffnen der Datenbankverbindung');
  
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/File/SearchReplace.php');
	require_once('../../../include/File/Match.php');


	if (!$user=get_uid())
		die('Sie sind nicht angemeldet. Es wurde keine Benutzer UID gefunden ! <a href="javascript:history.back()">Zur&uuml;ck</a>');


	if(!isset($txtUID))
		$txtUID='';
	if(!isset($txtPassword))
		$txtPassword='';

	if(check_lektor($user))
       $is_lector=true;
  else
        $is_lector=false;
	function ip_increment($ip = "")
	{
		$ip = split("\.", $ip);

        if($ip[3] > 0 && $ip[3] < 254)
		{
			++$ip[3];
        }
		else
		{
        	++$ip[2];
        	$ip[3] = 1;
        }

		return join(".", $ip);
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>

<body onLoad="document.regMAC.txtMAC.focus();">
<table class="tabcontent" id="inhalt">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td><table class="tabcontent">
      <tr>
        <td class="ContentHeader"><font class="ContentHeader">&nbsp;Infrastruktur - Notebook-Registration</font></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
	  <tr>
	  	<td>
		    <?php

			if (!$txtUID)
				$txtUID = $user;
			// wenn die übergebene UID nicht gleich dem
			// angemeldetem Benutzer ist, muss das Passwort
			// angegeben werden
			if ($txtUID != $user && !$txtPassword)
			{
				$error = 1;
			}
			else if ($txtUID && $txtPassword)
			{
				//$ldap_conn = @ConnectLDAP("pdc1.technikum-wien.at") or die("Der LDAP-Server ist nicht erreichbar.");
				$ldap_conn = ldap_connect(LDAP_SERVER) or die("Der LDAP-Server ist nicht erreichbar.");
				$user_dn = "uid=$txtUID, ou=People, dc=technikum-wien, dc=at";

				if(!@ldap_bind($ldap_conn, $user_dn, $txtPassword) === true)
				{
					$error = 2;
				}
				else
				{
					$error = 0;
				}
			}
			else
			{
				$error =0;
			}

			// ändern oder eintragen einer mac adresse
			if (!$error)
			{

			if(isset($txtMAC) && $txtMAC != "")
			{
				$sql_query = "SELECT DISTINCT vorname, nachname FROM campus.vw_benutzer WHERE uid='".addslashes($txtUID)."' LIMIT 1";

				if($result = $db->db_query($sql_query))
				{
					if($row = $db->db_fetch_object($result))
					{
						$name = $row->vorname.' '.$row->nachname;
					}
					else
						die('Fehler beim ermitteln der UID');
				}
				else
					die('Fehler beim ermitteln der UID');

			$mac = mb_eregi_replace(":", "", mb_eregi_replace("-", "", mb_strtoupper($txtMAC)));

			$filename_dat = '../../../../system/dhcp.dat';
			$filename_ip  = '../../../../system/dhcp.ip';

			copy($filename_dat, '../../../../system/backup/dhcp_'.date('j-m-Y_H-i-s').'.dat');

			unset($mfiles);
			// leich gepfuscht aber funktioniert
			$mfiles = new File_Match("/$mac?\s(.{1}) (.*)\s?/", $filename_dat, '', 0, array('#',';'));
			$mfiles->setFindFunction('preg');
			$mfiles->doFind();
			$VLAN='';
			if($mfiles->occurences)
			{
				$VLAN = $mfiles->match[1];
				$fuser = $mfiles->match[2];
				$fuser = split(" ", $fuser);
				$fuser = $fuser[0];
				//hier könnte man noch eine email schicken oder dgl.
				if ($fuser != $txtUID)
					$error = 3;
			}

			unset($mfiles);

		     	if(!$VLAN) $VLAN = 'S';


			if (!$error)
			{
			if($VLAN != 'S')
			{
				$mac_result = 3;
			}
			else if ($VLAN == 'S')
			{
				$mfiles = new File_SearchReplace("/.*?\sS\s$txtUID\s(.*)?\snb-$txtUID\s(.*)/", "$mac S $txtUID $1 nb-$txtUID $name", $filename_dat, '', 0, array("#", ";"));

				$mfiles->setSearchFunction('preg');

				if(preg_match("/[A-Fa-f0-9]{12}/", $mac) && $mac != '' && mb_strlen($mac) == 12)
				{
					$mfiles->doSearch();

					// neuen eintrag erzeugen und ip hochzählen
					if($mfiles->occurences == 0)
					{
						//$content = file($filename_dat, "r");
						//$content = implode('', $content);
						$content = file_get_contents($filename_dat);

						//$ip = file($filename_ip);
						//$ip = trim($ip[0]);
						$ip = file_get_contents($filename_ip);

						$ip = trim($ip);
						$ip = ip_increment($ip);

						// nachschauen ob, die mac adresse schon
						// einmal gespeichert wurde
						$sfiles = new File_Match("/$mac?\s/", $filename_dat, '', 0, array('#',';'));
						$sfiles->doFind();

						if($sfiles->occurences)
						{
							echo 'MAC IN USE';
							$error = 3;
						}
						else
						{
							$mfiles->writeout($filename_dat, $content."$mac S $txtUID $ip nb-$txtUID $name\n");
							$mfiles->writeout($filename_ip, $ip);
							$mac_result = 0;
							unset($txtMAC);
						}
						unset($sfiles);

					}
					else if($mfiles->occurences > 0)
					{
						$mac_result = 1;

						unset($txtMAC);
					}
				}
				else if($mac)
				{
					$mac_result = 2;
				}
			} // eof !$vlan == s
			} // eof !$error
			} // eof !error (2)
			} // eof if $txtMAC
?>
		  <p>
			  Sollten Sie mehr als ein Notebook registrieren lassen wollen, wenden Sie sich bitte an <a class="Item" href="mailto:support@technikum-wien.at?Subject=Notebook-Registration">support@technikum-wien.at</a>,
 da nur ein Eintrag pro Person m&ouml;glich ist.</p>
			  <p>Geben Sie die MAC-Adresse in folgendem Format an: 00-50-DA-C2-32-1C, oder 00:50:DA:C2:32:1C</p>
			  <p>
			  <form method="post" name="regMAC">
			    <table class="tabcontent">
				  <tr>
					<td width="90" class="tdwrap">MAC-Adresse:</td>
					<td class="tdwrap"><input class="TextBox" type="text" name="txtMAC" size="20"<?php if(isset($txtMAC) && $txtMAC != "") echo 'value="'.$txtMAC.'"'; ?>></td>
				  </tr>
				  <tr>
					<td class="tdwrap">UID:</td>
					<td class="tdwrap"><input class="TextBox" type="text" name="txtUID" size="20" value="<?php echo $txtUID;?>"></td>
				  </tr>
				  <tr>
					<td class="tdwrap">*Passwort:</td>
					<td class="tdwrap"><input class="TextBox" type="password" name="txtPassword" size="20" value=""><br><small>* ... muss nur angegeben werden, wenn UID nicht gleich dem angemeldetem Benutzer</td>
			  </tr>
		  <tr>
			  	<td>&nbsp;</td>
			  </tr>
			  <tr>
			  	<td class="tdwrap"><input type="submit" name="cmdRegsiter" value="Eintragen"></td>
				<td class="tdwrap"><input type="reset" name="cmdCancel" value="Abbrechen" onClick="document.regMAC.txtMAC.focus();"></td>
			  </tr>
			</table>
  	      </form>
		  <?php
		  	if ($error == 1)
				echo '<h3>Es muss ein Passwort eingegeben werden, wenn die UID geändert wird.</h3>';
			else if ($error == 2)
				echo '<h3>Geben Sie das Passwort bitte erneut ein.</h3>';
			else if ($error == 3)
				echo '<h3>Die MAC Adresse ist bereits in Verwendung, bitte melden Sie sich bei der ITS <a href="mailto:its@technikum-wien.at">its@technikum-wien.at</a></h3>';

		  	if(isset($mac_result))
			{
				if($mac_result == 0)
				{
					echo '<h3>Die MAC-Adresse wurde erfolgreich eingetragen!</h3>';
				}
				else if($mac_result == 1)
				{
					echo '<h3>Die MAC-Adresse wurde erfolgreich ge&auml;ndert!</h3>';
				}
				else if($mac_result == 2)
				{
					echo '<h3>Die angegebene MAC-Adresse ist fehlerhaft!</h3>';
				}
				else if($mac_result == 3)
				{
					echo '<h3>Sie k&ouml;nnen Ihre MAC-Adresse nicht eintragen, da Sie nicht daf&uuml;r freigeschalten wurden.</h3><br>';
				}
		  	}
		  ?>
		  <p>Die &Auml;nderungen werden in ca. 30 Minuten wirksam. Bitte haben Sie etwas Geduld.</p>
		  <p>Um das Internet nutzen zu k&ouml;nnen, lassen Sie bitte die Netzwerkverbindungseinstellungen vom DHCP-Server zuweisen.<br>
	      In Ihrem Browser tragen Sie bitte den Proxy-Server: <strong>proxy.technikum-wien.at</strong> und den Port <strong>3128</strong> ein.</p></td>
	  </tr>
    </table></td>
	<td class="tdwidth30">&nbsp;</td>
  </tr>
</table>
</body>
</html>
