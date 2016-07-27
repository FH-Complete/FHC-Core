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

$user = get_uid();

if(!isset($_GET["prestudent_ids"]) || !isset($_GET["vorlage_kurzbz"]))
	die($p->t('anwesenheitsliste/fehlerhafteParameteruebergabe'));

$prestudent_ids = explode(";", $_GET["prestudent_ids"]);

if(count($prestudent_ids) < 1)
	die($p->t('anwesenheitsliste/fehlerhafteParameteruebergabe'));



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
	$prestudent = new prestudent();
	if(!$prestudent->load($pid))
		cleanUpAndDie($p->t('tools/studentWurdeNichtGefunden')."(".$pid.")", $tmpDir);

	/*
	 * Deckblatt
	 */
	$filename = $tmpDir . "/".uniqid();
	$doc = new dokument_export('Bewerberakt');
	$doc->addDataArray(array('vorname' => $prestudent->vorname, 'nachname' => $prestudent->nachname),'bewerberakt');

	if(!$doc->create('pdf'))
		die($doc->errormsg);
	$doc->temp_filename = $filename;
	$doc->output(false);
	//$doc->close();
	$allDocs[] = $filename;


	/*
	 * Get all Documents
	 */
	$query= '
		SELECT
			titel, dms_id, inhalt
		FROM
			public.tbl_dokumentstudiengang
			JOIN public.tbl_prestudent USING(studiengang_kz)
			JOIN public.tbl_akte USING(person_id,dokument_kurzbz)
		WHERE
			onlinebewerbung
			AND prestudent_id='.$db->db_add_param($pid, FHC_INTEGER).';
	';

	$result = $db->db_query($query);
	while($row = $db->db_fetch_object($result))
	{


		$filename = "";
		if($row->dms_id != null)
		{
			$dms = new dms();
			$dms->load($row->dms_id);

			$filename = DMS_PATH . $dms->filename;
		}
		else if($row->inhalt != null)
		{
			$filename = $tmpDir . "/".uniqid();
			$fileData = base64_decode($row->inhalt);
			file_put_contents($filename, $fileData);
		}


		if($filename == "")
			continue;


		/*
		 * Determine the filetype
		 * and convert, if nessecary
		 */
		$explodedTitle = explode(".", $row->titel);
		$type = $explodedTitle[count($explodedTitle)-1];
		if($type == "jpg" || $type = "jpeg")
		{
			$fullFilename = $tmpDir . "/".uniqid() . ".pdf";
			if(!$pdf->jpegToPdf($filename, $fullFilename))
				cleanUpAndDie($pdf->errormsg, $tmpDir);
		}
		else if($type == "odt" || $type == "doc" || $type == "docx")
		{
			$fullFilename = $tmpDir . "/".uniqid() . ".pdf";
			$docExp->convert($filename, $fullFilename, "pdf");
		}
		else if($type == "pdf")
		{
			$fullFilename = $row->titel;
		}
		else
			cleanUpAndDie("falscher typ TODO", $tmpDir);

		// only filled, if the file is supported
		if($fullFilename != "")
		{
			$allDocs[] = $fullFilename;
		}

	}
}


/*
 * generate the merged PDF
 */
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
?>
