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
 * Erstellt eine Liste mit den Absolventen eines Studiensemesters
 * Aufteilung in 
 * - Anzahl Gesamt
 * - Prozent Anteil
 * - Vollzeit/Berufsbegleitend
 * - Geschlecht
 * - Herkunft (AT/EU/Nicht EU)
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
{
	$stsem_obj = new studiensemester($conn);
	$stsem = $stsem_obj->getaktorNext();
}
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	</head>
	<body>';


	echo "<h2>Absolventenstatistik $stsem";
	echo '<span style="position:absolute; right:15px;">'.date('d.m.Y').'</span></h2><br>';
	echo '</h2>';
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
	echo "<table class='liste table-stripeclass:alternate table-autostripe'>
				<thead>
					<tr>
						<th></th>
						<th></th>
						<th>Anteil an Gesamt</th>
						<th>Studienart</th>
						<th>Geschlecht</th>
						<th colspan=3>Staatsb&uuml;rgerschaft</th>
					</tr>
					<tr>
						<th>Bachelor</th>
						<th>Studiengänge</th>
						<th>Absolut / %</th>
						<th>BB / VZ</th>
						<th>m / w</th>
						<th>&Ouml;sterreich</th>
						<th>EU</th>
						<th>Nicht-EU</th>
					</tr>
				</thead>
				<tbody>
			 ";
	//Bachelor
	$qry = "SELECT studiengang_kz, kurzbz, typ, kurzbzlang, bezeichnung, orgform_kurzbz,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem'
					) a) AS gesamt_stg,
					
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_studiengang USING(studiengang_kz)
	   			 	WHERE rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem' AND typ='b'
					) a) AS gesamt_alle,
					
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem' AND orgform_kurzbz='BB'
					) a) AS bb,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem' AND orgform_kurzbz='VZ'
					) a) AS vz,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
					) a) AS w,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
					) a) AS m,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m' AND nation_code='A'
					) a) AS herkunft_at,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m' AND eu AND nation_code<>'A'
					) a) AS herkunft_eu,
				(SELECT count(*) FROM (SELECT distinct prestudent_id FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m' AND NOT eu
					) a) AS herkunft_noteu,
				true
			FROM
				public.tbl_studiengang stg
			WHERE
				studiengang_kz>0 AND studiengang_kz<10000 AND aktiv AND typ='b'
			ORDER BY typ, kurzbzlang; ";
	//echo '<pre>'.$qry.'</pre><br><br>';
	if($result = pg_query($conn, $qry))
	{
		
		$gesamt=0;
		$gesamt_prozent=0;
		$gesamt_bb=0;
		$gesamt_vz=0;
		$gesamt_m=0;
		$gesamt_w=0;
		$gesamt_at=0;
		$gesamt_eu=0;
		$gesamt_noteu=0;
		while($row = pg_fetch_object($result))
		{
			echo '<tr>';
			echo '<td>&nbsp;</td>';
			echo "<td>".strtoupper($row->typ.$row->kurzbz)." ($row->kurzbzlang)</td>";
			$prozent = ($row->gesamt_alle!=0?$row->gesamt_stg/$row->gesamt_alle*100:0);
			echo "<td align='center'>$row->gesamt_stg / ".sprintf('%0.2f', $prozent)." %</td>";
			echo "<td align='center'>$row->bb / $row->vz</td>";
			echo "<td align='center'>$row->m / $row->w</td>";
			echo "<td align='center'>$row->herkunft_at</td>";
			echo "<td align='center'>$row->herkunft_eu</td>";
			echo "<td align='center'>$row->herkunft_noteu</td>";
			echo "</tr>";
			$gesamt+=$row->gesamt_stg;
			$gesamt_prozent+=$prozent;
			$gesamt_bb += $row->bb;
			$gesamt_vz += $row->vz;
			$gesamt_m += $row->m;
			$gesamt_w += $row->w;
			$gesamt_at += $row->herkunft_at;
			$gesamt_eu += $row->herkunft_eu;
			$gesamt_noteu += $row->herkunft_noteu;
		}
		echo '<tr>';
		echo '<td><b>SUMME</b></td>';
		echo "<td>&nbsp;</td>";
		echo "<td align='center'><b>$gesamt / ".sprintf('%0.2f', $gesamt_prozent)." %</b></td>";
		echo "<td align='center'><b>$gesamt_bb / $gesamt_vz</b></td>";
		echo "<td align='center'><b>$gesamt_m / $gesamt_w</b></td>";
		echo "<td align='center'><b>$gesamt_at</b></td>";
		echo "<td align='center'><b>$gesamt_eu</b></td>";
		echo "<td align='center'><b>$gesamt_noteu</b></td>";
		echo "</tr>";
		
	}
	
	//Master
	echo '
	<tr>
		<th>Master</th>
		<th>Studiengänge</th>
		<th>Absolut / %</th>
		<th>BB / VZ</th>
		<th>m / w</th>
		<th>&Ouml;sterreich</th>
		<th>EU</th>
		<th>Nicht-EU</th>
	</tr>';
	$qry = "SELECT studiengang_kz, kurzbz, typ, kurzbzlang, bezeichnung, orgform_kurzbz,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem'
					) AS gesamt_stg,
					
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_studiengang USING(studiengang_kz)
	   			 	WHERE rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem' AND typ='m'
					) AS gesamt_alle,
					
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem' AND orgform_kurzbz='BB'
					) AS bb,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem' AND orgform_kurzbz='VZ'
					) AS vz,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem' AND geschlecht='w'
					) AS w,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m'
					) AS m,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m' AND nation_code='A'
					) AS herkunft_at,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m' AND eu AND nation_code<>'A'
					) AS herkunft_eu,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentrolle USING (prestudent_id) JOIN public.tbl_person USING(person_id) JOIN bis.tbl_nation on(staatsbuergerschaft=nation_code)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND rolle_kurzbz='Absolvent' AND studiensemester_kurzbz='$stsem' AND geschlecht='m' AND NOT eu
					) AS herkunft_noteu,
				true
			FROM
				public.tbl_studiengang stg
			WHERE
				studiengang_kz>0 AND studiengang_kz<10000 AND aktiv AND typ='m'
			ORDER BY typ, kurzbzlang; ";
	//echo '<pre>'.$qry.'</pre><br><br>';
	if($result = pg_query($conn, $qry))
	{
		
		$gesamt=0;
		$gesamt_prozent=0;
		$gesamt_bb=0;
		$gesamt_vz=0;
		$gesamt_m=0;
		$gesamt_w=0;
		$gesamt_at=0;
		$gesamt_eu=0;
		$gesamt_noteu=0;
		while($row = pg_fetch_object($result))
		{
			echo '<tr>';
			echo '<td>&nbsp;</td>';
			echo "<td>".strtoupper($row->typ.$row->kurzbz)." ($row->kurzbzlang)</td>";
			$prozent = ($row->gesamt_alle!=0?$row->gesamt_stg/$row->gesamt_alle*100:0);
			echo "<td align='center'>$row->gesamt_stg / ".sprintf('%0.2f', $prozent)." %</td>";
			echo "<td align='center'>$row->bb / $row->vz</td>";
			echo "<td align='center'>$row->m / $row->w</td>";
			echo "<td align='center'>$row->herkunft_at</td>";
			echo "<td align='center'>$row->herkunft_eu</td>";
			echo "<td align='center'>$row->herkunft_noteu</td>";
			echo "</tr>";
			$gesamt+=$row->gesamt_stg;
			$gesamt_prozent+=$prozent;
			$gesamt_bb += $row->bb;
			$gesamt_vz += $row->vz;
			$gesamt_m += $row->m;
			$gesamt_w += $row->w;
			$gesamt_at += $row->herkunft_at;
			$gesamt_eu += $row->herkunft_eu;
			$gesamt_noteu += $row->herkunft_noteu;
		}
		echo '<tr>';
		echo '<td><b>SUMME</b></td>';
		echo "<td>&nbsp;</td>";
		echo "<td align='center'><b>$gesamt / ".sprintf('%0.2f', $gesamt_prozent)." %</b></td>";
		echo "<td align='center'><b>$gesamt_bb / $gesamt_vz</b></td>";
		echo "<td align='center'><b>$gesamt_m / $gesamt_w</b></td>";
		echo "<td align='center'><b>$gesamt_at</b></td>";
		echo "<td align='center'><b>$gesamt_eu</b></td>";
		echo "<td align='center'><b>$gesamt_noteu</b></td>";
		echo "</tr>";
		
	}
	echo '</tbody></table>';
}
?>
</body>
</html>