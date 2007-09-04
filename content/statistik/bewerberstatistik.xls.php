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
require_once('../../include/Excel/excel.php');
require_once('../../include/studiengang.class.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$user = get_uid();

loadVariables($conn, $user);
	
	//Parameter holen
	$studiensemester_kurzbz = isset($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:'';
		
	$maxlength= array();
	$zeile=1;

	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$workbook->send("Bewerberstatistik". "_" . date("d_m_Y") . ".xls");

	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("Bewerberstatistik");

	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();
	
	$format_title =& $workbook->addFormat();
	$format_title->setBold();
	// let's merge
	$format_title->setAlign('merge');

	//Zeilenueberschriften ausgeben
		
	for($i=0;$i<10;$i++)
		$maxlength[$i]=0;
	$i=10;
	$stg_spalte=array();
	$studiengang = new studiengang($conn);
	$studiengang->getAll('typ, kurzbzlang', false);
	foreach ($studiengang->result as $row)
	{
		$worksheet->write(0,$i,'('.strtoupper($row->typ).') '.$row->kurzbzlang, $format_bold);
		$maxlength[$i]=strlen('('.strtoupper($row->typ).') '.$row->kurzbzlang);
		$stg_spalte[$row->studiengang_kz]=$i;
		$i++;
	}
			
	// Daten holen
	$qry = "SELECT *, tbl_person.person_id 
			FROM 
				public.tbl_prestudentrolle, public.tbl_prestudent, public.tbl_person 
			WHERE 
				tbl_prestudentrolle.prestudent_id=tbl_prestudent.prestudent_id AND
				tbl_prestudent.person_id=tbl_person.person_id AND
				studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' AND
				rolle_kurzbz in('Interessent','Bewerber','Student','Abbrecher','Unterbrecher')
			ORDER BY nachname, vorname, tbl_prestudentrolle.datum, tbl_prestudentrolle.insertamum";
				
	//echo $qry;

	if($result = pg_query($conn, $qry))
	{
		$lastperson='';
		$zeile=0;
				
		while($row = pg_fetch_object($result))
		{
			if($lastperson!=$row->person_id)
			{
				if($lastperson!='')
				{
					$anzahl_bewerbung=-1;
					foreach ($rollen as $stg=>$status)
					{
						//ROLLEN
						$worksheet->write($zeile,$stg_spalte[$stg],$status);
						if($maxlength[$stg_spalte[$stg]]<strlen($status))
							$maxlength[$stg_spalte[$stg]]=strlen($status);
						$anzahl_bewerbung++;
					}
					
					//ANZAHL BEWERBUNGEN
					$worksheet->write($zeile,4,$anzahl_bewerbung);
					if($maxlength[4]<strlen($anzahl_bewerbung))
						$maxlength[4]=strlen($anzahl_bewerbung);
					
					if($anzahl_bewerbung>0)
						$wert='M';
					else 
						$wert='E';
					
					$worksheet->write($zeile,5,$wert);
					if($maxlength[5]<strlen($wert))
						$maxlength[5]=strlen($wert);
				}
				
				$i=0;
				$zeile++;
				$rollen = array();
				$lastperson = $row->person_id;
				
				//DATUM
				$worksheet->write($zeile,$i,date('d.m.y'));
				if($maxlength[$i]<strlen(date('d.m.y')))
					$maxlength[$i]=strlen(date('d.m.y'));
					
				//VORNAME
				$worksheet->write($zeile,++$i,$row->vorname);
				if($maxlength[$i]<strlen($row->vorname))
					$maxlength[$i]=strlen($row->vorname);
					
				//NACHNAME
				$worksheet->write($zeile,++$i,$row->nachname);
				if($maxlength[$i]<strlen($row->nachname))
					$maxlength[$i]=strlen($row->nachname);
				
				//GESCHLECHT
				$worksheet->write($zeile,++$i,$row->geschlecht);
				if($maxlength[$i]<strlen($row->geschlecht))
					$maxlength[$i]=strlen($row->geschlecht);
				
				//Spalten fuer Anzahl der Bewerbungen freilassen
				$i++;
				$i++;
				
				//ZGV CODE
				$worksheet->write($zeile,++$i,$row->zgv_code);
				if($maxlength[$i]<strlen($row->zgv_code))
					$maxlength[$i]=strlen($row->zgv_code);
			}
			
			switch($row->rolle_kurzbz)
			{
				case 'Interessent': $kuerzel = 'i'; break;
				case 'Bewerber': $kuerzel='b'; break;
				case 'Student': $kuerzel='s'; break;
				case 'Abbrecher': $kuerzel='a'; break;
				case 'Unterbrecher': $kuerzel='u'; break;
				default: $kuerzel=''; break;
			}
			if(isset($rollen[$row->studiengang_kz]))
			{
				if(strpos($rollen[$row->studiengang_kz],$kuerzel)===false)
				{
					$rollen[$row->studiengang_kz] .= $kuerzel.$row->ausbildungssemester;
				}
			}
			else 
				$rollen[$row->studiengang_kz] = $kuerzel.$row->ausbildungssemester;
		}
		$anzahl_bewerbung=-1;
		foreach ($rollen as $stg=>$status)
		{
			//ROLLEN
			$worksheet->write($zeile,$stg_spalte[$stg],$status);
			if($maxlength[$stg_spalte[$stg]]<strlen($status))
				$maxlength[$stg_spalte[$stg]]=strlen($status);
			$anzahl_bewerbung++;
		}
		
		//ANZAHL BEWERBUNGEN
		$worksheet->write($zeile,4,$anzahl_bewerbung);
		if($maxlength[4]<strlen($anzahl_bewerbung))
			$maxlength[4]=strlen($anzahl_bewerbung);
		
		if($anzahl_bewerbung>0)
			$wert='M';
		else 
			$wert='E';
		
		$worksheet->write($zeile,5,$wert);
		if($maxlength[5]<strlen($wert))
			$maxlength[5]=strlen($wert);
	}
	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet->setColumn($i, $i, $breite+2);
    
	$workbook->close();

?>
