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
 *		  Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *		  Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/* Erstellt diverse Dokumente
 *
 * Erstellt ein XML File Transformiert dieses mit
 * Hilfe der XSL-FO Vorlage aus der DB und generiert
 * daraus ein PDF mittels unoconv
 */

/*
 * It raise an error, conflict with CI session
 * session_cache_limiter('none'); //muss gesetzt werden sonst funktioniert der Download mit IE8 nicht
session_start();*/

require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/akte.class.php');
require_once('../include/vorlage.class.php');
require_once('../include/student.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/variable.class.php');
require_once('../include/addon.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/studienordnung.class.php');
require_once('../include/dokument_export.class.php');
require_once('../include/dokument.class.php');
require_once('../include/pdf.class.php');

$user = get_uid();
$db = new basis_db();

$variable_obj = new variable();
$variable_obj->loadVariables($user);

$archivdokument = '';

// Wenn der Parameter archivdokument übergeben wird, werden ein oder mehrere Dokumente aus dem Archiv zu einem PDF zusammengefügt und ausgegeben
// Ansonsten wird ein neues XML-Dokument erstellt
if (isset($_GET['archivdokument']))
{
	$archivdokument = $_GET['archivdokument'];
	$allDocs = array();
	$errorText = '';

	$dokument = new dokument();
	$dokument->loadDokumenttyp($archivdokument);

	$pdf = new pdf();

	// Temporaeren Ordner fuer die Erstellung der Dokumente generieren
	$tmpDir = sys_get_temp_dir() . "/fhc_archivexport_" . uniqid();

	if (!file_exists($tmpDir))
		mkdir($tmpDir, 0777, true);

	// Studierende für Rechteabfrage laden
	if (isset($_GET['uid']) && $_GET['uid'] != '')
	{
		if (strstr($_GET['uid'],';'))
		{
			$uids = explode(';',$_GET['uid']);
		}
		else
			$uids[1] = $_GET['uid'];

		$student_obj = new student();
		if ($student_obj->load($uids[1]))
		{
			$rechte = new benutzerberechtigung();
			$rechte->getBerechtigungen($user);

			if (!$rechte->isBerechtigt('admin', $student_obj->studiengang_kz, 'suid')
				&& !$rechte->isBerechtigt('assistenz', $student_obj->studiengang_kz, 'suid'))
				die('Sie haben keine Berechtigung für diese Studierenden');
			else
			{
				// Die jeweils letzte (aktuellste) Akte dieses Typs von jedem Studierenden laden und in eine temporäre Datei schreiben
				foreach ($uids AS $value)
				{
					// Leere Einträge überspringen
					if ($value == '')
						continue;

					$student_obj = new student($value);
					$person_id = $student_obj->person_id;
					$akte = new akte();
					$akte->getAkten($person_id, $archivdokument, null, null, true, 'erstelltam DESC');

					if (isset($akte->result[0]))
					{
						$filename = '';
						if($akte->result[0]->inhalt != '')
						{
							$filename = $tmpDir . "/" . uniqid();

							$fileData = base64_decode($akte->result[0]->inhalt);
							file_put_contents($filename, $fileData);

							$allDocs[] = $filename;
						}
						else
							$errorText .= "Das Dokument ".$dokument->bezeichnung." bei ".$student_obj->nachname." ".$student_obj->vorname." (".$value.") ist leer\n";
					}
					else
						$errorText .= $student_obj->nachname." ".$student_obj->vorname." (".$value.") hat kein Dokument '".$dokument->bezeichnung."' im Archiv\n";
				}
				if (count($allDocs) == 0)
				{
					rmdir($tmpDir);
					die('Bei keinem der gewählten Studierenden ist einen Bescheid vorhanden');
				}

				// Textseite mit Errormessages generieren und in PDF umwandeln
				if ($errorText != '')
				{
					$errorfile = $tmpDir . "/" . uniqid() . ".txt";
					file_put_contents($errorfile, $errorText);

					$newnameErrorfile = $tmpDir . "/" . uniqid();

					$docExport = new dokument_export();
					$docExport->convert($errorfile, $newnameErrorfile, "pdf");
					unlink($errorfile);

					// Konvertiertes File an erste Position im Array hängen
					array_unshift($allDocs, $newnameErrorfile);
				}

				$finishedPdf = $tmpDir . "/".$archivdokument."_Album.pdf";
				$pdf->merge($allDocs, $finishedPdf);

				foreach ($allDocs as $doc)
					unlink($doc);

				$fsize = filesize($finishedPdf);

				if(!$handle = fopen($finishedPdf,'r'))
					die('load failed');

				header('Content-type: application/pdf');
				header('Content-Disposition: attachment; filename="'.$archivdokument.'_Album.pdf"');
				header('Content-Length: '.$fsize);

				while (!feof($handle))
				{
					echo fread($handle, 8192);
				}
				fclose($handle);

				unlink($finishedPdf);
				rmdir($tmpDir);
			}
		}
		else
			die('Der/Die Studierenden konnte nicht geladen werden');
	}
}
else
{
	//Parameter holen
	if (isset($_GET['xml']))
		$xml = $_GET['xml'];
	else
		die('Fehlerhafte Parameteruebergabe');

	if (isset($_GET['xsl']))
		$xsl = $_GET['xsl'];
	else
		die('Fehlerhafte Parameteruebergabe');

	if(isset($_GET['sign']))
		$sign = true;
	else
		$sign = false;

	// Studiengang ermitteln dessen Vorlage verwendet werden soll
	$xsl_stg_kz = 0;
	// Direkte uebergabe des Studienganges dessen Vorlage verwendet werden soll
	if (isset($_GET['xsl_stg_kz']))
		$xsl_stg_kz = $_GET['xsl_stg_kz'];
	else
	{
		// Wenn eine Studiengangskennzahl uebergeben wird, wird die Vorlage dieses Studiengangs verwendet
		if (isset($_GET['stg_kz']))
			$xsl_stg_kz = $_GET['stg_kz'];
		else
		{
			// Werden UIDs oder Prestudent_IDs uebergeben, wird die Vorlage des Studiengangs genommen
			// in dem der 1. Studierende in der Liste ist
			if (isset($_GET['uid']) && $_GET['uid']!='')
			{
				if (strstr($_GET['uid'],';'))
					$uids = explode(';',$_GET['uid']);
				else
					$uids[1] = $_GET['uid'];

				$student_obj = new student();
				if ($student_obj->load($uids[1]))
				{
					$xsl_stg_kz = $student_obj->studiengang_kz;
				}
			}
			elseif (isset($_GET['prestudent_id']) && $_GET['prestudent_id']!='')
			{
				if (strstr($_GET['prestudent_id'],';'))
					$prestudent_ids = explode(';',$_GET['prestudent_id']);
				else
					$prestudent_ids[1] = $_GET['prestudent_id'];

				$prestudent_obj = new prestudent();
				if ($prestudent_obj->load($prestudent_ids[1]))
				{
					$xsl_stg_kz = $prestudent_obj->studiengang_kz;
				}
			}
		}
	}
	if (isset($_GET['xsl_oe_kurzbz']))
		$xsl_oe_kurzbz = $_GET['xsl_oe_kurzbz'];
	else
		$xsl_oe_kurzbz = '';

	//Parameter setzen
	$params = 'xmlformat=xml';

	// GET Parameter die an XML durchgereicht werden
	foreach ($_GET as $getkey=>$getvalue)
	{
		if (in_array($getkey,
				array('uid', 'stg_kz', 'person_id', 'id', 'prestudent_id', 'buchungsnummern', 'ss', 'abschlusspruefung_id',
					'typ', 'all', 'preoutgoing_id', 'lvid', 'projekt_kurzbz', 'von', 'bis', 'stundevon', 'stundebis',
					'sem', 'lehreinheit', 'mitarbeiter_uid', 'studienordnung_id', 'fixangestellt', 'standort',
					'abrechnungsmonat', 'form', 'projektarbeit_id', 'betreuerart_kurzbz')
			)
		)
		{
			$params .= '&'.$getkey.'='.urlencode($getvalue);
		}
	}

	if (isset($_GET['vertrag_id']))
	{
		foreach($_GET['vertrag_id'] as $id)
		{
			$params .= '&vertrag_id[]='.urlencode($id);
		}
	}
	if (isset($_GET['version']) && is_numeric($_GET['version']))
		$version = $_GET['version'];
	else
		$version = null;

	$output = (isset($_GET['output'])?$_GET['output']:'odt');

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	//OE fuer Output ermitteln

	if ($xsl_oe_kurzbz != '')
	{
		$oe_kurzbz = $xsl_oe_kurzbz;
	}
	else
	{
		if ($xsl_stg_kz == '')
			$xsl_stg_kz = '0';
		$oe = new studiengang();
		$oe->load($xsl_stg_kz);
		$oe_kurzbz = $oe->oe_kurzbz;
	}

	//Darf der User Dokumente in einem NICHT-PDF-Format exportieren?
	if (isset($_GET['output']) && $_GET['output'] != 'pdf')
	{
		if (!$rechte->isBerechtigt('system/change_outputformat',$oe_kurzbz))
		{
			$output = 'pdf';
		}
		else
			$output = $_GET['output'];
	}
	else
		$output = 'pdf';

	// An der FHTW darf das Studienblatt und das Prüfungsprotokoll auch in anderen Formaten exportiert werden
	if (CAMPUS_NAME == 'FH Technikum Wien' &&
		($xsl == 'Studienblatt' ||
			$xsl == 'StudienblattEng' ||
			$xsl == 'PrProtBA' ||
			$xsl == 'PrProtBAEng' ||
			$xsl == 'PrProtMA' ||
			$xsl == 'PrProtMAEng'))
	{
		$output = $_GET['output'];
	}

	$vorlage = new vorlage();
	if(!$vorlage->loadVorlage($xsl))
		die('Vorlage wurde nicht gefunden');

	//Berechtigung pruefen
	if ($xsl == 'AccountInfo')
	{
		$isberechtigt = false;

		$uids = explode(';',$_GET['uid']);
		foreach ($uids as $uid)
		{
			//Berechtigung fuer das Drucken des Accountinfoblattes pruefen
			$ma = new mitarbeiter();
			if($ma->load($uid))
			{
				//Mitarbeiterrechte erforderlich
				if ($rechte->isBerechtigt('admin', 0, 'suid') || $rechte->isBerechtigt('mitarbeiter', 0, 'suid'))
				{
					$isberechtigt = true;
				}
			}

			$stud = new student();
			if ($stud->load($uid))
			{
				//Rechte pruefen
				if ($rechte->isBerechtigt('admin', $stud->studiengang_kz, 'suid') ||
					$rechte->isBerechtigt('admin', 0, 'suid') ||
					$rechte->isBerechtigt('assistenz', $stud->studiengang_kz, 'suid') ||
					$rechte->isBerechtigt('assistenz', 0, 'suid') ||
					$rechte->isBerechtigt('support', 0, 'suid'))
				{
					$isberechtigt=true;
				}
			}
		}

		if (!$isberechtigt)
		{
			echo 'Sie haben keine Berechtigung um dieses AccountInfoBlatt zu drucken';
			exit;
		}
	}
	else
	{
		$vorlagestudiengang = new vorlage();
		if ($xsl_oe_kurzbz != '')
		{
			$vorlagestudiengang->getAktuelleVorlage($xsl_oe_kurzbz, $xsl, $version);
		}
		else
		{
			if ($xsl_stg_kz == '')
				$xsl_stg_kz = '0';

			$vorlagestudiengang->getAktuelleVorlage($xsl_stg_kz, $xsl, $version);
		}
		// Wenn Berechtigung direkt beim der Vorlage angegeben ist
		if (count($vorlagestudiengang->berechtigung)>0)
		{
			$allowed = false;
			foreach($vorlagestudiengang->berechtigung as $berechtigung_kurzbz)
			{
				if ($rechte->isBerechtigt($berechtigung_kurzbz))
					$allowed = true;
			}
			if (!$allowed)
			{
				echo 'unbekanntes Dokument oder keine Berechtigung';
				exit;
			}
		}
		else
		{
			echo 'unbekanntes Dokument oder keine Berechtigung';
			exit;
		}
	}

	//wenn uid gefunden wird, dann den Nachnamen zum Dateinamen dazuhaengen
	$nachname = '';
	if (isset($_GET['uid']) && $_GET['uid']!='')
	{
		$uid = str_replace(';','',$_GET['uid']);
		$benutzer_obj = new benutzer();
		if ($benutzer_obj->load($uid))
			$nachname = '_'.convertProblemChars($benutzer_obj->nachname);

	}
	$filename = $xsl.$nachname;

	if ($xsl_oe_kurzbz == '')
	{
		if ($xsl_stg_kz == '')
			$xsl_stg_kz = '0';
		$stg_obj = new studiengang();
		if (!$stg_obj->load($xsl_stg_kz))
			die($stg_obj->errormsg);
		$xsl_oe_kurzbz = $stg_obj->oe_kurzbz;
	}

	if($sign === true && $vorlage->signierbar === false)
	{
		die('Diese Vorlage darf nicht signiert werden');
	}

	if (!isset($_REQUEST["archive"]))
	{
		if (mb_strstr($vorlage->mimetype, 'application/vnd.oasis.opendocument'))
		{
			$dokument = new dokument_export($xsl, $xsl_oe_kurzbz, $version);
			$dokument->addDataURL($xml, $params);

			/**
			 * Get Filename
			 * TODO cleanup
			 */
			if ($vorlage->bezeichnung!='')
				$filename = $vorlage->bezeichnung.$nachname;
			else
				$filename = $vorlage->vorlage_kurzbz.$nachname;

			switch($xsl)
			{
				case 'LV_Informationen':
					$studiengang = new studiengang($_GET['stg_kz']);
					$studiensemester = new studiensemester($_GET['ss']);
					$filename = $filename.'_'.$studiengang->kurzbzlang.'_'.$studiensemester->studiensemester_kurzbz;
					break;
				case 'Honorarvertrag':
					$filename = $filename.'_'.$benutzer_obj->nachname.'_'.$benutzer_obj->vorname;
					break;
				case 'Studienordnung':
					$studienordnung = new studienordnung();
					$studienordnung->loadStudienordnung($_GET['studienordnung_id']);
					$filename = 'Studienordnung-Studienplan-'. sprintf("%'.04d",$studienordnung->studiengang_kz).'-'.$studienordnung->studiengangkurzbzlang;
					break;
			}

			$dokument->setFilename($filename);
			
			if ($sign === true)
			{
				$dokument->sign($user);
			}

			if ($dokument->create($output))
				$dokument->output();
			else
				echo $dokument->errormsg;

			$dokument->close();
		}
	}
	else
	{
		if(!$vorlage->archivierbar)
			die('Dieses Dokument ist nicht archivierbar');

		$studiengang_kz = null;
		// Archivieren von Dokumenten
		// Wenn UID übergeben wurde ist es ein Student, sonst ein PreStudent (zB Ausbildungsvertrag)
		if (isset($_REQUEST['uid']) && $_REQUEST['uid'] != '')
		{
			$uid = $_REQUEST["uid"];
			$heute = date('Y-m-d');

			$student = new student();
			$student->load($uid);

			$studiengang = new studiengang();
			$studiengang->load($student->studiengang_kz);

			if (isset($_REQUEST['ss']))
			{
				$ss = $_REQUEST["ss"];

				$prestudent = new prestudent();
				$prestudent->getLastStatus($student->prestudent_id,$ss);
				$semester = $prestudent->ausbildungssemester;

				$query = "SELECT
						tbl_studiengang.studiengang_kz, tbl_studentlehrverband.semester, tbl_studiengang.typ,
						tbl_studiengang.kurzbz, tbl_person.person_id FROM tbl_person, tbl_benutzer,
						tbl_studentlehrverband, tbl_studiengang
					WHERE
						tbl_studentlehrverband.student_uid = tbl_benutzer.uid
						AND tbl_benutzer.person_id = tbl_person.person_id
						AND tbl_studentlehrverband.studiengang_kz = tbl_studiengang.studiengang_kz
						AND tbl_studentlehrverband.student_uid = ".$db->db_add_param($uid)."
						AND tbl_studentlehrverband.studiensemester_kurzbz = ".$db->db_add_param($ss);

				if ($result = $db->db_query($query))
				{
					if ($row = $db->db_fetch_object($result))
					{
						$person_id = $row->person_id;
						$titel = mb_substr($xsl."_".strtoupper($row->typ).strtoupper($row->kurzbz)."_".$semester.'_'.$ss, 0, 64);
						if ($xsl == 'Ausbildungsver' || $xsl == 'AusbVerEng')
						{
							$bezeichnung = mb_substr($vorlage->bezeichnung." ".$studiengang->kuerzel, 0, 64);
						}
						else
						{
							$bezeichnung = mb_substr($xsl." ".strtoupper($row->typ).strtoupper($row->kurzbz)." ".$semester.". Semester".' '.$ss, 0, 64);
						}
						$studiengang_kz = $row->studiengang_kz;
					}
					else
					{
						die('StudentIn hat keinen Status in diesem Semester');
					}
				}
			}
			else
			{
				$studiengang_kz = $student->studiengang_kz;
				$person_id = $student->person_id;
				$titel = $vorlage->bezeichnung.'_'.$studiengang->kuerzel;
				$bezeichnung = mb_substr($vorlage->bezeichnung." ".$studiengang->kuerzel, 0, 64);
			}
		}
		elseif (isset($_REQUEST['prestudent_id']) && $_REQUEST['prestudent_id'] != '')
		{
			$prestudent_id = $_REQUEST["prestudent_id"];
			$heute = date('Y-m-d');
			$uid = '';

			$prestudent = new prestudent($prestudent_id);
			$prestudent->getLastStatus($prestudent_id);
			$studiengang_kz = $prestudent->studiengang_kz;
			$studiengang = new studiengang();
			$studiengang->load($studiengang_kz);

			$person_id = $prestudent->person_id;
			$titel = mb_substr($xsl."_".$studiengang->kuerzel, 0, 64);
			$bezeichnung = mb_substr($vorlage->bezeichnung." ".$studiengang->kuerzel, 0, 64);
		}

		if ($rechte->isBerechtigt('admin', $studiengang_kz, 'suid')
		 || $rechte->isBerechtigt('assistenz', $studiengang_kz, 'suid'))
		{
			$dokument = new dokument_export($xsl, $xsl_oe_kurzbz, $version);
			$dokument->addDataURL($xml, $params);

			$dokument->setFilename($filename);

			$error = false;
			
			// XML-tag archivierbar ergaenzen
			$dokument->setXMLTag_archivierbar();

			// Beim Archivieren wird das Dokument immer signiert wenn moeglich
			if($vorlage->signierbar)
				$sign = true;

			if ($sign === true)
			{
				$dokument->sign($user);
			}
			
			if ($dokument->create($output))
				$doc = $dokument->output(false);
			else
			{
				$errormsg = $dokument->errormsg;
				$error = true;
			}

			$dokument->close();

			if(!$error)
			{
				$hex = base64_encode($doc);
				$akte = new akte();
				$akte->person_id = $person_id;
				if($vorlage->dokument_kurzbz!='')
					$akte->dokument_kurzbz = $vorlage->dokument_kurzbz;
				else
					$akte->dokument_kurzbz = 'Zeugnis';
				$akte->inhalt = $hex;
				$akte->mimetype = 'application/pdf';
				$akte->erstelltam = $heute;
				$akte->gedruckt = true;
				$akte->titel = $titel.'.pdf';
				$akte->bezeichnung = $bezeichnung;
				$akte->updateamum = '';
				$akte->updatevon = '';
				$akte->insertamum = date('Y-m-d H:i:s');
				$akte->insertvon = $user;
				$akte->ext_id = '';
				$akte->uid = $uid;
				$akte->new = true;
				$akte->archiv = true;
				$akte->signiert = $sign;
				$akte->stud_selfservice = $vorlage->stud_selfservice;

				if (!$akte->save())
				{
					echo 'Erstellen Fehlgeschlagen: '.$akte->errormsg;
					return false;
				}
				else
				{
					return true;
				}
			}
			else
			{
				echo $errormsg;
				return false;
			}
		}
		else
			echo 'Keine Berechtigung zum Speichern';
	}
}
?>
