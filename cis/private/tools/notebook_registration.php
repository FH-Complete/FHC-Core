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
require_once('../../../include/functions.inc.php');
require_once('../../../include/File/SearchReplace.php');
require_once('../../../include/File/Match.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/authentication.class.php');

$sprache = getSprache();
$p=new phrasen($sprache);

if (!$db = new basis_db())
	die($p->t("global/fehlerBeimOeffnenDerDatenbankverbindung"));
if (!$user=get_uid())
	die($p->t("global/nichtAngemeldet").'! <a href="javascript:history.back()">'.$p->t("global/zurueck").'</a>');

$mac_result = trim((isset($_REQUEST['mac_result']) ? $_REQUEST['mac_result']:''));
$txtUID = trim((isset($_REQUEST['txtUID']) ? $_REQUEST['txtUID']:''));
$txtPassword = trim((isset($_REQUEST['txtPassword']) ? $_REQUEST['txtPassword']:''));
$txtMAC = trim((isset($_REQUEST['txtMAC']) ? $_REQUEST['txtMAC']:''));

if(check_lektor($user))
	$is_lector=true;
else
	$is_lector=false;

function ip_increment($ip = "")
{
	$ip = explode(".", $ip);

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
<link href="../../../skin/flexcrollstyles.css" rel="stylesheet" type="text/css" />
</head>

<body onLoad="document.regMAC.txtMAC.focus();">
<div class="flexcroll" style="outline: none;">
<h1><?php echo $p->t("notebookregister/titelNotebookRegistration");?></h1>
<table class="cmstable" cellspacing="0" cellpadding="0">
  <tr>
    <td class="cmscontent" rowspan="3" valign="top">
		    <?php
			if($is_lector || check_lektor($txtUID))
			{
				echo 'Die Notebook Registrierung steht nur für Studierende zur Verfügung.<br>
				Wollen Sie als Mitarbeiter ein Notebook registrieren, wenden Sie sich bitte an den <a href="mailto:support@technikum-wien.at">Support</a>.';
				echo '</td></tr></table></div></body></html>';
				exit;
			}
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
				// Passwort pruefen
				$auth = new authentication();
				if($auth->checkpassword($txtUID, $txtPassword))
					$error=0;
				else
					$error=2;
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
					$sql_query = "SELECT DISTINCT vorname, nachname
					FROM campus.vw_benutzer WHERE uid=".$db->db_add_param($txtUID)." LIMIT 1";

					if($result = $db->db_query($sql_query))
					{
						if($row = $db->db_fetch_object($result))
						{
							$name = $row->vorname.' '.$row->nachname;
						}
						else
							die($p->t("global/fehlerBeimErmittelnDerUID"));
					}
					else
						die($p->t("global/fehlerBeimErmittelnDerUID"));

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
						$fuser = explode(" ", $fuser);
						$fuser = $fuser[0];
						//hier könnte man noch eine email oder dgl. schicken
						if ($fuser != $txtUID)
							$error = 3;
					}

					unset($mfiles);

			     	if(!$VLAN)
			     		$VLAN = 'S';

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
		  <p><?php echo $p->t("notebookregister/notebook_absatz1");?></p>
			  <p>
			  <form method="post" name="regMAC">
			    <table class="tabcontent">
				  <tr>
					<td width="90" class="tdwrap"><?php echo $p->t("notebookregister/MACadresse");?>:</td>
					<td class="tdwrap"><input class="TextBox" type="text" name="txtMAC" size="20"<?php if(isset($txtMAC) && $txtMAC != "") echo 'value="'.$txtMAC.'"'; ?>></td>
				  </tr>
				  <tr>
					<td class="tdwrap">UID:</td>
					<td class="tdwrap"><input class="TextBox" type="text" name="txtUID" size="20" value="<?php echo $txtUID;?>"></td>
				  </tr>
				  <tr>
					<td class="tdwrap">*<?php echo $p->t("global/passwort");?>:</td>
					<td class="tdwrap"><input class="TextBox" type="password" name="txtPassword" size="20" value=""><br><small>* ... <?php echo $p->t("notebookregister/notebook_anmerkung");?></td>
			  </tr>
		  <tr>
			  	<td>&nbsp;</td>
			  </tr>
			  <tr>
			  	<td class="tdwrap"><input type="submit" name="cmdRegsiter" value="<?php echo $p->t("global/eintragen");?>"></td>
				<td class="tdwrap"><input type="reset" name="cmdCancel" value="<?php echo $p->t("global/abbrechen");?>" onClick="document.regMAC.txtMAC.focus();"></td>
			  </tr>
			</table>
  	      </form>
		  <?php

		  	if ($error == 1)
				echo '<h3>'.$p->t("notebookregister/passwortEingebenWennUIDgeaendert").'.</h3>';
			else if ($error == 2)
				echo '<h3>'.$p->t("notebookregister/passwortErneutEingeben").'.</h3>';
			else if ($error == 3)
				echo '<h3>'.$p->t("notebookregister/MACadresseBereitsVerwendet").'.</h3>';

		  	if(isset($mac_result) && $mac_result!=='')
			{
				if($mac_result === 0)
				{
					echo '<h3>'.$p->t("notebookregister/MACadresseErfolgreichEingetragen").'.</h3>';
				}
				else if($mac_result === 1)
				{
					echo '<h3>'.$p->t("notebookregister/MACadresseErfolgreichGeaendert").'.</h3>';
				}
				else if($mac_result === 2)
				{
					echo '<h3>'.$p->t("notebookregister/MACadresseFehlerhaft").'.</h3>';
				}
				else if($mac_result === 3)
				{
					echo '<h3>'.$p->t("notebookregister/MACadresseNichtFreigeschalten").'.</h3>';
				}
		  	}
		  ?>
		  <p><?php echo $p->t("notebookregister/notebook_absatz2");?></p>
		</td>
  </tr>
</table>
</div>
</body>
</html>
