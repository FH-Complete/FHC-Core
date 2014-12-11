<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Exportiert eine Liste der Personen die einen Vertrag zugeordnet haben
 * inklusive Start und Endezeiten fuer die Anmeldung der SV
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/Excel/excel.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/datum.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('vertrag/mitarbeiter'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$studiensemester_kurzbz=(isset($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:'');

$db = new basis_db();

$datum_obj = new datum();

if($studiensemester_kurzbz=='')
{
	$stsem = new studiensemester();
	$studiensemester_kurzbz = $stsem->getAktOrNext();
}

if($studiensemester_kurzbz!='')
{
	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$workbook->send("Vertraege_".$studiensemester_kurzbz.".xls");
	$workbook->setVersion(8);
	
	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet($studiensemester_kurzbz);
	$worksheet->setInputEncoding('utf-8');
	
	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();
		
	$spalte=0;
	$zeile=0;
		
	$worksheet->write($zeile,$spalte,'Nachname',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet->write($zeile,++$spalte,'Vorname',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet->write($zeile,++$spalte,'Anmeldedatum',$format_bold);
	$maxlength[$spalte]=15;
	$worksheet->write($zeile,++$spalte,'Abmeldedatum',$format_bold);
	$maxlength[$spalte]=15;
	$worksheet->write($zeile,++$spalte,'Gesamthonorar',$format_bold);
	$maxlength[$spalte]=15;
	
	$stsem = new studiensemester($studiensemester_kurzbz);

	$start = $stsem->start;
	$ende = $stsem->ende;
	// Daten holen
	$qry = "SELECT
				vorname, nachname, tbl_bisverwendung.beginn, tbl_bisverwendung.ende, 
				sum(betrag) as gesamthonorar
			FROM
				lehre.tbl_vertrag
				JOIN campus.vw_mitarbeiter USING(person_id)
				JOIN bis.tbl_bisverwendung ON(uid=mitarbeiter_uid)
			WHERE
				NOT EXISTS(SELECT * FROM lehre.tbl_vertrag_vertragsstatus WHERE vertrag_id=tbl_vertrag.vertrag_id AND vertragsstatus_kurzbz='storno')
				AND tbl_vertrag.vertragsdatum>=".$db->db_add_param($start)." AND tbl_vertrag.vertragsdatum<=".$db->db_add_param($ende)."
				AND (tbl_bisverwendung.beginn is null 
					OR (tbl_bisverwendung.beginn>=".$db->db_add_param($start)." AND tbl_bisverwendung.beginn<=".$db->db_add_param($ende)."))
			GROUP BY vorname, nachname, tbl_bisverwendung.beginn, tbl_bisverwendung.ende, person_id
		   ";
		
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			
			$zeile++;
			$spalte=0;
			
			$worksheet->write($zeile,$spalte,$row->nachname);
			if(mb_strlen($row->nachname)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->nachname);
			
			$worksheet->write($zeile,++$spalte, $row->vorname);
			if(mb_strlen($row->vorname)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->vorname);
							
			$worksheet->write($zeile,++$spalte, $datum_obj->formatDatum($row->beginn,'d.m.Y'));
			if(mb_strlen($row->beginn)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->beginn);
			
			$worksheet->write($zeile,++$spalte, $datum_obj->formatDatum($row->ende,'d.m.Y'));
			if(mb_strlen($row->ende)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->ende);

			$worksheet->write($zeile,++$spalte, $row->gesamthonorar);
			if(mb_strlen($row->gesamthonorar)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->gesamthonorar);
		}
	}
	
	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet->setColumn($i, $i, $breite+2);
    
	$workbook->close();
}
else 
{
	echo '<!DOCTYPE HTML>
	<html>
	<head>
	<title>Vertraege</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	</head>
	<body>
	<h2>Vertraege</h2>
	Studiensemester muss uebergeben werden
	</body>
	</html>
	';
}
?>
