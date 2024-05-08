<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Exportiert die Termine von Lehreinheiten/Lehrveranstaltung/Studierenden/Mitarbeitern
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/rdf.class.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/variable.class.php');
require_once('../../include/lehrstunde.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/stunde.class.php');
require_once('../../include/Excel/excel.php');
require_once('../../include/benutzer.class.php');

$user = get_uid();
$variable = new variable();
$variable->loadVariables($user);

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('assistenz') && !$rechte->isBerechtigt('lvplan') && !$rechte->isBerechtigt('admin'))
	die($rechte->errormsg);

$stunde = new stunde();
$stunde->loadAll();

$stunden_arr=array();
foreach($stunde->stunden as $row)
{
	$stunden_arr[$row->stunde]['beginn']=$row->beginn->format('H:i');
	$stunden_arr[$row->stunde]['ende']=$row->ende->format('H:i');
}
$datum_obj = new datum();

$lehrveranstaltung_id = filter_input(INPUT_GET, 'lehrveranstaltung_id');
$lehreinheit_id = filter_input(INPUT_GET, 'lehreinheit_id');
$mitarbeiter_uid = filter_input(INPUT_GET,'mitarbeiter_uid');
$student_uid = filter_input(INPUT_GET,'student_uid');
$db_stpl_table = filter_input(INPUT_GET,'db_stpl_table');
if(!in_array($db_stpl_table,array('stundenplan','stundenplandev')))
	$db_stpl_table='stundenplan';

$db = new basis_db();

$lehrstunde = new lehrstunde();
//$variable->variable->db_stpl_table
$lehrstunde->getStundenplanData($db_stpl_table, $lehrveranstaltung_id, $variable->variable->semester_aktuell, $lehreinheit_id, $mitarbeiter_uid, $student_uid);

	function writecol($zeile, $i, $content)
	{
		global $worksheet, $maxlength;
		$worksheet->write($zeile, $i, $content);
		if(mb_strlen($content)>$maxlength[$i])
			$maxlength[$i]=mb_strlen($content);
	}

	$maxlength= array();
	$zeile=1;

	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$workbook->send("Termine". "_" . date("d_m_Y") . ".xls");
	$workbook->setVersion(8);
	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("Termine");
	$worksheet->setInputEncoding('utf-8');

	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();

	$format_title =& $workbook->addFormat();
	$format_title->setBold();
	// let's merge
	$format_title->setAlign('merge');

	//Zeilenueberschriften ausgeben
	$headline=array('Datum','Von','Bis','Ort','Lektoren','Gruppen','Lehrfach','Anmerkung','StundeVon','StundeBis');

	$i=0;
	foreach ($headline as $title)
	{
		$worksheet->write(0,$i,$title, $format_bold);
			$maxlength[$i]=mb_strlen($title);
		$i++;
	}

	$lektoren_arr=array();
	foreach($lehrstunde->result as $row)
	{
		$i=0;

		writecol($zeile, $i++, $datum_obj->formatDatum($row->datum,'d.m.Y'));
		writecol($zeile, $i++, $stunden_arr[$row->stundevon]['beginn']);
		writecol($zeile, $i++, $stunden_arr[$row->stundebis]['ende']);
		writecol($zeile, $i++, implode(',',$row->orte));

		$lektoren='';
		foreach($row->lektoren as $rowlkt)
		{
			if(!isset($lektoren_arr[$rowlkt]))
			{
				$lkt_obj = new benutzer();
				$lkt_obj->load($rowlkt);
				$lektoren_arr[$rowlkt]=$lkt_obj->nachname.' '.$lkt_obj->vorname;
			}
			$lektoren .=",".$lektoren_arr[$rowlkt];
		}
		$lektoren = mb_substr($lektoren,1);

		writecol($zeile, $i++, $lektoren);
		writecol($zeile, $i++, implode(',',$row->gruppen));
		writecol($zeile, $i++, $row->lehrfach_bezeichnung);
		writecol($zeile, $i++, implode(',',$row->titel));
		writecol($zeile, $i++, $row->stundevon);
		writecol($zeile, $i++, $row->stundebis);

		$zeile++;
	}

	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet->setColumn($i, $i, $breite+2);

	$workbook->close();
?>
