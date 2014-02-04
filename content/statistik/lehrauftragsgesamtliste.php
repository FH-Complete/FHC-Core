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
 * Erstellt ein Excel File mit allen Lektoren und den Studiengaengen in denen diese Unterrichten
 * Diese Liste wird dann per Mail an die Geschaeftsstelle gesendet.
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/Excel/excel.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/mail.class.php');

$stsem = new studiensemester();
$semester_aktuell  = $stsem->getaktorNext();

$file = 'lehrauftragsgesamtliste.xls';

// Creating a workbook
$workbook = new Spreadsheet_Excel_Writer($file);
$workbook->setVersion(8);
// Creating a worksheet
$worksheet =& $workbook->addWorksheet("Lektoren");
$worksheet->setInputEncoding('utf-8');

//Formate Definieren
$format_left =& $workbook->addFormat();
$format_left->setLeft(2);

$format_bold =& $workbook->addFormat();
$format_bold->setBold();

$format_bold_border =& $workbook->addFormat();
$format_bold_border->setBold();
$format_bold_border->setBorder(2);

$format_bold_center =& $workbook->addFormat();
$format_bold_center->setBold();
$format_bold_center->setAlign('center');
$format_bold_center->setBorder(2);

$format_number =& $workbook->addFormat();
$format_number->setNumFormat('0,0.00');

$format_number_bold =& $workbook->addFormat();
$format_number_bold->setNumFormat('0,0.00');
$format_number_bold->setBold();
$format_number_bold->setLeft(2);

$i=0;
$studiensemester = new studiensemester();
$stsem = $studiensemester->getNearest();

$worksheet->write(0,0,'Erstellt am '.date('d.m.Y')." Studiensemester: $stsem", $format_bold);
//Ueberschriften
$zeile=1;
$spalte=0;
$maxlength[$spalte]=10;
$worksheet->write($zeile+1,$spalte++,"Nachname", $format_bold);
$maxlength[$spalte]=10;
$worksheet->write($zeile+1,$spalte++,"Vorname", $format_bold);
$maxlength[$spalte]=10;
$worksheet->write($zeile+1,$spalte++,"UID", $format_bold);
$db = new basis_db();
$qry = "SELECT 
			distinct tbl_studiengang.studiengang_kz, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kuerzel
		FROM 
			lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
			JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) 
			JOIN public.tbl_studiengang USING(studiengang_kz)
		WHERE 
			tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsem)." AND
			tbl_lehreinheitmitarbeiter.faktor is not null AND
			tbl_lehreinheitmitarbeiter.faktor<>0 AND
			tbl_lehreinheitmitarbeiter.stundensatz is not null AND
			tbl_lehreinheitmitarbeiter.stundensatz<>0 AND
			tbl_lehreinheitmitarbeiter.semesterstunden is not null AND
			tbl_lehreinheitmitarbeiter.semesterstunden<>0 ORDER BY kuerzel";
if(!$result = $db->db_query($qry))
	die('Fehler in qry');

while($row = $db->db_fetch_object($result)) 
{
	$worksheet->write($zeile, $spalte,$row->kuerzel, $format_bold_center);
	$worksheet->write($zeile, $spalte+1,$row->kuerzel, $format_bold_center);
	$worksheet->write($zeile, $spalte+2,$row->kuerzel, $format_bold_center);
	$worksheet->write($zeile, $spalte+3,$row->kuerzel, $format_bold_center);
	$stg_spalte[$row->studiengang_kz]=$spalte;
	$maxlength[$spalte]=7;
	$worksheet->write($zeile+1, $spalte++,'Stunden', $format_bold_border);
	$maxlength[$spalte]=5;
	$worksheet->write($zeile+1, $spalte++,'Sätze', $format_bold_border);
	$maxlength[$spalte]=6;
	$worksheet->write($zeile+1, $spalte++,'Faktor', $format_bold_border);
	$maxlength[$spalte]=6;
	$worksheet->write($zeile+1, $spalte++,'Gesamt', $format_bold_border);	
	$worksheet->mergeCells($zeile,$spalte-4,$zeile,$spalte-1); 
}
$maxlength[$spalte]=12;
$worksheet->write($zeile+1, $spalte,'Gesamtsumme', $format_bold);	
$maxspalten = $spalte;

function drawStg($stg)
{
	global $faktor_arr, $satz_arr, $stunden, $gesamt, $worksheet, $stg_spalte;
	global $zeile, $gesamtsumme, $format_number, $maxlength, $format_left;
	
	$faktoren = '';
	$saetze = '';
	
	foreach ($faktor_arr as $faktor)
	{
		if($faktoren!='')
			$faktoren.=', ';
		$faktoren.=$faktor;
	}
	
	foreach ($satz_arr as $satz)
	{
		if($saetze!='')
			$saetze.=', ';
		$saetze.=$satz;
	}
	
	if(strlen($stunden)>$maxlength[$stg_spalte[$stg]])
		$maxlength[$stg_spalte[$stg]]=strlen($stunden);
	$worksheet->write($zeile, $stg_spalte[$stg], $stunden, $format_left);
	
	if(strlen($saetze)>$maxlength[$stg_spalte[$stg]+1])
		$maxlength[$stg_spalte[$stg]+1]=strlen($saetze);
	$worksheet->write($zeile, $stg_spalte[$stg]+1, $saetze);
	
	if(strlen($faktoren)>$maxlength[$stg_spalte[$stg]+2])
		$maxlength[$stg_spalte[$stg]+2]=strlen($faktoren);
	$worksheet->write($zeile, $stg_spalte[$stg]+2, $faktoren);
	
	if(strlen($gesamt)>$maxlength[$stg_spalte[$stg]+3])
		$maxlength[$stg_spalte[$stg]+3]=strlen($gesamt)+5;
	$worksheet->write($zeile, $stg_spalte[$stg]+3, $gesamt, $format_number);
	
	$gesamtsumme += $gesamt;
	$faktor_arr = array();
	$satz_arr = array();
	$stunden = 0;
	$gesamt = 0;
}

function drawGesamtsumme()
{
	global $maxspalten, $zeile, $gesamtsumme, $worksheet, $format_number_bold, $maxlength;
	
	if(strlen($gesamtsumme)>$maxlength[$maxspalten])
		$maxlength[$maxspalten]=strlen($gesamtsumme);
	$worksheet->write($zeile, $maxspalten, $gesamtsumme, $format_number_bold);
}

$qry = "
SELECT 
	mitarbeiter_uid, stundensatz, faktor, sum(tbl_lehreinheitmitarbeiter.semesterstunden) as stunden, 
	stundensatz*sum(tbl_lehreinheitmitarbeiter.semesterstunden)*faktor AS gesamt, studiengang_kz,
	nachname, vorname
FROM 
	lehre.tbl_lehreinheitmitarbeiter JOIN campus.vw_benutzer ON (mitarbeiter_uid=uid) 
	JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
	JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) 
WHERE 
	studiensemester_kurzbz=".$db->db_add_param($stsem)." AND 
	tbl_lehreinheitmitarbeiter.faktor is not null AND
	tbl_lehreinheitmitarbeiter.faktor<>0 AND
	tbl_lehreinheitmitarbeiter.stundensatz is not null AND
	tbl_lehreinheitmitarbeiter.stundensatz<>0 AND
	tbl_lehreinheitmitarbeiter.semesterstunden is not null AND
	tbl_lehreinheitmitarbeiter.semesterstunden<>0
GROUP BY mitarbeiter_uid, studiengang_kz, stundensatz, faktor, nachname, vorname ORDER BY nachname, vorname, studiengang_kz 
 ";

if($result = $db->db_query($qry))
{
	$lastuid='';
	$laststg='';
	$zeile++;
	
	while($row = $db->db_fetch_object($result))
	{
		if($lastuid!=$row->mitarbeiter_uid)
		{
			if($lastuid!='')
			{
				// letzten Studiengang ausgeben
				drawStg($laststg);
				//Gesamtsumme ausgeben
				drawGesamtsumme();
			}
			$zeile++;
			$lastuid=$row->mitarbeiter_uid;
			$laststg=$row->studiengang_kz;
			$faktor_arr = array();
			$satz_arr = array();
			$stunden = 0;
			$gesamt = 0;
			$gesamtsumme=0;
			
			if(strlen($row->nachname)>$maxlength[0])
				$maxlength[0]=strlen($row->nachname);
			$worksheet->write($zeile, 0, $row->nachname);
			
			if(strlen($row->vorname)>$maxlength[1])
				$maxlength[1]=strlen($row->vorname);
			$worksheet->write($zeile, 1, $row->vorname);
			
			if(strlen($row->mitarbeiter_uid)>$maxlength[2])
				$maxlength[2]=strlen($row->mitarbeiter_uid);
			$worksheet->write($zeile, 2, $row->mitarbeiter_uid);
			
			foreach ($stg_spalte as $spalte)
			{
				$worksheet->write($zeile, $spalte, null, $format_left);
			}
			
		}
		
		if($laststg!=$row->studiengang_kz && $laststg!='')
		{
			drawStg($laststg);			
		}
		
		if($row->faktor!='' && $row->faktor!='0' && $row->stunden!='' && $row->stunden!='0' && $row->stundensatz!='' && $row->stundensatz!='0')
		{
			if(!in_array($row->faktor, $faktor_arr))
				$faktor_arr[]=$row->faktor;
				
			if(!in_array($row->stundensatz, $satz_arr))
				$satz_arr[]=$row->stundensatz;
				
			$stunden += $row->stunden;
			$gesamt += $row->gesamt;
		}
		$laststg = $row->studiengang_kz;
	}
	drawStg($laststg);
	drawGesamtsumme();
}
else 
	die('Fehler in qry');

$workbook->close();

//Mail versenden mit Excel File im Anhang
	
	$to = MAIL_GST; 
    
    $subject = "Lehrauftragsgesamtliste ".date('d.m.Y');
    $message = "Dies ist eine automatische eMail!\nAnbei die Lehrauftragsgesamtliste vom ".date('d.m.Y');
    $fileatttype = "application/xls"; 
    $fileattname = "lehrauftragsgesamtliste_".date('Y_m_d').".xls";

    $mail = new mail($to, 'vilesci@'.DOMAIN, $subject, $message);
    $mail->addAttachmentBinary($file, $fileatttype, $fileattname);
    
    if($mail->send())
		echo "Email mit Lehrauftragsgesamtliste wurde versandt"; 
     else    
        echo "Fehler beim Versenden der Lehrauftragsgesamtliste"; 
?>
