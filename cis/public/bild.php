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
 * 
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 * 			Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 * 			Rudolf Hangl <rudolf.hangl@technikum-wien.at>
 * 			Manfred Kindl 	<manfred.kindl@technikum-wien.at>.
 */
// Holt ein Bildes aus der DB wandelt es
// um und gibt das ein Bild zurueck.
// Aufruf mit <img src='bild.php?src=person&person_id=1>
require_once ('../../config/cis.config.inc.php');
require_once ('../../include/functions.inc.php');
require_once ('../../include/basis_db.class.php');
require_once ('../../include/benutzer.class.php');
require_once ('../../include/dms.class.php');
require_once ('../../include/person.class.php');

session_start();

if (! $db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

$person_id_user = '';
$person_id_foto = isset($_GET['person_id']) && is_numeric($_GET['person_id']) ? $_GET['person_id'] : '';
$serverzugriff = false;
$source = isset($_GET['src']) ? $_GET['src'] : '';

// Wenn das Bild direkt aufgerufen wird, ist eine Authentifizierung erforderlich
// Wenn es vom Server selbst aufgerufen wird, ist keine Auth. notwendig
// (z.B. fuer die Erstellung von PDFs)
if ($_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR'])
{
	// Wenn Session gesetzt ist, keine Abfrage, da diese Personen noch keine UID haben
	// Von Incomingtool
	if (isset($_SESSION['incoming/user']))
	{
		$person = new person();
		$person_id_user = $person->checkZugangscode($_SESSION['incoming/user']);
	}
	// Von Prestudententool
	elseif (isset($_SESSION['prestudent/user']))
	{
		$person = new person();
		$person_id_user = $person->checkZugangscode($_SESSION['prestudent/user']);
	}
	// Von Bewerbungstool
	elseif (isset($_SESSION['bewerbung/personId']))
	{
		$person_id_user = $_SESSION['bewerbung/personId'];
	}
	else 
	{
		$uid = get_uid();
		$benutzer = new benutzer($uid);
		$person_id_user = $benutzer->person_id;
	}
}
else
	$serverzugriff = true;

// Default Bild (ein weisser Pixel)
/*$cTmpHEX = '/9j/4AAQSkZJRgABAQEASABIAAD/4QAWRXhpZgAATU0AKgAAAAgAAAAAAAD//gAXQ3JlYXRlZCB3aXRoIFRoZSBHSU1Q/9sAQwAFAwQEBAMFBAQEBQUFBgc
			MCAcHBwcPCwsJDBEPEhIRDxERExYcFxMUGhURERghGBodHR8fHxMXIiQiHiQcHh8e/9sAQwEFBQUHBgcOCAgOHhQRFB4eHh4eHh4eHh4eHh4eHh4eHh4eHh
			4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4e/8AAEQgAAQABAwEiAAIRAQMRAf/EABUAAQEAAAAAAAAAAAAAAAAAAAAI/8QAFBABAAAAAAAAAAAAAAAAA
			AAAAP/EABQBAQAAAAAAAAAAAAAAAAAAAAD/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCywAf/2Q==';*/

// Default Bild (Dummy Profilbild)
$cTmpHEX = base64_encode(file_get_contents('../../skin/images/profilbild_dummy.jpg'));

// Hex Dump aus der DB holen

if($source == 'person' && $person_id_foto != '')
{
	$foto_gesperrt = false;
	// Person laden und Fotosperre überprüfen
	$person_obj = new person($person_id_foto);
	if ($person_obj->foto_sperre === true)
	{
		$foto_gesperrt = true;
		// Wenn der User selbst darauf zugreift darf er das Bild sehen
		if ($person_id_user == $person_id_foto)
				$foto_gesperrt = false;
	}
	elseif ($person_id_user == '' && ! $serverzugriff)
	{
		$foto_gesperrt = true;
	}
	
	if ($person_obj->foto != '' && ! $foto_gesperrt)
	{
		$cTmpHEX = base64_decode($person_obj->foto);
	}
}
if($source == 'akte' && $person_id_foto != '')
{
	$qry = "SELECT tbl_akte.inhalt AS foto,
				tbl_person.foto_sperre,
				tbl_akte.dms_id,
				tbl_person.person_id
			FROM PUBLIC.tbl_akte
			JOIN PUBLIC.tbl_person USING (person_id)
			WHERE tbl_akte.person_id = " . $person_id_foto . "
				AND dokument_kurzbz = 'Lichtbil'";
	if ($result = $db->db_query($qry))
	{
		if ($row = $db->db_fetch_object($result))
		{
			$foto_gesperrt = false;
			
			// Schauen ob eine Foto Sperre existiert, wenn nicht, schauen, ob der User auch die selbe Person ist
			if ($db->db_parse_bool($row->foto_sperre))
			{
				$foto_gesperrt = true;
				if ($person_id_user == $person_id_foto)
				{
					// Wenn der User selbst darauf zugreift darf er das Bild sehen
					if ($person_id_user == $person_id_foto)
						$foto_gesperrt = false;
				}
			}
			elseif ($person_id_user == '' && ! $serverzugriff)
			{
				$foto_gesperrt = true;
			}

			// Wenn das Foto nicht im Inhalt steht wird aus aus dem DMS geladen
			if ($row->foto == '' && $row->dms_id != '')
			{
				$dms = new dms();
				if (! $dms->load($row->dms_id))
					die('Kein Dokument vorhanden');
				
				$filename = DMS_PATH . $dms->filename;
				
				$dms->touch($dms->dms_id, $dms->version);
				
				if (file_exists($filename))
				{
					if ($handle = fopen($filename, "r"))
					{
						while (! feof($handle))
						{
							$row->foto .= fread($handle, 8192);
						}
						fclose($handle);
					}
					else
						echo 'Fehler: Datei konnte nicht geoeffnet werden';
				}
				else
					echo 'Die Datei existiert nicht';
			}
			
			if ($row->foto != '' && ! $foto_gesperrt)
			{
				$cTmpHEX = $row->foto;
			}
		}
	}
}
// die bilder werden, sofern es funktioniert, in jpg umgewandelt da es sonst zu fehlern beim erstellen
// von pdfs kommen kann.

$im = @imagecreatefromstring(base64_decode($cTmpHEX));
if ($im != false)
{
	@ob_clean();
	header("Content-type: image/jpeg");
	exit(imagejpeg($im));
}
else
{
	// bei manchen Bildern funktioniert die konvertierung nicht
	// diese werden dann einfach so angezeigt.
	@ob_clean();
	header("Content-type: image/gif");
	exit($cTmpHEX);
}
?>
