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
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/mail.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/aufmerksamdurch.class.php');
require_once('../../include/studiengang.class.php');

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
	$stsem_obj->getNextStudiensemester();
	$stsem = $stsem_obj->studiensemester_kurzbz;
}
else 
	$mail=false;

//wenn die Statistik per Mail versandt wird (Chronjob), 
//keine Ruecksicht auf Berechtigungen nehmen
//das Mail enthaelt alle Studiengaenge
if(!$mail)
{
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen(get_uid());
}
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
	- <a href="'.APP_ROOT.'content/statistik/lektorenstatistik.php" target="_blank">Lektorenstatisitk</a><br>
	- <a href="'.APP_ROOT.'content/statistik/mitarbeiterstatistik.php" target="_blank">Mitarbeiterstatistik</a><br>
	- <a href="'.APP_ROOT.'content/statistik/bewerberstatistik.php" target="_blank">Bewerberstatistik</a><br>
	- <a href="'.APP_ROOT.'content/statistik/studentenstatistik.php" target="_blank">Studentenstatistik</a><br>
	- <a href="'.APP_ROOT.'content/statistik/absolventenstatistik.php" target="_blank">Absolventenstatistik</a><br><br>
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
	<h2>Bewerberstatistik Details - '.$stg_obj->kuerzel.' '.$stsem.'<span style="position:absolute; right:15px;">'.date('d.m.Y').'</span></h2><br>
	';
	$content.='<center><iframe src="bewerberstatistik.svg.php?stsem='.$stsem.'&studiengang_kz='.$studiengang_kz.'" width="500" height="500" ></iframe></center>';
	
	$hlp=array();
	//Aufmerksamdurch (Prestudent)
	$content.= '<br><h2>Aufmerksam durch (Prestudent)</h2><br>';
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
	<h2>Bewerberstatistik '.$stsem.'<span style="position:absolute; right:15px;">'.date('d.m.Y').'</span></h2><br>
	';

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
		
		$content.= '<option value="'.$stsemester->studiensemester_kurzbz.'" '.$selected.'>'.$stsemester->studiensemester_kurzbz.'</option>';
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
	   				AND (zgv_code IS NOT NULL OR zgvmas_code IS NOT NULL)) AS interessentenzgv,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
	   				AND (zgv_code IS NOT NULL OR zgvmas_code IS NOT NULL)) AS interessentenzgv_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
	   				AND (zgv_code IS NOT NULL OR zgvmas_code IS NOT NULL)) AS interessentenzgv_w,
	   				   			
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
						<th class='table-sortable:numeric'>Interessenten (m/w)</th>
						<th class='table-sortable:numeric'>Interessenten mit ZGV (m/w)</th>
						<th class='table-sortable:numeric'>Interessenten mit RT Anmeldung (m/w)</th>
						<th class='table-sortable:numeric'>Bewerber (m/w)</th>
						<th class='table-sortable:numeric'>Aufgenommener (m/w)</th>
						<th class='table-sortable:numeric'>Aufgenommener bereinigt (m/w)</th>
						<th class='table-sortable:numeric'>Student 1S (m/w)</th>
						<th class='table-sortable:numeric'>Student 3S (m/w)</th>
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
	   			 	AND orgform_kurzbz='FST'
					) AS interessenten_fst,	
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   				AND (zgv_code IS NOT NULL OR zgvmas_code IS NOT NULL) AND orgform_kurzbz='BB') AS interessentenzgv_bb,
	   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   				AND (zgv_code IS NOT NULL OR zgvmas_code IS NOT NULL) AND orgform_kurzbz='VZ') AS interessentenzgv_vz,
	   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   				AND (zgv_code IS NOT NULL OR zgvmas_code IS NOT NULL) AND orgform_kurzbz='FST') AS interessentenzgv_FST,

				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND anmeldungreihungstest IS NOT NULL AND orgform_kurzbz='VZ') AS interessentenrtanmeldung_vz,
	   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND anmeldungreihungstest IS NOT NULL AND orgform_kurzbz='BB') AS interessentenrtanmeldung_bb,
	   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND anmeldungreihungstest IS NOT NULL AND orgform_kurzbz='FST') AS interessentenrtanmeldung_fst,

	   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstest_id IS NOT NULL  AND orgform_kurzbz='BB') AS interessentenrttermin_bb,
	   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstest_id IS NOT NULL  AND orgform_kurzbz='VZ') AS interessentenrttermin_vz,
	   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstest_id IS NOT NULL  AND orgform_kurzbz='FST') AS interessentenrttermin_fst,
	   			 	
	   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstestangetreten AND orgform_kurzbz='VZ') AS interessentenrtabsolviert_vz,
	   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstestangetreten AND orgform_kurzbz='BB') AS interessentenrtabsolviert_bb,
	   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstestangetreten AND orgform_kurzbz='FST') AS interessentenrtabsolviert_fst,
	   			 	
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem'
					AND orgform_kurzbz='BB') AS bewerber_bb,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem'
					AND orgform_kurzbz='VZ') AS bewerber_vz,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem'
					AND orgform_kurzbz='FST') AS bewerber_fst,
					
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
					AND orgform_kurzbz='VZ') AS aufgenommener_vz,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
					AND orgform_kurzbz='BB') AS aufgenommener_bb,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
					AND orgform_kurzbz='FST') AS aufgenommener_fst,
					
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
					$qry.="AND orgform_kurzbz='FST') AS aufgenommenerber_fst,

				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
					AND orgform_kurzbz='BB') AS student1sem_bb,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
					AND orgform_kurzbz='VZ') AS student1sem_vz,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
					AND orgform_kurzbz='FST') AS student1sem_fst,
					
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3
					AND orgform_kurzbz='BB') AS student3sem_bb,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3
					AND orgform_kurzbz='VZ') AS student3sem_vz,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3
					AND orgform_kurzbz='FST') AS student3sem_fst
			FROM
				public.tbl_studiengang stg
			WHERE
				studiengang_kz>0 AND studiengang_kz<10000 AND aktiv $stgwhere AND orgform_kurzbz='VBB'
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
							<th class='table-sortable:numeric'>Interessenten VZ / BB / FST</th>
							<th class='table-sortable:numeric'>Interessenten mit ZGV VZ / BB / FST</th>
							<th class='table-sortable:numeric'>Interessenten mit RT Anmeldung VZ / BB / FST</th>
							<th class='table-sortable:numeric'>Bewerber 1S VZ / BB / FST</th>
							<th class='table-sortable:numeric'>Aufgenommener VZ / BB / FST</th>
							<th class='table-sortable:numeric'>Aufgenommener bereinigt VZ / BB / FST</th>
							<th class='table-sortable:numeric'>Student 1S VZ / BB / FST</th>
							<th class='table-sortable:numeric'>Student 3S VZ / BB / FST</th>
						</tr>
					</thead>
					<tbody>
				 ";
			
			$interessenten_vz_sum = 0;
			$interessenten_bb_sum = 0;
			$interessenten_fst_sum = 0;
			$interessentenzgv_vz_sum = 0;
			$interessentenzgv_bb_sum = 0;
			$interessentenzgv_fst_sum = 0;
			$interessentenrtanmeldung_vz_sum = 0;
			$interessentenrtanmeldung_bb_sum = 0;
			$interessentenrtanmeldung_fst_sum = 0;
			$bewerber_vz_sum = 0;
			$bewerber_bb_sum = 0;
			$bewerber_fst_sum = 0;
			$aufgenommener_vz_sum = 0;
			$aufgenommener_bb_sum = 0;
			$aufgenommener_fst_sum = 0;
			$aufgenommenerber_vz_sum = 0;
			$aufgenommenerber_bb_sum = 0;
			$aufgenommenerber_fst_sum = 0;
			$student1sem_vz_sum = 0;
			$student1sem_bb_sum = 0;
			$student1sem_fst_sum = 0;
			$student3sem_vz_sum = 0;
			$student3sem_bb_sum = 0;
			$student3sem_fst_sum = 0;
			
			while($row = $db->db_fetch_object($result))
			{
				$content.= "\n";
				$content.= '<tr>';
				$content.= "<td>".mb_strtoupper($row->typ.$row->kurzbz)." ($row->kurzbzlang)</td>";
				$content.= "<td align='center'>$row->interessenten_vz / $row->interessenten_bb / $row->interessenten_fst</td>";
				$content.= "<td align='center'>$row->interessentenzgv_vz / $row->interessentenzgv_bb / $row->interessentenzgv_fst</td>";
				$content.= "<td align='center'>$row->interessentenrtanmeldung_vz / $row->interessentenrtanmeldung_bb / $row->interessentenrtanmeldung_fst</td>";
				$content.= "<td align='center'>$row->bewerber_vz / $row->bewerber_bb / $row->bewerber_fst</td>";
				$content.= "<td align='center'>$row->aufgenommener_vz / $row->aufgenommener_bb / $row->aufgenommener_fst</td>";
				$content.= "<td align='center'>$row->aufgenommenerber_vz / $row->aufgenommenerber_bb / $row->aufgenommenerber_fst</td>";
				$content.= "<td align='center'>$row->student1sem_vz / $row->student1sem_bb / $row->student1sem_fst</td>";
				$content.= "<td align='center'>$row->student3sem_vz / $row->student3sem_bb / $row->student3sem_fst</td>";
				$content.= "</tr>";
				
				//Summe berechnen
				$interessenten_vz_sum += $row->interessenten_vz;
				$interessenten_bb_sum += $row->interessenten_bb;
				$interessenten_fst_sum += $row->interessenten_fst;
				$interessentenzgv_vz_sum += $row->interessentenzgv_vz;
				$interessentenzgv_bb_sum += $row->interessentenzgv_bb;
				$interessentenzgv_fst_sum += $row->interessentenzgv_fst;
				$interessentenrtanmeldung_vz_sum += $row->interessentenrtanmeldung_vz;
				$interessentenrtanmeldung_bb_sum += $row->interessentenrtanmeldung_bb;
				$interessentenrtanmeldung_fst_sum += $row->interessentenrtanmeldung_fst;
				$bewerber_vz_sum += $row->bewerber_vz;
				$bewerber_bb_sum += $row->bewerber_bb;
				$bewerber_fst_sum += $row->bewerber_fst;
				$aufgenommener_vz_sum += $row->aufgenommener_vz;
				$aufgenommener_bb_sum += $row->aufgenommener_bb;
				$aufgenommener_fst_sum += $row->aufgenommener_fst;
				$aufgenommenerber_vz_sum += $row->aufgenommenerber_vz;
				$aufgenommenerber_bb_sum += $row->aufgenommenerber_bb;
				$aufgenommenerber_fst_sum += $row->aufgenommenerber_fst;
				$student1sem_vz_sum += $row->student1sem_vz;
				$student1sem_bb_sum += $row->student1sem_bb;
				$student1sem_fst_sum += $row->student1sem_fst;
				$student3sem_vz_sum += $row->student3sem_vz;
				$student3sem_bb_sum += $row->student3sem_bb;
				$student3sem_fst_sum += $row->student3sem_fst;
			}
			$content.= "\n";
			$content.= '</tbody><tfoot style="font-weight: bold;"><tr>';
			$content.= "<td>Summe</td>";
			$content.= "<td align='center'>$interessenten_vz_sum / $interessenten_bb_sum / $interessenten_fst_sum</td>";
			$content.= "<td align='center'>$interessentenzgv_vz_sum / $interessentenzgv_bb_sum / $interessentenzgv_fst_sum</td>";
			$content.= "<td align='center'>$interessentenrtanmeldung_vz_sum / $interessentenrtanmeldung_bb_sum / $interessentenrtanmeldung_fst_sum</td>";
			$content.= "<td align='center'>$bewerber_vz_sum / $bewerber_bb_sum / $bewerber_fst_sum</td>";
			$content.= "<td align='center'>$aufgenommener_vz_sum / $aufgenommener_bb_sum / $aufgenommener_fst_sum</td>";
			$content.= "<td align='center'>$aufgenommenerber_vz_sum / $aufgenommenerber_bb_sum / $aufgenommenerber_fst_sum</td>";
			$content.= "<td align='center'>$student1sem_vz_sum / $student1sem_bb_sum / $student1sem_fst_sum</td>";
			$content.= "<td align='center'>$student3sem_vz_sum / $student3sem_bb_sum / $student3sem_fst_sum</td>";
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
					true $stgwhere
				GROUP BY 
					person_id,status_kurzbz,studiensemester_kurzbz
				HAVING 
					status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
			) AS prestd
			GROUP BY anzahl; ";

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
	<h2>Bewerberstatistik '.$stsem.'<span style="position:absolute; right:15px;">'.$datum_obj->formatDatum($datum,'d.m.Y').'</span></h2><br>
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
						<th class='table-sortable:numeric'>Interessenten (m/w)</th>
						<th class='table-sortable:numeric'>Interessenten mit ZGV (m/w)</th>
						<th class='table-sortable:numeric'>Interessenten mit RT Anmeldung (m/w)</th>
						<th class='table-sortable:numeric'>Bewerber (m/w)</th>
						<th class='table-sortable:numeric'>Aufgenommener (m/w)</th>
						<th class='table-sortable:numeric'>Aufgenommener bereinigt(m/w)</th>
						<th class='table-sortable:numeric'>Student 1S (m/w)</th>
						<th class='table-sortable:numeric'>Student 3S (m/w)</th>
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
						true $stgwhere
					GROUP BY 
						person_id,status_kurzbz,studiensemester_kurzbz
					HAVING 
						status_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
				) AS prestd
				GROUP BY anzahl; ";
	
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
	echo 'Bewerberstatistik.php - Sende Mail ...';
	$to = 'tw_sek@technikum-wien.at, tw_stgl@technikum-wien.at, russ@technikum-wien.at, ott@technikum-wien.at, vilesci@technikum-wien.at, lehner@technikum-wien.at, teschl@technikum-wien.at, maderdon@technikum-wien.at';
	$mailobj = new mail($to, 'vilesci@technikum-wien.at','Bewerberstatistik','Sie muessen diese Mail als HTML-Mail anzeigen, um die Statistik zu sehen');
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
?>