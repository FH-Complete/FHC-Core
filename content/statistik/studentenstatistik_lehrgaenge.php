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
 * Erstellt eine Liste der Studenten eines Studiensemesters
 * Aufteilung in 
 * - Anzahl Gesamt
 * - Prozent Anteil
 * - Vollzeit/Berufsbegleitend
 * - Geschlecht
 * - Herkunft (AT/EU/Nicht EU)
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');

$db = new basis_db();
$stsem_obj = new studiensemester();
if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
{
	$stsem = $stsem_obj->getaktorNext();
}
$stsem_obj->load($stsem);
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	</head>
	<body>';


	echo "<h2>Studierendenstatistik $stsem";
	echo '<span style="position:absolute; right:15px;">'.date('d.m.Y').'</span></h2><br>';
	echo '</h2>';
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">Studiensemester: <SELECT name="stsem">';
	$studsem = new studiensemester();
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
	echo "<table class='liste table-stripeclass:alternate table-autostripe'>
				<thead>
					<tr>
						<th></th>
						<th>Anteil an Gesamt</th>
						<th>Extern</th>
						<th>Studienart</th>
						<th colspan=2>Geschlecht</th>
						<th colspan=3>Staatsb&uuml;rgerschaft</th>
					</tr>
				</thead>
				<tbody>
				<tr>
						<th>Lehrgänge</th>
						<th>Absolut / %</th>
						<th>In / Out</th>
						<th>BB / VZ / DL</th>
						<th>m</th>
						<th>w</th>
						<th>&Ouml;sterreich</th>
						<th>EU</th>
						<th>Nicht-EU</th>
					</tr>
			 ";
	//Lehrgänge
	$qry = "SELECT studiengang_kz, kurzbz, typ, kurzbzlang, bezeichnung, orgform_kurzbz,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem'
					) a) AS gesamt_stg,
					
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_studiengang USING(studiengang_kz)
	   			 	WHERE status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND typ='l'
					) a) AS gesamt_alle,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Incoming' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) a) AS inc,
				(SELECT count(*) FROM (SELECT distinct student_uid FROM public.tbl_student JOIN bis.tbl_bisio USING (student_uid)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND (bis>='".addslashes($stsem_obj->start)."' OR bis is null) AND von<='".addslashes($stsem_obj->ende)."'
					) a) AS out,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND orgform_kurzbz='BB'
					) a) AS bb,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND orgform_kurzbz='VZ'
					) a) AS vz,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND orgform_kurzbz='DL'
					) a) AS fs,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND geschlecht='w'
					) a) AS w,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND geschlecht='m'
					) a) AS m,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND nation_code='A'
					) a) AS herkunft_at,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND eu AND nation_code<>'A'
					) a) AS herkunft_eu,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND NOT eu
					) a) AS herkunft_noteu,
				true
			FROM
				public.tbl_studiengang stg
			WHERE
				studiengang_kz<0 AND aktiv AND typ='l'
			ORDER BY typ, kurzbzlang; ";
	
	if($result = $db->db_query($qry))
	{
		
		$gesamt=0;
		$gesamt_prozent=0;
		$gesamt_bb=0;
		$gesamt_vz=0;
		$gesamt_fs=0;
		$gesamt_m=0;
		$gesamt_w=0;
		$gesamt_at=0;
		$gesamt_eu=0;
		$gesamt_noteu=0;
		$gesamt_inc=0;
		$gesamt_out=0;
		while($row = $db->db_fetch_object($result))
		{
			echo '<tr>';
			echo "<td>".mb_strtoupper($row->typ.$row->kurzbz)." ($row->kurzbzlang) - $row->bezeichnung</td>";
			$prozent = ($row->gesamt_alle!=0?$row->gesamt_stg/$row->gesamt_alle*100:0);
			echo "<td align='center'>$row->gesamt_stg / ".sprintf('%0.2f', $prozent)." %</td>";
			echo "<td align='center'>$row->inc / $row->out</td>";
			if($row->orgform_kurzbz=='BB')
			{
				//berufsbegleitend: gesamtzahl in spalte bb
				echo "<td align='center'>$row->gesamt_stg / $row->vz / $row->fs</td>";
				$gesamt_bb += $row->gesamt_stg;
				$gesamt_vz += $row->vz;
				$gesamt_fs += $row->fs;
			}
			else if($row->orgform_kurzbz=='VZ')
			{
				//vollzeit: gesamtzahl in spalte vz
				echo "<td align='center'>$row->bb / $row->gesamt_stg / $row->fs</td>";
				$gesamt_bb += $row->bb;
				$gesamt_vz += $row->gesamt_stg;
				$gesamt_fs += $row->fs;
			}
			else if($row->orgform_kurzbz=='DL')
			{
				//fernlehre: gesamtzahl in spalte DL
				echo "<td align='center'>$row->bb / $row->vz / $row->gesamt_stg</td>";
				$gesamt_bb += $row->bb;
				$gesamt_vz += $row->vz;
				$gesamt_fs += $row->gesamt_stg;
			}
			else 
			{
				echo "<td align='center'>$row->bb / $row->vz / $row->fs</td>";
				$gesamt_bb += $row->bb;
				$gesamt_vz += $row->vz;
				$gesamt_fs += $row->fs;
			}
			echo "<td align='center'>$row->m</td>";
			echo "<td align='center'>$row->w</td>";
			echo "<td align='center'>$row->herkunft_at</td>";
			echo "<td align='center'>$row->herkunft_eu</td>";
			echo "<td align='center'>$row->herkunft_noteu</td>";
			echo "</tr>";
			$gesamt+=$row->gesamt_stg;
			$gesamt_prozent+=$prozent;
			$gesamt_m += $row->m;
			$gesamt_w += $row->w;
			$gesamt_at += $row->herkunft_at;
			$gesamt_eu += $row->herkunft_eu;
			$gesamt_noteu += $row->herkunft_noteu;
			$gesamt_inc+=$row->inc;
			$gesamt_out+=$row->out;
		}
		echo '<tr>';
		echo '<td><b>SUMME</b></td>';
		echo "<td align='center'><b>$gesamt / ".sprintf('%0.2f', $gesamt_prozent)." %</b></td>";
		echo "<td align='center'><b>$gesamt_inc / $gesamt_out</b></td>";
		echo "<td align='center'><b>$gesamt_bb / $gesamt_vz / $gesamt_fs</b></td>";
		echo "<td align='center'><b>$gesamt_m</b></td>";
		echo "<td align='center'><b>$gesamt_w</b></td>";
		echo "<td align='center'><b>$gesamt_at</b></td>";
		echo "<td align='center'><b>$gesamt_eu</b></td>";
		echo "<td align='center'><b>$gesamt_noteu</b></td>";
		echo "</tr>";
		
	}
	
	$gesamtsumme = $gesamt;
	$gesamtsumme_prozent = $gesamt_prozent;
	$gesamtsumme_bb = $gesamt_bb;
	$gesamtsumme_vz = $gesamt_vz;
	$gesamtsumme_fs = $gesamt_fs;
	$gesamtsumme_m = $gesamt_m;
	$gesamtsumme_w = $gesamt_w;
	$gesamtsumme_at = $gesamt_at;
	$gesamtsumme_eu = $gesamt_eu;
	$gesamtsumme_noteu = $gesamt_noteu;
	$gesamtsumme_inc = $gesamt_inc;
	$gesamtsumme_out = $gesamt_out;
	
	//Kurzstudien
	echo '
	<tr>
		<th>Kurzstudien</th>
		<th>Absolut / %</th>
		<th>In / Out</th>
		<th>BB / VZ / DL</th>
		<th>m</th>
		<th>w</th>
		<th>&Ouml;sterreich</th>
		<th>EU</th>
		<th>Nicht-EU</th>
	</tr>';
	$qry = "SELECT studiengang_kz, kurzbz, typ, kurzbzlang, bezeichnung, orgform_kurzbz,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS gesamt_stg,
					
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_studiengang USING(studiengang_kz)
	   			 	WHERE status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND typ='k'
					) AS gesamt_alle,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Incoming' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) a) AS inc,
				(SELECT count(*) FROM (SELECT distinct student_uid FROM public.tbl_student JOIN bis.tbl_bisio USING (student_uid)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND (bis>='".addslashes($stsem_obj->start)."' OR bis is null) AND von<='".addslashes($stsem_obj->ende)."'
					) a) AS out,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND orgform_kurzbz='BB'
					) a) AS bb,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND orgform_kurzbz='VZ'
					) a) AS vz,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND orgform_kurzbz='DL'
					) a) AS fs,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND geschlecht='w'
					) a) AS w,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND geschlecht='m'
					) a) AS m,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND nation_code='A'
					) a) AS herkunft_at,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND eu AND nation_code<>'A'
					) a) AS herkunft_eu,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."' AND NOT eu
					) a) AS herkunft_noteu,
				true
			FROM
				public.tbl_studiengang stg
			WHERE
				studiengang_kz<0 AND aktiv AND typ='k'
			ORDER BY typ, kurzbzlang; ";
	
	if($result = $db->db_query($qry))
	{
		
		$gesamt=0;
		$gesamt_prozent=0;
		$gesamt_bb=0;
		$gesamt_vz=0;
		$gesamt_fs=0;
		$gesamt_m=0;
		$gesamt_w=0;
		$gesamt_at=0;
		$gesamt_eu=0;
		$gesamt_noteu=0;
		$gesamt_inc=0;
		$gesamt_out=0;
		while($row = $db->db_fetch_object($result))
		{
			echo '<tr>';
			echo "<td>".mb_strtoupper($row->typ.$row->kurzbz)." ($row->kurzbzlang) - $row->bezeichnung</td>";
			$prozent = ($row->gesamt_alle!=0?$row->gesamt_stg/$row->gesamt_alle*100:0);
			echo "<td align='center'>$row->gesamt_stg / ".sprintf('%0.2f', $prozent)." %</td>";
			echo "<td align='center'>$row->inc / $row->out</td>";
			if($row->orgform_kurzbz=='BB')
			{
				//berufsbegleitend: gesamtzahl in spalte bb
				echo "<td align='center'>$row->gesamt_stg / $row->vz / $row->fs</td>";
				$gesamt_bb += $row->gesamt_stg;
				$gesamt_vz += $row->vz;
				$gesamt_fs += $row->fs;
			}
			else if($row->orgform_kurzbz=='VZ')
			{
				//vollzeit: gesamtzahl in spalte vz
				echo "<td align='center'>$row->bb / $row->gesamt_stg / $row->fs</td>";
				$gesamt_bb += $row->bb;
				$gesamt_vz += $row->gesamt_stg;
				$gesamt_fs += $row->fs;
			}
			else if($row->orgform_kurzbz=='DL')
			{
				//fernlehre: gesamtzahl in spalte DL
				echo "<td align='center'>$row->bb / $row->vz / $row->gesamt_stg</td>";
				$gesamt_bb += $row->bb;
				$gesamt_vz += $row->vz;
				$gesamt_fs += $row->gesamt_stg;
			}
			else 
			{
				echo "<td align='center'>$row->bb / $row->vz / $row->fs</td>";
				$gesamt_bb += $row->bb;
				$gesamt_vz += $row->vz;
				$gesamt_fs += $row->fs;
			}
			echo "<td align='center'>$row->m</td>";
			echo "<td align='center'>$row->w</td>";
			echo "<td align='center'>$row->herkunft_at</td>";
			echo "<td align='center'>$row->herkunft_eu</td>";
			echo "<td align='center'>$row->herkunft_noteu</td>";
			echo "</tr>";
			$gesamt+=$row->gesamt_stg;
			$gesamt_prozent+=$prozent;
			$gesamt_m += $row->m;
			$gesamt_w += $row->w;
			$gesamt_at += $row->herkunft_at;
			$gesamt_eu += $row->herkunft_eu;
			$gesamt_noteu += $row->herkunft_noteu;
			$gesamt_inc+=$row->inc;
			$gesamt_out+=$row->out;
		}
		echo '<tr>';
		echo '<td><b>SUMME</b></td>';
		echo "<td align='center'><b>$gesamt / ".sprintf('%0.2f', $gesamt_prozent)." %</b></td>";
		echo "<td align='center'><b>$gesamt_inc / $gesamt_out</b></td>";
		echo "<td align='center'><b>$gesamt_bb / $gesamt_vz / $gesamt_fs</b></td>";
		echo "<td align='center'><b>$gesamt_m</b></td>";
		echo "<td align='center'><b>$gesamt_w</b></td>";
		echo "<td align='center'><b>$gesamt_at</b></td>";
		echo "<td align='center'><b>$gesamt_eu</b></td>";
		echo "<td align='center'><b>$gesamt_noteu</b></td>";
		echo "</tr>";
		
	}
	$gesamtsumme += $gesamt;
	$gesamtsumme_prozent = 100;
	$gesamtsumme_bb += $gesamt_bb;
	$gesamtsumme_vz += $gesamt_vz;
	$gesamtsumme_fs += $gesamt_fs;
	$gesamtsumme_m += $gesamt_m;
	$gesamtsumme_w += $gesamt_w;
	$gesamtsumme_at += $gesamt_at;
	$gesamtsumme_eu += $gesamt_eu;
	$gesamtsumme_noteu += $gesamt_noteu;
	$gesamtsumme_inc += $gesamt_inc;
	$gesamtsumme_out += $gesamt_out;
	echo '<tr>';
	echo '<td style="background-color: #8DBDD8;"><b>GESAMTSUMME</b></td>';
	echo "<td style='background-color: #8DBDD8;' align='center'><b>$gesamtsumme / ".sprintf('%0.2f', $gesamtsumme_prozent)." %</b></td>";
	echo "<td style='background-color: #8DBDD8;' align='center'><b>$gesamtsumme_inc / $gesamtsumme_out</b></td>";
	echo "<td style='background-color: #8DBDD8;' align='center'><b>$gesamtsumme_bb / $gesamtsumme_vz / $gesamtsumme_fs</b></td>";
	echo "<td style='background-color: #8DBDD8;' align='center'><b>$gesamtsumme_m</b></td>";
	echo "<td style='background-color: #8DBDD8;' align='center'><b>$gesamtsumme_w</b></td>";
	echo "<td style='background-color: #8DBDD8;' align='center'><b>$gesamtsumme_at</b></td>";
	echo "<td style='background-color: #8DBDD8;' align='center'><b>$gesamtsumme_eu</b></td>";
	echo "<td style='background-color: #8DBDD8;' align='center'><b>$gesamtsumme_noteu</b></td>";
	echo "</tr>";
	echo '</tbody></table>';
}
?>
</body>
</html>