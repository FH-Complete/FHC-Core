<?php
/* Copyright (C) 2007 Technikum-Wien
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
 * Erstellt eine Statistik ueber die verschiedenen Stati der Bewerber
 * mit Aufteilung nach Studiengaengen und Geschlecht.
 * Mischformen werden nochmals getrennt aufgelistet (VZ/BB)
 * Ausserdem erfolgt noch eine Auflistung in wie vielen verschiedenen Studiengaengen 
 * sich die Personen Beworben haben.
 * Im unteren Teil wird die Statistiktabelle erneut angezeigt mit dem vergleichswerten des
 * Vorjahres zum Selben Stichtag
 * 
 * Wenn Showdetails gesetzt ist wird ein SVG Graph mit Interessent/Bewerber/Student angezeigt
 * und eine Uebersicht ueber die Berufstaetigkeit und Aufmerksamdurch
 *
 * GET-Parameter:
 * stsem          ... Studiensemester fuer die Statistik
 * mail           ... Wenn der Parameter "mail" uebergeben wird, dann wird die Statistik 
 *                    per Mail an "tw_sek" und "tw_stgl" versandt
 *                    per CLI (Cronjob) wird das Script mit "php bewerberstatistik.php mail" aufgerufen
 * showdetails    ... wenn true, dann wird die Detailansicht fuer einen Studiengang geliefert
 * studiengang_kz ... gibt den Studiengang an der angezeigt werden soll, wenn showdetails=true
 * excel          ... statt HTML wird die Statistik als Excel exportiert
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/mail.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/aufmerksamdurch.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/Excel/excel.php');

$ausgeschieden=array();

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	$stsem = '';
	
$db = new basis_db();
// Wenn der Parameter Mail per GET oder Commandline Argument uebergeben wird,
// dann wird die Statistik per Mail versandt
if(isset($_GET['mail']) || (isset($_SERVER['argv']) && in_array('mail',$_SERVER['argv'])))
{
	$mail=true;
	$stsem_obj = new studiensemester();
	$stsem_obj->getNextStudiensemester('WS');
	$stsem = $stsem_obj->studiensemester_kurzbz;
}
else 
	$mail=false;

//wenn die Statistik per Mail versandt wird (Cronjob), 
//keine Ruecksicht auf Berechtigungen nehmen
//das Mail enthaelt alle Studiengaenge
if(!$mail)
{
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen(get_uid());
}

if(isset($_GET['excel']))
{
	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();
	$workbook->setVersion(8);
	
	// sending HTTP headers
	$workbook->send("Bewerberstatistik_".date('dmY').".xls");
	
	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("BewerberInnenstatistik ".$stsem);
	$worksheet->setInputEncoding('utf-8');
	
	//Formate Definieren
	$format_bold =& $workbook->addFormat();
	$format_alignc0 =& $workbook->addFormat();
	$format_alignl0 =& $workbook->addFormat();
	$format_alignc1 =& $workbook->addFormat();
	$format_alignl1 =& $workbook->addFormat();
	$format_bold->setBold();
	$format_bold->setAlign("center");
	$format_bold->setFgColor(44);
	$format_alignc0->setAlign("center");
	$format_alignl0->setAlign("left");
	$format_alignc1->setAlign("center");
	$format_alignl1->setAlign("left");
	$format_alignc1->setFgColor(26);
	$format_alignl1->setFgColor(26);
	
	//Überschriften 1.Zeile
	$i=0;
	$worksheet->write(0,0,'BewerberInnenstatistik Details'.$stsem.', erstellt am '.date('d.m.Y'), $format_bold);
	$worksheet->mergeCells(0,$i,0,$i+7);
	//Ueberschriften
	$i=0;
	$worksheet->write(1,$i,"Studiengang", $format_bold);
	$maxlength[$i] = 15;
	$worksheet->write(1,++$i,"InteressentInnen", $format_bold);
	$worksheet->mergeCells(1,$i,1,$i+2);
	$i=$i+3;
	$worksheet->write(1,$i,"InteressentInnen mit ZGV", $format_bold);
	$worksheet->mergeCells(1,$i,1,$i+2);
	$i=$i+3;
	$worksheet->write(1,$i,"InteressentInnen mit RT Anmeldung", $format_bold);
	$worksheet->mergeCells(1,$i,1,$i+2);
	$i=$i+3;
	$worksheet->write(1,$i,"BewerberInnen", $format_bold);
	$worksheet->mergeCells(1,$i,1,$i+2);
	$i=$i+3;
	$worksheet->write(1,$i,"Aufgenommene", $format_bold);
	$worksheet->mergeCells(1,$i,1,$i+2);
	$i=$i+3;
	$worksheet->write(1,$i,"Aufgenommene bereinigt", $format_bold);
	$worksheet->mergeCells(1,$i,1,$i+2);
	$i=$i+3;
	$worksheet->write(1,$i,"StudentIn 1S", $format_bold);
	$worksheet->mergeCells(1,$i,1,$i+2);
	$i=$i+3;
	$worksheet->write(1,$i,"StudentIn 3S", $format_bold);
	$worksheet->mergeCells(1,$i,1,$i+2);
	
	//Überschriften 2.Zeile
	$i=0;
	$worksheet->write(2,$i,"", $format_bold);
	$maxlength[$i] = 0;
	$worksheet->write(2,++$i,"gesamt", $format_bold);
	$maxlength[$i] = 6;
	$worksheet->write(2,++$i,"m", $format_bold);
	$maxlength[$i] = 3;
	$worksheet->write(2,++$i,"w", $format_bold);
	$maxlength[$i] = 3;
	$worksheet->write(2,++$i,"gesamt", $format_bold);
	$maxlength[$i] = 6;
	$worksheet->write(2,++$i,"m", $format_bold);
	$maxlength[$i] = 3;
	$worksheet->write(2,++$i,"w", $format_bold);
	$maxlength[$i] = 3;
	$worksheet->write(2,++$i,"gesamt", $format_bold);
	$maxlength[$i] = 6;
	$worksheet->write(2,++$i,"m", $format_bold);
	$maxlength[$i] = 3;
	$worksheet->write(2,++$i,"w", $format_bold);
	$maxlength[$i] = 3;
	$worksheet->write(2,++$i,"gesamt", $format_bold);
	$maxlength[$i] = 6;
	$worksheet->write(2,++$i,"m", $format_bold);
	$maxlength[$i] = 3;
	$worksheet->write(2,++$i,"w", $format_bold);
	$maxlength[$i] = 3;
	$worksheet->write(2,++$i,"gesamt", $format_bold);
	$maxlength[$i] = 6;
	$worksheet->write(2,++$i,"m", $format_bold);
	$maxlength[$i] = 3;
	$worksheet->write(2,++$i,"w", $format_bold);
	$maxlength[$i] = 3;
	$worksheet->write(2,++$i,"gesamt", $format_bold);
	$maxlength[$i] = 6;
	$worksheet->write(2,++$i,"m", $format_bold);
	$maxlength[$i] = 3;
	$worksheet->write(2,++$i,"w", $format_bold);
	$maxlength[$i] = 3;
	$worksheet->write(2,++$i,"gesamt", $format_bold);
	$maxlength[$i] = 6;
	$worksheet->write(2,++$i,"m", $format_bold);
	$maxlength[$i] = 3;
	$worksheet->write(2,++$i,"w", $format_bold);
	$maxlength[$i] = 3;
	$worksheet->write(2,++$i,"gesamt", $format_bold);
	$maxlength[$i] = 6;
	$worksheet->write(2,++$i,"m", $format_bold);
	$maxlength[$i] = 3;
	$worksheet->write(2,++$i,"w", $format_bold);
	$maxlength[$i] = 3;
	
	//Tabellenzeilen
	$stgs = $rechte->getStgKz();
		
	if($stgs[0]=='')
		$stgwhere='';
	else 
	{
		$stgwhere=' AND studiengang_kz in(';
		foreach ($stgs as $stg)
			$stgwhere.="'$stg',";
		$stgwhere = mb_substr($stgwhere,0, mb_strlen($stgwhere)-1);
		$stgwhere.=' )';
	}
	$j=0;
	$qry="SELECT prestudent_id FROM public.tbl_prestudentstatus WHERE status_kurzbz='Abgewiesener' AND studiensemester_kurzbz='$stsem'";
	if($result = $db->db_query($qry))
	{
		While ($row = $db->db_fetch_object($result))
		{
			$ausgeschieden[$j]=$row->prestudent_id;
			$j++;
		}
	}
		
	//Bewerberdaten holen
	$qry = "SELECT studiengang_kz, kurzbz, typ, kurzbzlang, bezeichnung, orgform_kurzbz,
	
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
					) AS interessenten,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
					) AS interessenten_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
					) AS interessenten_w,
				
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   				AND ((stg.typ<>'m' AND zgv_code IS NOT NULL) OR zgvmas_code IS NOT NULL)) AS interessentenzgv,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
	   				AND ((stg.typ<>'m' AND zgv_code IS NOT NULL) OR zgvmas_code IS NOT NULL)) AS interessentenzgv_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
	   				AND ((stg.typ<>'m' AND zgv_code IS NOT NULL) OR zgvmas_code IS NOT NULL)) AS interessentenzgv_w,
	   				   			
	   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND anmeldungreihungstest IS NOT NULL) AS interessentenrtanmeldung,
	   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
	   			 	AND anmeldungreihungstest IS NOT NULL) AS interessentenrtanmeldung_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
	   			 	AND anmeldungreihungstest IS NOT NULL) AS interessentenrtanmeldung_w,
	   				   			 
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem'
					) AS bewerber,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
					) AS bewerber_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
					) AS bewerber_w,
					
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
					) AS aufgenommener,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
					WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
					) AS aufgenommener_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
					WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
					) AS aufgenommener_w,
									
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' ";
				if(count($ausgeschieden)>0)
				{
						$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')     ";
				}
					$qry.=") AS aufgenommenerber,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
					WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' AND geschlecht='m' ";
				if(count($ausgeschieden)>0)
				{
						$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
				}
					$qry.=") AS aufgenommenerber_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
					WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' AND geschlecht='w' ";
				if(count($ausgeschieden)>0)
				{
						$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
				}
					$qry.=") AS aufgenommenerber_w, 
						
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_student USING(prestudent_id) JOIN public.tbl_benutzer ON(uid=student_uid)
					WHERE tbl_prestudent.studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1 AND tbl_benutzer.aktiv
				) AS student1sem,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN public.tbl_student USING(prestudent_id) JOIN public.tbl_benutzer ON(uid=student_uid)
					WHERE tbl_prestudent.studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1 AND geschlecht='m' AND tbl_benutzer.aktiv
				) AS student1sem_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN public.tbl_student USING(prestudent_id) JOIN public.tbl_benutzer ON(uid=student_uid)
					WHERE tbl_prestudent.studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1 AND geschlecht='w' AND tbl_benutzer.aktiv
				) AS student1sem_w,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_student USING(prestudent_id) JOIN public.tbl_benutzer ON(uid=student_uid)
					WHERE tbl_prestudent.studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3 AND tbl_benutzer.aktiv
				) AS student3sem,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN public.tbl_student USING(prestudent_id) JOIN public.tbl_benutzer ON(uid=student_uid)
					WHERE tbl_prestudent.studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3 AND geschlecht='m' AND tbl_benutzer.aktiv
				) AS student3sem_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN public.tbl_student USING(prestudent_id) JOIN public.tbl_benutzer ON(student_uid=uid)
					WHERE tbl_prestudent.studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3 AND geschlecht='w' AND tbl_benutzer.aktiv
				) AS student3sem_w
	
			FROM
				public.tbl_studiengang stg
			WHERE
				studiengang_kz>0 AND studiengang_kz<10000 AND aktiv $stgwhere
			ORDER BY typ, kurzbz; ";
	
	if($result = $db->db_query($qry))
	{	
		$interessenten_sum = 0;
		$interessenten_m_sum = 0;
		$interessenten_w_sum = 0;
		$interessentenzgv_sum = 0;
		$interessentenzgv_m_sum = 0;
		$interessentenzgv_w_sum = 0;
		$interessentenrtanmeldung_sum = 0;
		$interessentenrtanmeldung_m_sum = 0;
		$interessentenrtanmeldung_w_sum = 0;
		$bewerber_sum = 0;
		$bewerber_m_sum = 0;
		$bewerber_w_sum = 0;
		$aufgenommener_sum = 0;
		$aufgenommener_m_sum = 0;
		$aufgenommener_w_sum = 0;
		$aufgenommenerber_sum = 0;
		$aufgenommenerber_m_sum = 0;
		$aufgenommenerber_w_sum = 0;
		$student1sem_sum = 0;
		$student1sem_m_sum = 0;
		$student1sem_w_sum = 0;
		$student3sem_sum = 0;
		$student3sem_m_sum = 0;
		$student3sem_w_sum = 0;
		
		$zeile=3;
		while($row = $db->db_fetch_object($result))
		{
			$i=0;
			$format="format_alignl".$zeile%2;
			$worksheet->write($zeile,$i,strtoupper($row->typ.$row->kurzbz)."(".($row->kurzbzlang).")", $$format);
			if(strlen(strtoupper($row->typ.$row->kurzbz).($row->kurzbzlang))>$maxlength[$i])
					$maxlength[$i] = mb_strlen(strtoupper($row->typ.$row->kurzbz).($row->kurzbzlang)." ");
			$format="format_alignc".$zeile%2;
			$worksheet->write($zeile,++$i,$row->interessenten, $$format);
			if(strlen($row->interessenten)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->interessenten);
			$worksheet->write($zeile,++$i,$row->interessenten_m, $$format);
			if(strlen($row->interessenten_m)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->interessenten_m);
			$worksheet->write($zeile,++$i,$row->interessenten_w, $$format);
			if(strlen($row->interessenten_w)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->interessenten_w);
			$worksheet->write($zeile,++$i,$row->interessentenzgv, $$format);
			if(strlen($row->interessentenzgv)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->interessentenzgv);
			$worksheet->write($zeile,++$i,$row->interessentenzgv_m, $$format);
			if(strlen($row->interessentenzgv_m)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->interessentenzgv_m);	
			$worksheet->write($zeile,++$i,$row->interessentenzgv_w, $$format);
			if(strlen($row->interessentenzgv_w)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->interessentenzgv_w);	
			$worksheet->write($zeile,++$i,$row->interessentenrtanmeldung, $$format);
			if(strlen($row->interessentenrtanmeldung)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->interessentenrtanmeldung);	
			$worksheet->write($zeile,++$i,$row->interessentenrtanmeldung_m, $$format);
			if(strlen($row->interessentenrtanmeldung_m)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->interessentenrtanmeldung_m);	
			$worksheet->write($zeile,++$i,$row->interessentenrtanmeldung_w, $$format);
			if(strlen($row->interessentenrtanmeldung_w)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->interessentenrtanmeldung_w);		
			$worksheet->write($zeile,++$i,$row->bewerber, $$format);
			if(strlen($row->bewerber)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->bewerber);		
			$worksheet->write($zeile,++$i,$row->bewerber_m, $$format);
			if(strlen($row->bewerber_m)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->bewerber_m);		
			$worksheet->write($zeile,++$i,$row->bewerber_w, $$format);
			if(strlen($row->bewerber_w)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->bewerber_w);
			$worksheet->write($zeile,++$i,$row->aufgenommener, $$format);
			if(strlen($row->aufgenommener)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->aufgenommener);
			$worksheet->write($zeile,++$i,$row->aufgenommener_m, $$format);
			if(strlen($row->aufgenommener_m)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->aufgenommener_m);
			$worksheet->write($zeile,++$i,$row->aufgenommener_w, $$format);
			if(strlen($row->aufgenommener_w)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->aufgenommener_w);
			$worksheet->write($zeile,++$i,$row->aufgenommenerber, $$format);
			if(strlen($row->aufgenommenerber)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->aufgenommenerber);
			$worksheet->write($zeile,++$i,$row->aufgenommenerber_m, $$format);
			if(strlen($row->aufgenommenerber_m)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->aufgenommenerber_m);		
			$worksheet->write($zeile,++$i,$row->aufgenommenerber_w, $$format);
			if(strlen($row->aufgenommenerber_w)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->aufgenommenerber_w);		
			$worksheet->write($zeile,++$i,$row->student1sem, $$format);
			if(strlen($row->student1sem)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->student1sem);		
			$worksheet->write($zeile,++$i,$row->student1sem_m, $$format);
			if(strlen($row->student1sem_m)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->student1sem_m);			
			$worksheet->write($zeile,++$i,$row->student1sem_w, $$format);
			if(strlen($row->student1sem_w)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->student1sem_w);			
			$worksheet->write($zeile,++$i,$row->student3sem, $$format);
			if(strlen($row->student3sem)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->student3sem);		
			$worksheet->write($zeile,++$i,$row->student3sem_m, $$format);
			if(strlen($row->student3sem_m)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->student3sem_m);			
			$worksheet->write($zeile,++$i,$row->student3sem_w, $$format);
			if(strlen($row->student3sem_w)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($row->student3sem_w);			

			$zeile++;
			
			//Summe berechnen
			$interessenten_sum += $row->interessenten;
			$interessenten_m_sum += $row->interessenten_m;
			$interessenten_w_sum += $row->interessenten_w;
			$interessentenzgv_sum += $row->interessentenzgv;
			$interessentenzgv_m_sum += $row->interessentenzgv_m;
			$interessentenzgv_w_sum += $row->interessentenzgv_w;
			$interessentenrtanmeldung_sum += $row->interessentenrtanmeldung;
			$interessentenrtanmeldung_m_sum += $row->interessentenrtanmeldung_m;
			$interessentenrtanmeldung_w_sum += $row->interessentenrtanmeldung_w;
			$bewerber_sum += $row->bewerber;
			$bewerber_m_sum += $row->bewerber_m;
			$bewerber_w_sum += $row->bewerber_w;
			$aufgenommener_sum += $row->aufgenommener;
			$aufgenommener_m_sum += $row->aufgenommener_m;
			$aufgenommener_w_sum += $row->aufgenommener_w;
			$aufgenommenerber_sum += $row->aufgenommenerber;
			$aufgenommenerber_m_sum += $row->aufgenommenerber_m;
			$aufgenommenerber_w_sum += $row->aufgenommenerber_w;
			$student1sem_sum += $row->student1sem;
			$student1sem_m_sum += $row->student1sem_m;
			$student1sem_w_sum += $row->student1sem_w;
			$student3sem_sum += $row->student3sem;
			$student3sem_m_sum += $row->student3sem_m;
			$student3sem_w_sum += $row->student3sem_w;
		}
	
		$i=0;
		$worksheet->write($zeile,$i,"Summe", $format_bold);
		if($maxlength[$i]<5)
				$maxlength[$i] = 5;
		$worksheet->write($zeile,++$i,$interessenten_sum, $format_bold);
		if(strlen($interessenten_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($interessenten_sum);	
		$worksheet->write($zeile,++$i,$interessenten_m_sum, $format_bold);
		if(strlen($interessenten_m_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($interessenten_m_sum);
		$worksheet->write($zeile,++$i,$interessenten_w_sum, $format_bold);
		if(strlen($interessenten_w_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($interessenten_w_sum);		
		$worksheet->write($zeile,++$i,$interessentenzgv_sum, $format_bold);
		if(strlen($interessentenzgv_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($interessentenzgv_sum);	
		$worksheet->write($zeile,++$i,$interessentenzgv_m_sum, $format_bold);
		if(strlen($interessentenzgv_m_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($interessentenzgv_m_sum);
		$worksheet->write($zeile,++$i,$interessentenzgv_w_sum, $format_bold);
		if(strlen($interessentenzgv_w_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($interessentenzgv_w_sum);		
		$worksheet->write($zeile,++$i,$interessentenrtanmeldung_sum, $format_bold);
		if(strlen($interessentenrtanmeldung_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($interessentenrtanmeldung_sum);			
		$worksheet->write($zeile,++$i,$interessentenrtanmeldung_m_sum, $format_bold);
		if(strlen($interessentenrtanmeldung_m_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($interessentenrtanmeldung_m_sum);	
		$worksheet->write($zeile,++$i,$interessentenrtanmeldung_w_sum, $format_bold);
		if(strlen($interessentenrtanmeldung_w_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($interessentenrtanmeldung_w_sum);			
		$worksheet->write($zeile,++$i,$bewerber_sum, $format_bold);
		if(strlen($bewerber_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($bewerber_sum);
		$worksheet->write($zeile,++$i,$bewerber_m_sum, $format_bold);
		if(strlen($bewerber_m_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($bewerber_m_sum);
		$worksheet->write($zeile,++$i,$bewerber_w_sum, $format_bold);
		if(strlen($bewerber_w_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($bewerber_w_sum);
		$worksheet->write($zeile,++$i,$aufgenommener_sum, $format_bold);
		if(strlen($aufgenommener_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($aufgenommener_sum);
		$worksheet->write($zeile,++$i,$aufgenommener_m_sum, $format_bold);
		if(strlen($aufgenommener_m_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($aufgenommener_m_sum);
		$worksheet->write($zeile,++$i,$aufgenommener_w_sum, $format_bold);
		if(strlen($aufgenommener_w_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($aufgenommener_w_sum);		
		$worksheet->write($zeile,++$i,$aufgenommenerber_sum, $format_bold);
		if(strlen($aufgenommenerber_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($aufgenommenerber_sum);
		$worksheet->write($zeile,++$i,$aufgenommenerber_m_sum, $format_bold);
		if(strlen($aufgenommenerber_m_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($aufgenommenerber_m_sum);
		$worksheet->write($zeile,++$i,$aufgenommenerber_w_sum, $format_bold);
		if(strlen($aufgenommenerber_w_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($aufgenommenerber_w_sum);		
		$worksheet->write($zeile,++$i,$student1sem_sum, $format_bold);
		if(strlen($student1sem_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($student1sem_sum);
		$worksheet->write($zeile,++$i,$student1sem_m_sum, $format_bold);
		if(strlen($student1sem_m_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($student1sem_m_sum);
		$worksheet->write($zeile,++$i,$student1sem_w_sum, $format_bold);
		if(strlen($student1sem_w_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($student1sem_w_sum);
		$worksheet->write($zeile,++$i,$student3sem_sum, $format_bold);
		if(strlen($student3sem_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($student3sem_sum);
		$worksheet->write($zeile,++$i,$student3sem_m_sum, $format_bold);
		if(strlen($student3sem_m_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($student3sem_m_sum);
		$worksheet->write($zeile,++$i,$student3sem_w_sum, $format_bold);
		if(strlen($student3sem_w_sum)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($student3sem_w_sum);
		
		//Aufsplittungen für Mischformen holen
		$qry = "SELECT studiengang_kz, kurzbz, typ, kurzbzlang, bezeichnung, orgform_kurzbz,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND orgform_kurzbz='VZ'
						) AS interessenten_vz,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND orgform_kurzbz='BB'
						) AS interessenten_bb,	
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND orgform_kurzbz='DL'
						) AS interessenten_dl,	
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND orgform_kurzbz='DDP'
						) AS interessenten_ddp,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   				AND ((stg.typ<>'m' AND zgv_code IS NOT NULL) OR zgvmas_code IS NOT NULL) AND orgform_kurzbz='BB') AS interessentenzgv_bb,
		   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   				AND ((stg.typ<>'m' AND zgv_code IS NOT NULL) OR zgvmas_code IS NOT NULL) AND orgform_kurzbz='VZ') AS interessentenzgv_vz,
		   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   				AND ((stg.typ<>'m' AND zgv_code IS NOT NULL) OR zgvmas_code IS NOT NULL) AND orgform_kurzbz='DL') AS interessentenzgv_dl,
		   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   				AND ((stg.typ<>'m' AND zgv_code IS NOT NULL) OR zgvmas_code IS NOT NULL) AND orgform_kurzbz='DDP') AS interessentenzgv_ddp,
	
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND anmeldungreihungstest IS NOT NULL AND orgform_kurzbz='VZ') AS interessentenrtanmeldung_vz,
		   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND anmeldungreihungstest IS NOT NULL AND orgform_kurzbz='BB') AS interessentenrtanmeldung_bb,
		   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND anmeldungreihungstest IS NOT NULL AND orgform_kurzbz='DL') AS interessentenrtanmeldung_dl,
		   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND anmeldungreihungstest IS NOT NULL AND orgform_kurzbz='DDP') AS interessentenrtanmeldung_ddp,
	
		   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND reihungstest_id IS NOT NULL  AND orgform_kurzbz='BB') AS interessentenrttermin_bb,
		   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND reihungstest_id IS NOT NULL  AND orgform_kurzbz='VZ') AS interessentenrttermin_vz,
		   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND reihungstest_id IS NOT NULL  AND orgform_kurzbz='DL') AS interessentenrttermin_dl,
		   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND reihungstest_id IS NOT NULL  AND orgform_kurzbz='DDP') AS interessentenrttermin_ddp,
		   			 	
		   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND reihungstestangetreten AND orgform_kurzbz='VZ') AS interessentenrtabsolviert_vz,
		   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND reihungstestangetreten AND orgform_kurzbz='BB') AS interessentenrtabsolviert_bb,
		   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND reihungstestangetreten AND orgform_kurzbz='DL') AS interessentenrtabsolviert_dl,
		   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND reihungstestangetreten AND orgform_kurzbz='DDP') AS interessentenrtabsolviert_ddp,
		   			 	
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem'
						AND orgform_kurzbz='BB') AS bewerber_bb,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem'
						AND orgform_kurzbz='VZ') AS bewerber_vz,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem'
						AND orgform_kurzbz='DL') AS bewerber_dl,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem'
						AND orgform_kurzbz='DDP') AS bewerber_ddp,
					
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
						AND orgform_kurzbz='VZ') AS aufgenommener_vz,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
						AND orgform_kurzbz='BB') AS aufgenommener_bb,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
						AND orgform_kurzbz='DL') AS aufgenommener_dl,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
						AND orgform_kurzbz='DDP') AS aufgenommener_ddp,
							
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' ";
					if(count($ausgeschieden)>0)
					{
							$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
					}
						$qry.="AND orgform_kurzbz='VZ') AS aufgenommenerber_vz,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' ";
					if(count($ausgeschieden)>0)
					{
							$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
					}
						$qry.="AND orgform_kurzbz='BB') AS aufgenommenerber_bb,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' ";
					if(count($ausgeschieden)>0)
					{
							$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
					}
						$qry.="AND orgform_kurzbz='DL') AS aufgenommenerber_dl,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' ";
					if(count($ausgeschieden)>0)
					{
							$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
					}
						$qry.="AND orgform_kurzbz='DDP') AS aufgenommenerber_ddp,
					
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
						AND orgform_kurzbz='BB') AS student1sem_bb,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
						AND orgform_kurzbz='VZ') AS student1sem_vz,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
						AND orgform_kurzbz='DL') AS student1sem_dl,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
						AND orgform_kurzbz='DDP') AS student1sem_ddp,
							
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3
						AND orgform_kurzbz='BB') AS student3sem_bb,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3
						AND orgform_kurzbz='VZ') AS student3sem_vz,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3
						AND orgform_kurzbz='DL') AS student3sem_dl,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3
						AND orgform_kurzbz='DDP') AS student3sem_ddp
				FROM
					public.tbl_studiengang stg
				WHERE
					studiengang_kz>0 AND studiengang_kz<10000 AND aktiv $stgwhere AND stg.mischform=true
				ORDER BY kurzbzlang; ";
	
		if($result = $db->db_query($qry))
		{
			if($db->db_num_rows($result)>0)
			{
				//Überschriften 1.Zeile
				$zeile=$zeile+3;
				$i=0;
				$worksheet->write($zeile,0,'Aufsplittung Mischformen', $format_bold);
				$worksheet->mergeCells($zeile,$i,$zeile,$i+6);
				//Ueberschriften
				$i=0;
				$worksheet->write(++$zeile,$i,"Studiengang", $format_bold);
				$maxlength[$i] = 15;
				$worksheet->write($zeile,++$i,"InteressentInnen", $format_bold);
				$worksheet->mergeCells($zeile,$i,$zeile,$i+3);
				$i=$i+4;
				$worksheet->write($zeile,$i,"InteressentInnen mit ZGV", $format_bold);
				$worksheet->mergeCells($zeile,$i,$zeile,$i+3);
				$i=$i+4;
				$worksheet->write($zeile,$i,"InteressentInnen mit RT Anmeldung", $format_bold);
				$worksheet->mergeCells($zeile,$i,$zeile,$i+3);
				$i=$i+4;
				$worksheet->write($zeile,$i,"BewerberInnen", $format_bold);
				$worksheet->mergeCells($zeile,$i,$zeile,$i+3);
				$i=$i+4;
				$worksheet->write($zeile,$i,"Aufgenommene", $format_bold);
				$worksheet->mergeCells($zeile,$i,$zeile,$i+3);
				$i=$i+4;
				$worksheet->write($zeile,$i,"Aufgenommene bereinigt", $format_bold);
				$worksheet->mergeCells($zeile,$i,$zeile,$i+3);
				$i=$i+4;
				$worksheet->write($zeile,$i,"StudentIn 1S", $format_bold);
				$worksheet->mergeCells($zeile,$i,$zeile,$i+3);				
				$i=$i+4;
				$worksheet->write($zeile,$i,"StudentIn 3S", $format_bold);
				$worksheet->mergeCells($zeile,$i,$zeile,$i+3);
				
				//Überschriften 2.Zeile
				$i=0;
				$worksheet->write(++$zeile,$i,"", $format_bold);
				$maxlength[$i] = 0;
				$worksheet->write($zeile,++$i,"VZ", $format_bold);
				$maxlength[$i] = 6;
				$worksheet->write($zeile,++$i,"BB", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"DL", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"DDP", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"VZ", $format_bold);
				$maxlength[$i] = 6;
				$worksheet->write($zeile,++$i,"BB", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"DL", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"DDP", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"VZ", $format_bold);
				$maxlength[$i] = 6;
				$worksheet->write($zeile,++$i,"BB", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"DL", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"DDP", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"VZ", $format_bold);
				$maxlength[$i] = 6;
				$worksheet->write($zeile,++$i,"BB", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"DL", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"DDP", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"VZ", $format_bold);
				$maxlength[$i] = 6;
				$worksheet->write($zeile,++$i,"BB", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"DL", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"DDP", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"VZ", $format_bold);
				$maxlength[$i] = 6;
				$worksheet->write($zeile,++$i,"BB", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"DL", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"DDP", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"VZ", $format_bold);
				$maxlength[$i] = 6;
				$worksheet->write($zeile,++$i,"BB", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"DL", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"DDP", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"VZ", $format_bold);
				$maxlength[$i] = 6;
				$worksheet->write($zeile,++$i,"BB", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"DL", $format_bold);
				$maxlength[$i] = 3;
				$worksheet->write($zeile,++$i,"DDP", $format_bold);
				$maxlength[$i] = 3;
				
				$interessenten_vz_sum = 0;
				$interessenten_bb_sum = 0;
				$interessenten_dl_sum = 0;
				$interessenten_ddp_sum = 0;
				$interessentenzgv_vz_sum = 0;
				$interessentenzgv_bb_sum = 0;
				$interessentenzgv_dl_sum = 0;
				$interessentenzgv_ddp_sum = 0;
				$interessentenrtanmeldung_vz_sum = 0;
				$interessentenrtanmeldung_bb_sum = 0;
				$interessentenrtanmeldung_dl_sum = 0;
				$interessentenrtanmeldung_ddp_sum = 0;
				$bewerber_vz_sum = 0;
				$bewerber_bb_sum = 0;
				$bewerber_dl_sum = 0;
				$bewerber_ddp_sum = 0;
				$aufgenommener_vz_sum = 0;
				$aufgenommener_bb_sum = 0;
				$aufgenommener_dl_sum = 0;
				$aufgenommener_ddp_sum = 0;
				$aufgenommenerber_vz_sum = 0;
				$aufgenommenerber_bb_sum = 0;
				$aufgenommenerber_dl_sum = 0;
				$aufgenommenerber_ddp_sum = 0;
				$student1sem_vz_sum = 0;
				$student1sem_bb_sum = 0;
				$student1sem_dl_sum = 0;
				$student1sem_ddp_sum = 0;
				$student3sem_vz_sum = 0;
				$student3sem_bb_sum = 0;
				$student3sem_dl_sum = 0;
				$student3sem_ddp_sum = 0;
								
				while($row = $db->db_fetch_object($result))
				{
					$i=0;
					$format="format_alignl".$zeile%2;
					$worksheet->write(++$zeile,$i,mb_strtoupper($row->typ.$row->kurzbz)." ($row->kurzbzlang)", $$format);
					if(strlen(mb_strtoupper($row->typ.$row->kurzbz)." ".($row->kurzbzlang)." ")>$maxlength[$i])
							$maxlength[$i] = mb_strlen(mb_strtoupper($row->typ.$row->kurzbz)." ");	
					$format="format_alignc".$zeile%2;
					//Interessenten
					$worksheet->write($zeile,++$i,$row->interessenten_vz, $$format);
					if(strlen($row->interessenten_vz)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->interessenten_vz);
					$worksheet->write($zeile,++$i,$row->interessenten_bb, $$format);
					if(strlen($row->interessenten_bb)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->interessenten_bb);		
					$worksheet->write($zeile,++$i,$row->interessenten_dl, $$format);
					if(strlen($row->interessenten_dl)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->interessenten_dl);			
					$worksheet->write($zeile,++$i,$row->interessenten_ddp, $$format);
					if(strlen($row->interessenten_ddp)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->interessenten_ddp);
					
					//Interessenten ZGV
					$worksheet->write($zeile,++$i,$row->interessentenzgv_vz, $$format);
					if(strlen($row->interessentenzgv_vz)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->interessentenzgv_vz);
					$worksheet->write($zeile,++$i,$row->interessentenzgv_bb, $$format);
					if(strlen($row->interessentenzgv_bb)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->interessentenzgv_bb);
					$worksheet->write($zeile,++$i,$row->interessentenzgv_dl, $$format);
					if(strlen($row->interessentenzgv_dl)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->interessentenzgv_dl);
					$worksheet->write($zeile,++$i,$row->interessentenzgv_ddp, $$format);
					if(strlen($row->interessentenzgv_ddp)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->interessentenzgv_ddp);
						
					//Interessenten RT Anmeldung
					$worksheet->write($zeile,++$i,$row->interessentenrtanmeldung_vz, $$format);
					if(strlen($row->interessentenrtanmeldung_vz)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->interessentenrtanmeldung_vz);		
					$worksheet->write($zeile,++$i,$row->interessentenrtanmeldung_bb, $$format);
					if(strlen($row->interessentenrtanmeldung_bb)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->interessentenrtanmeldung_bb);		
					$worksheet->write($zeile,++$i,$row->interessentenrtanmeldung_dl, $$format);
					if(strlen($row->interessentenrtanmeldung_dl)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->interessentenrtanmeldung_dl);
					$worksheet->write($zeile,++$i,$row->interessentenrtanmeldung_ddp, $$format);
					if(strlen($row->interessentenrtanmeldung_ddp)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->interessentenrtanmeldung_ddp);
					
					//Bewerber
					$worksheet->write($zeile,++$i,$row->bewerber_vz, $$format);
					if(strlen($row->bewerber_vz)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->bewerber_vz);		
					$worksheet->write($zeile,++$i,$row->bewerber_bb, $$format);
					if(strlen($row->bewerber_bb)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->bewerber_bb);
					$worksheet->write($zeile,++$i,$row->bewerber_dl, $$format);
					if(strlen($row->bewerber_dl)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->bewerber_dl);
					$worksheet->write($zeile,++$i,$row->bewerber_ddp, $$format);
					if(strlen($row->bewerber_ddp)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->bewerber_ddp);
					
					//Aufgenommener
					$worksheet->write($zeile,++$i,$row->aufgenommener_vz, $$format);
					if(strlen($row->aufgenommener_vz)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->aufgenommener_vz);		
					$worksheet->write($zeile,++$i,$row->aufgenommener_bb, $$format);
					if(strlen($row->aufgenommener_bb)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->aufgenommener_bb);
					$worksheet->write($zeile,++$i,$row->aufgenommener_dl, $$format);
					if(strlen($row->aufgenommener_dl)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->aufgenommener_dl);
					$worksheet->write($zeile,++$i,$row->aufgenommener_ddp, $$format);
					if(strlen($row->aufgenommener_ddp)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->aufgenommener_ddp);
					
					//Aufgenommener Bereinigt
					$worksheet->write($zeile,++$i,$row->aufgenommenerber_vz, $$format);
					if(strlen($row->aufgenommenerber_vz)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->aufgenommenerber_vz);
					$worksheet->write($zeile,++$i,$row->aufgenommenerber_bb, $$format);
					if(strlen($row->aufgenommenerber_bb)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->aufgenommenerber_bb);
					$worksheet->write($zeile,++$i,$row->aufgenommenerber_dl, $$format);
					if(strlen($row->aufgenommenerber_dl)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->aufgenommenerber_dl);
					$worksheet->write($zeile,++$i,$row->aufgenommenerber_ddp, $$format);
					if(strlen($row->aufgenommenerber_ddp)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->aufgenommenerber_ddp);
							
					//Studenten 1. Semester
					$worksheet->write($zeile,++$i,$row->student1sem_vz, $$format);
					if(strlen($row->student1sem_vz)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->student1sem_vz);
					$worksheet->write($zeile,++$i,$row->student1sem_bb, $$format);
					if(strlen($row->student1sem_bb)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->student1sem_bb);		
					$worksheet->write($zeile,++$i,$row->student1sem_dl, $$format);
					if(strlen($row->student1sem_dl)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->student1sem_dl);
					$worksheet->write($zeile,++$i,$row->student1sem_ddp, $$format);
					if(strlen($row->student1sem_ddp)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->student1sem_ddp);
							
					//Studenten 3. Semester
					$worksheet->write($zeile,++$i,$row->student3sem_vz, $$format);
					if(strlen($row->student3sem_vz)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->student3sem_vz);
					$worksheet->write($zeile,++$i,$row->student3sem_bb, $$format);
					if(strlen($row->student3sem_bb)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->student3sem_bb);		
					$worksheet->write($zeile,++$i,$row->student3sem_dl, $$format);
					if(strlen($row->student3sem_dl)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->student3sem_dl);
					$worksheet->write($zeile,++$i,$row->student3sem_ddp, $$format);
					if(strlen($row->student3sem_ddp)>$maxlength[$i])
							$maxlength[$i] = mb_strlen($row->student3sem_ddp);			
					
					//Summe berechnen
					$interessenten_vz_sum += $row->interessenten_vz;
					$interessenten_bb_sum += $row->interessenten_bb;
					$interessenten_dl_sum += $row->interessenten_dl;
					$interessenten_ddp_sum += $row->interessenten_ddp;
					$interessentenzgv_vz_sum += $row->interessentenzgv_vz;
					$interessentenzgv_bb_sum += $row->interessentenzgv_bb;
					$interessentenzgv_dl_sum += $row->interessentenzgv_dl;
					$interessentenzgv_ddp_sum += $row->interessentenzgv_ddp;
					$interessentenrtanmeldung_vz_sum += $row->interessentenrtanmeldung_vz;
					$interessentenrtanmeldung_bb_sum += $row->interessentenrtanmeldung_bb;
					$interessentenrtanmeldung_dl_sum += $row->interessentenrtanmeldung_dl;
					$interessentenrtanmeldung_ddp_sum += $row->interessentenrtanmeldung_ddp;
					$bewerber_vz_sum += $row->bewerber_vz;
					$bewerber_bb_sum += $row->bewerber_bb;
					$bewerber_dl_sum += $row->bewerber_dl;
					$bewerber_ddp_sum += $row->bewerber_ddp;
					$aufgenommener_vz_sum += $row->aufgenommener_vz;
					$aufgenommener_bb_sum += $row->aufgenommener_bb;
					$aufgenommener_dl_sum += $row->aufgenommener_dl;
					$aufgenommener_ddp_sum += $row->aufgenommener_ddp;
					$aufgenommenerber_vz_sum += $row->aufgenommenerber_vz;
					$aufgenommenerber_bb_sum += $row->aufgenommenerber_bb;
					$aufgenommenerber_dl_sum += $row->aufgenommenerber_dl;
					$aufgenommenerber_ddp_sum += $row->aufgenommenerber_ddp;
					$student1sem_vz_sum += $row->student1sem_vz;
					$student1sem_bb_sum += $row->student1sem_bb;
					$student1sem_dl_sum += $row->student1sem_dl;
					$student1sem_ddp_sum += $row->student1sem_ddp;
					$student3sem_vz_sum += $row->student3sem_vz;
					$student3sem_bb_sum += $row->student3sem_bb;
					$student3sem_dl_sum += $row->student3sem_dl;
					$student3sem_ddp_sum += $row->student3sem_ddp;
				}
				$i=0;
				$worksheet->write(++$zeile,$i,"Summe", $format_bold);
				if($maxlength[$i]<5)
						$maxlength[$i] = 5;
				//Interessenten
				$worksheet->write($zeile,++$i,$interessenten_vz_sum, $format_bold);
				if(strlen($interessenten_vz_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($interessenten_vz_sum);
				$worksheet->write($zeile,++$i,$interessenten_bb_sum, $format_bold);
				if(strlen($interessenten_bb_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($interessenten_bb_sum);
				$worksheet->write($zeile,++$i,$interessenten_dl_sum, $format_bold);
				if(strlen($interessenten_dl_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($interessenten_dl_sum);
				$worksheet->write($zeile,++$i,$interessenten_ddp_sum, $format_bold);
				if(strlen($interessenten_ddp_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($interessenten_ddp_sum);
				
				//Interessenten ZGV
				$worksheet->write($zeile,++$i,$interessentenzgv_vz_sum, $format_bold);
				if(strlen($interessentenzgv_vz_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($interessentenzgv_vz_sum);		
				$worksheet->write($zeile,++$i,$interessentenzgv_bb_sum, $format_bold);
				if(strlen($interessentenzgv_bb_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($interessentenzgv_bb_sum);
				$worksheet->write($zeile,++$i,$interessentenzgv_dl_sum, $format_bold);
				if(strlen($interessentenzgv_dl_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($interessentenzgv_dl_sum);
				$worksheet->write($zeile,++$i,$interessentenzgv_ddp_sum, $format_bold);
				if(strlen($interessentenzgv_ddp_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($interessentenzgv_ddp_sum);

				//Interessenten RT Anmeldung
				$worksheet->write($zeile,++$i,$interessentenrtanmeldung_vz_sum, $format_bold);
				if(strlen($interessentenrtanmeldung_vz_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($interessentenrtanmeldung_vz_sum);
				$worksheet->write($zeile,++$i,$interessentenrtanmeldung_bb_sum, $format_bold);
				if(strlen($interessentenrtanmeldung_bb_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($interessentenrtanmeldung_bb_sum);		
				$worksheet->write($zeile,++$i,$interessentenrtanmeldung_dl_sum, $format_bold);
				if(strlen($interessentenrtanmeldung_dl_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($interessentenrtanmeldung_dl_sum);
				$worksheet->write($zeile,++$i,$interessentenrtanmeldung_ddp_sum, $format_bold);
				if(strlen($interessentenrtanmeldung_ddp_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($interessentenrtanmeldung_ddp_sum);

				//Bewerber
				$worksheet->write($zeile,++$i,$bewerber_vz_sum, $format_bold);
				if(strlen($bewerber_vz_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($bewerber_vz_sum);		
				$worksheet->write($zeile,++$i,$bewerber_bb_sum, $format_bold);
				if(strlen($bewerber_bb_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($bewerber_bb_sum);		
				$worksheet->write($zeile,++$i,$bewerber_dl_sum, $format_bold);
				if(strlen($bewerber_dl_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($bewerber_dl_sum);
				$worksheet->write($zeile,++$i,$bewerber_ddp_sum, $format_bold);
				if(strlen($bewerber_ddp_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($bewerber_ddp_sum);
						
				//Aufgenommener
				$worksheet->write($zeile,++$i,$aufgenommener_vz_sum, $format_bold);
				if(strlen($aufgenommener_vz_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($aufgenommener_vz_sum);		
				$worksheet->write($zeile,++$i,$aufgenommener_bb_sum, $format_bold);
				if(strlen($aufgenommener_bb_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($aufgenommener_bb_sum);
				$worksheet->write($zeile,++$i,$aufgenommener_dl_sum, $format_bold);
				if(strlen($aufgenommener_dl_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($aufgenommener_dl_sum);
				$worksheet->write($zeile,++$i,$aufgenommener_ddp_sum, $format_bold);
				if(strlen($aufgenommener_ddp_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($aufgenommener_ddp_sum);
						
				//Aufgenommener Bereinigt
				$worksheet->write($zeile,++$i,$aufgenommenerber_vz_sum, $format_bold);
				if(strlen($aufgenommenerber_vz_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($aufgenommenerber_vz_sum);		
				$worksheet->write($zeile,++$i,$aufgenommenerber_bb_sum, $format_bold);
				if(strlen($aufgenommenerber_bb_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($aufgenommenerber_bb_sum);		
				$worksheet->write($zeile,++$i,$aufgenommenerber_dl_sum, $format_bold);
				if(strlen($aufgenommenerber_dl_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($aufgenommenerber_dl_sum);
				$worksheet->write($zeile,++$i,$aufgenommenerber_ddp_sum, $format_bold);
				if(strlen($aufgenommenerber_ddp_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($aufgenommenerber_ddp_sum);

				//Studenten 1. Semester
				$worksheet->write($zeile,++$i,$student1sem_vz_sum, $format_bold);
				if(strlen($student1sem_vz_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($student1sem_vz_sum);
				$worksheet->write($zeile,++$i,$student1sem_bb_sum, $format_bold);
				if(strlen($student1sem_bb_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($student1sem_bb_sum);
				$worksheet->write($zeile,++$i,$student1sem_dl_sum, $format_bold);
				if(strlen($student1sem_dl_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($student1sem_dl_sum);
				$worksheet->write($zeile,++$i,$student1sem_ddp_sum, $format_bold);
				if(strlen($student1sem_ddp_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($student1sem_ddp_sum);
						
				//Studenten 3. Semester
				$worksheet->write($zeile,++$i,$student3sem_vz_sum, $format_bold);
				if(strlen($student3sem_vz_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($student3sem_vz_sum);
				$worksheet->write($zeile,++$i,$student3sem_bb_sum, $format_bold);
				if(strlen($student3sem_bb_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($student3sem_bb_sum);
				$worksheet->write($zeile,++$i,$student3sem_dl_sum, $format_bold);
				if(strlen($student3sem_dl_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($student3sem_dl_sum);
				$worksheet->write($zeile,++$i,$student3sem_ddp_sum, $format_bold);
				if(strlen($student3sem_ddp_sum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($student3sem_ddp_sum);
			}
		}
		
		//Verteilung
		$zeile=$zeile+3;
		$i=0;
		$worksheet->write($zeile,0,'Verteilung'.$stsem, $format_bold);
		$worksheet->mergeCells($zeile,$i,$zeile,$i+1);

		$qry = "SELECT 
					count(anzahl) AS anzahlpers,anzahl AS anzahlstg 
				FROM
				(
					SELECT 
						count(*) AS anzahl
					FROM 
						public.tbl_person JOIN public.tbl_prestudent USING (person_id) 
						JOIN public.tbl_prestudentstatus USING (prestudent_id)
					WHERE 
						true $stgwhere AND studiengang_kz>0 AND studiengang_kz<10000
					GROUP BY 
						person_id,status_kurzbz,studiensemester_kurzbz
					HAVING 
						status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
				) AS prestd
				GROUP BY anzahl
				ORDER BY anzahl; ";
		
		$i=0;
		$worksheet->write(++$zeile,$i,"Personen", $format_bold);
		$maxlength[$i] = 10;
		$worksheet->write($zeile,++$i,"Stg", $format_bold);
		$maxlength[$i] = 5;
				
		
		if($db->db_query($qry))
		{
			$summestudenten=0;
			
			while($row = $db->db_fetch_object())
			{
				$i=0;
				$summestudenten += $row->anzahlpers;
				$format="format_alignc".$zeile%2;
				$worksheet->write(++$zeile,$i,$row->anzahlpers, $$format);
				if(strlen($row->anzahlpers)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->anzahlpers);
				$worksheet->write($zeile,++$i,$row->anzahlstg, $$format);
				if(strlen($row->anzahlstg)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->anzahlstg);
				
			}
			$i=0;
			$worksheet->write(++$zeile,$i,$summestudenten, $format_bold);
			if(strlen($summestudenten)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($summestudenten);
			$worksheet->write($zeile,++$i,"", $format_bold);
		}
		
		//Die Breite der Spalten setzen
		foreach($maxlength as $i=>$breite)
			$worksheet->setColumn($i, $i, $breite+2);

		//zweites Blatt mit Statistik des Vorjahres zum gleichen Datum
		if(!$mail)
		{
			$stgs = $rechte->getStgKz();
		
			if($stgs[0]=='')
				$stgwhere='';
			else 
			{
				$stgwhere=' AND studiengang_kz in(';
				foreach ($stgs as $stg)
					$stgwhere.="'$stg',";
				$stgwhere = mb_substr($stgwhere,0, mb_strlen($stgwhere)-1);
				$stgwhere.=' )';
			}
		}
		else 
			$stgwhere='';
		
		$stsem_obj = new studiensemester();
		$stsem = $stsem_obj->getPreviousFrom($stsem); // voriges semester
		$stsem = $stsem_obj->getPreviousFrom($stsem); // voriges jahr
		
		$datum = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')-1));
		$datum_obj = new datum();
		
		// Creating second worksheet
		$worksheet2 =& $workbook->addWorksheet("BewerberInnenstatistik ".$stsem. " (".$datum_obj->formatDatum($datum,'d.m.Y').")");
		$worksheet2->setInputEncoding('utf-8');	
				
		$j=0;
		$qry="SELECT prestudent_id FROM public.tbl_prestudentstatus WHERE status_kurzbz='Abgewiesener' AND studiensemester_kurzbz='$stsem' AND datum<='$datum'";
		if($result = $db->db_query($qry))
		{
			While ($row = $db->db_fetch_object($result))
			{
				$ausgeschieden[$j]=$row->prestudent_id;
				$j++;
			}
		}
		
		//Bewerberdaten holen
		$qry = "SELECT studiengang_kz, kurzbz, typ, kurzbzlang, bezeichnung, orgform_kurzbz,
	
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' 
		   			 	AND studiensemester_kurzbz='$stsem' AND datum<='$datum'
						) AS interessenten,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' 
		   			 	AND studiensemester_kurzbz='$stsem' AND geschlecht='m'  AND datum<='$datum'
						) AS interessenten_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' 
		   			 	AND studiensemester_kurzbz='$stsem' AND geschlecht='w'  AND datum<='$datum'
						) AS interessenten_w,
	
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND datum<='$datum'
		   			 	AND (anmeldungreihungstest<='$datum' AND anmeldungreihungstest IS NOT NULL)) AS interessentenrtanmeldung,
		   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m' AND datum<='$datum'
		   			 	AND (anmeldungreihungstest<='$datum' AND anmeldungreihungstest IS NOT NULL)) AS interessentenrtanmeldung_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='w' AND datum<='$datum'
		   			 	AND (anmeldungreihungstest<='$datum' AND anmeldungreihungstest IS NOT NULL)) AS interessentenrtanmeldung_w,
		   			    			 
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' 
		   				AND studiensemester_kurzbz='$stsem' AND datum<='$datum'
						) AS bewerber,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' 
		   				AND studiensemester_kurzbz='$stsem' AND geschlecht='m' AND datum<='$datum'
						) AS bewerber_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' 
		   				AND studiensemester_kurzbz='$stsem' AND geschlecht='w' AND datum<='$datum'
						) AS bewerber_w,
						
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' 
						AND studiensemester_kurzbz='$stsem' AND datum<='$datum'
						) AS aufgenommener,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' 
						AND studiensemester_kurzbz='$stsem' AND geschlecht='m' AND datum<='$datum'
						) AS aufgenommener_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' 
						AND studiensemester_kurzbz='$stsem' AND geschlecht='w' AND datum<='$datum'
						) AS aufgenommener_w,
						
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' 
						AND studiensemester_kurzbz='$stsem' AND datum<='$datum' ";
					if(count($ausgeschieden)>0)
					{
							$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
					}
						$qry.=") AS aufgenommenerber,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' 
						AND studiensemester_kurzbz='$stsem' AND geschlecht='m' AND datum<='$datum' ";
					if(count($ausgeschieden)>0)
					{
							$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
					}
						$qry.=") AS aufgenommenerber_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' 
						AND studiensemester_kurzbz='$stsem' AND geschlecht='w' AND datum<='$datum' ";
					if(count($ausgeschieden)>0)
					{
							$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
					}
						$qry.=") AS aufgenommenerber_w,
						
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' 
						AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1 AND datum<='$datum'
					) AS student1sem,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' 
						AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1 AND geschlecht='m' AND datum<='$datum'
					) AS student1sem_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' 
						AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1 AND geschlecht='w' AND datum<='$datum'
					) AS student1sem_w,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' 
						AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3 AND datum<='$datum'
					) AS student3sem,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' 
						AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3 AND geschlecht='m' AND datum<='$datum'
					) AS student3sem_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' 
						AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3 AND geschlecht='w' AND datum<='$datum'
					) AS student3sem_w
	
				FROM
					public.tbl_studiengang stg
				WHERE
					studiengang_kz>0 AND studiengang_kz<10000 AND aktiv $stgwhere
				ORDER BY typ, kurzbz; ";
		if($result = $db->db_query($qry))
		{
			//Überschriften 1.Zeile
			$i=0;
			$worksheet2->write(0,0,'BewerberInnenstatistik Details'.$stsem.', erstellt am '.date('d.m.Y'), $format_bold);
			$worksheet2->mergeCells(0,$i,0,$i+6);
			//Ueberschriften
			$i=0;
			$worksheet2->write(1,$i,"Studiengang", $format_bold);
			$maxlength[$i] = 15;
			$worksheet2->write(1,++$i,"InteressentInnen", $format_bold);
			$worksheet2->mergeCells(1,$i,1,$i+2);
			$i=$i+3;
			$worksheet2->write(1,$i,"InteressentInnen mit ZGV", $format_bold);
			$worksheet2->mergeCells(1,$i,1,$i+2);
			$i=$i+3;
			$worksheet2->write(1,$i,"InteressentInnen mit RT Anmeldung", $format_bold);
			$worksheet2->mergeCells(1,$i,1,$i+2);
			$i=$i+3;
			$worksheet2->write(1,$i,"BewerberInnen", $format_bold);
			$worksheet2->mergeCells(1,$i,1,$i+2);
			$i=$i+3;
			$worksheet2->write(1,$i,"Aufgenommene", $format_bold);
			$worksheet2->mergeCells(1,$i,1,$i+2);
			$i=$i+3;
			$worksheet2->write(1,$i,"Aufgenommene bereinigt", $format_bold);
			$worksheet2->mergeCells(1,$i,1,$i+2);
			$i=$i+3;
			$worksheet2->write(1,$i,"StudentIn 1S", $format_bold);
			$worksheet2->mergeCells(1,$i,1,$i+2);
			$i=$i+3;
			$worksheet2->write(1,$i,"StudentIn 3S", $format_bold);
			$worksheet2->mergeCells(1,$i,1,$i+2);
			
			//Überschriften 2.Zeile
			$i=0;
			$worksheet2->write(2,$i,"", $format_bold);
			$maxlength[$i] = 0;
			$worksheet2->write(2,++$i,"gesamt", $format_bold);
			$maxlength[$i] = 6;
			$worksheet2->write(2,++$i,"m", $format_bold);
			$maxlength[$i] = 3;
			$worksheet2->write(2,++$i,"w", $format_bold);
			$maxlength[$i] = 3;
			$worksheet2->write(2,++$i,"gesamt", $format_bold);
			$maxlength[$i] = 6;
			$worksheet2->write(2,++$i,"m", $format_bold);
			$maxlength[$i] = 3;
			$worksheet2->write(2,++$i,"w", $format_bold);
			$maxlength[$i] = 3;
			$worksheet2->write(2,++$i,"gesamt", $format_bold);
			$maxlength[$i] = 6;
			$worksheet2->write(2,++$i,"m", $format_bold);
			$maxlength[$i] = 3;
			$worksheet2->write(2,++$i,"w", $format_bold);
			$maxlength[$i] = 3;
			$worksheet2->write(2,++$i,"gesamt", $format_bold);
			$maxlength[$i] = 6;
			$worksheet2->write(2,++$i,"m", $format_bold);
			$maxlength[$i] = 3;
			$worksheet2->write(2,++$i,"w", $format_bold);
			$maxlength[$i] = 3;
			$worksheet2->write(2,++$i,"gesamt", $format_bold);
			$maxlength[$i] = 6;
			$worksheet2->write(2,++$i,"m", $format_bold);
			$maxlength[$i] = 3;
			$worksheet2->write(2,++$i,"w", $format_bold);
			$maxlength[$i] = 3;
			$worksheet2->write(2,++$i,"gesamt", $format_bold);
			$maxlength[$i] = 6;
			$worksheet2->write(2,++$i,"m", $format_bold);
			$maxlength[$i] = 3;
			$worksheet2->write(2,++$i,"w", $format_bold);
			$maxlength[$i] = 3;
			$worksheet2->write(2,++$i,"gesamt", $format_bold);
			$maxlength[$i] = 6;
			$worksheet2->write(2,++$i,"m", $format_bold);
			$maxlength[$i] = 3;
			$worksheet2->write(2,++$i,"w", $format_bold);
			$maxlength[$i] = 3;
			$worksheet2->write(2,++$i,"gesamt", $format_bold);
			$maxlength[$i] = 6;
			$worksheet2->write(2,++$i,"m", $format_bold);
			$maxlength[$i] = 3;
			$worksheet2->write(2,++$i,"w", $format_bold);
			$maxlength[$i] = 3;
			$interessenten_sum = 0;
			$interessenten_m_sum = 0;
			$interessenten_w_sum = 0;
			$interessentenzgv_sum = 0;
			$interessentenzgv_m_sum = 0;
			$interessentenzgv_w_sum = 0;
			$interessentenrtanmeldung_sum = 0;
			$interessentenrtanmeldung_m_sum = 0;
			$interessentenrtanmeldung_w_sum = 0;
			$bewerber_sum = 0;
			$bewerber_m_sum = 0;
			$bewerber_w_sum = 0;
			$aufgenommener_sum = 0;
			$aufgenommener_m_sum = 0;
			$aufgenommener_w_sum = 0;
			$aufgenommenerber_sum = 0;
			$aufgenommenerber_m_sum = 0;
			$aufgenommenerber_w_sum = 0;
			$student1sem_sum = 0;
			$student1sem_m_sum = 0;
			$student1sem_w_sum = 0;
			$student3sem_sum = 0;
			$student3sem_m_sum = 0;
			$student3sem_w_sum = 0;
			
			$zeile=3;
			while($row = $db->db_fetch_object($result))
			{
				$i=0;
				$format="format_alignl".$zeile%2;
				$worksheet2->write($zeile,$i,strtoupper($row->typ.$row->kurzbz)."(".($row->kurzbzlang).")", $$format);
				if(strlen(strtoupper($row->typ.$row->kurzbz).($row->kurzbzlang))>$maxlength[$i])
						$maxlength[$i] = mb_strlen(strtoupper($row->typ.$row->kurzbz).($row->kurzbzlang)." ");
				$format="format_alignc".$zeile%2;
				$worksheet2->write($zeile,++$i,$row->interessenten, $$format);
				if(strlen($row->interessenten)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->interessenten);
				$worksheet2->write($zeile,++$i,$row->interessenten_m, $$format);
				if(strlen($row->interessenten_m)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->interessenten_m);
				$worksheet2->write($zeile,++$i,$row->interessenten_w, $$format);
				if(strlen($row->interessenten_w)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->interessenten_w);
				$worksheet2->write($zeile,++$i,"k.A.", $$format);
				if(strlen("k.A.")>$maxlength[$i])
						$maxlength[$i] = mb_strlen("k.A.");
				$worksheet2->write($zeile,++$i,"k.A.", $$format);
				if(strlen("k.A.")>$maxlength[$i])
						$maxlength[$i] = mb_strlen("k.A.");	
				$worksheet2->write($zeile,++$i,"k.A.", $$format);
				if(strlen("k.A.")>$maxlength[$i])
						$maxlength[$i] = mb_strlen("k.A.");	
				$worksheet2->write($zeile,++$i,$row->interessentenrtanmeldung, $$format);
				if(strlen($row->interessentenrtanmeldung)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->interessentenrtanmeldung);	
				$worksheet2->write($zeile,++$i,$row->interessentenrtanmeldung_m, $$format);
				if(strlen($row->interessentenrtanmeldung_m)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->interessentenrtanmeldung_m);	
				$worksheet2->write($zeile,++$i,$row->interessentenrtanmeldung_w, $$format);
				if(strlen($row->interessentenrtanmeldung_w)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->interessentenrtanmeldung_w);		
				$worksheet2->write($zeile,++$i,$row->bewerber, $$format);
				if(strlen($row->bewerber)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->bewerber);		
				$worksheet2->write($zeile,++$i,$row->bewerber_m, $$format);
				if(strlen($row->bewerber_m)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->bewerber_m);		
				$worksheet2->write($zeile,++$i,$row->bewerber_w, $$format);
				if(strlen($row->bewerber_w)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->bewerber_w);
				$worksheet2->write($zeile,++$i,$row->aufgenommener, $$format);
				if(strlen($row->aufgenommener)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->aufgenommener);
				$worksheet2->write($zeile,++$i,$row->aufgenommener_m, $$format);
				if(strlen($row->aufgenommener_m)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->aufgenommener_m);
				$worksheet2->write($zeile,++$i,$row->aufgenommener_w, $$format);
				if(strlen($row->aufgenommener_w)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->aufgenommener_w);
				$worksheet2->write($zeile,++$i,$row->aufgenommenerber, $$format);
				if(strlen($row->aufgenommenerber)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->aufgenommenerber);
				$worksheet2->write($zeile,++$i,$row->aufgenommenerber_m, $$format);
				if(strlen($row->aufgenommenerber_m)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->aufgenommenerber_m);		
				$worksheet2->write($zeile,++$i,$row->aufgenommenerber_w, $$format);
				if(strlen($row->aufgenommenerber_w)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->aufgenommenerber_w);		
				$worksheet2->write($zeile,++$i,$row->student1sem, $$format);
				if(strlen($row->student1sem)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->student1sem);		
				$worksheet2->write($zeile,++$i,$row->student1sem_m, $$format);
				if(strlen($row->student1sem_m)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->student1sem_m);			
				$worksheet2->write($zeile,++$i,$row->student1sem_w, $$format);
				if(strlen($row->student1sem_w)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->student1sem_w);			
				$worksheet2->write($zeile,++$i,$row->student3sem, $$format);
				if(strlen($row->student3sem)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->student3sem);		
				$worksheet2->write($zeile,++$i,$row->student3sem_m, $$format);
				if(strlen($row->student3sem_m)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->student3sem_m);			
				$worksheet2->write($zeile,++$i,$row->student3sem_w, $$format);
				if(strlen($row->student3sem_w)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->student3sem_w);			
	
				$zeile++;
				
				//Summe berechnen
				$interessenten_sum += $row->interessenten;
				$interessenten_m_sum += $row->interessenten_m;
				$interessenten_w_sum += $row->interessenten_w;
				$interessentenrtanmeldung_sum += $row->interessentenrtanmeldung;
				$interessentenrtanmeldung_m_sum += $row->interessentenrtanmeldung_m;
				$interessentenrtanmeldung_w_sum += $row->interessentenrtanmeldung_w;
				$bewerber_sum += $row->bewerber;
				$bewerber_m_sum += $row->bewerber_m;
				$bewerber_w_sum += $row->bewerber_w;
				$aufgenommener_sum += $row->aufgenommener;
				$aufgenommener_m_sum += $row->aufgenommener_m;
				$aufgenommener_w_sum += $row->aufgenommener_w;
				$aufgenommenerber_sum += $row->aufgenommenerber;
				$aufgenommenerber_m_sum += $row->aufgenommenerber_m;
				$aufgenommenerber_w_sum += $row->aufgenommenerber_w;
				$student1sem_sum += $row->student1sem;
				$student1sem_m_sum += $row->student1sem_m;
				$student1sem_w_sum += $row->student1sem_w;
				$student3sem_sum += $row->student3sem;
				$student3sem_m_sum += $row->student3sem_m;
				$student3sem_w_sum += $row->student3sem_w;
			}
		
			$i=0;
			$worksheet2->write($zeile,$i,"Summe", $format_bold);
			if($maxlength[$i]<5)
					$maxlength[$i] = 5;
			$worksheet2->write($zeile,++$i,$interessenten_sum, $format_bold);
			if(strlen($interessenten_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($interessenten_sum);	
			$worksheet2->write($zeile,++$i,$interessenten_m_sum, $format_bold);
			if(strlen($interessenten_m_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($interessenten_m_sum);
			$worksheet2->write($zeile,++$i,$interessenten_w_sum, $format_bold);
			if(strlen($interessenten_w_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($interessenten_w_sum);		
			$worksheet2->write($zeile,++$i,"k.A.", $format_bold);
			if(strlen("k.A.")>$maxlength[$i])
					$maxlength[$i] = mb_strlen("k.A.");	
			$worksheet2->write($zeile,++$i,"k.A.", $format_bold);
			if(strlen("k.A.")>$maxlength[$i])
					$maxlength[$i] = mb_strlen("k.A.");
			$worksheet2->write($zeile,++$i,"k.A.", $format_bold);
			if(strlen("k.A.")>$maxlength[$i])
					$maxlength[$i] = mb_strlen("k.A.");		
			$worksheet2->write($zeile,++$i,$interessentenrtanmeldung_sum, $format_bold);
			if(strlen($interessentenrtanmeldung_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($interessentenrtanmeldung_sum);			
			$worksheet2->write($zeile,++$i,$interessentenrtanmeldung_m_sum, $format_bold);
			if(strlen($interessentenrtanmeldung_m_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($interessentenrtanmeldung_m_sum);	
			$worksheet2->write($zeile,++$i,$interessentenrtanmeldung_w_sum, $format_bold);
			if(strlen($interessentenrtanmeldung_w_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($interessentenrtanmeldung_w_sum);			
			$worksheet2->write($zeile,++$i,$bewerber_sum, $format_bold);
			if(strlen($bewerber_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($bewerber_sum);
			$worksheet2->write($zeile,++$i,$bewerber_m_sum, $format_bold);
			if(strlen($bewerber_m_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($bewerber_m_sum);
			$worksheet2->write($zeile,++$i,$bewerber_w_sum, $format_bold);
			if(strlen($bewerber_w_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($bewerber_w_sum);
			$worksheet2->write($zeile,++$i,$aufgenommener_sum, $format_bold);
			if(strlen($aufgenommener_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($aufgenommener_sum);
			$worksheet2->write($zeile,++$i,$aufgenommener_m_sum, $format_bold);
			if(strlen($aufgenommener_m_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($aufgenommener_m_sum);
			$worksheet2->write($zeile,++$i,$aufgenommener_w_sum, $format_bold);
			if(strlen($aufgenommener_w_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($aufgenommener_w_sum);		
			$worksheet2->write($zeile,++$i,$aufgenommenerber_sum, $format_bold);
			if(strlen($aufgenommenerber_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($aufgenommenerber_sum);
			$worksheet2->write($zeile,++$i,$aufgenommenerber_m_sum, $format_bold);
			if(strlen($aufgenommenerber_m_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($aufgenommenerber_m_sum);
			$worksheet2->write($zeile,++$i,$aufgenommenerber_w_sum, $format_bold);
			if(strlen($aufgenommenerber_w_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($aufgenommenerber_w_sum);		
			$worksheet2->write($zeile,++$i,$student1sem_sum, $format_bold);
			if(strlen($student1sem_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($student1sem_sum);
			$worksheet2->write($zeile,++$i,$student1sem_m_sum, $format_bold);
			if(strlen($student1sem_m_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($student1sem_m_sum);
			$worksheet2->write($zeile,++$i,$student1sem_w_sum, $format_bold);
			if(strlen($student1sem_w_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($student1sem_w_sum);
			$worksheet2->write($zeile,++$i,$student3sem_sum, $format_bold);
			if(strlen($student3sem_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($student3sem_sum);
			$worksheet2->write($zeile,++$i,$student3sem_m_sum, $format_bold);
			if(strlen($student3sem_m_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($student3sem_m_sum);
			$worksheet2->write($zeile,++$i,$student3sem_w_sum, $format_bold);
			if(strlen($student3sem_w_sum)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($student3sem_w_sum);		
		}
		//Verteilung
		$zeile=$zeile+3;
		$i=0;
		$worksheet2->write($zeile,0,'Verteilung'.$stsem, $format_bold);
		$worksheet2->mergeCells($zeile,$i,$zeile,$i+1);

		$qry = "SELECT 
					count(anzahl) AS anzahlpers,anzahl AS anzahlstg 
				FROM
				(
					SELECT 
						count(*) AS anzahl
					FROM 
						public.tbl_person JOIN public.tbl_prestudent USING (person_id) 
						JOIN public.tbl_prestudentstatus USING (prestudent_id)
					WHERE 
						true $stgwhere AND studiengang_kz>0 AND studiengang_kz<10000 AND datum<='$datum'
					GROUP BY 
						person_id,status_kurzbz,studiensemester_kurzbz
					HAVING 
						status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
				) AS prestd
				GROUP BY anzahl
				ORDER BY anzahl; ";
		$i=0;
		$worksheet2->write(++$zeile,$i,"Personen", $format_bold);
		$maxlength[$i] = 10;
		$worksheet2->write($zeile,++$i,"Stg", $format_bold);
		$maxlength[$i] = 5;	
		
		if($db->db_query($qry))
		{
			$summestudenten=0;
			
			while($row = $db->db_fetch_object())
			{
				$i=0;
				$summestudenten += $row->anzahlpers;
				$format="format_alignc".$zeile%2;
				$worksheet2->write(++$zeile,$i,$row->anzahlpers, $$format);
				if(strlen($row->anzahlpers)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->anzahlpers);
				$worksheet2->write($zeile,++$i,$row->anzahlstg, $$format);
				if(strlen($row->anzahlstg)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->anzahlstg);
				
			}
			$i=0;
			$worksheet2->write(++$zeile,$i,$summestudenten, $format_bold);
			if(strlen($summestudenten)>$maxlength[$i])
					$maxlength[$i] = mb_strlen($summestudenten);
			$worksheet2->write($zeile,++$i,"", $format_bold);
		}
		
		
		//Die Breite der Spalten setzen
		foreach($maxlength as $i=>$breite)
			$worksheet2->setColumn($i, $i, $breite+2);
		
		$workbook->close();
	}
}
else 
{
	$content='';
	
	$content.= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
	if($mail)
	{
		//Wenn die Statistik per Mail versandt wird, wird das CSS File direkt mitgeliefert
		$content.='<style>';
		$content.= file_get_contents('../../skin/vilesci.css');
		$content.='</style>';
	}
	else 
	{
		$content.='	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">';
	}
	$content.='
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	</head>
		<body>';
	if($mail)
	{
		//im Kopf des Mails Links zu den anderen Statistiken anzeigen
		$content.='Dies ist ein automatisches Mail!<br><br>';
		$content.='<b>Links zu den Statistiken:</b><br>
		- <a href="'.APP_ROOT.'content/statistik/lektorenstatistik.php" target="_blank">LektorInnenstatisitk</a><br>
		- <a href="'.APP_ROOT.'content/statistik/mitarbeiterstatistik.php" target="_blank">MitarbeiterInnenstatistik</a><br>
		- <a href="'.APP_ROOT.'content/statistik/bewerberstatistik.php" target="_blank">BewerberInnenstatistik</a><br>
		- <a href="'.APP_ROOT.'content/statistik/studentenstatistik.php" target="_blank">Studierendenstatistik</a><br>
		- <a href="'.APP_ROOT.'content/statistik/absolventenstatistik.php" target="_blank">AbsolventInnenstatistik</a><br><br>
		';
	}
	
	
	//Details fuer einen bestimmten Studiengang anzeigen
	if(isset($_GET['showdetails']))
	{
		$studiengang_kz  = $_GET['studiengang_kz'];
		$stgwhere = " AND studiengang_kz='".addslashes($studiengang_kz)."'";
		
		$stg_obj = new studiengang();
		if(!$stg_obj->load($studiengang_kz))
			die('Studiengang existiert nicht');
	
		$content.='
		<h2>BewerberInnenstatistik Details - '.$stg_obj->kuerzel.' '.$stsem.'<span style="position:absolute; right:15px;">'.date('d.m.Y').'</span></h2><br>
		';
		$content.='<center><iframe src="bewerberstatistik.svg.php?stsem='.$stsem.'&studiengang_kz='.$studiengang_kz.'" width="500" height="500" ></iframe></center>';
		
		$hlp=array();
		//Aufmerksamdurch (Prestudent)
		$content.= '<br><h2>Aufmerksam durch (PrestudentIn)</h2><br>';
		$qry = "SELECT beschreibung, COALESCE(a.anzahl,0) as anzahl
				FROM public.tbl_aufmerksamdurch LEFT JOIN 
					(SELECT aufmerksamdurch_kurzbz, count(*) as anzahl 
					FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING(prestudent_id) 
					WHERE studiensemester_kurzbz='".addslashes($stsem)."' AND studiengang_kz='".addslashes($studiengang_kz)."' 
					GROUP BY aufmerksamdurch_kurzbz) as a USING(aufmerksamdurch_kurzbz) 
				";
		$content.= "\n<table class='liste table-stripeclass:alternate table-autostripe' style='width:auto'>
					<thead>
						<tr>
							<th>Aufmerksam durch</th>
							<th>Anzahl</th>
						</tr>
					</thead>
					<tbody>";
		
		if($db->db_query($qry))
		{
			while($row = $db->db_fetch_object())
			{
				$content.='<tr>';
				$content.="<td>$row->beschreibung</td>";
				$content.="<td>$row->anzahl</td>";
				$content.='</tr>';
			}
		}
	
		$content.='</tbody></table>';	
		
		//Berufstaetigkeit
		$content.= '<br><h2>Berufst&auml;tigkeit</h2><br>';
		$qry = "SELECT berufstaetigkeit_bez, COALESCE(a.anzahl,0) as anzahl
				FROM bis.tbl_berufstaetigkeit LEFT JOIN 
					(SELECT berufstaetigkeit_code, count(*) as anzahl 
					FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING(prestudent_id) 
					WHERE studiensemester_kurzbz='".addslashes($stsem)."' AND studiengang_kz='".addslashes($studiengang_kz)."' 
					GROUP BY berufstaetigkeit_code) as a USING(berufstaetigkeit_code) 
				";
	
		$content.= "\n<table class='liste table-stripeclass:alternate table-autostripe' style='width:auto'>
					<thead>
						<tr>
							<th>Berufst&auml;tigkeit</th>
							<th>Anzahl</th>
						</tr>
					</thead>
					<tbody>";
		if($db->db_query($qry))
		{
			while($row = $db->db_fetch_object())
			{
				$content.='<tr>';
				$content.="<td>$row->berufstaetigkeit_bez</td>";
				$content.="<td>$row->anzahl</td>";
				$content.='</tr>';
			}
		}
		
		$content.='</tbody></table>';	
		
		echo $content;
		echo '</body></html>';
		exit;
	}
	
	$content.='
		<h2>BewerberInnenstatistik '.$stsem.'<span style="position:absolute; right:15px;">'.date('d.m.Y').'</span></h2><br>
		';
	if($stsem!='')
	{
		$content.= "<a href='".$_SERVER['PHP_SELF']."?stsem=$stsem&excel=true'>Excel Export</a>";
	}
	if(!$mail)
	{
		$content.= '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">Studiensemester: <SELECT name="stsem">';
		$studsem = new studiensemester();
		$studsem->getAll();
	
		foreach ($studsem->studiensemester as $stsemester)
		{
			if($stsemester->studiensemester_kurzbz==$stsem)
				$selected='selected';
			else 
				$selected='';
			
			$content.= '<option value="'.$stsemester->studiensemester_kurzbz.'" '.$selected.'>'.$stsemester->studiensemester_kurzbz.' </option>';
		}
		$content.= '</SELECT>
			<input type="submit" value="Anzeigen" /></form><br><br>';
	}
	
	if($stsem!='')
	{
		if(!$mail)
		{
			$stgs = $rechte->getStgKz();
		
			if($stgs[0]=='')
				$stgwhere='';
			else 
			{
				$stgwhere=' AND studiengang_kz in(';
				foreach ($stgs as $stg)
					$stgwhere.="'$stg',";
				$stgwhere = mb_substr($stgwhere,0, mb_strlen($stgwhere)-1);
				$stgwhere.=' )';
			}
		}
		else 
			$stgwhere='';
		
		$i=0;
		$qry="SELECT prestudent_id FROM public.tbl_prestudentstatus WHERE status_kurzbz='Abgewiesener' AND studiensemester_kurzbz='$stsem'";
		if($result = $db->db_query($qry))
		{
			While ($row = $db->db_fetch_object($result))
			{
				$ausgeschieden[$i]=$row->prestudent_id;
				$i++;
			}
		}
		//echo $qry;
		//var_dump($ausgeschieden);
			
		//Bewerberdaten holen
		$qry = "SELECT studiengang_kz, kurzbz, typ, kurzbzlang, bezeichnung, orgform_kurzbz,
	
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
						) AS interessenten,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
						) AS interessenten_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
						) AS interessenten_w,
					
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   				AND ((stg.typ<>'m' AND zgv_code IS NOT NULL) OR zgvmas_code IS NOT NULL)) AS interessentenzgv,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
		   				AND ((stg.typ<>'m' AND zgv_code IS NOT NULL) OR zgvmas_code IS NOT NULL)) AS interessentenzgv_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
		   				AND ((stg.typ<>'m' AND zgv_code IS NOT NULL) OR zgvmas_code IS NOT NULL)) AS interessentenzgv_w,
		   				   			
		   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND anmeldungreihungstest IS NOT NULL) AS interessentenrtanmeldung,
		   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
		   			 	AND anmeldungreihungstest IS NOT NULL) AS interessentenrtanmeldung_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
		   			 	AND anmeldungreihungstest IS NOT NULL) AS interessentenrtanmeldung_w,
		   				   			 
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem'
						) AS bewerber,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
						) AS bewerber_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
						) AS bewerber_w,
						
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
						) AS aufgenommener,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
						) AS aufgenommener_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
						) AS aufgenommener_w,
										
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' ";
					if(count($ausgeschieden)>0)
					{
							$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')     ";
					}
						$qry.=") AS aufgenommenerber,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' AND geschlecht='m' ";
					if(count($ausgeschieden)>0)
					{
							$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
					}
						$qry.=") AS aufgenommenerber_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' AND geschlecht='w' ";
					if(count($ausgeschieden)>0)
					{
							$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
					}
						$qry.=") AS aufgenommenerber_w, 
							
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_student USING(prestudent_id) JOIN public.tbl_benutzer ON(uid=student_uid)
						WHERE tbl_prestudent.studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1 AND tbl_benutzer.aktiv
					) AS student1sem,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN public.tbl_student USING(prestudent_id) JOIN public.tbl_benutzer ON(uid=student_uid)
						WHERE tbl_prestudent.studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1 AND geschlecht='m' AND tbl_benutzer.aktiv
					) AS student1sem_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN public.tbl_student USING(prestudent_id) JOIN public.tbl_benutzer ON(uid=student_uid)
						WHERE tbl_prestudent.studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1 AND geschlecht='w' AND tbl_benutzer.aktiv
					) AS student1sem_w,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_student USING(prestudent_id) JOIN public.tbl_benutzer ON(uid=student_uid)
						WHERE tbl_prestudent.studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3 AND tbl_benutzer.aktiv
					) AS student3sem,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN public.tbl_student USING(prestudent_id) JOIN public.tbl_benutzer ON(uid=student_uid)
						WHERE tbl_prestudent.studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3 AND geschlecht='m' AND tbl_benutzer.aktiv
					) AS student3sem_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN public.tbl_student USING(prestudent_id) JOIN public.tbl_benutzer ON(student_uid=uid)
						WHERE tbl_prestudent.studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3 AND geschlecht='w' AND tbl_benutzer.aktiv
					) AS student3sem_w
	
				FROM
					public.tbl_studiengang stg
				WHERE
					studiengang_kz>0 AND studiengang_kz<10000 AND aktiv $stgwhere
				ORDER BY typ, kurzbz; ";
	
		if($result = $db->db_query($qry))
		{
			$content.= "\n<table class='liste table-autosort:0 table-stripeclass:alternate table-autostripe'>
					<thead>
						<tr>
							<th class='table-sortable:default'>Studiengang</th>
							<th class='table-sortable:numeric'>InteressentInnen (m/w)</th>
							<th class='table-sortable:numeric'>InteressentInnen mit ZGV (m/w)</th>
							<th class='table-sortable:numeric'>InteressentInnen mit RT Anmeldung (m/w)</th>
							<th class='table-sortable:numeric'>BewerberIn (m/w)</th>
							<th class='table-sortable:numeric'>Aufgenommene (m/w)</th>
							<th class='table-sortable:numeric'>Aufgenommene bereinigt (m/w)</th>
							<th class='table-sortable:numeric'>StudentIn 1S (m/w)</th>
							<th class='table-sortable:numeric'>StudentIn 3S (m/w)</th>
						</tr>
					</thead>
					<tbody>
				 ";
			$interessenten_sum = 0;
			$interessenten_m_sum = 0;
			$interessenten_w_sum = 0;
			$interessentenzgv_sum = 0;
			$interessentenzgv_m_sum = 0;
			$interessentenzgv_w_sum = 0;
			$interessentenrtanmeldung_sum = 0;
			$interessentenrtanmeldung_m_sum = 0;
			$interessentenrtanmeldung_w_sum = 0;
			$bewerber_sum = 0;
			$bewerber_m_sum = 0;
			$bewerber_w_sum = 0;
			$aufgenommener_sum = 0;
			$aufgenommener_m_sum = 0;
			$aufgenommener_w_sum = 0;
			$aufgenommenerber_sum = 0;
			$aufgenommenerber_m_sum = 0;
			$aufgenommenerber_w_sum = 0;
			$student1sem_sum = 0;
			$student1sem_m_sum = 0;
			$student1sem_w_sum = 0;
			$student3sem_sum = 0;
			$student3sem_m_sum = 0;
			$student3sem_w_sum = 0;
			
			while($row = $db->db_fetch_object($result))
			{
				$content.= "\n";
				$content.= '<tr>';
				$content.= "<td><a href='".APP_ROOT."content/statistik/bewerberstatistik.php?showdetails=true&studiengang_kz=$row->studiengang_kz&stsem=$stsem'>".strtoupper($row->typ.$row->kurzbz)." ($row->kurzbzlang)</a></td>";
				$content.= "<td align='center'>$row->interessenten ($row->interessenten_m / $row->interessenten_w)</td>";
				$content.= "<td align='center'>$row->interessentenzgv ($row->interessentenzgv_m / $row->interessentenzgv_w)</td>";
				$content.= "<td align='center'>$row->interessentenrtanmeldung ($row->interessentenrtanmeldung_m / $row->interessentenrtanmeldung_w)</td>";
				$content.= "<td align='center'>$row->bewerber ($row->bewerber_m / $row->bewerber_w)</td>";
				$content.= "<td align='center'>$row->aufgenommener ($row->aufgenommener_m / $row->aufgenommener_w)</td>";
				$content.= "<td align='center'>$row->aufgenommenerber ($row->aufgenommenerber_m / $row->aufgenommenerber_w)</td>";
				$content.= "<td align='center'>$row->student1sem ($row->student1sem_m / $row->student1sem_w)</td>";
				$content.= "<td align='center'>$row->student3sem ($row->student3sem_m / $row->student3sem_w)</td>";
				$content.= "</tr>";
				
				//Summe berechnen
				$interessenten_sum += $row->interessenten;
				$interessenten_m_sum += $row->interessenten_m;
				$interessenten_w_sum += $row->interessenten_w;
				$interessentenzgv_sum += $row->interessentenzgv;
				$interessentenzgv_m_sum += $row->interessentenzgv_m;
				$interessentenzgv_w_sum += $row->interessentenzgv_w;
				$interessentenrtanmeldung_sum += $row->interessentenrtanmeldung;
				$interessentenrtanmeldung_m_sum += $row->interessentenrtanmeldung_m;
				$interessentenrtanmeldung_w_sum += $row->interessentenrtanmeldung_w;
				$bewerber_sum += $row->bewerber;
				$bewerber_m_sum += $row->bewerber_m;
				$bewerber_w_sum += $row->bewerber_w;
				$aufgenommener_sum += $row->aufgenommener;
				$aufgenommener_m_sum += $row->aufgenommener_m;
				$aufgenommener_w_sum += $row->aufgenommener_w;
				$aufgenommenerber_sum += $row->aufgenommenerber;
				$aufgenommenerber_m_sum += $row->aufgenommenerber_m;
				$aufgenommenerber_w_sum += $row->aufgenommenerber_w;
				$student1sem_sum += $row->student1sem;
				$student1sem_m_sum += $row->student1sem_m;
				$student1sem_w_sum += $row->student1sem_w;
				$student3sem_sum += $row->student3sem;
				$student3sem_m_sum += $row->student3sem_m;
				$student3sem_w_sum += $row->student3sem_w;
			}
			
			$content.= "\n";
			$content.= '</tbody><tfoot style="font-weight: bold;"><tr>';
			$content.= "<td>Summe</td>";
			$content.= "<td align='center'>$interessenten_sum ($interessenten_m_sum / $interessenten_w_sum)</td>";
			$content.= "<td align='center'>$interessentenzgv_sum ($interessentenzgv_m_sum / $interessentenzgv_w_sum)</td>";
			$content.= "<td align='center'>$interessentenrtanmeldung_sum ($interessentenrtanmeldung_m_sum / $interessentenrtanmeldung_w_sum)</td>";
			$content.= "<td align='center'>$bewerber_sum ($bewerber_m_sum / $bewerber_w_sum)</td>";
			$content.= "<td align='center'>$aufgenommener_sum ($aufgenommener_m_sum / $aufgenommener_w_sum)</td>";
			$content.= "<td align='center'>$aufgenommenerber_sum ($aufgenommenerber_m_sum / $aufgenommenerber_w_sum)</td>";
			$content.= "<td align='center'>$student1sem_sum ($student1sem_m_sum / $student1sem_w_sum)</td>";
			$content.= "<td align='center'>$student3sem_sum ($student3sem_m_sum / $student3sem_w_sum)</td>";
			$content.= "</tr>";
			
			$content.= '</tfoot></table>';
		}
		
		//Aufsplittungen für Mischformen holen
		$qry = "SELECT studiengang_kz, kurzbz, typ, kurzbzlang, bezeichnung, orgform_kurzbz,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND orgform_kurzbz='VZ'
						) AS interessenten_vz,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND orgform_kurzbz='BB'
						) AS interessenten_bb,	
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND orgform_kurzbz='DL'
						) AS interessenten_dl,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND orgform_kurzbz='DDP'
						) AS interessenten_ddp,	
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   				AND ((stg.typ<>'m' AND zgv_code IS NOT NULL) OR zgvmas_code IS NOT NULL) AND orgform_kurzbz='BB') AS interessentenzgv_bb,
		   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   				AND ((stg.typ<>'m' AND zgv_code IS NOT NULL) OR zgvmas_code IS NOT NULL) AND orgform_kurzbz='VZ') AS interessentenzgv_vz,
		   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   				AND ((stg.typ<>'m' AND zgv_code IS NOT NULL) OR zgvmas_code IS NOT NULL) AND orgform_kurzbz='DL') AS interessentenzgv_dl,
		   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   				AND ((stg.typ<>'m' AND zgv_code IS NOT NULL) OR zgvmas_code IS NOT NULL) AND orgform_kurzbz='DDP') AS interessentenzgv_ddp,
	
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND anmeldungreihungstest IS NOT NULL AND orgform_kurzbz='VZ') AS interessentenrtanmeldung_vz,
		   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND anmeldungreihungstest IS NOT NULL AND orgform_kurzbz='BB') AS interessentenrtanmeldung_bb,
		   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND anmeldungreihungstest IS NOT NULL AND orgform_kurzbz='DL') AS interessentenrtanmeldung_dl,
		   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND anmeldungreihungstest IS NOT NULL AND orgform_kurzbz='DDP') AS interessentenrtanmeldung_ddp,
	
		   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND reihungstest_id IS NOT NULL  AND orgform_kurzbz='BB') AS interessentenrttermin_bb,
		   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND reihungstest_id IS NOT NULL  AND orgform_kurzbz='VZ') AS interessentenrttermin_vz,
		   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND reihungstest_id IS NOT NULL  AND orgform_kurzbz='DL') AS interessentenrttermin_dl,
		   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND reihungstest_id IS NOT NULL  AND orgform_kurzbz='DDP') AS interessentenrttermin_ddp,
		   			 	
		   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND reihungstestangetreten AND orgform_kurzbz='VZ') AS interessentenrtabsolviert_vz,
		   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND reihungstestangetreten AND orgform_kurzbz='BB') AS interessentenrtabsolviert_bb,
		   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND reihungstestangetreten AND orgform_kurzbz='DL') AS interessentenrtabsolviert_dl,
		   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
		   			 	AND reihungstestangetreten AND orgform_kurzbz='DDP') AS interessentenrtabsolviert_ddp,
		   			 	
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem'
						AND orgform_kurzbz='BB') AS bewerber_bb,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem'
						AND orgform_kurzbz='VZ') AS bewerber_vz,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem'
						AND orgform_kurzbz='DL') AS bewerber_dl,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem'
						AND orgform_kurzbz='DDP') AS bewerber_ddp,
						
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
						AND orgform_kurzbz='VZ') AS aufgenommener_vz,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
						AND orgform_kurzbz='BB') AS aufgenommener_bb,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
						AND orgform_kurzbz='DL') AS aufgenommener_dl,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
						AND orgform_kurzbz='DDP') AS aufgenommener_ddp,
						
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' ";
					if(count($ausgeschieden)>0)
					{
							$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
					}
						$qry.="AND orgform_kurzbz='VZ') AS aufgenommenerber_vz,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' ";
					if(count($ausgeschieden)>0)
					{
							$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
					}
						$qry.="AND orgform_kurzbz='BB') AS aufgenommenerber_bb,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' ";
					if(count($ausgeschieden)>0)
					{
							$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
					}
						$qry.="AND orgform_kurzbz='DL') AS aufgenommenerber_dl,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' ";
					if(count($ausgeschieden)>0)
					{
							$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
					}
						$qry.="AND orgform_kurzbz='DDP') AS aufgenommenerber_ddp,
	
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
						AND orgform_kurzbz='BB') AS student1sem_bb,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
						AND orgform_kurzbz='VZ') AS student1sem_vz,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
						AND orgform_kurzbz='DL') AS student1sem_dl,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
						AND orgform_kurzbz='DDP') AS student1sem_ddp,
						
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3
						AND orgform_kurzbz='BB') AS student3sem_bb,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3
						AND orgform_kurzbz='VZ') AS student3sem_vz,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3
						AND orgform_kurzbz='DL') AS student3sem_dl,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3
						AND orgform_kurzbz='DDP') AS student3sem_ddp
				FROM
					public.tbl_studiengang stg
				WHERE
					studiengang_kz>0 AND studiengang_kz<10000 AND aktiv $stgwhere AND stg.mischform=true
				ORDER BY kurzbzlang; ";
	
		if($result = $db->db_query($qry))
		{
			if($db->db_num_rows($result)>0)
			{
				$content.= "<br><br><h2>Aufsplittung Mischformen</h2><br>";
				$content.= "\n<table class='liste table-autosort:0 table-stripeclass:alternate table-autostripe'>
						<thead>
							<tr>
								<th class='table-sortable:default'>Studiengang</th>
								<th class='table-sortable:numeric'>InteressentInnen VZ / BB / DL / DDP</th>
								<th class='table-sortable:numeric'>InteressentInnen mit ZGV VZ / BB / DL / DDP</th>
								<th class='table-sortable:numeric'>InteressentInnen mit RT Anmeldung VZ / BB / DL / DDP</th>
								<th class='table-sortable:numeric'>BewerberInnen 1S VZ / BB / DL / DDP</th>
								<th class='table-sortable:numeric'>Aufgenommene VZ / BB / DL / DDP</th>
								<th class='table-sortable:numeric'>Aufgenommene bereinigt VZ / BB / DL / DDP</th>
								<th class='table-sortable:numeric'>StudentIn 1S VZ / BB / DL / DDP</th>
								<th class='table-sortable:numeric'>StudentIn 3S VZ / BB / DL / DDP</th>
							</tr>
						</thead>
						<tbody>
					 ";
				
				$interessenten_vz_sum = 0;
				$interessenten_bb_sum = 0;
				$interessenten_dl_sum = 0;
				$interessenten_ddp_sum = 0;
				$interessentenzgv_vz_sum = 0;
				$interessentenzgv_bb_sum = 0;
				$interessentenzgv_dl_sum = 0;
				$interessentenzgv_ddp_sum = 0;
				$interessentenrtanmeldung_vz_sum = 0;
				$interessentenrtanmeldung_bb_sum = 0;
				$interessentenrtanmeldung_dl_sum = 0;
				$interessentenrtanmeldung_ddp_sum = 0;
				$bewerber_vz_sum = 0;
				$bewerber_bb_sum = 0;
				$bewerber_dl_sum = 0;
				$bewerber_ddp_sum = 0;
				$aufgenommener_vz_sum = 0;
				$aufgenommener_bb_sum = 0;
				$aufgenommener_dl_sum = 0;
				$aufgenommener_ddp_sum = 0;
				$aufgenommenerber_vz_sum = 0;
				$aufgenommenerber_bb_sum = 0;
				$aufgenommenerber_dl_sum = 0;
				$aufgenommenerber_ddp_sum = 0;
				$student1sem_vz_sum = 0;
				$student1sem_bb_sum = 0;
				$student1sem_dl_sum = 0;
				$student1sem_ddp_sum = 0;
				$student3sem_vz_sum = 0;
				$student3sem_bb_sum = 0;
				$student3sem_dl_sum = 0;
				$student3sem_ddp_sum = 0;
				
				while($row = $db->db_fetch_object($result))
				{
					$content.= "\n";
					$content.= '<tr>';
					$content.= "<td>".mb_strtoupper($row->typ.$row->kurzbz)." ($row->kurzbzlang)</td>";
					$content.= "<td align='center'>$row->interessenten_vz / $row->interessenten_bb / $row->interessenten_dl / $row->interessenten_ddp</td>";
					$content.= "<td align='center'>$row->interessentenzgv_vz / $row->interessentenzgv_bb / $row->interessentenzgv_dl / $row->interessentenzgv_ddp</td>";
					$content.= "<td align='center'>$row->interessentenrtanmeldung_vz / $row->interessentenrtanmeldung_bb / $row->interessentenrtanmeldung_dl / $row->interessentenrtanmeldung_ddp</td>";
					$content.= "<td align='center'>$row->bewerber_vz / $row->bewerber_bb / $row->bewerber_dl / $row->bewerber_ddp</td>";
					$content.= "<td align='center'>$row->aufgenommener_vz / $row->aufgenommener_bb / $row->aufgenommener_dl / $row->aufgenommener_ddp</td>";
					$content.= "<td align='center'>$row->aufgenommenerber_vz / $row->aufgenommenerber_bb / $row->aufgenommenerber_dl / $row->aufgenommenerber_ddp</td>";
					$content.= "<td align='center'>$row->student1sem_vz / $row->student1sem_bb / $row->student1sem_dl / $row->student1sem_ddp</td>";
					$content.= "<td align='center'>$row->student3sem_vz / $row->student3sem_bb / $row->student3sem_dl / $row->student3sem_ddp</td>";
					$content.= "</tr>";
					
					//Summe berechnen
					$interessenten_vz_sum += $row->interessenten_vz;
					$interessenten_bb_sum += $row->interessenten_bb;
					$interessenten_dl_sum += $row->interessenten_dl;
					$interessenten_ddp_sum += $row->interessenten_ddp;
					$interessentenzgv_vz_sum += $row->interessentenzgv_vz;
					$interessentenzgv_bb_sum += $row->interessentenzgv_bb;
					$interessentenzgv_dl_sum += $row->interessentenzgv_dl;
					$interessentenzgv_ddp_sum += $row->interessentenzgv_ddp;
					$interessentenrtanmeldung_vz_sum += $row->interessentenrtanmeldung_vz;
					$interessentenrtanmeldung_bb_sum += $row->interessentenrtanmeldung_bb;
					$interessentenrtanmeldung_dl_sum += $row->interessentenrtanmeldung_dl;
					$interessentenrtanmeldung_ddp_sum += $row->interessentenrtanmeldung_ddp;
					$bewerber_vz_sum += $row->bewerber_vz;
					$bewerber_bb_sum += $row->bewerber_bb;
					$bewerber_dl_sum += $row->bewerber_dl;
					$bewerber_ddp_sum += $row->bewerber_ddp;
					$aufgenommener_vz_sum += $row->aufgenommener_vz;
					$aufgenommener_bb_sum += $row->aufgenommener_bb;
					$aufgenommener_dl_sum += $row->aufgenommener_dl;
					$aufgenommener_ddp_sum += $row->aufgenommener_ddp;
					$aufgenommenerber_vz_sum += $row->aufgenommenerber_vz;
					$aufgenommenerber_bb_sum += $row->aufgenommenerber_bb;
					$aufgenommenerber_dl_sum += $row->aufgenommenerber_dl;
					$aufgenommenerber_ddp_sum += $row->aufgenommenerber_ddp;
					$student1sem_vz_sum += $row->student1sem_vz;
					$student1sem_bb_sum += $row->student1sem_bb;
					$student1sem_dl_sum += $row->student1sem_dl;
					$student1sem_ddp_sum += $row->student1sem_ddp;
					$student3sem_vz_sum += $row->student3sem_vz;
					$student3sem_bb_sum += $row->student3sem_bb;
					$student3sem_dl_sum += $row->student3sem_dl;
					$student3sem_ddp_sum += $row->student3sem_ddp;
				}
				$content.= "\n";
				$content.= '</tbody><tfoot style="font-weight: bold;"><tr>';
				$content.= "<td>Summe</td>";
				$content.= "<td align='center'>$interessenten_vz_sum / $interessenten_bb_sum / $interessenten_dl_sum / $interessenten_ddp_sum</td>";
				$content.= "<td align='center'>$interessentenzgv_vz_sum / $interessentenzgv_bb_sum / $interessentenzgv_dl_sum / $interessentenzgv_ddp_sum</td>";
				$content.= "<td align='center'>$interessentenrtanmeldung_vz_sum / $interessentenrtanmeldung_bb_sum / $interessentenrtanmeldung_dl_sum / $interessentenrtanmeldung_ddp_sum</td>";
				$content.= "<td align='center'>$bewerber_vz_sum / $bewerber_bb_sum / $bewerber_dl_sum / $bewerber_ddp_sum</td>";
				$content.= "<td align='center'>$aufgenommener_vz_sum / $aufgenommener_bb_sum / $aufgenommener_dl_sum / $aufgenommener_ddp_sum</td>";
				$content.= "<td align='center'>$aufgenommenerber_vz_sum / $aufgenommenerber_bb_sum / $aufgenommenerber_dl_sum / $aufgenommenerber_ddp_sum</td>";
				$content.= "<td align='center'>$student1sem_vz_sum / $student1sem_bb_sum / $student1sem_dl_sum / $student1sem_ddp_sum</td>";
				$content.= "<td align='center'>$student3sem_vz_sum / $student3sem_bb_sum / $student3sem_dl_sum / $student3sem_ddp_sum</td>";
				$content.= "</tfoot></tr>";
				$content.= '</table>';
			}
		}
		
		//Verteilung
		$content.= '<br><h2>Verteilung '.$stsem.'</h2><br>';
		$qry = "SELECT 
					count(anzahl) AS anzahlpers,anzahl AS anzahlstg 
				FROM
				(
					SELECT 
						count(*) AS anzahl
					FROM 
						public.tbl_person JOIN public.tbl_prestudent USING (person_id) 
						JOIN public.tbl_prestudentstatus USING (prestudent_id)
					WHERE 
						true $stgwhere AND studiengang_kz>0 AND studiengang_kz<10000
					GROUP BY 
						person_id,status_kurzbz,studiensemester_kurzbz
					HAVING 
						status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
				) AS prestd
				GROUP BY anzahl
				ORDER BY anzahl; ";
	
		$content.= "\n<table class='liste table-stripeclass:alternate table-autostripe' style='width:auto'>
					<thead>
						<tr>
							<th>Personen</th>
							<th>Stg</th>
						</tr>
					</thead>
					<tbody>";
		if($db->db_query($qry))
		{
			$summestudenten=0;
			
			while($row = $db->db_fetch_object())
			{
				$summestudenten += $row->anzahlpers;
				$content.= "\n<tr><td>$row->anzahlpers</td><td>$row->anzahlstg</td></tr>";
			}
			$content.= "<tr><td style='border-top: 1px solid black;'><b>$summestudenten</b></td><td></td></tr>";
		}
		$content.= '</tbody></table>';
		
		// Bewerberstatistik fuer Vorjahr (selbes Datum)
		if(!$mail)
		{
			$stgs = $rechte->getStgKz();
		
			if($stgs[0]=='')
				$stgwhere='';
			else 
			{
				$stgwhere=' AND studiengang_kz in(';
				foreach ($stgs as $stg)
					$stgwhere.="'$stg',";
				$stgwhere = mb_substr($stgwhere,0, mb_strlen($stgwhere)-1);
				$stgwhere.=' )';
			}
		}
		else 
			$stgwhere='';
		
		$stsem_obj = new studiensemester();
		$stsem = $stsem_obj->getPreviousFrom($stsem); // voriges semester
		$stsem = $stsem_obj->getPreviousFrom($stsem); // voriges jahr
		
		$datum = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')-1));
		$datum_obj = new datum();
		
		$i=0;
		$qry="SELECT prestudent_id FROM public.tbl_prestudentstatus WHERE status_kurzbz='Abgewiesener' AND studiensemester_kurzbz='$stsem' AND datum<='$datum'";
		if($result = $db->db_query($qry))
		{
			While ($row = $db->db_fetch_object($result))
			{
				$ausgeschieden[$i]=$row->prestudent_id;
				$i++;
			}
		}
		//echo $qry;
		//var_dump($ausgeschieden);
		
		$content.='
		<br><br>
		<h2>BewerberInnenstatistik '.$stsem.'<span style="position:absolute; right:15px;">'.$datum_obj->formatDatum($datum,'d.m.Y').'</span></h2><br>
		';
		//Bewerberdaten holen
		$qry = "SELECT studiengang_kz, kurzbz, typ, kurzbzlang, bezeichnung, orgform_kurzbz,
	
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' 
		   			 	AND studiensemester_kurzbz='$stsem' AND datum<='$datum'
						) AS interessenten,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' 
		   			 	AND studiensemester_kurzbz='$stsem' AND geschlecht='m'  AND datum<='$datum'
						) AS interessenten_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' 
		   			 	AND studiensemester_kurzbz='$stsem' AND geschlecht='w'  AND datum<='$datum'
						) AS interessenten_w,
	
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND datum<='$datum'
		   			 	AND (anmeldungreihungstest<='$datum' AND anmeldungreihungstest IS NOT NULL)) AS interessentenrtanmeldung,
		   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m' AND datum<='$datum'
		   			 	AND (anmeldungreihungstest<='$datum' AND anmeldungreihungstest IS NOT NULL)) AS interessentenrtanmeldung_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='w' AND datum<='$datum'
		   			 	AND (anmeldungreihungstest<='$datum' AND anmeldungreihungstest IS NOT NULL)) AS interessentenrtanmeldung_w,
		   			    			 
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' 
		   				AND studiensemester_kurzbz='$stsem' AND datum<='$datum'
						) AS bewerber,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' 
		   				AND studiensemester_kurzbz='$stsem' AND geschlecht='m' AND datum<='$datum'
						) AS bewerber_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
		   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' 
		   				AND studiensemester_kurzbz='$stsem' AND geschlecht='w' AND datum<='$datum'
						) AS bewerber_w,
						
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' 
						AND studiensemester_kurzbz='$stsem' AND datum<='$datum'
						) AS aufgenommener,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' 
						AND studiensemester_kurzbz='$stsem' AND geschlecht='m' AND datum<='$datum'
						) AS aufgenommener_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' 
						AND studiensemester_kurzbz='$stsem' AND geschlecht='w' AND datum<='$datum'
						) AS aufgenommener_w,
						
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' 
						AND studiensemester_kurzbz='$stsem' AND datum<='$datum' ";
					if(count($ausgeschieden)>0)
					{
							$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
					}
						$qry.=") AS aufgenommenerber,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' 
						AND studiensemester_kurzbz='$stsem' AND geschlecht='m' AND datum<='$datum' ";
					if(count($ausgeschieden)>0)
					{
							$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
					}
						$qry.=") AS aufgenommenerber_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' 
						AND studiensemester_kurzbz='$stsem' AND geschlecht='w' AND datum<='$datum' ";
					if(count($ausgeschieden)>0)
					{
							$qry.="AND (prestudent_id) NOT IN ('".implode("','",$ausgeschieden)."')  ";
					}
						$qry.=") AS aufgenommenerber_w,
						
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' 
						AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1 AND datum<='$datum'
					) AS student1sem,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' 
						AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1 AND geschlecht='m' AND datum<='$datum'
					) AS student1sem_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' 
						AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1 AND geschlecht='w' AND datum<='$datum'
					) AS student1sem_w,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' 
						AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3 AND datum<='$datum'
					) AS student3sem,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' 
						AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3 AND geschlecht='m' AND datum<='$datum'
					) AS student3sem_m,
					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
						WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' 
						AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3 AND geschlecht='w' AND datum<='$datum'
					) AS student3sem_w
	
				FROM
					public.tbl_studiengang stg
				WHERE
					studiengang_kz>0 AND studiengang_kz<10000 AND aktiv $stgwhere
				ORDER BY typ, kurzbz; ";
	
		if($result = $db->db_query($qry))
		{
			$content.= "\n<table class='liste table-autosort:0 table-stripeclass:alternate table-autostripe'>
					<thead>
						<tr>
							<th class='table-sortable:default'>Studiengang</th>
							<th class='table-sortable:numeric'>InteressentInnen (m/w)</th>
							<th class='table-sortable:numeric'>InteressentInnen mit ZGV (m/w)</th>
							<th class='table-sortable:numeric'>InteressentInnen mit RT Anmeldung (m/w)</th>
							<th class='table-sortable:numeric'>BewerberIn (m/w)</th>
							<th class='table-sortable:numeric'>Aufgenommene (m/w)</th>
							<th class='table-sortable:numeric'>Aufgenommene bereinigt(m/w)</th>
							<th class='table-sortable:numeric'>StudentIn 1S (m/w)</th>
							<th class='table-sortable:numeric'>StudentIn 3S (m/w)</th>
						</tr>
					</thead>
					<tbody>
				 ";
			$interessenten_sum = 0;
			$interessenten_m_sum = 0;
			$interessenten_w_sum = 0;
			$interessentenrt_sum = 0;
			$interessentenrt_m_sum = 0;
			$interessentenrt_w_sum = 0;
			$bewerber_sum = 0;
			$bewerber_m_sum = 0;
			$bewerber_w_sum = 0;
			$aufgenommener_sum = 0;
			$aufgenommener_m_sum = 0;
			$aufgenommener_w_sum = 0;
			$aufgenommenerber_sum = 0;
			$aufgenommenerber_m_sum = 0;
			$aufgenommenerber_w_sum = 0;
			$student1sem_sum = 0;
			$student1sem_m_sum = 0;
			$student1sem_w_sum = 0;
			$student3sem_sum = 0;
			$student3sem_m_sum = 0;
			$student3sem_w_sum = 0;
			
			while($row = $db->db_fetch_object($result))
			{
				$content.= "\n";
				$content.= '<tr>';
				$content.= "<td>".mb_strtoupper($row->typ.$row->kurzbz)." ($row->kurzbzlang)</td>";
				$content.= "<td align='center'>$row->interessenten ($row->interessenten_m / $row->interessenten_w)</td>";
				$content.= "<td align='center'>k.A.</td>";
				$content.= "<td align='center'>$row->interessentenrtanmeldung ($row->interessentenrtanmeldung_m / $row->interessentenrtanmeldung_w)</td>";
				$content.= "<td align='center'>$row->bewerber ($row->bewerber_m / $row->bewerber_w)</td>";
				$content.= "<td align='center'>$row->aufgenommener ($row->aufgenommener_m / $row->aufgenommener_w)</td>";
				$content.= "<td align='center'>$row->aufgenommenerber ($row->aufgenommenerber_m / $row->aufgenommenerber_w)</td>";
				$content.= "<td align='center'>$row->student1sem ($row->student1sem_m / $row->student1sem_w)</td>";
				$content.= "<td align='center'>$row->student3sem ($row->student3sem_m / $row->student3sem_w)</td>";
				$content.= "</tr>";
				
				//Summe berechnen
				$interessenten_sum += $row->interessenten;
				$interessenten_m_sum += $row->interessenten_m;
				$interessenten_w_sum += $row->interessenten_w;
				$interessentenrt_sum += $row->interessentenrtanmeldung;
				$interessentenrt_m_sum += $row->interessentenrtanmeldung_m;
				$interessentenrt_w_sum += $row->interessentenrtanmeldung_w;
				$bewerber_sum += $row->bewerber;
				$bewerber_m_sum += $row->bewerber_m;
				$bewerber_w_sum += $row->bewerber_w;
				$aufgenommener_sum += $row->aufgenommener;
				$aufgenommener_m_sum += $row->aufgenommener_m;
				$aufgenommener_w_sum += $row->aufgenommener_w;
				$aufgenommenerber_sum += $row->aufgenommenerber;
				$aufgenommenerber_m_sum += $row->aufgenommenerber_m;
				$aufgenommenerber_w_sum += $row->aufgenommenerber_w;
				$student1sem_sum += $row->student1sem;
				$student1sem_m_sum += $row->student1sem_m;
				$student1sem_w_sum += $row->student1sem_w;
				$student3sem_sum += $row->student3sem;
				$student3sem_m_sum += $row->student3sem_m;
				$student3sem_w_sum += $row->student3sem_w;
			}
			
			$content.= "\n";
			$content.= '</tbody><tfoot style="font-weight: bold;"><tr>';
			$content.= "<td>Summe</td>";
			$content.= "<td align='center'>$interessenten_sum ($interessenten_m_sum / $interessenten_w_sum)</td>";
			$content.= "<td align='center'>k.A.</td>";
			$content.= "<td align='center'>$interessentenrt_sum ($interessentenrt_m_sum / $interessentenrt_w_sum)</td>";
			$content.= "<td align='center'>$bewerber_sum ($bewerber_m_sum / $bewerber_w_sum)</td>";
			$content.= "<td align='center'>$aufgenommener_sum ($aufgenommener_m_sum / $aufgenommener_w_sum)</td>";
			$content.= "<td align='center'>$aufgenommenerber_sum ($aufgenommenerber_m_sum / $aufgenommenerber_w_sum)</td>";
			$content.= "<td align='center'>$student1sem_sum ($student1sem_m_sum / $student1sem_w_sum)</td>";
			$content.= "<td align='center'>$student3sem_sum ($student3sem_m_sum / $student3sem_w_sum)</td>";
			$content.= "</tr>";
			
			$content.= '</tfoot></table>';
			
			//Verteilung
			$content.= '<br><h2>Verteilung '.$stsem.'</h2><br>';
			$qry = "SELECT 
						count(anzahl) AS anzahlpers,anzahl AS anzahlstg 
					FROM
					(
						SELECT 
							count(*) AS anzahl
						FROM 
							public.tbl_person JOIN public.tbl_prestudent USING (person_id) 
							JOIN public.tbl_prestudentstatus USING (prestudent_id)
						WHERE 
							true $stgwhere AND studiengang_kz>0 AND studiengang_kz<10000 AND datum<='$datum'
						GROUP BY 
							person_id,status_kurzbz,studiensemester_kurzbz
						HAVING 
							status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
					) AS prestd
					GROUP BY anzahl
					ORDER BY anzahl; ";
		
			$content.= "\n<table class='liste table-stripeclass:alternate table-autostripe' style='width:auto'>
						<thead>
							<tr>
								<th>Personen</th>
								<th>Stg</th>
							</tr>
						</thead>
						<tbody>";
			if($result = $db->db_query($qry))
			{
				$summestudenten=0;
				
				while($row = $db->db_fetch_object($result))
				{
					$summestudenten += $row->anzahlpers;
					$content.= "\n<tr><td>$row->anzahlpers</td><td>$row->anzahlstg</td></tr>";
				}
				$content.= "<tr><td style='border-top: 1px solid black;'><b>$summestudenten</b></td><td></td></tr>";
			}
			$content.= '</tbody></table>';
		}
	}
	$content.= '</body>
	</html>';
	
	if(!$mail)
	{
		echo $content;
	}
	else 
	{
		//Mail versenden
		echo 'BewerberInnenstatistik.php - Sende Mail ...';
		$to = 'tw_sek@technikum-wien.at, tw_stgl@technikum-wien.at, bewerberstatistik@technikum-wien.at, vilesci@technikum-wien.at';
		$mailobj = new mail($to, 'vilesci@technikum-wien.at','BewerberInnenstatistik','Sie muessen diese Mail als HTML-Mail anzeigen, um die Statistik zu sehen');
		$mailobj->setHTMLContent($content);
		
		if($mailobj->send())
		{
			echo 'Mail wurde erfolgreich versandt';
		}
		else 
		{
			echo 'Fehler beim Versenden des Mails';
		}
	}
}
?>