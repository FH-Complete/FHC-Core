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
/*
 * Erstellt eine Statistik ueber die verschiedenen Stati der Bewerber
 * mit Aufteilung nach Studiengaengen und Geschlecht.
 * Mischformen werden nochmals getrennt aufgelistet (VZ/BB)
 * Ausserdem erfolgt noch eine Auflistung in wie vielen verschiedenen Studiengaengen 
 * sich die Personen Beworben haben.
 *
 * GET-Parameter:
 * stsem ... Studiensemester fuer die Statistik
 * mail  ... Wenn der Parameter "mail" uebergeben wird, dann wird die Statistik 
 *           per Mail an "tw_sek" und "tw_stgl" versandt
 *
 */

require_once('../../vilesci/config.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/mail.class.php');
require_once('../../include/aufmerksamdurch.class.php');
require_once('../../include/studiengang.class.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Connecten zur DB');

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	$stsem = '';
	
// Wenn der Parameter Mail per GET oder Commandline Argument uebergeben wird,
// dann wird die Statistik per Mail versandt
if(isset($_GET['mail']) || (isset($_SERVER['argv']) && in_array('mail',$_SERVER['argv'])))
{
	$mail=true;
	$stsem_obj = new studiensemester($conn);
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
	$rechte = new benutzerberechtigung($conn);
	$rechte->getBerechtigungen(get_uid());
}
$content='';

$content.= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">';
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
	
	$stg_obj = new studiengang($conn);
	if(!$stg_obj->load($studiengang_kz))
		die('Studiengang existiert nicht');

	$content.='
	<h2>Bewerberstatistik Details - '.$stg_obj->kuerzel.' '.$stsem.'<span style="position:absolute; right:15px;">'.date('d.m.Y').'</span></h2><br>
	';
	//$content.='<center><iframe src="bewerberstatistik.svg.php?stsem='.$stsem.'&studiengang_kz='.$studiengang_kz.'" width="500" height="500" ></iframe></center>';
	
	$hlp=array();
	//Aufmerksamdurch (Prestudent)
	$content.= '<br><h2>Aufmerksam durch (Prestudent)</h2><br>';
	$qry = "SELECT beschreibung, COALESCE(a.anzahl,0) as anzahl
			FROM public.tbl_aufmerksamdurch LEFT JOIN 
				(SELECT aufmerksamdurch_kurzbz, count(*) as anzahl 
				FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING(prestudent_id) 
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
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
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
				FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING(prestudent_id) 
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
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
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
	$studsem = new studiensemester($conn);
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
			$stgwhere = substr($stgwhere,0, strlen($stgwhere)-1);
			$stgwhere.=' )';
		}
	}
	else 
		$stgwhere='';
	
	//Bewerberdaten holen
	$qry = "SELECT studiengang_kz, kurzbz, typ, kurzbzlang, bezeichnung, orgform_kurzbz,

				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
					) AS interessenten,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
					) AS interessenten_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
					) AS interessenten_w,
				
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   				AND (zgv_code IS NOT NULL OR zgvmas_code IS NOT NULL)) AS interessentenzgv,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
	   				AND (zgv_code IS NOT NULL OR zgvmas_code IS NOT NULL)) AS interessentenzgv_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
	   				AND (zgv_code IS NOT NULL OR zgvmas_code IS NOT NULL)) AS interessentenzgv_w,
	   				   			
	   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND anmeldungreihungstest IS NOT NULL) AS interessentenrtanmeldung,
	   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
	   			 	AND anmeldungreihungstest IS NOT NULL) AS interessentenrtanmeldung_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
	   			 	AND anmeldungreihungstest IS NOT NULL) AS interessentenrtanmeldung_w,
	   				   			 
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem'
					) AS bewerber,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
					) AS bewerber_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
					) AS bewerber_w,
					
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
					) AS aufgenommener,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
					) AS aufgenommener_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
					) AS aufgenommener_w,
					
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
				) AS student1sem,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1 AND geschlecht='m'
				) AS student1sem_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1 AND geschlecht='w'
				) AS student1sem_w,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3
				) AS student3sem,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3 AND geschlecht='m'
				) AS student3sem_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3 AND geschlecht='w'
				) AS student3sem_w

			FROM
				public.tbl_studiengang stg
			WHERE
				studiengang_kz>0 AND studiengang_kz<10000 AND aktiv $stgwhere
			ORDER BY typ, kurzbz; ";

	if($result = pg_query($conn, $qry))
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
						<th class='table-sortable:numeric'>Student 1S (m/w)</th>
						<th class='table-sortable:numeric'>Student 3S (m/w)</th>
					</tr>
				</thead>
				<tbody>
			 ";
		
		while($row = pg_fetch_object($result))
		{
			$content.= "\n";
			$content.= '<tr>';
			$content.= "<td><a href='".$_SERVER['PHP_SELF']."?showdetails=true&studiengang_kz=$row->studiengang_kz&stsem=$stsem'>".strtoupper($row->typ.$row->kurzbz)." ($row->kurzbzlang)</a></td>";
			$content.= "<td align='center'>$row->interessenten ($row->interessenten_m / $row->interessenten_w)</td>";
			$content.= "<td align='center'>$row->interessentenzgv ($row->interessentenzgv_m / $row->interessentenzgv_w)</td>";
			$content.= "<td align='center'>$row->interessentenrtanmeldung ($row->interessentenrtanmeldung_m / $row->interessentenrtanmeldung_w)</td>";
			$content.= "<td align='center'>$row->bewerber ($row->bewerber_m / $row->bewerber_w)</td>";
			$content.= "<td align='center'>$row->aufgenommener ($row->aufgenommener_m / $row->aufgenommener_w)</td>";
			$content.= "<td align='center'>$row->student1sem ($row->student1sem_m / $row->student1sem_w)</td>";
			$content.= "<td align='center'>$row->student3sem ($row->student3sem_m / $row->student3sem_w)</td>";
			$content.= "</tr>";
		}
		$content.= '</tbody></table>';
	}
	
	//Aufsplittungen für Mischformen holen
	$qry = "SELECT studiengang_kz, kurzbz, typ, kurzbzlang, bezeichnung, orgform_kurzbz,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND orgform_kurzbz='VZ'
					) AS interessenten_vz,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND orgform_kurzbz='BB'
					) AS interessenten_bb,	
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND orgform_kurzbz='FST'
					) AS interessenten_fst,	
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   				AND (zgv_code IS NOT NULL OR zgvmas_code IS NOT NULL) AND orgform_kurzbz='BB') AS interessentenzgv_bb,
	   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   				AND (zgv_code IS NOT NULL OR zgvmas_code IS NOT NULL) AND orgform_kurzbz='VZ') AS interessentenzgv_vz,
	   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   				AND (zgv_code IS NOT NULL OR zgvmas_code IS NOT NULL) AND orgform_kurzbz='FST') AS interessentenzgv_FST,

				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND anmeldungreihungstest IS NOT NULL AND orgform_kurzbz='VZ') AS interessentenrtanmeldung_vz,
	   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND anmeldungreihungstest IS NOT NULL AND orgform_kurzbz='BB') AS interessentenrtanmeldung_bb,
	   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND anmeldungreihungstest IS NOT NULL AND orgform_kurzbz='FST') AS interessentenrtanmeldung_fst,

	   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstest_id IS NOT NULL  AND orgform_kurzbz='BB') AS interessentenrttermin_bb,
	   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstest_id IS NOT NULL  AND orgform_kurzbz='VZ') AS interessentenrttermin_vz,
	   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstest_id IS NOT NULL  AND orgform_kurzbz='FST') AS interessentenrttermin_fst,
	   			 	
	   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstestangetreten AND orgform_kurzbz='VZ') AS interessentenrtabsolviert_vz,
	   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstestangetreten AND orgform_kurzbz='BB') AS interessentenrtabsolviert_bb,
	   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstestangetreten AND orgform_kurzbz='FST') AS interessentenrtabsolviert_fst,
	   			 	
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem'
					AND orgform_kurzbz='BB') AS bewerber_bb,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem'
					AND orgform_kurzbz='VZ') AS bewerber_vz,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem'
					AND orgform_kurzbz='FST') AS bewerber_fst,
					
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
					AND orgform_kurzbz='VZ') AS aufgenommener_vz,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
					AND orgform_kurzbz='BB') AS aufgenommener_bb,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
					AND orgform_kurzbz='FST') AS aufgenommener_fst,

				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
					AND orgform_kurzbz='BB') AS student1sem_bb,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
					AND orgform_kurzbz='VZ') AS student1sem_vz,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
					AND orgform_kurzbz='FST') AS student1sem_fst,
					
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3
					AND orgform_kurzbz='BB') AS student3sem_bb,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3
					AND orgform_kurzbz='VZ') AS student3sem_vz,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=3
					AND orgform_kurzbz='FST') AS student3sem_fst
			FROM
				public.tbl_studiengang stg
			WHERE
				studiengang_kz>0 AND studiengang_kz<10000 AND aktiv $stgwhere AND orgform_kurzbz='VBB'
			ORDER BY kurzbzlang; ";

	if($result = pg_query($conn, $qry))
	{
		if(pg_num_rows($result)>0)
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
							<th class='table-sortable:numeric'>Student 1S VZ / BB / FST</th>
							<th class='table-sortable:numeric'>Student 3S VZ / BB / FST</th>
						</tr>
					</thead>
					<tbody>
				 ";
			
			while($row = pg_fetch_object($result))
			{
				$content.= "\n";
				$content.= '<tr>';
				$content.= "<td>".strtoupper($row->typ.$row->kurzbz)." ($row->kurzbzlang)</td>";
				$content.= "<td align='center'>$row->interessenten_vz / $row->interessenten_bb / $row->interessenten_fst</td>";
				$content.= "<td align='center'>$row->interessentenzgv_vz / $row->interessentenzgv_bb / $row->interessentenzgv_fst</td>";
				$content.= "<td align='center'>$row->interessentenrtanmeldung_vz / $row->interessentenrtanmeldung_bb / $row->interessentenrtanmeldung_fst</td>";
				$content.= "<td align='center'>$row->bewerber_vz / $row->bewerber_bb / $row->bewerber_fst</td>";
				$content.= "<td align='center'>$row->aufgenommener_vz / $row->aufgenommener_bb / $row->aufgenommener_fst</td>";
				$content.= "<td align='center'>$row->student1sem_vz / $row->student1sem_bb / $row->student1sem_fst</td>";
				$content.= "<td align='center'>$row->student3sem_vz / $row->student3sem_bb / $row->student3sem_fst</td>";
				$content.= "</tr>";
			}
			$content.= '</tbody></table>';
		}
	}
	
	//Verteilung
	$content.= '<br><h2>Verteilung</h2><br>';
	$qry = "SELECT 
				count(anzahl) AS anzahlpers,anzahl AS anzahlstg 
			FROM
			(
				SELECT 
					count(*) AS anzahl
				FROM 
					public.tbl_person JOIN public.tbl_prestudent USING (person_id) 
					JOIN public.tbl_prestudentrolle USING (prestudent_id)
				WHERE 
					true $stgwhere
				GROUP BY 
					person_id,rolle_kurzbz,studiensemester_kurzbz
				HAVING 
					rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
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
	if($result = pg_query($conn, $qry))
	{
		$summestudenten=0;
		
		while($row = pg_fetch_object($result))
		{
			$summestudenten += $row->anzahlpers;
			$content.= "\n<tr><td>$row->anzahlpers</td><td>$row->anzahlstg</td></tr>";
		}
		$content.= "<tr><td style='border-top: 1px solid black;'><b>$summestudenten</b></td><td></td></tr>";
	}
	$content.= '</tbody></table>';
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
	$to = 'tw_sek@technikum-wien.at, tw_stgl@technikum-wien.at';
	$mailobj = new mail($to, 'vilesci@technikum-wien.at','Bewerberstatistik','Sie muessen diese Mail als HTML-Mail anzeigen um die Statistik zu sehen');
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