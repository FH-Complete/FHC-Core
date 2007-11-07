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
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/Excel/excel.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$user = get_uid();
$datum_obj = new datum();
loadVariables($conn, $user);
	
	//Parameter holen
	$data = $_POST['data'];
	$studiensemester_kurzbz = $_GET['studiensemester_kurzbz'];
	//$typ = $_GET['typ'];
	$maxlength= array();
	$zeile=1;
	$zgv_arr=array();
	$zgvmas_arr=array();
	
	//ZGV laden
	$qry = "SELECT * FROM bis.tbl_zgv ORDER BY zgv_kurzbz";
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			$zgv_arr[$row->zgv_code]=$row->zgv_kurzbz;
		}
	}
	
	//ZGV Master laden
	$qry = "SELECT * FROM bis.tbl_zgvmaster ORDER BY zgvmas_kurzbz";
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			$zgvmas_arr[$row->zgvmas_code]=$row->zgvmas_kurzbz;
		}
	}
	
	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$workbook->send("Studenten". "_" . date("d_m_Y") . ".xls");

	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("Studenten");

	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();

	$format_title =& $workbook->addFormat();
	$format_title->setBold();
	// let's merge
	$format_title->setAlign('merge');

	//Zeilenueberschriften ausgeben
	$i=0;
	$zeile=1;
	
	$worksheet->write($zeile,$i,"UID", $format_bold);
	$maxlength[$i]=3;
	$worksheet->write($zeile,++$i,"ANREDE", $format_bold);
	$maxlength[$i]=6;
	$worksheet->write($zeile,++$i,"TITELPRE", $format_bold);
	$maxlength[$i]=8;
	$worksheet->write($zeile,++$i,"NACHNAME", $format_bold);
	$maxlength[$i]=8;
	$worksheet->write($zeile,++$i,"VORNAME", $format_bold);
	$maxlength[$i]=7;
	$worksheet->write($zeile,++$i,"VORNAMEN", $format_bold);
	$maxlength[$i]=8;
	$worksheet->write($zeile,++$i,"TITELPOST", $format_bold);
	$maxlength[$i]=9;
	$worksheet->write($zeile,++$i,"SVNR", $format_bold);
	$maxlength[$i]=4;
	$worksheet->write($zeile,++$i,"ERSATZKENNZEICHEN", $format_bold);
	$maxlength[$i]=17;
	$worksheet->write($zeile,++$i,"GEBURTSDATUM", $format_bold);
	$maxlength[$i]=12;
	$worksheet->write($zeile,++$i,"SEMESTER", $format_bold);
	$maxlength[$i]=8;
	$worksheet->write($zeile,++$i,"VERBAND", $format_bold);
	$maxlength[$i]=7;
	$worksheet->write($zeile,++$i,"GRUPPE", $format_bold);
	$maxlength[$i]=6;
	$worksheet->write($zeile,++$i,"PERSONENKENNZEICHEN", $format_bold);
	$maxlength[$i]=19;
	$worksheet->write($zeile,++$i,"ZGV", $format_bold);
	$maxlength[$i]=10;
	$worksheet->write($zeile,++$i,"ZGV Ort", $format_bold);
	$maxlength[$i]=14;
	$worksheet->write($zeile,++$i,"ZGV Datum", $format_bold);
	$maxlength[$i]=6;
	$worksheet->write($zeile,++$i,"ZGV Master", $format_bold);
	$maxlength[$i]=10;
	$worksheet->write($zeile,++$i,"ZGV Master Ort", $format_bold);
	$maxlength[$i]=14;
	$worksheet->write($zeile,++$i,"ZGV Master Datum", $format_bold);
	$maxlength[$i]=16;
	$worksheet->write($zeile,++$i,"STAATSBÜRGERSCHAFT", $format_bold);
	$maxlength[$i]=16;
	$worksheet->write($zeile,++$i,"STATUS", $format_bold);
	$maxlength[$i]=6;
	$worksheet->write($zeile,++$i,"EMail Intern", $format_bold);
	$maxlength[$i]=12;
	$worksheet->write($zeile,++$i,"EMail Privat", $format_bold);
	$maxlength[$i]=12;
	$worksheet->write($zeile,++$i,"STRASSE", $format_bold);
	$maxlength[$i]=7;
	$worksheet->write($zeile-1,$i,"Zustelladresse", $format_bold);
	$worksheet->write($zeile,++$i,"PLZ", $format_bold);
	$maxlength[$i]=3;
	$worksheet->write($zeile,++$i,"ORT", $format_bold);
	$maxlength[$i]=3;
	$worksheet->write($zeile,++$i,"STRASSE", $format_bold);
	$maxlength[$i]=7;
	$worksheet->write($zeile-1,$i,"Nebenwohnsitz", $format_bold);
	$worksheet->write($zeile,++$i,"PLZ", $format_bold);
	$maxlength[$i]=3;
	$worksheet->write($zeile,++$i,"ORT", $format_bold);
	$maxlength[$i]=3;
	$worksheet->write($zeile,++$i,"TELEFON", $format_bold);
	$maxlength[$i]=3;
	$worksheet->write($zeile,++$i,"GRUPPEN", $format_bold);
	$maxlength[$i]=3;
	
	$zeile++;
	
	$ids = explode(';',$data);
	$prestudent_ids = '';
	
	foreach ($ids as $id) 
	{
		if($id!='')
		{
			if($prestudent_ids!='')
				$prestudent_ids .= ',';
			$prestudent_ids .= "'$id'";
		}
	}
	// Student holen
	$qry = "SELECT * FROM public.tbl_prestudent JOIN public.tbl_person USING(person_id) LEFT JOIN public.tbl_student USING(prestudent_id) LEFT JOIN public.tbl_studentlehrverband USING(student_uid) WHERE (studiensemester_kurzbz='$studiensemester_kurzbz' OR studiensemester_kurzbz is null) AND prestudent_id in($prestudent_ids) ORDER BY nachname, vorname";

	if($result = pg_query($conn, $qry))
		while($row = pg_fetch_object($result))
		{
			draw_content($row);
			$zeile++;
		}

	function draw_content($row)
	{
		global $maxlength, $datum_obj;
		global $zeile, $worksheet, $conn;
		global $zgv_arr, $zgvmas_arr;
		global $studiensemester_kurzbz;
		
		$prestudent = new prestudent($conn, null, null);
		$prestudent->getLastStatus($row->prestudent_id);
		$status = $prestudent->rolle_kurzbz;
			
		$i=0;
		if(isset($row->student_uid))
		{
			if(strlen($row->student_uid)>$maxlength[$i])
				$maxlength[$i] = strlen($row->student_uid);
			$worksheet->write($zeile,$i, $row->student_uid);
		}
		$i++;
		
		if(strlen($row->anrede)>$maxlength[$i])
			$maxlength[$i] = strlen($row->anrede);
		$worksheet->write($zeile,$i, $row->anrede);
		$i++;
		
		if(strlen($row->titelpre)>$maxlength[$i])
			$maxlength[$i] = strlen($row->titelpre);
		$worksheet->write($zeile,$i, $row->titelpre);
		$i++;
		
		if(strlen($row->nachname)>$maxlength[$i])
			$maxlength[$i] = strlen($row->nachname);
		$worksheet->write($zeile,$i, $row->nachname);
		$i++;
		
		if(strlen($row->vorname)>$maxlength[$i])
			$maxlength[$i] = strlen($row->vorname);
		$worksheet->write($zeile,$i, $row->vorname);
		$i++;
		
		if(strlen($row->vornamen)>$maxlength[$i])
			$maxlength[$i] = strlen($row->vornamen);
		$worksheet->write($zeile,$i, $row->vornamen);
		$i++;
						
		if(strlen($row->titelpost)>$maxlength[$i])
			$maxlength[$i] = strlen($row->titelpost);
		$worksheet->write($zeile,$i, $row->titelpost);
		$i++;
		
		if(strlen($row->svnr)>$maxlength[$i])
			$maxlength[$i] = strlen($row->svnr);
		$worksheet->write($zeile,$i, $row->svnr);
		$i++;
		
		if(strlen($row->ersatzkennzeichen)>$maxlength[$i])
			$maxlength[$i] = strlen($row->ersatzkennzeichen);
		$worksheet->write($zeile,$i, $row->ersatzkennzeichen);
		$i++;
		
		if(strlen($row->gebdatum)>$maxlength[$i])
			$maxlength[$i] = strlen($row->gebdatum);
		$worksheet->write($zeile,$i, $datum_obj->convertISODate($row->gebdatum));
		$i++;
		
		if(isset($row->semester))
		{
			if(strlen($row->semester)>$maxlength[$i])
				$maxlength[$i] = strlen($row->semester);
			$worksheet->write($zeile,$i, $row->semester);
		}
		$i++;
		
		if(isset($row->verband))
		{
			if(strlen($row->verband)>$maxlength[$i])
				$maxlength[$i] = strlen($row->verband);
			$worksheet->write($zeile,$i, $row->verband);
		}
		$i++;
		
		if(isset($row->gruppe))
		{
			if(strlen($row->gruppe)>$maxlength[$i])
				$maxlength[$i] = strlen($row->gruppe);
			$worksheet->write($zeile,$i, $row->gruppe);
		}
		$i++;
		
		if(isset($row->matrikelnr))
		{
			if(strlen($row->matrikelnr)>$maxlength[$i])
				$maxlength[$i] = strlen($row->matrikelnr);
			$worksheet->write($zeile,$i, $row->matrikelnr);
		}
		$i++;
		
		if($row->zgv_code!='' && isset($zgv_arr[$row->zgv_code]))
		{
			if(strlen($zgv_arr[$row->zgv_code])>$maxlength[$i])
				$maxlength[$i] = strlen($zgv_arr[$row->zgv_code]);
			$worksheet->write($zeile,$i, $zgv_arr[$row->zgv_code]);
		}
		$i++;
		
		if(strlen($row->zgvort)>$maxlength[$i])
			$maxlength[$i] = strlen($row->zgvort);
		$worksheet->write($zeile,$i, $row->zgvort);
		$i++;
		
		if(strlen($row->zgvdatum)>$maxlength[$i])
			$maxlength[$i] = strlen($row->zgvdatum);
		$worksheet->write($zeile,$i, $row->zgvdatum);
		$i++;
		
		if($row->zgvmas_code!='' && isset($zgvmas_arr[$row->zgvmas_code]))
		{
			if(strlen($zgvmas_arr[$row->zgvmas_code])>$maxlength[$i])
				$maxlength[$i] = strlen($zgvmas_arr[$row->zgvmas_code]);
			$worksheet->write($zeile,$i, $zgvmas_arr[$row->zgvmas_code]);
		}
		$i++;
		
		if(strlen($row->zgvmaort)>$maxlength[$i])
			$maxlength[$i] = strlen($row->zgvmaort);
		$worksheet->write($zeile,$i, $row->zgvmaort);
		$i++;
		
		if(strlen($row->zgvmadatum)>$maxlength[$i])
			$maxlength[$i] = strlen($row->zgvmadatum);
		$worksheet->write($zeile,$i, $row->zgvmadatum);
		$i++;
		
		if(strlen($row->staatsbuergerschaft)>$maxlength[$i])
			$maxlength[$i] = strlen($row->staatsbuergerschaft);
		$worksheet->write($zeile,$i, $row->staatsbuergerschaft);
		$i++;
		
		if(strlen($status)>$maxlength[$i])
			$maxlength[$i] = strlen($status);
		$worksheet->write($zeile,$i, $status);
		$i++;
		
		if(isset($row->student_uid))
		{
			if(strlen($row->student_uid.'@'.DOMAIN)>$maxlength[$i])
				$maxlength[$i] = strlen($row->student_uid.'@'.DOMAIN);
			$worksheet->write($zeile,$i, $row->student_uid.'@'.DOMAIN);
		}
		$i++;
		
		//ZustellEmailAdresse aus der Datenbank holen und dazuhaengen
		$qry_1 = "SELECT kontakt FROM public.tbl_kontakt WHERE kontakttyp='email' AND person_id='$row->person_id' AND zustellung=true ORDER BY kontakt_id DESC LIMIT 1";
		if($result_1 = pg_query($conn, $qry_1))
		{
			if($row_1 = pg_fetch_object($result_1))
			{	
				if(strlen($row_1->kontakt)>$maxlength[$i])
					$maxlength[$i]=strlen($row_1->kontakt);
				$worksheet->write($zeile,$i, $row_1->kontakt);
			}
		}
		$i++;
		
		//Zustelladresse aus der Datenbank holen und dazuhaengen
		$qry_1 = "SELECT * FROM public.tbl_adresse WHERE person_id='$row->person_id' ORDER BY zustelladresse LIMIT 1";
		if($result_1 = pg_query($conn, $qry_1))
		{
			if($row_1 = pg_fetch_object($result_1))
			{	
				if(strlen($row_1->strasse)>$maxlength[$i])
					$maxlength[$i]=strlen($row_1->strasse);
				$worksheet->write($zeile,$i, $row_1->strasse);
				$i++;
				
				if(strlen($row_1->plz)>$maxlength[$i])
					$maxlength[$i]=strlen($row_1->plz);
				$worksheet->write($zeile,$i, $row_1->plz);
				$i++;
				
				if(strlen($row_1->ort)>$maxlength[$i])
					$maxlength[$i]=strlen($row_1->ort);
				$worksheet->write($zeile,$i, $row_1->ort);
				$i++;
			}
			else 
				$i+=3;
		}
		else 
			$i+=3;
		
		//Nebenwohnsitz aus der Datenbank holen und dazuhaengen
		$qry_1 = "SELECT * FROM public.tbl_adresse WHERE person_id='$row->person_id' AND typ='n' LIMIT 1";
		if($result_1 = pg_query($conn, $qry_1))
		{
			if($row_1 = pg_fetch_object($result_1))
			{	
				if(strlen($row_1->strasse)>$maxlength[$i])
					$maxlength[$i]=strlen($row_1->strasse);
				$worksheet->write($zeile,$i, $row_1->strasse);
				$i++;
				
				if(strlen($row_1->plz)>$maxlength[$i])
					$maxlength[$i]=strlen($row_1->plz);
				$worksheet->write($zeile,$i, $row_1->plz);
				$i++;
				
				if(strlen($row_1->ort)>$maxlength[$i])
					$maxlength[$i]=strlen($row_1->ort);
				$worksheet->write($zeile,$i, $row_1->ort);
				$i++;
			}
			else 
				$i+=3;
		}
		else 
			$i+=3;
			
		$qry_1 = "SELECT kontakt FROM public.tbl_kontakt WHERE kontakttyp in('mobil','telefon','so.tel') AND person_id='$row->person_id' ORDER BY zustellung DESC LIMIT 1";
		if($result_1 = pg_query($conn, $qry_1))
		{
			if($row_1 = pg_fetch_object($result_1))
			{
				if(strlen($row_1->kontakt)>$maxlength[$i])
					$maxlength[$i]=strlen($row_1->kontakt);
				$worksheet->write($zeile,$i, $row_1->kontakt);
			}
		}
		$i++;
		
		$grps='';
		$qry_1 = "SELECT gruppe_kurzbz FROM public.tbl_student JOIN public.tbl_benutzergruppe ON (student_uid=uid) WHERE tbl_student.prestudent_id='$row->prestudent_id' AND tbl_benutzergruppe.studiensemester_kurzbz='$studiensemester_kurzbz'";
		if($result_1 = pg_query($conn, $qry_1))
		{
			while($row_1 = pg_fetch_object($result_1))
			{
				if($grps!='')
					$grps.=',';
				
				$grps.=$row_1->gruppe_kurzbz;
			}
		}
		if(strlen($grps)>$maxlength[$i])
			$maxlength[$i]=strlen($grps);
		$worksheet->write($zeile,$i, $grps);
		$i++;
		
	}
		

	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet->setColumn($i, $i, $breite+2);
    
	$workbook->close();

?>
