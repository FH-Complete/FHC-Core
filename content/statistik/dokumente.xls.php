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
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/Excel/excel.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/dokument.class.php');

loadVariables(get_uid());
//Parameter holen
$studiengang_kz = isset($_GET['studiengang_kz'])?$_GET['studiengang_kz']:'';
$studiensemester_kurzbz  = isseT($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:$semester_aktuell;
$db = new basis_db();

if($studiengang_kz!='')
{
	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$workbook->send("Dokumente_".$studiensemester_kurzbz.".xls");
	$workbook->setVersion(8);
	
	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("Dokumente");
	$worksheet->setInputEncoding('utf-8');
	
	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();
		
	$format_bold_merge =& $workbook->addFormat();
	$format_bold_merge->setBold();
	$format_bold_merge->setAlign('merge');
	
	$format_center =& $workbook->addFormat();
	$format_center->setAlign('merge');
		
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
	$worksheet->write($zeile,++$spalte,'SEMESTER',$format_bold);
	$maxlength[$spalte]=8;
	
	$dokumente = new dokument();
	$dokumente->getDokumente($studiengang_kz);
	$dokumente_arr = array();
	foreach ($dokumente->result as $row)
	{
		$worksheet->write($zeile,++$spalte,$row->bezeichnung,$format_rotate);
		$maxlength[$spalte]=3;
		$dokumente_arr[$row->dokument_kurzbz]=$spalte;
	}
		
	// Daten holen
	$qry = "SELECT DISTINCT nachname, vorname, prestudent_id, public.get_rolle_prestudent(prestudent_id, NULL) AS status, tbl_studentlehrverband.semester FROM 
				public.tbl_person JOIN public.tbl_prestudent USING(person_id)
				LEFT JOIN public.tbl_student USING (prestudent_id)
				LEFT JOIN public.tbl_studentlehrverband USING (student_uid)
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
					 							prestudent_id=tbl_prestudent.prestudent_id) AND studiengang_kz='".addslashes($studiengang_kz)."')<>0 
					 	AND tbl_prestudentstatus.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' AND studiengang_kz='".addslashes($studiengang_kz)."'
			)
			AND (tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' OR tbl_studentlehrverband.studiensemester_kurzbz is null)
			
			ORDER BY nachname, vorname, semester
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
							
			$worksheet->write($zeile,++$spalte, $row->status);
			if(mb_strlen($row->status)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->status);

			$worksheet->write($zeile,++$spalte, $row->semester, $format_center);
			if(mb_strlen($row->semester)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->semester);

			$dokumente = new dokument();
			$dokumente->getPrestudentDokumente($row->prestudent_id);
			
			foreach ($dokumente->result as $docs)
			{
				if(isset($dokumente_arr[$docs->dokument_kurzbz]))
					$worksheet->write($zeile,$dokumente_arr[$docs->dokument_kurzbz], 'X', $format_bold_merge);
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
