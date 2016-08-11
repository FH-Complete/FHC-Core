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
				titel, dms_id, inhalt, mimetype, dokument_kurzbz, tbl_dokument.bezeichnung, sort, akte_id
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

				$filename = DMS_PATH . $dms->filename;

				if(!file_exists($filename))
				{
					$preErrors[] = "'" . $filename . "': Datei nicht gefunden";
				}
			}

			// this should never happen
			if($filename == "")
			{
				$preErrors[] = "'" . $row->akte_id . "': Diese Akte hat keinen Inhalt und keine dms_id";
			}

			if(empty($preErrors))
			{
				/*
				 * Determine the filetype
				 * and convert, if nessecary
				 */
				 $fullFilename = "";
				$explodedTitle = explode(".", $row->titel);
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

					if(!$docExp->convert($filename, $fullFilename, "pdf"))
					{
						$convertSuccess = false;
						$preErrors[] ="'$row->titel': Konvertierung fehlgeschlagen(".$row->mimetype.")";
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
							$dokumente[] = array("name" => $row->bezeichnung);
						else
							$dokumente[] = array("name" => $row->dokument_kurzbz);
					}
					else
					{
						$addString = "";
						if($row->dms_id)
							$addString = "(DMS)";
						else
							$addString = "(DB)";
						if($convertSuccess)
							$preErrors[] = '"' . $row->titel . '":' . $addString . ' Dokument nicht gefunden';
					}
				}
				else
					$preErrors[] ="'$row->titel' hat einen nicht unterstützten mimetype: $row->mimetype";
			}
		}

		/*
		 * Deckblatt
		 */
		$filename = $tmpDir . "/".uniqid();
		$doc = new dokument_export($vorlage_kurzbz, $oe_kurzbz);
		$doc->addDataArray(array('vorname' => $prestudent->vorname, 'nachname' => $prestudent->nachname, array('dokumente'=> $dokumente)),"dokumentenakt");

		if(!$doc->create('pdf'))
			die($doc->errormsg);

		$filename = $tmpDir.'/'.uniqid();
		file_put_contents($filename, $doc->output(false));
		$doc->close();
		$allDocs[] = $filename;
		$allDocs = array_merge($allDocs, $preDocs);
		unset($doc);
	}

	if(!empty($preErrors))
	{
		$errors[$pid] = $preErrors;
	}
}


/*
 * generate the merged PDF
 */
if(count($errors) == 0 || $force)
{
	$finishedPdf = $tmpDir . "/Dokumentenakt.pdf";
	if(!$pdf->merge($allDocs, $finishedPdf))
		cleanUpAndDie($pdf->errormsg, $tmpDir);
	$fsize = filesize($finishedPdf);

	if(!$handle = fopen($finishedPdf,'r'))
		die('load failed');

	header('Content-type: application/pdf');
	header('Content-Disposition: attachment; filename="'.$finishedPdf);
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
