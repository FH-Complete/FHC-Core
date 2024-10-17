<?php
/* Copyright (C) 2016 Technikum-Wien
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
 * Authors: Andreas Moik <moik@technikum-wien.at>.
 */


require_once(dirname(__FILE__).'/../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../include/pdf.class.php');
require_once(dirname(__FILE__).'/../include/dokument_export.class.php');
require_once(dirname(__FILE__).'/../include/phrasen.class.php');
require_once(dirname(__FILE__).'/../include/prestudent.class.php');
require_once(dirname(__FILE__).'/../include/dms.class.php');
require_once(dirname(__FILE__).'/../include/adresse.class.php');
require_once(dirname(__FILE__).'/../include/nation.class.php');
require_once(dirname(__FILE__).'/../include/kontakt.class.php');
require_once(dirname(__FILE__).'/../include/studiengang.class.php');
require_once(dirname(__FILE__).'/../include/notiz.class.php');

$sprache = getSprache();
$p=new phrasen($sprache);

$db = new basis_db();

$errors = array();

$user = get_uid();

if(!isset($_GET["prestudent_ids"]) || !isset($_GET["vorlage_kurzbz"]))
	die($p->t('anwesenheitsliste/fehlerhafteParameteruebergabe'));


$vorlage_kurzbz = $_GET["vorlage_kurzbz"];
$prestudent_ids = explode(";", $_GET["prestudent_ids"]);

if(empty($prestudent_ids))
	die($p->t('anwesenheitsliste/fehlerhafteParameteruebergabe'));

( isset($_GET["force"]) ? $force = true : $force = false);

/*
 * Temporaeren Ordner fuer die erstellung der Dokumente generieren
 */
$tmpDir = sys_get_temp_dir() . "/dokumentenakt_" . uniqid();

if (!file_exists($tmpDir))
	mkdir($tmpDir, 0777, true);

/*
 * converter classes
 */
$pdf = new pdf();
$docExp = new dokument_export();


/*
 * Create Documents
 */
$allDocs = array();
foreach($prestudent_ids as $pid)
{
	$preErrors = array();
	$prestudent = new prestudent();
	$dokumente = array();
	if(!$prestudent->load($pid))
	{
		$preErrors[] = $p->t('tools/studentWurdeNichtGefunden')."(".$pid.")";
	}

	if(empty($preErrors))
	{
		/*
		 * Determine the oe_kurzbz
		 */
		$query= '
			SELECT
				oe_kurzbz
			FROM
				public.tbl_prestudent
				JOIN public.tbl_studiengang USING(studiengang_kz)
			WHERE
				prestudent_id='.$db->db_add_param($pid, FHC_INTEGER).'
		';
		if(!$res = $db->db_query($query))
			die("Es ist ein Fehler bei der Ermittlung der Organisationseinheit aufgetreten");

		if(!$row = $db->db_fetch_object($res))
			die("Es ist ein Fehler bei der Ermittlung der Organisationseinheit aufgetreten");

		$vorlagestudiengang_id = recursiveGetVorlagestudiengang_id($row->oe_kurzbz, $db);
		$oe_kurzbz = $row->oe_kurzbz;
		/*
		 * Get all Documents
		 */
		$query= '
			SELECT
				tbl_akte.bezeichnung as dateiname,
				titel,
				dms_id,
				inhalt,
				mimetype,
				dokument_kurzbz,
				tbl_dokument.bezeichnung,
				tbl_akte.bezeichnung as aktbezeichnung,
				sort,
				akte_id,
				nachgereicht,
				tbl_akte.anmerkung
			FROM
				public.tbl_vorlagedokument
				JOIN public.tbl_dokument USING(dokument_kurzbz)
				JOIN public.tbl_akte USING(dokument_kurzbz)
				JOIN public.tbl_prestudent USING(person_id)
			WHERE
				vorlagestudiengang_id='.$db->db_add_param($vorlagestudiengang_id).'
				AND prestudent_id='.$db->db_add_param($pid).'
			ORDER BY sort asc;
		';

		$preDocs = array();
		$result = $db->db_query($query);

		while($row = $db->db_fetch_object($result))
		{
			$docErrors = array();
			$convertSuccess = true;
			$filename = "";
			if($row->inhalt != null)
			{
				$filename = $tmpDir . "/" . uniqid();
				$fileData = base64_decode($row->inhalt);
				file_put_contents($filename, $fileData);
			}
			else if($row->dms_id != null)
			{
				$dms = new dms();
				$dms->load($row->dms_id);

				if ($dms->mimetype != $row->mimetype)
				{
					$docErrors[] = "Mimetype von Akte-ID ".$row->akte_id." \"".$row->mimetype."\" und DMS-ID ".$row->dms_id." \"".$dms->mimetype."\" stimmen nicht ueberein. Bitte kontaktieren Sie einen Administrator";
				}

				$filename = DMS_PATH . $dms->filename;

				if(!file_exists($filename))
				{
					$docErrors[] = "".$row->dokument_kurzbz." '".$filename."': Datei nicht gefunden (DMS-ID ".$row->dms_id."; Akte-ID ".$row->akte_id.")";
				}
			}

			// this should never happen
			if($filename == "" && $row->nachgereicht == 'f')
			{
				$docErrors[] = "".$row->dokument_kurzbz." '".$row->akte_id."': Diese Akte hat keinen Inhalt und keine dms_id";
			}

			if(empty($docErrors))
			{
				/*
				 * Determine the filetype
				 * and convert, if nessecary
				 */
				 $fullFilename = "";
				$explodedTitle = explode(".", $row->dateiname);
				$type = $explodedTitle[count($explodedTitle)-1];

				if(
					 $type == "jpg"
				|| $type == "jpeg"
				|| $row->mimetype == "image/jpeg"
				|| $row->mimetype == "image/jpg"
				|| $row->mimetype == "image/pjpeg"
				)
				{
					$fullFilename = $tmpDir . "/".uniqid() . ".pdf";
					if(!$pdf->jpegToPdf($filename, $fullFilename))
						cleanUpAndDie($pdf->errormsg, $tmpDir);
				}
				else if
				(
					 $type == "odt"
				|| $type == "doc"
				|| $type == "docx"
				|| $row->mimetype == "application/vnd.oasis.opendocument.spreadsheet"
				|| $row->mimetype == "application/msword"
				|| $row->mimetype == "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
				|| $row->mimetype == "application/haansoftdocx"
				|| $row->mimetype == "application/vnd.ms-word"
				|| $row->mimetype == "application/vnd.oasis.opendocument.text"
				)
				{
					$fullFilename = $tmpDir . "/".uniqid() . ".pdf";
					$convert = $docExp->convert($filename, $fullFilename, "pdf");

					if(!$convert)
					{
						$convertSuccess = false;
						$docErrors[] = ($row->titel != ''? $row->titel:$row->bezeichnung)." (Akte_ID ".$row->akte_id."): Konvertierung fehlgeschlagen(".$row->mimetype.")";
					}
				}
				else if(
					 $type == "pdf"
				|| $row->mimetype == "application/pdf"
				)
				{
					$fullFilename = $filename;
				}

				// only filled, if the file is supported
				if($fullFilename != "")
				{
					if(file_exists($fullFilename))
					{
						$preDocs[] = $fullFilename;
						if(isset($row->bezeichnung) && $row->bezeichnung && $row->bezeichnung != "")
						{
							if ($row->titel != '')
								$dokumente[]['dokument'] = array(
															"name" => $row->bezeichnung,
															"filename" => $row->titel,
															"nachgereicht" => ($row->nachgereicht == 't'?'true':'false'),
															"anmerkung" => $row->anmerkung);
							else
								$dokumente[]['dokument'] = array(
															"name" => $row->bezeichnung,
															"filename" => $row->aktbezeichnung,
															"nachgereicht" => ($row->nachgereicht == 't'?'true':'false'),
															"anmerkung" => $row->anmerkung);
						}
						else
						{
							if ($row->titel != '')
								$dokumente[]['dokument'] = array(
															"name" => $row->dokument_kurzbz,
															"filename" => $row->titel,
															"nachgereicht" => ($row->nachgereicht == 't'?'true':'false'),
															"anmerkung" => $row->anmerkung);
							else
								$dokumente[]['dokument'] = array(
															"name" => $row->dokument_kurzbz,
															"filename" => $row->aktbezeichnung,
															"nachgereicht" => ($row->nachgereicht == 't'?'true':'false'),
															"anmerkung" => $row->anmerkung);
						}
					}
					else
					{
						$addString = "";
						if($row->dms_id)
							$addString = "(DMS)";
						else
							$addString = "(DB)";
						if($convertSuccess)
							$docErrors[] = '"$row->dokument_kurzbz: Akte-ID '.$row->akte_id.' '.$row->titel.'":' . $addString . ' Dokument nicht gefunden';
					}
				}
				else
				{
					$docErrors[] ="$row->dokument_kurzbz: Akte-ID $row->akte_id $row->titel hat einen nicht unterstützten mimetype: $row->mimetype";
					$dokumente[]['dokument'] = array(
							"name" => $row->bezeichnung,
							"filename" => $row->aktbezeichnung,
							"nachgereicht" => ($row->nachgereicht == 't'?'true':'false'),
							"anmerkung" => $row->anmerkung,
							"errormsg" => 'Dieses Dateiformat kann nicht in diesem PDF ausgegeben werden.');
				}
			}
			if(!empty($docErrors))
			{
				if(!isset($errors[$pid]))
					$errors[$pid] = array();

				$errors[$pid] = array_merge($errors[$pid], $docErrors);
			}
		}

		/*
		 * Deckblatt
		 */
		$adresse = new adresse();
		$adresse->load_pers($prestudent->person_id);
		$strasse = '';
		$plz = '';
		$bundesland = '';
		$heimatBundesland = '';
		$zustellBundesland = '';

		$nation = new nation($prestudent->geburtsnation);
		$geburtsnation = $nation->kurztext;
		$nation->load($prestudent->staatsbuergerschaft);
		$staatsbuergerschaft = $nation->kurztext;
		$nation->load($prestudent->zgvnation);
		$zgvnation = $nation->kurztext;

		$svnr = ($prestudent->svnr == '')?($prestudent->ersatzkennzeichen != ''?'Ersatzkennzeichen: '.$prestudent->ersatzkennzeichen:''):$prestudent->svnr;

		foreach($adresse->result as $row_adresse)
		{
			if($row_adresse->heimatadresse)
			{
				$heimatStrasse = $row_adresse->strasse;
				$heimatPlz = $row_adresse->plz;
				$heimatOrt = $row_adresse->ort;
				// Bundesland ermitteln
				if ($row_adresse->gemeinde != '')
				{
					$qry= '
						SELECT
							tbl_bundesland.bezeichnung AS bundesland
						FROM
							bis.tbl_gemeinde
						JOIN
							bis.tbl_bundesland ON (tbl_gemeinde.bulacode = tbl_bundesland.bundesland_code)
						WHERE
							tbl_gemeinde.name='.$db->db_add_param($row_adresse->gemeinde).'
						LIMIT 1;
					';
					if ($res = $db->db_query($qry))
					{
						if ($row = $db->db_fetch_object($res))
							$heimatBundesland = $row->bundesland;
						else
							$errors[$pid][] = "Es ist ein Fehler bei der Ermittlung des Bundeslandes der Heimatadresse aufgetreten";
					}
					else
						$errors[$pid][] = "Es ist ein Fehler bei der Ermittlung des Bundeslandes der Heimatadresse aufgetreten";
				}
				else
					$errors[$pid][] = "Heimat-Bundesland kann nicht ermittelt werden, da keine Gemeinde eingetragen ist";
			}
			if($row_adresse->zustelladresse)
			{
				$zustellStrasse = $row_adresse->strasse;
				$zustellPlz = $row_adresse->plz;
				$zustellOrt = $row_adresse->ort;
				// Bundesland ermitteln
				if ($row_adresse->gemeinde != '')
				{
					$qry= '
						SELECT
							tbl_bundesland.bezeichnung AS bundesland
						FROM
							bis.tbl_gemeinde
						JOIN
							bis.tbl_bundesland ON (tbl_gemeinde.bulacode = tbl_bundesland.bundesland_code)
						WHERE
							tbl_gemeinde.name='.$db->db_add_param($row_adresse->gemeinde).'
						LIMIT 1;
					';
					if ($res = $db->db_query($qry))
					{
						if ($row = $db->db_fetch_object($res))
							$zustellBundesland = $row->bundesland;
						else
							$errors[$pid][] = "Es ist ein Fehler bei der Ermittlung des Bundeslandes der Heimatadresse aufgetreten";
					}
					else
						$errors[$pid][] = "Es ist ein Fehler bei der Ermittlung des Bundeslandes der Heimatadresse aufgetreten";
				}
				else
					$errors[$pid][] = "Zustelladresse-Bundesland kann nicht ermittelt werden, da keine Gemeinde eingetragen ist";
				//break;
			}
		}
		$kontakt = new kontakt();
		$kontakt->load_pers($prestudent->person_id);
		$telefonnummer = '';
		foreach($kontakt->result as $row)
		{
			if ($row->zustellung)
			{
				if ($row->kontakttyp == 'mobil')
				{
					$telefonnummer = $row->kontakt;
				}
				elseif ($row->kontakttyp == 'telefon' && $telefonnummer == '')
				{
					$telefonnummer = $row->kontakt;
				}

				if ($row->kontakttyp == 'email')
				{
					$mailadresse = $row->kontakt;
				}
			}

		}
		// Studiengang der Bewerbung
		$studiengang = new studiengang();
		$studiengang->load($prestudent->studiengang_kz);

		// Datum Bewerbungabgschickt
		$prestudentstatus = new prestudent();
		$prestudentstatus->getLastStatus($pid, '', 'Interessent');

		// Spezielle Notizen auslesen und aufbereiten
		$notiz = new notiz();
		$notiz->getNotiz('','','','','','',$pid,'','','','','','aufnahme/spezialisierung');

		$notiz_text_array = array();
		foreach ($notiz->result as $notiz_row)
		{
			// Entfernt alle HTML und PHP-Tags aus der Notiz und splittet sie als array auf
			$array_help = explode(';', strip_tags($notiz_row->text));
			foreach ($array_help as $key => $value)
				$notiz_text_array[]['aufnahme_notiz'] = $value;
		}

		$doc = new dokument_export($vorlage_kurzbz, $oe_kurzbz);
		$doc->addDataArray(array(
				'studiengang_kuerzel' => strtoupper($studiengang->typ.$studiengang->kurzbz),
				'orgform_kurzbz' => $prestudent->orgform_kurzbz,
				'bewerbung_abgeschicktamum' => date('d.m.Y H:i',strtotime($prestudentstatus->bewerbung_abgeschicktamum)),
				'vorname' => $prestudent->vorname,
				'nachname' => $prestudent->nachname,
				'geb_datum' => date('d.m.Y',strtotime($prestudent->gebdatum)),
				'gebort' => $prestudent->gebort,
				'heimat_strasse' => $heimatStrasse,
				'heimat_plz' => $heimatPlz,
				'heimat_ort' => $heimatOrt,
				'heimat_bundesland' => $heimatBundesland,
				'zustell_strasse' => $zustellStrasse,
				'zustell_plz' => $zustellPlz,
				'zustell_ort' => $zustellOrt,
				'zustell_bundesland' => $zustellBundesland,
				'geburtsnation' => $geburtsnation,
				'svnr' => $svnr,
				'staatsbuergerschaft' => $staatsbuergerschaft,
				'geschlecht' => $prestudent->geschlecht,
				'telefonnummer' => $telefonnummer,
				'email' => $mailadresse,
				'zgvort' => $prestudent->zgvort,
				'zgvdatum' => $prestudent->zgvdatum,
				'zgvnation' => $zgvnation,
				array('dokumente'=> $dokumente),
				array('aufnahme_notizen'=> $notiz_text_array)

		),"dokumentenakt"
				);
		//echo $doc->getXML();exit;
		if(!$doc->create('pdf'))
			die($doc->errormsg);

		$filename = $tmpDir.'/'.uniqid();
		file_put_contents($filename, $doc->output(false));
		$doc->close();
		$allDocs[] = $filename;
		$allDocs = array_merge($allDocs, $preDocs);
		unset($doc);
	}
}

/*
 * generate the merged PDF
 */
if(count($errors) == 0 || $force)
{
	$finishedPdf = $tmpDir . "/Dokumentenakt.pdf";
	//$finishedPdf = "/Dokumentenakt.pdf";
	if(!$pdf->merge($allDocs, $finishedPdf))
		cleanUpAndDie($pdf->errormsg, $tmpDir);
	$fsize = filesize($finishedPdf);

	if(!$handle = fopen($finishedPdf,'r'))
		die('load failed');

	header('Content-type: application/pdf');
	header('Content-Disposition: attachment; filename="Dokumentenakt.pdf"');
	header('Content-Length: '.$fsize);

	while (!feof($handle))
	{
		echo fread($handle, 8192);
	}
	fclose($handle);

}
else
{
?>
	<html>
		<head>
			<link rel="stylesheet" href="../skin/vilesci.css" type="text/css">
		</head>
		<body>
<?php
	echo "<h1>Es sind folgende Fehler aufgetreten:</h1>";

	foreach($errors as $pid => $pre)
	{
		$ps = new prestudent();
		if(!$ps->load($pid))
			echo "<h2>$pid</h2>";
		else
			echo "<h2>$ps->vorname $ps->nachname</h2>";
		echo "<ul>";
		foreach($pre as $pe)
		{
			echo "<li>$pe</li>";
		}
		echo "</ul>";
	}
	echo "<p>Fehlerhafte Dokumente können übersprungen werden:</p>";
	echo "<form action='dokumentenakt.pdf.php' method='GET'>";
	echo '<input type="hidden" name="prestudent_ids" value="'.$_GET["prestudent_ids"].'"/>';
	echo '<input type="hidden" name="vorlage_kurzbz" value="'.$vorlage_kurzbz.'"/>';
	echo '<input type="submit" name="force" value="Fortfahren" title="Fehlerhafte Dokumente auslassen"/>';
	echo "</form>";
	?>
		</body>
	</html>
	<?php
}


/*
 * Cleanup
 */
removeFolder($tmpDir);





/*
 * Functions
 */
function cleanUpAndDie($msg, $tmpDir)
{
	removeFolder($tmpDir);
	die($msg);
}

function removeFolder($dir)
{
	if($dir == "/")
		return false;
	if (is_dir($dir) === true)
	{
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach ($files as $file)
		{
			unlink($dir . "/" . $file);
		}
		return rmdir($dir);
	}
	return false;
}


function recursiveGetVorlagestudiengang_id($oe_kurzbz, $db)
{
	$query= '
		SELECT
			vorlagedokument_id, vorlagestudiengang_id
		FROM
			public.tbl_vorlagestudiengang
			JOIN public.tbl_vorlagedokument USING(vorlagestudiengang_id)
		WHERE
			oe_kurzbz='.$db->db_add_param($oe_kurzbz).'
			AND aktiv
		ORDER BY version DESC LIMIT 1
	';

	if(!$res = $db->db_query($query))
		die("Fehler beim holen der Dokumentenliste");


	if(!$db->db_num_rows($res))
	{
		/*
		 * Nothing found, so we determine the
		 * oe_parent_kurzbz and try it again
		 */
		$queryParent= '
			SELECT
				oe_parent_kurzbz
			FROM
				public.tbl_organisationseinheit
			WHERE
				oe_kurzbz='.$db->db_add_param($oe_kurzbz).'
		';
		if(!$resultParent = $db->db_query($queryParent))
			die("Fehler beim holen der Dokumentenliste");
		if(!$rowParent = $db->db_fetch_object($resultParent))
			return false;

		return recursiveGetVorlagestudiengang_id($rowParent->oe_parent_kurzbz, $db);
	}
	else
	{
		$row = $db->db_fetch_object($res);
		return $row->vorlagestudiengang_id;
	}
	return false;
}
?>
