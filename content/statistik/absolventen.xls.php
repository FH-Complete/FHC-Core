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
 * Exportiert eine Liste der Absolventen in ein Excel File.
 * Das betreffende Studiensemester wird uebergeben.
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/Excel/excel.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/studiensemester.class.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
	die('Es konnte keine Verbindung zum Server aufgebaut werden!');

//Parameter holen
$studiensemester_kurzbz = isset($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:'';

if($studiensemester_kurzbz!='')
{
	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$workbook->send("Absolventenstatistik". "_".$studiensemester_kurzbz.".xls");

	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("Absolventenstatistik");

	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();
		
	$stg_arr=array();
	$studiengang = new studiengang($conn);
	$studiengang->getAll('typ, kurzbzlang', false);
	foreach ($studiengang->result as $row)
		$stg_arr[$row->studiengang_kz] = $row->kuerzel;

	$spalte=0;
	$zeile=0;
	
	$worksheet->write($zeile,$spalte,'Absolventenstatistik '.$studiensemester_kurzbz.' erstellt am '.date("d.m.Y"), $format_bold);
	
	$spalte=0;
	$zeile++;
	
	$worksheet->write($zeile,$spalte,'UID',$format_bold);
	$maxlength[$spalte]=3;
	$worksheet->write($zeile,++$spalte,'NACHNAME',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet->write($zeile,++$spalte,'VORNAME',$format_bold);
	$maxlength[$spalte]=7;
	$worksheet->write($zeile,++$spalte,'STG',$format_bold);
	$maxlength[$spalte]=3;
	$worksheet->write($zeile,++$spalte,'GESCHLECHT',$format_bold);
	$maxlength[$spalte]=10;
		
	// Daten holen
	$qry = "SELECT 
				uid, vorname, nachname, studiengang_kz, geschlecht 
			FROM 
				campus.vw_student 
			WHERE 
				public.get_rolle_prestudent (prestudent_id, '$studiensemester_kurzbz')='Absolvent'
			ORDER BY studiengang_kz, nachname, vorname";

	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			$zeile++;
			$spalte=0;
			
			$worksheet->write($zeile,$spalte,$row->uid);
			if(strlen($row->uid)>$maxlength[$spalte])
				$maxlength[$spalte]=strlen($row->uid);
			
			$worksheet->write($zeile,++$spalte, $row->nachname);
			if(strlen($row->nachname)>$maxlength[$spalte])
				$maxlength[$spalte]=strlen($row->nachname);
			
			$worksheet->write($zeile,++$spalte, $row->vorname);
			if(strlen($row->vorname)>$maxlength[$spalte])
				$maxlength[$spalte]=strlen($row->vorname);
			
			$worksheet->write($zeile,++$spalte, $stg_arr[$row->studiengang_kz]);
			if(strlen($stg_arr[$row->studiengang_kz])>$maxlength[$spalte])
				$maxlength[$spalte]=strlen($stg_arr[$row->studiengang_kz]);
			
			$worksheet->write($zeile,++$spalte, $row->geschlecht);
			if(strlen($row->geschlecht)>$maxlength[$spalte])
				$maxlength[$spalte]=strlen($row->geschlecht);
		}
	}
	
	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet->setColumn($i, $i, $breite+2);
    
	$workbook->close();
}
else 
{
	echo '
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<title>Absolventen</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	</head>
	<body class="Background_main">
	<h2>Absolventenstatistik</h2>
	';
	
	echo '<form method="GET" action="'.$_SERVER['PHP_SELF'].'">';
	echo 'Studiensemester: <SELECT name="studiensemester_kurzbz">';
	
	$stsem = new studiensemester($conn);
	$stsem_akt = $stsem->getaktorNext();
	$stsem->getAll();
	
	foreach ($stsem->studiensemester as $row)
	{
		if($row->studiensemester_kurzbz==$stsem_akt)
			$selected='selected';
		else 
			$selected='';
		
		echo "<OPTION value='$row->studiensemester_kurzbz' $selected>$row->studiensemester_kurzbz</OPTION>";
	}
	echo "</SELECT>";
	echo " <input type='submit' value='Erstellen'>";
	echo "</form></body></html>";
}
?>