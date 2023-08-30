<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 */
/* Erstellt ein Dokument zum Drucken der Zutrittskarten
 *
 * Parameter:
 * data ... Liste der UIDs mit Strichpunkt getrennt
 * type ... normal | datum - wenn datum übergeben wird, wird nur das Gueltigkeitsdatum erstellt,
 *                           sonst alle Kartendaten
 *
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/akte.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/vorlage.class.php');
require_once('../include/datum.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/konto.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/student.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/benutzerfunktion.class.php');
require_once('../include/organisationseinheit.class.php');
require_once('../include/dokument_export.class.php');
require_once('../include/person.class.php');

$user = get_uid();
$db = new basis_db();

$datum_obj = new datum();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if (!$rechte->isBerechtigt('mitarbeiter/stammdaten') && !$rechte->isBerechtigt('student/stammdaten'))
{
	die('Sie haben keine Berechtigung fuer diese Seite');
}

//UIDs werden entweder als data Parameter mit ";" getrennt übergeben
$uid = isset($_REQUEST['data'])?$_REQUEST['data']:'';
//ODER als POST Array über den Parameter users
$users = isset($_REQUEST['users'])?$_REQUEST['users']:'';
$type = isset($_REQUEST['type'])?$_REQUEST['type']:'normal';
$output = isset($_REQUEST['output'])?$_REQUEST['output']:'pdf';
if ($uid == '' && $users == '')
	die('Parameter data is missing');

$images = array();
$uid_arr = array();
if ($users != '')
	$uid_arr = $users;
else
	$uid_arr = explode(';', $uid);

// Wenn Array mehrere Elemente hat und erstes Element im Array leer ist -> entfernen
if (isset($uid_arr[1]) && $uid_arr[0] == '')
	array_shift($uid_arr);

// Tempordner fuer das erstellen des ODT anlegen
$tempfolder = '/tmp/fhc-'.uniqid();
mkdir($tempfolder);
chdir($tempfolder);

// Unterordner fuer die Bilder erstellen
mkdir('Pictures');

// Studiengang ermitteln dessen Vorlage verwendet werden soll
$xsl_stg_kz = 0;
// Direkte uebergabe des Studienganges dessen Vorlage verwendet werden soll
if (isset($_GET['xsl_stg_kz']))
{
	$xsl_stg_kz = $_GET['xsl_stg_kz'];
}
else
{
	// Wenn eine Studiengangskennzahl uebergeben wird, wird die Vorlage dieses Studiengangs verwendet
	if (isset($_GET['stg_kz']))
		$xsl_stg_kz = $_GET['stg_kz'];
	else
	{
		// Vorlage des Studiengangs aus $uid_arr ermitteln (1. Studierender im Array)
		if ($uid_arr[0] != '')
		{
			$student_obj = new student();
			if ($student_obj->load($uid_arr[0]))
			{
				$xsl_stg_kz = $student_obj->studiengang_kz;
			}
		}
	}
}

// Vorlage der Zutrittskarte laden

if ($xsl_stg_kz == '')
	$xsl_stg_kz = '0';

$xsl_oe_kurzbz = '';
$stg = new studiengang();
if ($stg->load($xsl_stg_kz))
{
	$xsl_oe_kurzbz = $stg->oe_kurzbz;
}
else
	die('Unknown Studiengang');

$dokument = new dokument_export('Zutrittskarte', $xsl_oe_kurzbz);
$filename = 'Zutrittskarte';

foreach ($uid_arr as $uid)
{
	$bn = new benutzer();
	if ($bn->load($uid))
	{
		$gueltigbis = '';

		// Bild der Person holen
		$qry = "SELECT
					inhalt as foto
				FROM
					public.tbl_akte
				WHERE
					dokument_kurzbz = 'Lichtbil'
					AND person_id = ".$db->db_add_param($bn->person_id, FHC_INTEGER);

		$b64bild = '/9j/4AAQSkZJRgABAQEASABIAAD/4QAWRXhpZgAATU0AKgAAAAgAAAAAAAD//gAXQ3JlYXRlZCB3aXRoIFRoZSBHSU1Q/';
		$b64bild .= '9sAQwAFAwQEBAMFBAQEBQUFBgcMCAcHBwcPCwsJDBEPEhIRDxERExYcFxMUGhURERghGBodHR8fHxMXIiQiHiQcHh8e/';
		$b64bild .= '9sAQwEFBQUHBgcOCAgOHhQRFB4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4e/';
		$b64bild .= '8AAEQgAAQABAwEiAAIRAQMRAf/EABUAAQEAAAAAAAAAAAAAAAAAAAAI/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/';
		$b64bild .= 'EABQBAQAAAAAAAAAAAAAAAAAAAAD/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCywAf/2Q==';

		if ($result = $db->db_query($qry))
		{
			//Wenn kein Lichtbild in den Akten vorhanden ist, Foto aus tbl_person holen
			if ($db->db_num_rows($result) == 0)
			{
				$qry_person = "SELECT foto FROM public.tbl_person
					WHERE person_id=".$db->db_add_param($bn->person_id, FHC_INTEGER);

				if ($result_person = $db->db_query($qry_person))
				{
					if ($row_person = $db->db_fetch_object($result_person))
					{
						//Wenn auch kein Foto in tbl_person gespeichert ist, mit der naechsten UID fortfahren
						if ($row_person->foto == '')
							continue;
						else
							$b64bild = $row_person->foto;
					}
				}
			}

			if ($row = $db->db_fetch_object($result))
			{
				//Wenn der Inhalt des Lichtbilds leer ist, Foto aus tbl_person holen
				if ($row->foto == '')
				{
					$qry_person = "SELECT foto FROM public.tbl_person
						WHERE person_id=".$db->db_add_param($bn->person_id, FHC_INTEGER);

					if ($result_person = $db->db_query($qry_person))
					{
						if ($row_person = $db->db_fetch_object($result_person))
						{
							//Wenn auch kein Foto in tbl_person gespeichert ist, mit der naechsten UID fortfahren
							if ($row_person->foto == '')
								continue;
							else
								$b64bild = $row_person->foto;
						}
					}
				}
				else
					$b64bild = $row->foto;
			}
		}

		$imagefilename = $tempfolder.'/Pictures/'.$bn->uid.'.jpg';
		// Bild in den Temp Ordner zwischenspeichern
		file_put_contents($imagefilename, base64_decode($b64bild));

		$images[] = $imagefilename;
		// Bild zum Manifest-File des ODTs hinzufuegen
		$dokument->addImage($imagefilename, $bn->uid.'.jpg', 'image/jpg');

		if (check_lektor($uid))
		{
			$ma = new mitarbeiter();
			$ma->load($uid);
			$benutzerfunktion = new benutzerfunktion();
			$benutzerfunktion->getBenutzerFunktionByUid($uid, null, date("Y-m-d"), date("Y-m-d"));
			if (!empty($benutzerfunktion->result[0]))
			{
				$oe = new organisationseinheit($benutzerfunktion->result[0]->oe_kurzbz);
			}
			else
			{
				$oe = new organisationseinheit();
			}
			$data[]['mitarbeiter'] = array(
				'uid' => $bn->uid,
				'vorname' => $bn->vorname,
				'nachname' => $bn->nachname,
				'titelpre' => $bn->titelpre,
				'titelpost' => $bn->titelpost,
				'personalnummer' => $ma->personalnummer,
				'ausstellungsdatum' => date('d.m.Y'),
				'gebdatum' => $datum_obj->formatDatum($ma->gebdatum, 'd.m.Y'),
				'organisationseinheit' => $oe->bezeichnung
			);
		}
		else
		{
			$student = new student();
			$student->load($bn->uid);
			$konto = new konto();
			$studiengang = new studiengang();
			$studiengang->load($student->studiengang_kz);
			$person = new person();
			$person->getPersonFromBenutzer($uid);

			$stsem_obj = new studiensemester();
			$stsem = $stsem_obj->getaktorNext();
			$stsem_obj->load($stsem);

			if ($konto->checkStudienbeitrag($bn->uid, $stsem_obj->studiensemester_kurzbz))
			{
				$gueltigbis = $stsem_obj->ende;
			}
			else
			{
				// Studiengebuehr noch nicht bezahlt
				$gueltigbis = $stsem_obj->ende;
			}

			if ($type == 'datum')
			{
				//Nur der Datumsstempel wird erstellt
				$data[]['datum'] = array(
					'gueltigbis' => $datum_obj->formatDatum($gueltigbis, 'd/m/Y')
				);
			}
			else
			{
				$data[]['student'] = array(
					'uid' => $bn->uid,
					'vorname' => $bn->vorname,
					'nachname' => $bn->nachname,
					'titelpre' => $bn->titelpre,
					'titelpost' => $bn->titelpost,
					'studiengang' => $studiengang->kurzbzlang,
					'gebdatum' => $datum_obj->formatDatum($bn->gebdatum, 'd.m.Y'),
					'matrikelnummer' => rtrim($student->matrikelnr),
					'matr_nr' => $person->matr_nr,
					'ausstellungsdatum' => date('M.Y'),
					'gueltigbis' => $datum_obj->formatDatum($gueltigbis, 'd.m.Y'),
                    'gueltigbis_3jahre' => date('d.m.Y', strtotime('+3 years'))
				);
			}
		}
	}
}

$dokument->addDataArray($data, 'zutrittskarte');
$dokument->setFilename($filename);

if (!$dokument->create($output))
	die($dokument->errormsg);

$dokument->output();
$dokument->close();

// Cleanup Temp Images
foreach($images as $image)
{
	if(file_exists($image))
		unlink($image);
}
rmdir($tempfolder.'/Pictures');
rmdir($tempfolder);
