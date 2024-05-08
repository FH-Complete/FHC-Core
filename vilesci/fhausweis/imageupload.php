<?php
/*
 * Copyright (C) 2006 Technikum-Wien
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 * Authors: Manfred Kindl <manfred.kindl@technikum-wien.at>
 */
header("Content-Type: text/html; charset=utf-8");

require_once ('../../config/cis.config.inc.php');
require_once ('../../include/functions.inc.php');
require_once ('../../include/person.class.php');
require_once ('../../include/benutzer.class.php');
require_once ('../../include/akte.class.php');
require_once ('../../include/phrasen.class.php');
require_once ('../../include/fotostatus.class.php');
require_once ('../../include/dms.class.php');
require_once ('../../include/benutzerberechtigung.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('basis/fhausweis','suid'))
{
	die($rechte->errormsg);
}

// Bild kommt im Seitenverhältnis 3:4 passend für FH-Ausweis
$base64_src = isset($_POST['src']) ? $_POST['src'] : die($p->t('global/fehlerBeiDerParameteruebergabe').'"src"');
$person_id = isset($_POST['person_id']) ? $_POST['person_id'] : die($p->t('global/fehlerBeiDerParameteruebergabe').'"person_id"');
$img_filename = isset($_POST['img_filename']) ? $_POST['img_filename'] : die($p->t('global/fehlerBeiDerParameteruebergabe').'"img_filename"');
$img_type = isset($_POST['img_type']) ? $_POST['img_type'] : die($p->t('global/fehlerBeiDerParameteruebergabe').'"img_type"');
$result_obj = array();

// Entfernt den data-string (data:image/png;base64,) vom Beginn des Codes damit nur der reine base64 Code zurueckgegeben wird
$base64_src = (preg_replace('/^data:(.*?)base64,/', '', $base64_src));

// Falls die $base64_src danach leer sein sollte, wird abgebrochen
if ($base64_src == '')
{
	$result_obj['type'] = "error";
	$result_obj['msg'] = "<b>Fehler: $akte->errormsg</b>";
	echo json_encode($result_obj);
	exit;
}

function resize($base64, $width, $height) // 828 x 1104 -> 240 x 320
{
	ob_start();
	$image = imagecreatefromstring (base64_decode($base64));
	
	// Hoehe und Breite neu berechnen
	list ($width_orig, $height_orig) = getimagesizefromstring (base64_decode($base64));

	if ($width && ($width_orig < $height_orig))
	{
		$width = intval(($height / $height_orig) * $width_orig);
	}
	else
	{
		$height = intval(($width / $width_orig) * $height_orig);
	}

	$image_p = imagecreatetruecolor($width, $height);
	//$image = imagecreatefromjpeg($filename);
	
	// Bild nur verkleinern aber nicht vergroessern
	if ($width_orig > $width || $height_orig > $height)
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
	else
		$image_p = $image;

	imagejpeg($image_p);
	$retval =  ob_get_contents();
	ob_end_clean();
	$retval = base64_encode($retval);
	
	@imagedestroy($image_p);
	@imagedestroy($image);
	return $retval;
}

// Wenn der Fotostatus "Akzeptiert" ist, darf kein neues Foto hochgeladen werden
// Auskommentiert, da mit Recht basis/fhausweis schon möglich
/*$fs = new fotostatus();
if ($fs->akzeptiert($person_id))
	die($p->t('profil/profilfotoUploadGesperrt'));*/

// DMS eintrag erstellen
$ext = strtolower(pathinfo($img_filename, PATHINFO_EXTENSION));
$dms_filename = uniqid();
$dms_filename .= "." . $ext;
$filename_path = DMS_PATH . $dms_filename;

// Im DMS wird das Bild in der Originalauflösung von 828x1104 gespeichert
$newfile = fopen($filename_path, 'w');
fwrite($newfile, base64_decode($base64_src));

if (fclose($newfile))
{
	// Wenn Akte mit DMS-ID vorhanden, dann neue DMS-Version hochladen
	$akte = new akte();
	$version = '0';
	$dms_id = '';
	if ($akte->getAkten($person_id, 'Lichtbil'))
	{
		// Erste Akte @todo: Ist auch so in content/akte.php. Kann irrefuehrende Ergebisse liefern, wenn bereits mehrere Akten des selben Typs vorhanden sind.
		if (isset($akte->result[0]))
		{
			$akte = $akte->result[0];
			if ($akte->dms_id != '')
			{
				$dms = new dms();
				$dms->load($akte->dms_id);
				
				$version = $dms->version + 1;
				$dms_id = $akte->dms_id;
			}
		}
	}
	
	$dms = new dms();
	
	$dms->dms_id = $dms_id;
	$dms->version = $version;
	$dms->kategorie_kurzbz = 'Akte';
	
	$dms->insertamum = date('Y-m-d H:i:s');
	$dms->insertvon = $uid;
	$dms->mimetype = cutString($img_type, 256);
	$dms->filename = $dms_filename;
	$dms->name = cutString($img_filename, 256, '~', true);
	
	if ($dms->save(true))
	{
		$dms_id = $dms->dms_id;

		$akte = new akte();
		
		if ($akte->getAkten($person_id, 'Lichtbil'))
		{
			if (count($akte->result) > 0)
			{
				$akte = $akte->result[0];
				$akte->new = false;
				$akte->updateamum = date('Y-m-d H:i:s');
				$akte->updatevon = $uid;
			}
			else
			{
				$akte->new = true;
				$akte->insertamum = date('Y-m-d H:i:s');
				$akte->insertvon = $uid;
			}
		}
		else
		{
			$akte->new = true;
			$akte->insertamum = date('Y-m-d H:i:s');
			$akte->insertvon = $uid;
		}
		
		$akte->dokument_kurzbz = 'Lichtbil';
		$akte->person_id = $person_id;
		//$akte->inhalt = base64_encode($content); Fotos werden nur als DMS und in tbl_person gespeichert
		$akte->mimetype = $img_type;
		$akte->erstelltam = date('Y-m-d H:i:s');
		$akte->gedruckt = false;
		$akte->titel = cutString($img_filename, 32, '~', true); // Filename
		$akte->bezeichnung = "Lichtbild gross";
		$akte->uid = '';
 		$akte->nachgereicht = false;
// 		$akte->anmerkung = ''; Auch bei nachträglichem Upload bleibt die Anmerkung erhalten
		$akte->dms_id = $dms_id;
		
		if (! $akte->save())
		{
			$result_obj['type'] = "error";
			$result_obj['msg'] = "<b>Fehler: $akte->errormsg</b>";
			echo json_encode($result_obj);
		}
	}
	else
	{
		$result_obj['type'] = "error";
		$result_obj['msg'] = $p->t('global/fehlerBeimSpeichernDerDaten');
		echo json_encode($result_obj);
	}
}
else
{
	$result_obj['type'] = "error";
	$result_obj['msg'] = $p->t('global/dateiNichtErfolgreichHochgeladen');
	echo json_encode($result_obj);
}

// Bild in tbl_person auf 240x320 skalieren
$base64_src = resize($base64_src, 240, 320);

$person = new person();
if ($person->load($person_id))
{
	// base64 Wert in die Datenbank speichern
	$person->foto = $base64_src;
	$person->new = false;
	// Fotostatus auf "hochgeladen" setzen
	if ($person->save())
	{
		$fs = new fotostatus();
		$fs->person_id = $person->person_id;
		$fs->fotostatus_kurzbz = 'hochgeladen';
		$fs->datum = date('Y-m-d');
		$fs->insertamum = date('Y-m-d H:i:s');
		$fs->insertvon = $uid;
		$fs->updateamum = date('Y-m-d H:i:s');
		$fs->updatevon = $uid;
		if (! $fs->save(true))
			echo '<span class="error">Fehler beim Setzen des Bildstatus</span>';
		else
		{
			$result_obj['type'] = "success";
			$result_obj['msg'] = "<b>Bild wurde erfolgreich gespeichert</b>";
			echo json_encode($result_obj);
		}
	}
	else
	{
		$result_obj['type'] = "error";
		$result_obj['msg'] = "<b>" . $person->errormsg . "</b>";
		echo json_encode($result_obj);
	}
}
