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
require_once('../../include/organisationsform.class.php');

$db = new basis_db();
$stsem_obj = new studiensemester();
if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
{
	$stsem = $stsem_obj->getaktorNext();
}
$stsem_obj->load($stsem);

$orgform = new organisationsform();
$orgform->getAll();
$orgform_arr = array();

foreach($orgform->result as $row_orgform)
	if($row_orgform->rolle==true)
		$orgform_arr[] = $row_orgform->orgform_kurzbz;

echo '<!DOCTYPE HTML>
	<html>
	<head>
	<meta charset="UTF-8">
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
						<th></th>
						<th>Anteil an Gesamt</th>
						<th>Extern</th>
						<th colspan=".count($orgform_arr).">Studienart</th>
						<th colspan=2>Geschlecht</th>
						<th colspan=3>Staatsb&uuml;rgerschaft</th>
					</tr>
				</thead>
				<tbody>
				<tr>
						<th>Bachelor</th>
						<th>Studiengänge</th>
						<th>Absolut / %</th>
						<th>In / Out</th>";
	foreach($orgform_arr as $row_orgform)
		echo "<th>".$row_orgform."</th>";
	echo "
						<th>m</th>
						<th>w</th>
						<th>&Ouml;sterreich</th>
						<th>EU</th>
						<th>Nicht-EU</th>
					</tr>
			 ";
	//Bachelor
	$qry = "SELECT studiengang_kz, kurzbz, typ, kurzbzlang, bezeichnung, orgform_kurzbz, mischform,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='$stsem'
					) a) AS gesamt_stg,

				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_studiengang USING(studiengang_kz)
	   			 	WHERE status_kurzbz='Student' AND studiensemester_kurzbz=".$db->db_add_param($stsem)." AND typ='b'
					) a) AS gesamt_alle,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Incoming' AND studiensemester_kurzbz=".$db->db_add_param($stsem)."
					) a) AS inc,
				(SELECT count(*) FROM (SELECT distinct student_uid FROM public.tbl_student JOIN bis.tbl_bisio USING (student_uid)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND (bis>=".$db->db_add_param($stsem_obj->start)." OR bis is null) AND von<=".$db->db_add_param($stsem_obj->ende)."
					) a) AS out,";

	foreach($orgform_arr as $row_orgform)
	{
		$qry.="	(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz=".$db->db_add_param($stsem)." AND orgform_kurzbz=".$db->db_add_param($row_orgform)."
					) a) AS orgform_".$row_orgform.",";
	}

	$qry.="		(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz=".$db->db_add_param($stsem)." AND geschlecht='w'
					) a) AS w,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz=".$db->db_add_param($stsem)." AND geschlecht='m'
					) a) AS m,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz=".$db->db_add_param($stsem)." AND nation_code='A'
					) a) AS herkunft_at,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz=".$db->db_add_param($stsem)." AND eu AND nation_code<>'A'
					) a) AS herkunft_eu,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz=".$db->db_add_param($stsem)." AND NOT eu
					) a) AS herkunft_noteu,
				true
			FROM
				public.tbl_studiengang stg
			WHERE
				studiengang_kz>0 AND studiengang_kz<10000 AND aktiv AND typ='b'
			ORDER BY typ, kurzbzlang; ";

	if($result = $db->db_query($qry))
	{

		$gesamt=0;
		$gesamt_prozent=0;
		foreach($orgform_arr as $row_orgform)
			$gesamt_orgform[$row_orgform] = 0;
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
			echo '<td>&nbsp;</td>';
			echo "<td>".mb_strtoupper($row->typ.$row->kurzbz)." ($row->kurzbzlang)</td>";
			$prozent = ($row->gesamt_alle!=0?$row->gesamt_stg/$row->gesamt_alle*100:0);
			echo "<td align='center'>$row->gesamt_stg / ".sprintf('%0.2f', $prozent)." %</td>";
			echo "<td align='center'>$row->inc / $row->out</td>";

			foreach($orgform_arr as $row_orgform)
			{
				echo "<td align='center'>";
				if($row->orgform_kurzbz ==  $row_orgform && $db->db_parse_bool($row->mischform) == false)
				{
					echo $row->gesamt_stg;
					$gesamt_orgform[$row_orgform] += $row->gesamt_stg;
				}
				else
				{
					echo $row->{'orgform_'.mb_strtolower($row_orgform)};
					$gesamt_orgform[$row_orgform] += $row->{'orgform_'.mb_strtolower($row_orgform)};
				}
				echo "</td>";
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
		echo "<td>&nbsp;</td>";
		echo "<td align='center'><b>$gesamt / ".sprintf('%0.2f', $gesamt_prozent)." %</b></td>";
		echo "<td align='center'><b>$gesamt_inc / $gesamt_out</b></td>";
		foreach($orgform_arr as $row_orgform)
			echo "<td align='center'><b>".$gesamt_orgform[$row_orgform]."</b></td>";
		echo "<td align='center'><b>$gesamt_m</b></td>";
		echo "<td align='center'><b>$gesamt_w</b></td>";
		echo "<td align='center'><b>$gesamt_at</b></td>";
		echo "<td align='center'><b>$gesamt_eu</b></td>";
		echo "<td align='center'><b>$gesamt_noteu</b></td>";
		echo "</tr>";

	}

	$gesamtsumme = $gesamt;
	$gesamtsumme_prozent = $gesamt_prozent;
	$gesamtsumme_orgform = array();

	foreach($orgform_arr as $row_orgform)
	{
		$gesamtsumme_orgform[$row_orgform] = $gesamt_orgform[$row_orgform];
	}
	$gesamtsumme_m = $gesamt_m;
	$gesamtsumme_w = $gesamt_w;
	$gesamtsumme_at = $gesamt_at;
	$gesamtsumme_eu = $gesamt_eu;
	$gesamtsumme_noteu = $gesamt_noteu;
	$gesamtsumme_inc = $gesamt_inc;
	$gesamtsumme_out = $gesamt_out;

	//Master
	echo '
	<tr>
		<th>Master</th>
		<th>Studiengänge</th>
		<th>Absolut / %</th>
		<th>In / Out</th>';
	foreach($orgform_arr as $row_orgform)
		echo '<th>'.$row_orgform.'</th>';

	echo '
		<th>m</th>
		<th>w</th>
		<th>&Ouml;sterreich</th>
		<th>EU</th>
		<th>Nicht-EU</th>
	</tr>';
	$qry = "SELECT studiengang_kz, kurzbz, typ, kurzbzlang, bezeichnung, orgform_kurzbz,mischform,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS gesamt_stg,

				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_studiengang USING(studiengang_kz)
	   			 	WHERE status_kurzbz='Student' AND studiensemester_kurzbz=".$db->db_add_param($stsem)." AND typ='m'
					) AS gesamt_alle,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Incoming' AND studiensemester_kurzbz=".$db->db_add_param($stsem)."
					) a) AS inc,
				(SELECT count(*) FROM (SELECT distinct student_uid FROM public.tbl_student JOIN bis.tbl_bisio USING (student_uid)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND (bis>=".$db->db_add_param($stsem_obj->start)." OR bis is null) AND von<=".$db->db_add_param($stsem_obj->ende)."
					) a) AS out,";

	foreach($orgform_arr as $row_orgform)
	{
		$qry .= "
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz=".$db->db_add_param($stsem)." AND orgform_kurzbz=".$db->db_add_param($row_orgform)."
					) a) AS orgform_".$row_orgform.",";
	}
	$qry .= "
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz=".$db->db_add_param($stsem)." AND geschlecht='w'
					) a) AS w,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz=".$db->db_add_param($stsem)." AND geschlecht='m'
					) a) AS m,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz=".$db->db_add_param($stsem)." AND nation_code='A'
					) a) AS herkunft_at,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz=".$db->db_add_param($stsem)." AND eu AND nation_code<>'A'
					) a) AS herkunft_eu,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Student' AND studiensemester_kurzbz=".$db->db_add_param($stsem)." AND NOT eu
					) a) AS herkunft_noteu,
				true
			FROM
				public.tbl_studiengang stg
			WHERE
				studiengang_kz>0 AND studiengang_kz<10000 AND aktiv AND typ='m'
			ORDER BY typ, kurzbzlang; ";

	if($result = $db->db_query($qry))
	{

		$gesamt=0;
		$gesamt_prozent=0;
		foreach($orgform_arr as $row_orgform)
			$gesamt_orgform[$row_orgform] = 0;
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
			echo '<td>&nbsp;</td>';
			echo "<td>".mb_strtoupper($row->typ.$row->kurzbz)." ($row->kurzbzlang)</td>";
			$prozent = ($row->gesamt_alle!=0?$row->gesamt_stg/$row->gesamt_alle*100:0);
			echo "<td align='center'>$row->gesamt_stg / ".sprintf('%0.2f', $prozent)." %</td>";
			echo "<td align='center'>$row->inc / $row->out</td>";

			foreach($orgform_arr as $row_orgform)
			{
				echo "<td align='center'>";
				if($row->orgform_kurzbz ==  $row_orgform && $db->db_parse_bool($row->mischform) == false)
				{
					echo $row->gesamt_stg;
					$gesamt_orgform[$row_orgform] += $row->gesamt_stg;
				}
				else
				{
					echo $row->{'orgform_'.mb_strtolower($row_orgform)};
					$gesamt_orgform[$row_orgform] += $row->{'orgform_'.mb_strtolower($row_orgform)};
				}
				echo "</td>";
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
		echo "<td>&nbsp;</td>";
		echo "<td align='center'><b>$gesamt / ".sprintf('%0.2f', $gesamt_prozent)." %</b></td>";
		echo "<td align='center'><b>$gesamt_inc / $gesamt_out</b></td>";
		foreach($orgform_arr as $row_orgform)
		{
			echo "<td align='center'><b>".$gesamt_orgform[$row_orgform]."</b></td>";
		}
		echo "<td align='center'><b>$gesamt_m</b></td>";
		echo "<td align='center'><b>$gesamt_w</b></td>";
		echo "<td align='center'><b>$gesamt_at</b></td>";
		echo "<td align='center'><b>$gesamt_eu</b></td>";
		echo "<td align='center'><b>$gesamt_noteu</b></td>";
		echo "</tr>";

	}
	$gesamtsumme += $gesamt;
	$gesamtsumme_prozent = 100;

	foreach($orgform_arr as $row_orgform)
		$gesamtsumme_orgform[$row_orgform] += $gesamt_orgform[$row_orgform];

	$gesamtsumme_m += $gesamt_m;
	$gesamtsumme_w += $gesamt_w;
	$gesamtsumme_at += $gesamt_at;
	$gesamtsumme_eu += $gesamt_eu;
	$gesamtsumme_noteu += $gesamt_noteu;
	$gesamtsumme_inc += $gesamt_inc;
	$gesamtsumme_out += $gesamt_out;
	echo '<tr>';
	echo '<td><b>GESAMTSUMME</b></td>';
	echo "<td>&nbsp;</td>";
	echo "<td align='center'><b>$gesamtsumme / ".sprintf('%0.2f', $gesamtsumme_prozent)." %</b></td>";
	echo "<td align='center'><b>$gesamtsumme_inc / $gesamtsumme_out</b></td>";
	foreach($orgform_arr as $row_orgform)
		echo "<td align='center'><b>".$gesamtsumme_orgform[$row_orgform]."</b></td>";
	echo "<td align='center'><b>$gesamtsumme_m</b></td>";
	echo "<td align='center'><b>$gesamtsumme_w</b></td>";
	echo "<td align='center'><b>$gesamtsumme_at</b></td>";
	echo "<td align='center'><b>$gesamtsumme_eu</b></td>";
	echo "<td align='center'><b>$gesamtsumme_noteu</b></td>";
	echo "</tr>";
	echo '</tbody></table>';
}
?>
</body>
</html>
