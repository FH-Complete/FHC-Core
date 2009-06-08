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
require_once('../../include/dokument.class.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
	die('Es konnte keine Verbindung zum Server aufgebaut werden!');

loadVariables($conn, get_uid());
//Parameter holen
$studiengang_kz = isset($_GET['studiengang_kz'])?$_GET['studiengang_kz']:'';
$studiensemester_kurzbz  = isseT($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:$semester_aktuell;

if($studiengang_kz!='')
{
	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$workbook->send("Dokumente_".$studiensemester_kurzbz.".xls");

	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("Dokumente");

	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();
		
	$format_rotate =& $workbook->addFormat();
	$format_rotate->setTextRotation(270);
	$format_rotate->setAlign('center');
	
	$spalte=0;
	$zeile=0;
		
	$worksheet->write($zeile,$spalte,'NACHNAME',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet->write($zeile,++$spalte,'VORNAME',$format_bold);
	$maxlength[$spalte]=7;
	$worksheet->write($zeile,++$spalte,'STATUS',$format_bold);
	$maxlength[$spalte]=6;
	
	$dokumente = new dokument($conn);
	$dokumente->getDokumente($studiengang_kz);
	$dokumente_arr = array();
	foreach ($dokumente->result as $row)
	{
		$worksheet->write($zeile,++$spalte,$row->bezeichnung,$format_rotate);
		$maxlength[$spalte]=3;
		$dokumente_arr[$row->dokument_kurzbz]=$spalte;
	}
		
	// Daten holen
	$qry = "SELECT nachname, vorname, prestudent_id, public.get_rolle_prestudent(prestudent_id, NULL) as status FROM 
				public.tbl_person JOIN public.tbl_prestudent USING(person_id) 
			WHERE 
				prestudent_id IN(
				SELECT 
					distinct prestudent_id 
				FROM 
					public.tbl_prestudent JOIN public.tbl_prestudentstatus USING(prestudent_id)
				WHERE 
					(SELECT count(*) as anzahl FROM public.tbl_dokumentstudiengang 
					 WHERE 
					 	dokument_kurzbz NOT IN(	SELECT dokument_kurzbz FROM tbl_dokumentprestudent WHERE 
					 							prestudent_id=tbl_prestudent.prestudent_id) AND studiengang_kz='$studiengang_kz')<>0 
					 	AND tbl_prestudentstatus.studiensemester_kurzbz='$studiensemester_kurzbz' AND studiengang_kz='$studiengang_kz'
			)
			ORDER BY nachname, vorname
		   ";
		
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			
			$zeile++;
			$spalte=0;
			
			$worksheet->write($zeile,$spalte,$row->nachname);
			if(strlen($row->nachname)>$maxlength[$spalte])
				$maxlength[$spalte]=strlen($row->nachname);
			
			$worksheet->write($zeile,++$spalte, $row->vorname);
			if(strlen($row->vorname)>$maxlength[$spalte])
				$maxlength[$spalte]=strlen($row->vorname);
							
			$worksheet->write($zeile,++$spalte, $row->status);
			if(strlen($row->status)>$maxlength[$spalte])
				$maxlength[$spalte]=strlen($row->status);

			$dokumente = new dokument($conn);
			$dokumente->getPrestudentDokumente($row->prestudent_id);
			
			foreach ($dokumente->result as $docs)
			{
				if(isset($dokumente_arr[$docs->dokument_kurzbz]))
					$worksheet->write($zeile,$dokumente_arr[$docs->dokument_kurzbz], 'X', $format_bold);
			}
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
	<title>Dokumente</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	</head>
	<body class="Background_main">
	<h2>Dokumente</h2>
	Studiengang_kz und Studiensemester_kurzbz muss uebergeben werden
	</body>
	</html>
	';
}
?>