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
	$verband = isset($_GET['verband'])?$_GET['verband']:'';
	$gruppe = isset($_GET['gruppe'])?$_GET['gruppe']:'';
	$gruppe_kurzbz = isset($_GET['gruppe_kurzbz'])?$_GET['gruppe_kurzbz']:'';
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

	//Zeilenueberschriften ausgeben
	
	$headline=array('Typ der Projektarbeit','Titel der Projektarbeit','Student',
	                'Note','Punkte','Beginn','Ende','Freigegeben','Gesperrt bis','Gesamtstunden','Themenbereich',
	                'Anmerkung','Projektarbeit ID');
	
	$i=0;
	foreach ($headline as $title)
	{
		$worksheet->write(0,$i,$title, $format_bold);
			$maxlength[$i]=strlen($title);
		$i++;
	}
			
	// Daten holen
	$qry = "SELECT 
				tbl_projekttyp.bezeichnung, titel, trim(COALESCE(titelpre,'') || ' ' || COALESCE(vorname,'') || ' ' || COALESCE(nachname,'') || COALESCE(titelpost,'')), tbl_note.anmerkung, punkte, beginn,
				ende, CASE WHEN freigegeben THEN 'Ja' ELSE 'Nein' END, gesperrtbis, gesamtstunden, themenbereich, tbl_projektarbeit.anmerkung, projektarbeit_id
			FROM 
				lehre.tbl_projektarbeit, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung, 
				public.tbl_benutzer, public.tbl_person, lehre.tbl_projekttyp, lehre.tbl_note
			WHERE
				tbl_projektarbeit.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
				tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_projektarbeit.student_uid=tbl_benutzer.uid AND
				tbl_benutzer.person_id=tbl_person.person_id AND
				tbl_projektarbeit.projekttyp_kurzbz=tbl_projekttyp.projekttyp_kurzbz AND
				tbl_projektarbeit.note=tbl_note.note AND
				tbl_lehreinheit.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' AND
				tbl_lehrveranstaltung.studiengang_kz='".addslashes($studiengang_kz)."' AND
				tbl_projektarbeit.projekttyp_kurzbz IN ('Bachelor','Diplom','Projekt')";
	
	if($semester!='')
		$qry.= " AND tbl_lehrveranstaltung.semester='".addslashes($semester)."'";
	
	//echo $qry;
	$zeile=1;
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_array($result))
		{
			$zeile++;
			$i=0;
			
			//Projektarbeit
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
						
			//Betreuer
									
			$qry_betreuer = "SELECT betreuerart_kurzbz, COALESCE(titelpre,'') || ' ' || COALESCE(vorname,'') || ' ' || COALESCE(nachname,'') || ' ' || COALESCE(titelpost,''), tbl_note.anmerkung, faktor, name, punkte, stunden, stundensatz FROM (lehre.tbl_projektbetreuer JOIN tbl_person USING(person_id)) LEFT JOIN lehre.tbl_note USING(note) WHERE projektarbeit_id='".$row['projektarbeit_id']."'";
			
			if($result_betreuer = pg_query($conn, $qry_betreuer))
			{
				if(pg_num_rows($result_betreuer)>0)
				{
					$headline=array('Betreuerart','Betreuer','Note','Faktor','Name','Punkte','Stunden','Stundensatz');
		
					$i=1;
					
					foreach ($headline as $title)
					{
						$worksheet->write($zeile,$i,$title, $format_bold);
						if(strlen($title)>$maxlength[$i])
							$maxlength[$i]=strlen($title);
						$i++;
					}
					
					$zeile++;
					while($row_betreuer = pg_fetch_array($result_betreuer))
					{
						$i=1;
				
						foreach ($row_betreuer as $idx=>$content)
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
			}
			
		}
	}
	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet->setColumn($i, $i, $breite+2);
    
	$workbook->close();

?>
