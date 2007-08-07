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
 * Exportiert die Studentendaten in ein Excel File.
 * Die zu exportierenden Spalten werden per GET uebergeben.
 * Die Adressen werden immer dazugehaengt
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/datum.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/Excel/excel.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$user = get_uid();
$datum_obj = new datum();
loadVariables($conn, $user);
	
	//Parameter holen
	$studiengang_kz = isset($_GET['studiengang_kz'])?$_GET['studiengang_kz']:'';
	$semester = isset($_GET['semester'])?$_GET['semester']:'';
	$studiensemester_kurzbz = isset($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:'';
		
	$maxlength= array();
	$zeile=1;

	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$workbook->send("Projektarbeit". "_" . date("d_m_Y") . ".xls");

	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("Studenten");

	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();

	$format_title =& $workbook->addFormat();
	$format_title->setBold();
	// let's merge
	$format_title->setAlign('merge');

	$stsem = new studiensemester($conn);
	$stsem->load($studiensemester_kurzbz);
	
	//Zeilenueberschriften ausgeben	
	$headline=array('Titelpre','Vorname','Nachname','Titelpost','Vorsitz','Pruefer1','Pruefer2','Pruefer3',
					'Abschlussbeurteilung','Typ','Datum','Sponsion','Anmerkung');
	
	$i=0;
	foreach ($headline as $title)
	{
		$worksheet->write(0,$i,$title, $format_bold);
			$maxlength[$i]=strlen($title);
		$i++;
	}
			
	// Daten holen
	$qry = "SELECT 
				titelpre, vorname, nachname, titelpost, 
				(SELECT COALESCE(titelpre,'') || ' ' || COALESCE(vorname,'') || ' ' || COALESCE(nachname,'') || ' ' || COALESCE(titelpost,'') FROM public.tbl_person JOIN public.tbl_benutzer USING(person_id) WHERE uid=vorsitz),
				(SELECT COALESCE(titelpre,'') || ' ' || COALESCE(vorname,'') || ' ' || COALESCE(nachname,'') || ' ' || COALESCE(titelpost,'') FROM public.tbl_person WHERE person_id=pruefer1),
				(SELECT COALESCE(titelpre,'') || ' ' || COALESCE(vorname,'') || ' ' || COALESCE(nachname,'') || ' ' || COALESCE(titelpost,'') FROM public.tbl_person WHERE person_id=pruefer2),
				(SELECT COALESCE(titelpre,'') || ' ' || COALESCE(vorname,'') || ' ' || COALESCE(nachname,'') || ' ' || COALESCE(titelpost,'') FROM public.tbl_person WHERE person_id=pruefer3),  
				tbl_abschlussbeurteilung.bezeichnung, tbl_pruefungstyp.beschreibung, datum, sponsion, anmerkung
			FROM 
				lehre.tbl_abschlusspruefung, public.tbl_studentlehrverband, public.tbl_benutzer, public.tbl_person, 
				lehre.tbl_abschlussbeurteilung, lehre.tbl_pruefungstyp
			WHERE
				tbl_abschlusspruefung.student_uid=public.tbl_studentlehrverband.student_uid AND
				tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' AND
				tbl_studentlehrverband.studiengang_kz='".addslashes($studiengang_kz)."' AND
				tbl_benutzer.uid = tbl_abschlusspruefung.student_uid AND
				tbl_person.person_id = tbl_benutzer.person_id AND
				tbl_abschlussbeurteilung.abschlussbeurteilung_kurzbz = tbl_abschlusspruefung.abschlussbeurteilung_kurzbz AND
				tbl_abschlusspruefung.pruefungstyp_kurzbz = tbl_pruefungstyp.pruefungstyp_kurzbz AND
				datum>='".$stsem->start."' AND datum<='".$stsem->ende."'";

	if($semester!='')
		$qry.= " AND tbl_studentlehrverband.semester='".addslashes($semester)."'";
	
	$zeile=1;
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_array($result))
		{
			$i=0;
			
			foreach ($row as $idx=>$content)
			{
				if(is_numeric($idx))
				{
					$worksheet->write($zeile, $i, $content);
					if(strlen($content)>$maxlength[$i])
						$maxlength[$i]=strlen($content);
					$i++;
				}
			}
			$zeile++;
		}
	}
	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet->setColumn($i, $i, $breite+2);
    
	$workbook->close();

?>
