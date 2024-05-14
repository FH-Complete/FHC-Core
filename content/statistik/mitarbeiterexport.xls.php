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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Exportiert die Mitarbeiterdaten in ein Excel File.
 * Der Mitarbeiterfilter und die zu exportierenden Spalten werden per GET uebergeben.
 * Die Adressen der Mitarbeiter werden immer dazugehaengt
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/Excel/excel.php');
require_once('../../include/udf.class.php');

$db = new basis_db();
$user = get_uid();
loadVariables($user);

$data = $_POST['data'];
$uids= explode(';',$data);

// Mitarbeiter holen
$mitarbeiterDAO = new mitarbeiter();
//$mitarbeiterDAO->getPersonal($fix, $stgl, $fbl, $aktiv, $karenziert, $ausgeschieden, $semester_aktuell);
$mitarbeiterDAO->getMitarbeiterArray($uids);

//Sortieren der Eintraege nach Nachname, Vorname
//Umlaute werden ersetzt damit diese nicht unten angereiht werden
//sondern richtig mitsortiert
$vorname=array();
$nachname=array();

$umlaute = array('ö','Ö','ü','Ü','ä','Ä');
$umlauterep = array('o','O','u','U','a','A');
foreach ($mitarbeiterDAO->result as $key => $foo)
{
	$vorname[$key]=str_replace($umlaute, $umlauterep, $foo->vorname);
	$nachname[$key]=str_replace($umlaute, $umlauterep, $foo->nachname);
}

array_multisort($nachname, SORT_ASC, $vorname, SORT_ASC, $mitarbeiterDAO->result);

$spalte = array('anrede','titelpre', 'vorname', 'vornamen', 'nachname', 'titelpost','gebdatum','svnr','ersatzkennzeichen',
	'aktiv','personalnummer', 'kurzbz','fixangestellt','lektor','uid');
$anzSpalten = count($spalte);

// Creating a workbook
$workbook = new Spreadsheet_Excel_Writer();
$workbook->setVersion(8);
// sending HTTP headers
$workbook->send("Mitarbeiter". "_" . date("d_m_Y") . ".xls");

// Creating a worksheet
$worksheet =& $workbook->addWorksheet("Mitarbeiter");
$worksheet->setInputEncoding('utf-8');

$format_bold =& $workbook->addFormat();
$format_bold->setBold();

$format_title =& $workbook->addFormat();
$format_title->setBold();
// let's merge
$format_title->setAlign('merge');

$zeile = 0;

// Zeilenueberschriften ausgeben
$col = 0;
for ($col = 0; $col < $anzSpalten; $col++)
{
	$worksheet->write($zeile, $col, mb_strtoupper(str_replace('_bezeichnung', '', $spalte[$col])), $format_bold);
}

$worksheet->write($zeile, $col, "STRASSE", $format_bold);
$worksheet->write($zeile, ++$col, "PLZ", $format_bold);
$worksheet->write($zeile, ++$col, "ORT", $format_bold);
$worksheet->write($zeile, ++$col, "FIRMENNAME", $format_bold);

// Maximale Spaltenbreite ermitteln damit sie am Schluss gesetzt werden kann
$maxlength = array();
for ($col = 0; $col < $anzSpalten; $col++)
{
	$maxlength[$col] = mb_strlen(str_replace('_bezeichnung','',$spalte[$col]));
}

$maxlength[$col] = mb_strlen('STRASSE');
$maxlength[++$col] = mb_strlen('PLZ');
$maxlength[++$col] = mb_strlen('ORT');
$maxlength[++$col] = mb_strlen('FIRMENNAME');

// UDF titles
$udf = new UDF();
$udfTitlesPerson = $udf->getTitlesPerson();

foreach($udfTitlesPerson as $udfTitle)
{
	$worksheet->write($zeile, ++$col, $udfTitle['description'], $format_bold);
	$maxlength[$col] = mb_strlen($udfTitle['description']);
}

$zeile++;

// Zeilen (Mitarbeiter) ausgeben
foreach ($mitarbeiterDAO->result as $mitarbeiter)
{
	//Spalten ausgeben
	for ($col = 0; $col < $anzSpalten; $col++)
	{
		if (is_bool($mitarbeiter->{$spalte[$col]}))
			$mitarbeiter->{$spalte[$col]} = ($mitarbeiter->{$spalte[$col]} ? 'Ja' : 'Nein');

		if (mb_strlen($mitarbeiter->{$spalte[$col]}) > $maxlength[$col])
			$maxlength[$col] = mb_strlen($mitarbeiter->{$spalte[$col]});
		$worksheet->write($zeile, $col, $mitarbeiter->{$spalte[$col]});
	}

	//Zustelladresse aus der Datenbank holen und dazuhaengen
	$qry = "SELECT * FROM public.tbl_adresse WHERE person_id = '$mitarbeiter->person_id' AND zustelladresse = true LIMIT 1";
	if ($result = $db->db_query($qry))
	{
		if ($row = $db->db_fetch_object($result))
		{
			if (mb_strlen($row->strasse) > $maxlength[$col])
				$maxlength[$col] = mb_strlen($row->strasse);
			$worksheet->write($zeile, $col, $row->strasse);
			$col++;
			if (mb_strlen($row->plz) > $maxlength[$col])
				$maxlength[$col] = mb_strlen($row->plz);
			$worksheet->write($zeile, $col, $row->plz);
			$col++;
			if (mb_strlen($row->ort) > $maxlength[$col])
				$maxlength[$col] = mb_strlen($row->ort);
			$worksheet->write($zeile, $col, $row->ort);

			$col++;
			if ($row->firma_id != '')
			{
				$qry = "SELECT * FROM public.tbl_firma WHERE firma_id = '$row->firma_id'";
				if ($result = $db->db_query($qry))
				{
					if ($row = $db->db_fetch_object($result))
					{
						if (mb_strlen($row->name) > $maxlength[$col])
							$maxlength[$col] = mb_strlen($row->name);
						$worksheet->write($zeile, $col, $row->name);
					}
				}
			}
		}
		else // if the address is not present for this user
		{
			$col += 3;
		}
	}
	else // if the address is not present for this user
	{
		$col += 3;
	}

	$col++;

	// UDF
	if (isset($mitarbeiter->p_udf_values))
	{
		$udfPerson = json_decode($mitarbeiter->p_udf_values);
		if (is_object($udfPerson)) $udfPerson = (array)$udfPerson;

		foreach($udfTitlesPerson as $udfTitle)
		{
			$toWrite = $udf->encodeToString($udfPerson, $udfTitle);

			if (mb_strlen($toWrite) > $maxlength[$col])
			{
				$maxlength[$col] = mb_strlen($toWrite);
			}

			$worksheet->write($zeile, $col, $toWrite);

			$col++;
		}
	}

	$zeile++;
}

//Die Breite der Spalten setzen
for ($col = 0; $col < $anzSpalten; $col++)
{
	$worksheet->setColumn($col, $col, $maxlength[$col] + 2);
}

$worksheet->setColumn($col, $col, $maxlength[$col] + 2);
$col++;
$worksheet->setColumn($col, $col, $maxlength[$col] + 2);
$col++;
$worksheet->setColumn($col, $col, $maxlength[$col] + 2);

$workbook->close();

?>
