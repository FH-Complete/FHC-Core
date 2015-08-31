<?php

require_once('../../config/cis.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/akte.class.php');
require_once('../../include/phrasen.class.php');
require_once('../../include/fotostatus.class.php');

$src = $_POST['src'];
$user = get_uid();

//kopiert von bildupload.php
function resize($filename, $width, $height)
{
	$ext = 'jpg';

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

if(isset($_POST['person_idValue']))
{
	$benutzer = new benutzer();
	$benutzer->load($user);

	if($benutzer->person_id!=$_POST['person_idValue'])
		die($p->t('global/keineBerechtigungFuerDieseSeite'));

	$fs = new fotostatus();
	if($fs->akzeptiert($benutzer->person_id))
		die($p->t('profil/profilfotoUploadGesperrt'));
}
else
	die($p->t('global/fehlerBeiDerParameteruebergabe'));

//file als png und jpg abspeichern
$tmpfname = tempnam(sys_get_temp_dir(), 'FHC');
file_put_contents($tmpfname, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $src)));
$imageTmp=imagecreatefrompng($tmpfname);
imagejpeg($imageTmp, $tmpfname, 100);

$person_id = $_POST['person_idValue'];

//profilbild speichern
if(file_exists($tmpfname))
{
	$width=101;
	$height=130;

	//groesse auf maximal 827x1063 begrenzen
	resize($tmpfname, 827, 1063);

	$fp = fopen($tmpfname,'r');
	//auslesen
	$content = fread($fp, filesize($tmpfname));
	fclose($fp);

	$akte = new akte();

	if($akte->getAkten($person_id, 'Lichtbil'))
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
	$akte->person_id = $person_id;
	$akte->inhalt = base64_encode($content);
	$akte->mimetype = "image/jpg";
	$akte->erstelltam = date('Y-m-d H:i:s');
	$akte->gedruckt = false;
	$akte->titel = "Lichtbild_".$person_id.".jpg";
	$akte->bezeichnung = "Lichtbild gross";
	$akte->updateamum = date('Y-m-d H:i:s');
	$akte->updatevon = $user;
	$akte->insertamum = date('Y-m-d H:i:s');
	$akte->insertvon = $user;
	$akte->uid = '';

	if(!$akte->save())
	{
		echo "<b>Fehler: $akte->errormsg</b>";
	}

	//groesse auf maximal 101x130 begrenzen
	resize($tmpfname, 101, 130);

	//in DB speichern
	//File oeffnen
	$fp = fopen($tmpfname,'r');
	//auslesen
	$content = fread($fp, filesize($tmpfname));
	fclose($fp);
	//in base64-Werte umrechnen
	$content = base64_encode($content);

	$person = new person();
	if($person->load($person_id))
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
				echo '<span class="error">Fehler beim Setzen des Bildstatus</span>';
			else
			{

				echo "<b>Bild wurde erfolgreich gespeichert</b>";
			}
		}
		else
			echo '<b>'.$person->errormsg.'</b><br />';
	}
}

//temporäre files löschen
unlink($tmpfname);
?>
