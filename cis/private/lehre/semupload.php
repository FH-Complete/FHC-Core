<?php
/* Copyright (C) 2010 FH Technikum Wien
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
/**
 * Formular zum Uploaden und Loeschen von
 * Semesterplaenen.
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/phrasen.class.php');

$user = get_uid();
$sprache=getSprache(); 
$p = new phrasen($sprache); 

$is_lector=false;
if(check_lektor($user))
	$is_lector=true;

if(!isset($_GET['lvid']) || !is_numeric($_GET['lvid']))
{
	die($p->t('semesterplan/fehlerBeiDerParameteruebergabe'));
}
else
	$lvid = $_GET['lvid'];

$lv_obj = new lehrveranstaltung();
if(!$lv_obj->load($lvid))
	die($p->t('semesterplan/fehlerBeimLadenDerLv'));
$stg_obj = new studiengang();

if(!$stg_obj->load($lv_obj->studiengang_kz))
	die($p->t('semesterplan/fehlerBeimLadenDesStudienganges'));

$openpath = DOC_ROOT.'/documents/'.strtolower($stg_obj->kuerzel).'/'.$lv_obj->semester.'/'.strtolower($lv_obj->lehreverzeichnis).'/semesterplan/';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Upload Semesterplan</title>
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<script language="JavaScript" type="text/javascript">
	
	/**
	 * Zeigt eine Sicherheitsabfrage ob die Datei
	 * wirklich gelöscht werden soll
	 */
	function ConfirmFile(handle)
	{
		return confirm("<?php echo $p->t('upload/wollenSieOrdnerWirklichLoeschen'); ?>");
	}
	
	</script>
</head>
<body id="inhalt">
	<table class="tabcontent" >
	<tr>
		<td class="tdwidth10">&nbsp;</td>
		<td class="ContentHeader"><font class="ContentHeader">Upload Semesterplan</font></td>

	</tr>
	<tr>
		<td class="tdwidth10">&nbsp;</td>
		<td class="tdwidth10">&nbsp;</td>
	</tr>

<?php


	if(!$is_lector)
		die('<tr><td class="tdwidth10">&nbsp;</td><td>'.$p->t('global/keineBerechtigungFuerDieseSeite').'</td></tr>');

	echo '<tr><td class="tdwidth10">&nbsp;</td><td>';
	if(isset($_POST['inhalt']))
	{
		$inhalt = $_POST['inhalt'];
		if($inhalt!="____".$p->t('semesterplan/ordnerinhalt')."____")
		{
			if(!mb_strstr($inhalt,'..'))
			{
				if(is_file($openpath . $inhalt))
				{
					writeCISlog('DELETE', "rm -r '$openpath$inhalt'");
					exec("rm -r ".escapeshellarg($openpath.$inhalt));
					echo '<center>'.$p->t('global/erfolgreichgelöscht').'</center>';
				}
				else
				{
				   echo "<center>".$p->t('semesterplan/dateiKonnteNichtGefundenWerden',array($openpath,$inhalt))."</center>";
				}
			}
			else
			{
				writeCISlog('REPORT', 'versuchter Loeschvorgang von '.$openpath.$inhalt);
				echo '<center>Fehlerhafte Parameter</center>';
			}
		}
		else
		{
			echo '<center>'.$p->t('semesterplan/bitteZuerstDateiAuswaehlen').'</center>';
		}
	}

	if(isset($_POST['upload']))
	{
		if(is_uploaded_file($_FILES['userfile']['tmp_name']))
		{
			$fn = $_FILES['userfile']['name']; //Original Dateiname

			if(check_filename($fn))
			{
				if(!stristr($fn, '.php') && !stristr($fn, '.php3') &&
				   !stristr($fn,'.php4') && !stristr($fn, '.php5') &&
				   !stristr($fn, '.cgi') && !stristr($fn, '.pl') && !stristr($fn, '.phtml'))
				{
					if(move_uploaded_file($_FILES['userfile']['tmp_name'],$openpath . $fn))
					{
						exec('sudo chown www-data:teacher '.escapeshellarg($openpath.$fn));
						echo '<center>'.$p->t('semesterplan/fileErfolgreichHochgeladen').'</center>';
					}
					else
						echo '<center>'.$p->t('semesterplan/fehlerBeimUpload').'</center>';
				}
				else
				{
					echo '<center>'.$p->t('semesterplan/dateitypIstNichtErlaubt').'<center>';
				}
			}
			else
				echo '<center>'.$p->t('semesterplan/dateinameNurBuchstaben').'</center>';
		}
		else
			echo '<center>'.$p->t('semesterplan/fehlerBeimUpload').'</center>';
	}

	echo '</tr></td>';

	echo '<tr><td class="tdwidth10">&nbsp;</td><td><form accept-charset="UTF-8" name="form1"  method="POST" action="semupload.php?lvid='.$lvid.'"  onSubmit="return ConfirmFile(this);">';
	echo '<select name="inhalt" size=5>';
	echo '<option selected>____'.$p->t('semesterplan/ordnerinhalt').'____</option>';
	//Inhalt des Semesterplan Ordners Auslesen
	if(is_dir($openpath))
	{
  		$dest_dir = dir($openpath);
		while($entry = $dest_dir->read())
		{
			if(!is_dir($entry))
				echo "<option>$entry</option>";
		}
	}
	echo '</select>';
	echo '<br><input type="submit" value="'.$p->t('global/dateiLoeschen').'">';
	echo '</form></td><td>';

    //FileAuswahlfeld
	echo '<tr><td class="tdwidth10">&nbsp;</td><td><br><form enctype="multipart/form-data" method="POST" action="semupload.php?lvid='.$lvid.'">';
	echo ' <input type="file" name="userfile" size="30">';
	echo ' <input type="submit" name="upload" value="'.$p->t('upload/upload').'">';
	echo '</form></td><td>';
?>
	</table>
</body>
</html>
