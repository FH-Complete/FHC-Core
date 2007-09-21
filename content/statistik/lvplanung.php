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
require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Datenbankverbindung konnte nicht hergestellt werden');

if(isset($_GET['studiengang_kz']))
	$studiengang_kz = $_GET['studiengang_kz'];
else
	$studiengang_kz = '';

if(isset($_GET['semester']))
	$semester = $_GET['semester'];
else
	$semester = '';

if(isset($_GET['uid']))
	$mitarbeiter_uid = $_GET['uid'];
else
	$mitarbeiter_uid = '';

if(isset($_GET['fachbereich_kurzbz']))
	$fachbereich_kurzbz = $_GET['fachbereich_kurzbz'];
else 
	$fachbereich_kurzbz = '';

$user = get_uid();
loadVariables($conn, $user);

$stg_arr = array();
$studiengang = new studiengang($conn);
$studiengang->getAll();

foreach ($studiengang->result as $row)
	$stg_arr[$row->studiengang_kz] = $row->kuerzel;

if($studiengang_kz!='')
{
	$studiengang = new studiengang($conn);
	$studiengang->load($studiengang_kz);
}

if($mitarbeiter_uid!='')
{
	$mitarbeiter = new benutzer($conn);
	$mitarbeiter->load($mitarbeiter_uid);
}

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
<title>Lehreinheit</title>
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<style>
table td
{
	font-size: small;
}
</style>
</head>
<body>';

if($studiengang_kz!='')
	echo '<h2>LV Uebersicht '.$studiengang->kuerzel.' '.($semester!=''?"$semester. Semester":'').'</h2>';
elseif($mitarbeiter_uid!='')
	echo '<h2>LV Uebersicht '.$mitarbeiter->nachname.' '.$mitarbeiter->vorname.'</h2>';
elseif($fachbereich_kurzbz!='')
	echo '<h2>LV Uebersicht '.$fachbereich_kurzbz.'</h2>';

if($studiengang_kz!='') //Liste nach Studiengang
{
	$qry = "SELECT
				tbl_lehrveranstaltung.kurzbz as kurzbz, tbl_lehrveranstaltung.bezeichnung as bezeichnung, tbl_lehrveranstaltung.lehrveranstaltung_id,
				tbl_lehrveranstaltung.ects as ects, tbl_lehrveranstaltung.semesterstunden as semesterstunden,
				tbl_lehrfach.kurzbz as lf_kurzbz, tbl_lehrfach.bezeichnung as lf_bezeichnung, tbl_lehreinheit.lehreinheit_id as lehreinheit_id,
				tbl_lehreinheit.lehrform_kurzbz as lehrform_kurzbz, tbl_lehreinheitmitarbeiter.semesterstunden as lektor_semesterstunden,
				tbl_lehreinheitmitarbeiter.stundensatz as lektor_stundensatz, tbl_lehreinheitmitarbeiter.faktor as lektor_faktor,
				tbl_person.vorname, tbl_person.nachname
			FROM
				lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter,
				lehre.tbl_lehrfach, public.tbl_benutzer, public.tbl_person
			WHERE
				tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
				tbl_lehrfach.lehrfach_id=tbl_lehreinheit.lehrfach_id AND
				tbl_benutzer.uid=tbl_lehreinheitmitarbeiter.mitarbeiter_uid AND
				tbl_person.person_id=tbl_benutzer.person_id AND
				tbl_lehrveranstaltung.studiengang_kz='".addslashes($studiengang_kz)."' AND
				tbl_lehreinheit.studiensemester_kurzbz='".addslashes($semester_aktuell)."'";
	if($semester!='')
		$qry.=" AND tbl_lehrveranstaltung.semester='".addslashes($semester)."'";
	$qry.=" ORDER BY tbl_lehrveranstaltung.bezeichnung, tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehreinheit.lehreinheit_id";
}
elseif($mitarbeiter_uid!='') // Liste nach Mitarbeiter
{
$qry = "SELECT
				tbl_lehrveranstaltung.kurzbz as kurzbz, tbl_lehrveranstaltung.bezeichnung as bezeichnung, tbl_lehrveranstaltung.lehrveranstaltung_id,
				tbl_lehrveranstaltung.ects as ects, tbl_lehrveranstaltung.semesterstunden as semesterstunden,
				tbl_lehrfach.kurzbz as lf_kurzbz, tbl_lehrfach.bezeichnung as lf_bezeichnung, tbl_lehreinheit.lehreinheit_id as lehreinheit_id,
				tbl_lehreinheit.lehrform_kurzbz as lehrform_kurzbz, tbl_lehreinheitmitarbeiter.semesterstunden as lektor_semesterstunden,
				tbl_lehreinheitmitarbeiter.stundensatz as lektor_stundensatz, tbl_lehreinheitmitarbeiter.faktor as lektor_faktor,
				tbl_person.vorname, tbl_person.nachname
			FROM
				lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter,
				lehre.tbl_lehrfach, public.tbl_benutzer, public.tbl_person
			WHERE
				tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
				tbl_lehrfach.lehrfach_id=tbl_lehreinheit.lehrfach_id AND
				tbl_benutzer.uid=tbl_lehreinheitmitarbeiter.mitarbeiter_uid AND
				tbl_person.person_id=tbl_benutzer.person_id AND
				tbl_lehreinheitmitarbeiter.mitarbeiter_uid='".addslashes($mitarbeiter_uid)."' AND
				tbl_lehreinheit.studiensemester_kurzbz='".addslashes($semester_aktuell)."'";
	$qry.=" ORDER BY tbl_lehrveranstaltung.bezeichnung, tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehreinheit.lehreinheit_id";
}
elseif($fachbereich_kurzbz!='') // Liste nach Fachbereich
{
$qry = "SELECT
				tbl_lehrveranstaltung.kurzbz as kurzbz, tbl_lehrveranstaltung.bezeichnung as bezeichnung, tbl_lehrveranstaltung.lehrveranstaltung_id,
				tbl_lehrveranstaltung.ects as ects, tbl_lehrveranstaltung.semesterstunden as semesterstunden,
				tbl_lehrfach.kurzbz as lf_kurzbz, tbl_lehrfach.bezeichnung as lf_bezeichnung, tbl_lehreinheit.lehreinheit_id as lehreinheit_id,
				tbl_lehreinheit.lehrform_kurzbz as lehrform_kurzbz, tbl_lehreinheitmitarbeiter.semesterstunden as lektor_semesterstunden,
				tbl_lehreinheitmitarbeiter.stundensatz as lektor_stundensatz, tbl_lehreinheitmitarbeiter.faktor as lektor_faktor,
				tbl_person.vorname, tbl_person.nachname
			FROM
				lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter,
				lehre.tbl_lehrfach, public.tbl_benutzer, public.tbl_person
			WHERE
				tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
				tbl_lehrfach.lehrfach_id=tbl_lehreinheit.lehrfach_id AND
				tbl_benutzer.uid=tbl_lehreinheitmitarbeiter.mitarbeiter_uid AND
				tbl_person.person_id=tbl_benutzer.person_id AND
				tbl_lehrfach.fachbereich_kurzbz='".addslashes($fachbereich_kurzbz)."' AND
				tbl_lehreinheit.studiensemester_kurzbz='".addslashes($semester_aktuell)."'";
	$qry.=" ORDER BY tbl_lehrveranstaltung.bezeichnung, tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehreinheit.lehreinheit_id";	
}
else 
	die('Fehlerhafte Parameteruebergabe');

echo '<table class="liste">';
echo '<tr>';
echo '<th>Kurzbz</th>';
echo '<th>Bezeichnung</th>';
echo '<th>Lehrform</th>';
echo '<th>ECTS</th>';
echo '<th>Stunden</th>';
echo '<th>Gruppen</th>';
echo '<th>Lektor</th>';
echo '<th>Kosten</th>';
echo '<th>Gesamtkosten</th>';
echo '</tr>';
if($result = pg_query($conn, $qry))
{
	$last_lva='';
	$stunden_lv=0;
	$kosten_lv=0;
	$gesamtkosten_lva=0;
	while($row = pg_fetch_object($result))
	{
		if($last_lva!=$row->lehrveranstaltung_id)
		{
			if($last_lva!='')
			{
				echo '<tr>';
				echo '<td>&nbsp;</td>';
				echo '<td>&nbsp;</td>';
				echo '<td>&nbsp;</td>';
				echo '<td>&nbsp;</td>';
				echo "<td align='right' style='border-top: 1px solid black; font-weight: bold;'>".sprintf('%.2f',$stunden_lv)."</td>";
				echo '<td>&nbsp;</td>';
				echo '<td>&nbsp;</td>';
				echo "<td align='right' style='border-top: 1px solid black; font-weight: bold'>".number_format($kosten_lv,2,',','.')." &euro;</td>";
				echo '<td>&nbsp;</td>';
				echo '</tr>';
				$gesamtkosten_lva +=$kosten_lv;
				$stunden_lv=0;
				$kosten_lv=0;
			}
			$last_lva=$row->lehrveranstaltung_id;
			echo '<tr class="liste1">';
			echo '<td>'.$row->kurzbz.'</td>';
			echo '<td>'.$row->bezeichnung.'</td>';
			echo '<td>&nbsp;</td>';
			echo '<td>'.$row->ects.'</td>';
			echo '<td>'.$row->semesterstunden.'</td>';
			echo '<td>&nbsp;</td>';
			echo '<td>&nbsp;</td>';
			echo '<td>&nbsp;</td>';
			echo '<td>&nbsp;</td>';
			echo '</tr>';
		}

		$gruppen='';
		$qry_grp = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id='$row->lehreinheit_id'";
		if($result_grp=pg_query($conn, $qry_grp))
		{
			while($row_grp = pg_fetch_object($result_grp))
			{
				if($gruppen=='')
					$gruppen = ($row_grp->gruppe_kurzbz!=''?$row_grp->gruppe_kurzbz:trim($stg_arr[$row_grp->studiengang_kz].'-'.$row_grp->semester.$row_grp->verband.$row_grp->gruppe));
				else
					$gruppen .= ','.($row_grp->gruppe_kurzbz!=''?$row_grp->gruppe_kurzbz:trim($stg_arr[$row_grp->studiengang_kz].'-'.$row_grp->semester.$row_grp->verband.$row_grp->gruppe));
			}
		}
		echo '<tr>';
		echo '<td>&nbsp;</td>';
		echo "<td>$row->lf_bezeichnung ($row->lf_kurzbz)</td>";
		echo "<td>$row->lehrform_kurzbz</td>";
		echo '<td>&nbsp;</td>';
		echo "<td align='right'>$row->lektor_semesterstunden</td>";
		echo "<td>$gruppen</td>";
		echo "<td>$row->nachname $row->vorname</td>";
		echo "<td align='right'>".number_format(($row->lektor_stundensatz*$row->lektor_faktor*$row->lektor_semesterstunden),2,',','.')." &euro;</td>";
		echo '<td>&nbsp;</td>';
		echo '</tr>';
		$kosten_lv +=($row->lektor_stundensatz*$row->lektor_faktor*$row->lektor_semesterstunden);
		$stunden_lv +=$row->lektor_semesterstunden;
	}
	$gesamtkosten_lva +=$kosten_lv;
	echo '<tr>';
	echo '<td>&nbsp;</td>';
	echo '<td>&nbsp;</td>';
	echo '<td>&nbsp;</td>';
	echo '<td>&nbsp;</td>';
	echo "<td align='right' style='border-top: 1px solid black; font-weight: bold;'>".sprintf('%.2f',$stunden_lv)."</td>";
	echo '<td>&nbsp;</td>';
	echo '<td>&nbsp;</td>';
	echo "<td align='right' style='border-top: 1px solid black; font-weight: bold'>".number_format($kosten_lv,2,',','.')." &euro;</td>";
	echo '<td align="right"><b>'.number_format($gesamtkosten_lva,2,',','.').' &euro;</b></td>';
	echo '</tr>';
}

if($studiengang_kz!='')
{
	$qry = "SELECT
				*
			FROM
				lehre.tbl_projektarbeit, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung, lehre.tbl_projektbetreuer, public.tbl_person
			WHERE
				tbl_projektarbeit.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
				tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND
				tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id AND
				tbl_person.person_id=tbl_projektbetreuer.person_id AND
				tbl_lehrveranstaltung.studiengang_kz='$studiengang_kz' AND
				tbl_lehreinheit.studiensemester_kurzbz='$semester_aktuell' AND
				(tbl_projektbetreuer.faktor*tbl_projektbetreuer.stundensatz*tbl_projektbetreuer.stunden)>0
				";

	if($semester!='')
		$qry.=" AND tbl_lehrveranstaltung.semester='$semester'";
}
elseif($mitarbeiter_uid!='')
{
	$qry = "SELECT
				*
			FROM
				lehre.tbl_projektarbeit, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung, lehre.tbl_projektbetreuer, public.tbl_person
			WHERE
				tbl_projektarbeit.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
				tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND
				tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id AND
				tbl_person.person_id=tbl_projektbetreuer.person_id AND
				tbl_projektbetreuer.person_id='$mitarbeiter->person_id' AND
				tbl_lehreinheit.studiensemester_kurzbz='$semester_aktuell' AND
				(tbl_projektbetreuer.faktor*tbl_projektbetreuer.stundensatz*tbl_projektbetreuer.stunden)>0
				";
}
elseif($fachbereich_kurzbz!='')
{
	$qry = "SELECT
				*
			FROM
				lehre.tbl_projektarbeit, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung, lehre.tbl_projektbetreuer, public.tbl_person, lehre.tbl_lehrfach
			WHERE
				tbl_projektarbeit.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
				tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND
				tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id AND
				tbl_lehreinheit.lehrfach_id=tbl_lehrfach.lehrfach_id AND
				tbl_person.person_id=tbl_projektbetreuer.person_id AND
				tbl_lehreinheit.studiensemester_kurzbz='$semester_aktuell' AND
				tbl_lehrfach.fachbereich_kurzbz='".addslashes($fachbereich_kurzbz)."' AND
				(tbl_projektbetreuer.faktor*tbl_projektbetreuer.stundensatz*tbl_projektbetreuer.stunden)>0
				";
}
else 
	die('Something unexpected happend');

if($result = pg_query($conn, $qry))
{
	if(pg_num_rows($result)>0)
	{
		echo '<tr><td colspan="2"><b>Betreuungen</b></td></tr>';

		echo '<tr>';
		echo '<th>&nbsp;</th>';
		echo "<th colspan='3'>Titel</th>";
		//echo "<th>&nbsp;</th>";
		//echo '<th>&nbsp;</th>';
		echo "<th align='right'>Stunden</th>";
		echo "<th>Student</th>";
		echo "<th>Lektor</th>";
		echo "<th>Kosten</th>";
		echo '<th>&nbsp;</th>';
		echo '</tr>';
		
		$gesamtkosten_betreuung=0;
		$stunden_betreuung=0;
		while($row = pg_fetch_object($result))
		{
			echo '<tr class="liste1">';
			echo '<td>&nbsp;</td>';
			echo "<td colspan='3'>$row->titel</td>";
			//echo "<td>&nbsp;</td>";
			//echo '<td>&nbsp;</td>';
			echo "<td align='right'>".number_format($row->stunden,2)."</td>";
			$benutzer = new benutzer($conn);
			$benutzer->load($row->student_uid);
			echo "<td>$benutzer->nachname $benutzer->vorname</td>";
			echo "<td>$row->nachname $row->vorname</td>";
			echo "<td align='right'>".number_format(($row->stundensatz*$row->faktor*$row->stunden),2,',','.')." &euro;</td>";
			echo '<td>&nbsp;</td>';
			echo '</tr>';
			$gesamtkosten_betreuung +=($row->stundensatz*$row->faktor*$row->stunden);
			$stunden_betreuung+=$row->stunden;
		}

		echo '<tr>';
		echo '<td>&nbsp;</td>';
		echo "<td>&nbsp;</td>";
		echo "<td>&nbsp;</td>";
		echo '<td>&nbsp;</td>';
		echo "<td align='right' style='border-top: 1px solid black;'><b>".number_format($stunden_betreuung,2)."</b></td>";
		echo "<td>&nbsp;</td>";
		echo "<td>&nbsp;</td>";
		echo "<td align='right' style='border-top: 1px solid black;'><b>".number_format($gesamtkosten_betreuung,2,',','.')." &euro;</b></td>";
		echo "<td align='right' ><b>".number_format($gesamtkosten_betreuung,2,',','.')." &euro;</b></td>";
		echo '</tr>';

		echo '<tr>';
		echo '<td>&nbsp;</td>';
		echo "<td>&nbsp;</td>";
		echo "<td>&nbsp;</td>";
		echo '<td>&nbsp;</td>';
		echo "<td align='right'></td>";
		echo "<td>&nbsp;</td>";
		echo "<td>&nbsp;</td>";
		echo "<td>&nbsp;</td>";
		echo "<td align='right' style='border-top: 1px solid black;'><b>".number_format(($gesamtkosten_betreuung+$gesamtkosten_lva),2,',','.')." &euro;</b></td>";
		echo '</tr>';
	}
}

echo '</table>';
?>
</body>
</html>