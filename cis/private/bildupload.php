<?php
/* Copyright (C) 2006 Technikum-Wien
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
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *
 */

// Oberflaeche zum Upload von Bildern
require_once('../../config/cis.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/akte.class.php');
require_once('../../include/phrasen.class.php');
require_once('../../include/fotostatus.class.php');
$user = get_uid();
$sprache = getSprache();
$p = new phrasen($sprache);
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" type="text/css" href="../../vendor/twbs/bootstrap3/dist/css/bootstrap.min.css">
	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/twbs/bootstrap3/dist/js/bootstrap.min.js"></script>
	<title>'.$p->t('profil/Bildupload').'</title>
</head>
<body>
<div style="padding: 10px">
<h1>'.$p->t('profil/Bildupload').'</h1>';
function resize($filename, $width, $height)
{
	$ext = explode('.',$_FILES['bild']['name']);
	$ext = strtolower($ext[count($ext)-1]);
	// Hoehe und Breite neu berechnen
	list($width_orig, $height_orig) = getimagesize($filename);
	if ($width && ($width_orig < $height_orig))
	{
		$width = ($height / $height_orig) * $width_orig;
	}
	else
	{
		$height = ($width / $width_orig) * $height_orig;
	}

	$image_p = imagecreatetruecolor($width, $height);

	$image = imagecreatefromjpeg($filename);

	//Bild nur verkleinern aber nicht vergroessern
	if($width_orig>$width || $height_orig>$height)
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
	else
		$image_p = $image;

	imagejpeg($image_p, $filename, 80);

	@imagedestroy($image_p);
	@imagedestroy($image);
}
if(isset($_GET['person_id']))
{
	$benutzer = new benutzer();
	$benutzer->load($user);

	if($benutzer->person_id!=$_GET['person_id'])
		die($p->t('global/keineBerechtigungFuerDieseSeite'));

	$fs = new fotostatus();
	if($fs->akzeptiert($benutzer->person_id))
		die($p->t('profil/profilfotoUploadGesperrt'));
}
else
	die($p->t('global/fehlerBeiDerParameteruebergabe'));
//Bei Upload des Bildes
if(isset($_POST['submitbild']))
{
	if(isset($_FILES['bild']['tmp_name']))
	{
		//Extension herausfiltern
		$ext = explode('.',$_FILES['bild']['name']);
		$ext = mb_strtolower($ext[count($ext)-1]);
		$width=101;
		$height=130;

		//--check that it's a jpeg
		if ($ext=='jpg' || $ext=='jpeg')
		{
			$filename = $_FILES['bild']['tmp_name'];

			//groesse auf maximal 827x1063 begrenzen
			resize($filename, 827, 1063);

			$fp = fopen($filename,'r');
			//auslesen
			$content = fread($fp, filesize($filename));
			fclose($fp);

			$akte = new akte();

			if($akte->getAkten($_GET['person_id'], 'Lichtbil'))
			{
				if(count($akte->result)>0)
				{
					$akte = $akte->result[0];
					$akte->new = false;
				}
				else
					$akte->new = true;
			}
			else
			{
				$akte->new = true;
			}

			$akte->dokument_kurzbz = 'Lichtbil';
			$akte->person_id = $_GET['person_id'];
			$akte->inhalt = base64_encode($content);
			$akte->mimetype = "image/jpg";
			$akte->erstelltam = date('Y-m-d H:i:s');
			$akte->gedruckt = false;
			$akte->titel = "Lichtbild_".$_GET['person_id'].".jpg";
			$akte->bezeichnung = "Lichtbild gross";
			$akte->updateamum = date('Y-m-d H:i:s');
			$akte->updatevon = $user;
			$akte->insertamum = date('Y-m-d H:i:s');
			$akte->insertvon = $user;
			$akte->uid = '';

			if(!$akte->save())
			{
				echo '<div class="alert alert-danger">Fehler: '.$akte->errormsg.'</div>';
			}

			//groesse auf maximal 101x130 begrenzen
			resize($filename, 101, 130);

			//in DB speichern
			//File oeffnen
			$fp = fopen($filename,'r');
			//auslesen
			$content = fread($fp, filesize($filename));
			fclose($fp);
			//in base64-Werte umrechnen
			$content = base64_encode($content);
			$person = new person();
			if($person->load($_GET['person_id']))
			{
				//base64 Wert in die Datenbank speichern
				$person->foto = $content;
				$person->new = false;
				if($person->save())
				{
					$fs = new fotostatus();
					$fs->person_id=$person->person_id;
					$fs->fotostatus_kurzbz='hochgeladen';
					$fs->datum = date('Y-m-d');
					$fs->insertamum = date('Y-m-d H:i:s');
					$fs->insertvon = $user;
					$fs->updateamum = date('Y-m-d H:i:s');
					$fs->updatevon = $user;
					if(!$fs->save(true))
						echo '<div class="alert alert-danger">Fehler beim Setzen des Bildstatus</div>';
					else
					{

						echo "<div class='alert alert-success'>Bild wurde erfolgreich gespeichert</div>
						<script language='Javascript'>
							if(typeof(opener.StudentAuswahl) == 'function') 
								opener.StudentAuswahl(); 
							if(typeof(opener.MitarbeiterAuswahl) == 'function') 
								opener.MitarbeiterAuswahl(); 
							if(typeof(opener.RefreshImage) == 'function' ||
							   typeof(opener.RefreshImage) == 'object') 
							{
								opener.RefreshImage(); 
							}
							window.close();
						</script>";
					}
				}
				else
					echo '<div class="alert alert-danger">'.$person->errormsg.'</div>';
			}
			else
				echo '<div class="alert alert-danger">'.$person->errormsg.'</div>';
		}
		else
			echo '<div class="alert alert-danger">'.$p->t('profil/nurJPGBilder').'</div>';
	}
}

//echo '<br>';
echo $p->t('profil/BilduploadInfotext',array($p->t('dms_link/bildRichtlinien'))).'<br><br>
		<form accept-charset="UTF-8" method="POST" enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'?person_id='.$_GET['person_id'].'">
		'.$p->t('profil/Bild').': <input type="file" accept="image/jpeg" name="bild" /><br>
		<button type="submit" class="btn btn-primary" name="submitbild">Upload</button>
		</form>
	</td></tr>';
?>
</div>
</body>
</html>
