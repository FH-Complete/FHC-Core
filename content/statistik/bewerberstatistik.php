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

require_once('../../vilesci/config.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Connecten zur DB');

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	$stsem = '';
	
$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen(get_uid());

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	</head>
	<body>
	<h2>Bewerberstatistik '.$stsem.'<span style="position:absolute; right:15px;">'.date('d.m.Y').'</span></h2><br>
	';


	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">Studiensemester: <SELECT name="stsem">';
	$studsem = new studiensemester($conn);
	$studsem->getAll();

	foreach ($studsem->studiensemester as $stsemester)
	{
		if($stsemester->studiensemester_kurzbz==$stsem)
			$selected='selected';
		else 
			$selected='';
		
		echo '<option value="'.$stsemester->studiensemester_kurzbz.'" '.$selected.'>'.$stsemester->studiensemester_kurzbz.'</option>';
	}
	echo '</SELECT>
		<input type="submit" value="Anzeigen" /></form><br><br>';

if($stsem!='')
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
	
	// SELECT count(*) FROM public.tbl_prestudent WHERE studiengang_kz=stg.studiengang_kz) AS prestd,
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
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
					) AS bewerber,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1 AND geschlecht='m'
					) AS bewerber_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1 AND geschlecht='w'
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
				) AS student1sem_w

			FROM
				public.tbl_studiengang stg
			WHERE
				studiengang_kz>0 AND studiengang_kz<10000 AND aktiv $stgwhere
			ORDER BY kurzbzlang; ";
/*
(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstest_id IS NOT NULL) AS interessentenrttermin,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geshclecht='m'
	   			 	AND reihungstest_id IS NOT NULL) AS interessentenrttermin_m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem' AND geshclecht='w'
	   			 	AND reihungstest_id IS NOT NULL) AS interessentenrttermin_w,
	   			 	   			 
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstestangetreten) AS interessentenrtabsolviert,
*/
	if($result = pg_query($conn, $qry))
	{
		echo "<table class='liste table-autosort:0 table-stripeclass:alternate table-autostripe'>
				<thead>
					<tr>
						<th class='table-sortable:default'>Studiengang</th>
						<th class='table-sortable:numeric'>Interessenten (m/w)</th>
						<th class='table-sortable:numeric'>Interessenten mit ZGV (m/w)</th>
						<th class='table-sortable:numeric'>Interessenten mit RT Anmeldung (m/w)</th>
						<th class='table-sortable:numeric'>Bewerber 1S (m/w)</th>
						<th class='table-sortable:numeric'>Aufgenommener (m/w)</th>
						<th class='table-sortable:numeric'>Student 1S (m/w)</th>
					</tr>
				</thead>
				<tbody>
			 ";
		//<th class='table-sortable:numeric'>Interessenten mit RT Termin</th>
		//<th class='table-sortable:numeric'>Interessenten mit absolviertem RT</th>
		
		while($row = pg_fetch_object($result))
		{
			echo '<tr>';
			echo "<td>".strtoupper($row->typ.$row->kurzbz)." ($row->kurzbzlang)</td>";
			echo "<td align='center'>$row->interessenten ($row->interessenten_m / $row->interessenten_w)</td>";
			echo "<td align='center'>$row->interessentenzgv ($row->interessentenzgv_m / $row->interessentenzgv_w)</td>";
			echo "<td align='center'>$row->interessentenrtanmeldung ($row->interessentenrtanmeldung_m / $row->interessentenrtanmeldung_w)</td>";
			//echo "<td align='center'>$row->interessentenrttermin</td>";
			//echo "<td align='center'>$row->interessentenrtabsolviert</td>";
			echo "<td align='center'>$row->bewerber ($row->bewerber_m / $row->bewerber_w)</td>";
			echo "<td align='center'>$row->aufgenommener ($row->aufgenommener_m / $row->aufgenommener_w)</td>";
			echo "<td align='center'>$row->student1sem ($row->student1sem_m / $row->student1sem_w)</td>";
			echo "</tr>";
		}
		echo '</tbody></table>';
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
	   				AND (zgv_code IS NOT NULL OR zgvmas_code IS NOT NULL) AND orgform_kurzbz='BB') AS interessentenzgv_bb,
	   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   				AND (zgv_code IS NOT NULL OR zgvmas_code IS NOT NULL) AND orgform_kurzbz='VZ') AS interessentenzgv_vz,

				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND anmeldungreihungstest IS NOT NULL AND orgform_kurzbz='VZ') AS interessentenrtanmeldung_vz,
	   			(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND anmeldungreihungstest IS NOT NULL AND orgform_kurzbz='BB') AS interessentenrtanmeldung_bb,
	   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstest_id IS NOT NULL  AND orgform_kurzbz='BB') AS interessentenrttermin_bb,
	   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstest_id IS NOT NULL  AND orgform_kurzbz='VZ') AS interessentenrttermin_vz,
	   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstestangetreten AND orgform_kurzbz='VZ') AS interessentenrtabsolviert_vz,
	   			 (SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Interessent' AND studiensemester_kurzbz='$stsem'
	   			 	AND reihungstestangetreten AND orgform_kurzbz='BB') AS interessentenrtabsolviert_bb,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
					AND orgform_kurzbz='BB') AS bewerber_bb,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   				WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Bewerber' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
					AND orgform_kurzbz='VZ') AS bewerber_vz,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
					AND orgform_kurzbz='VZ') AS aufgenommener_vz,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Aufgenommener' AND studiensemester_kurzbz='$stsem'
					AND orgform_kurzbz='BB') AS aufgenommener_bb,

					(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
					AND orgform_kurzbz='BB') AS student1sem_bb,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
					WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Student' AND studiensemester_kurzbz='$stsem' AND ausbildungssemester=1
					AND orgform_kurzbz='VZ') AS student1sem_vz
			FROM
				public.tbl_studiengang stg
			WHERE
				studiengang_kz>0 AND studiengang_kz<10000 AND aktiv $stgwhere AND orgform_kurzbz='VBB'
			ORDER BY kurzbzlang; ";

	if($result = pg_query($conn, $qry))
	{
		if(pg_num_rows($result)>0)
		{
			echo "<br><br><h2>Aufsplittung Mischformen</h2><br>";
			echo "<table class='liste table-autosort:0 table-stripeclass:alternate table-autostripe'>
					<thead>
						<tr>
							<th class='table-sortable:default'>Studiengang</th>
							<th class='table-sortable:numeric'>Interessenten VZ / BB</th>
							<th class='table-sortable:numeric'>Interessenten mit ZGV VZ / BB</th>
							<th class='table-sortable:numeric'>Interessenten mit RT Anmeldung VZ / BB</th>
							<th class='table-sortable:numeric'>Bewerber 1S VZ / BB</th>
							<th class='table-sortable:numeric'>Aufgenommener VZ / BB</th>
							<th class='table-sortable:numeric'>Student 1S VZ / BB</th>
						</tr>
					</thead>
					<tbody>
				 ";
			//<th class='table-sortable:numeric'>Interessenten mit RT Termin VZ / BB</th>
			//<th class='table-sortable:numeric'>Interessenten mit absolviertem RT VZ / BB</th>
			while($row = pg_fetch_object($result))
			{
				echo '<tr>';
				echo "<td>".strtoupper($row->typ.$row->kurzbz)." ($row->kurzbzlang)</td>";
				echo "<td align='center'>$row->interessenten_vz / $row->interessenten_bb</td>";
				echo "<td align='center'>$row->interessentenzgv_vz / $row->interessentenzgv_bb</td>";
				echo "<td align='center'>$row->interessentenrtanmeldung_vz / $row->interessentenrtanmeldung_bb</td>";
				//echo "<td align='center'>$row->interessentenrttermin_vz / $row->interessentenrttermin_bb</td>";
				//echo "<td align='center'>$row->interessentenrtabsolviert_vz / $row->interessentenrtabsolviert_bb</td>";
				echo "<td align='center'>$row->bewerber_vz / $row->bewerber_bb</td>";
				echo "<td align='center'>$row->aufgenommener_vz / $row->aufgenommener_bb</td>";
				echo "<td align='center'>$row->student1sem_vz / $row->student1sem_bb</td>";
				echo "</tr>";
			}
			echo '</tbody></table>';
		}
	}
	
	//Verteilung
	echo '<br><h2>Verteilung</h2><br>';
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

	echo "<table class='liste table-stripeclass:alternate table-autostripe' style='width:auto'>
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
			echo "<tr><td>$row->anzahlpers</td><td>$row->anzahlstg</td></tr>";
		}
		echo "<tr><td style='border-top: 1px solid black;'><b>$summestudenten</b></td><td></td></tr>";
	}
	echo '</tbody></table>';
}
?>
</body>
</html>