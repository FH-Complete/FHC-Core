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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
 */
require_once('../config.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/functions.inc.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}
   
$s=new studiengang($conn);
$s->getAll('typ, kurzbz', true);
$studiengang=$s->result;

$ss = new studiensemester($conn);
$ss->getAll();
foreach($ss->studiensemester as $studiensemester)
{
	$ss_arr[] = $studiensemester->studiensemester_kurzbz;
}

$user = get_uid();

if (isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
	$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:$_POST['stg_kz']);
else
	$stg_kz=0;
if (isset($_GET['semester']) || isset($_POST['semester']))
	$semester=(isset($_GET['semester'])?$_GET['semester']:$_POST['semester']);
else
	$semester=100;
if (isset($_GET['studiensemester_kurzbz']) || isset($_POST['studiensemester_kurzbz']))
	$studiensemester_kurzbz=(isset($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:$_POST['studiensemester_kurzbz']);
else
	$studiensemester_kurzbz=null;
if (is_null($studiensemester_kurzbz))
{
	$studiensemester_kurzbz=$ss->getakt();
}
$studiensemester_kurzbz_akt=$ss->getakt();			//aktuelles Semester
$ss->getNextStudiensemester();
$studiensemester_kurzbz_zk=$ss->studiensemester_kurzbz;	//nächstes Semester


if(!is_numeric($stg_kz))
	$stg_kz=0;
if(!is_numeric($semester))
	$semester=100;

//select für die Anzeige
$sql_query="SELECT tbl_student.*,tbl_person.*, tbl_studentlehrverband.semester as semester_stlv,  tbl_studentlehrverband.verband as verband_stlv, 
			tbl_studentlehrverband.gruppe as gruppe_stlv FROM tbl_studentlehrverband JOIN tbl_student USING (student_uid)
				JOIN tbl_benutzer ON (student_uid=uid)
				JOIN tbl_person USING (person_id)
			WHERE tbl_benutzer.aktiv AND tbl_studentlehrverband.studiengang_kz='$stg_kz' 
			AND studiensemester_kurzbz='$studiensemester_kurzbz' ";
if($semester<100)
{
	$sql_query.="AND tbl_studentlehrverband.semester='$semester' "; //semester = 100 wählt alle aus
}
$sql_query.="ORDER BY semester, nachname";

//echo $sql_query;
if (!$result_std=pg_query($conn, $sql_query))
	error("Studenten not found!");
$outp='';

// ****************************** Vorrücken ******************************
if (isset($_POST['vorr']))
{
//select für die Vorrückung
$sql_query="SELECT tbl_student.*,tbl_person.*, tbl_studentlehrverband.semester as semester_stlv,  tbl_studentlehrverband.verband as verband_stlv, 
			tbl_studentlehrverband.gruppe as gruppe_stlv FROM tbl_studentlehrverband JOIN tbl_student USING (student_uid)
			JOIN tbl_benutzer ON (student_uid=uid)
			JOIN tbl_person USING (person_id)
			WHERE tbl_benutzer.aktiv AND tbl_studentlehrverband.studiengang_kz='$stg_kz' 
			AND studiensemester_kurzbz='$studiensemester_kurzbz_akt' 
			AND semester_stlv>0";
	if($semester<100)
	{
		$sql_query.="AND tbl_studentlehrverband.semester='$semester' "; //semester = 100 wählt alle aus
	}
	$sql_query.="ORDER BY semester, nachname";
	
	//echo $sql_query;
	if (!$result_std=pg_query($conn, $sql_query))
		error("Studenten not found!");
	$next_ss=$studiensemester_kurzbz_zk;
	while($row=pg_fetch_object($result_std))
	{
		//aktuelle Rolle laden
		$qry_status="SELECT rolle_kurzbz FROM public.tbl_prestudentrolle JOIN public.tbl_prestudent USING(prestudent_id) 
		WHERE person_id=".myaddslashes($row->person_id)." 
		AND studiengang_kz=".$row->studiengang_kz."  
		AND studiensemester_kurzbz=".myaddslashes($studiensemester_kurzbz_akt)." 
		ORDER BY datum desc LIMIT 1;";
		if ($result_status=pg_query($conn, $qry_status))
		{
			if($row_status=pg_fetch_object($result_status))
			{
				$s=$row->semester_stlv+1;
				//Lehrverbandgruppe anlegen, wenn noch nicht vorhanden
				$qry_lvb="SELECT * FROM public.tbl_lehrverband 
				WHERE studiengang_kz=".myaddslashes($row->studiengang_kz)." AND semester=".myaddslashes($s)."
				AND verband=".myaddslashes($row->verband_stlv)." AND gruppe=".myaddslashes($row->gruppe_stlv).";";
				if(pg_num_rows(pg_query($conn, $qry_lvb))<1)
				{
					$lvb_ins="INSERT INTO public.tbl_lehrverband VALUES (".
					myaddslashes($row->studiengang_kz).", ".
					myaddslashes($s).", ".
					myaddslashes($row->verband_stlv).", ".
					myaddslashes($row->gruppe_stlv).", 
					TRUE, NULL, NULL);";
					if (!$r=pg_query($conn, $lvb_ins))
						die(pg_last_error($conn));
				}
				//Eintragen der neuen Gruppe und Rolle
				$sql="INSERT INTO tbl_studentlehrverband
					VALUES ('$row->student_uid','$next_ss','$row->studiengang_kz',
					'$s','$row->verband_stlv','$row->gruppe_stlv',NULL,NULL,now(),'$user',NULL);
					INSERT INTO tbl_prestudentrolle
					VALUES ($row->prestudent_id,'$row_status->rolle_kurzbz','$next_ss',$s,now(),now(),'$user',
					NULL,NULL,NULL);";
				if (!$r=pg_query($conn, $sql))
					die(pg_last_error($conn));
			}
		}
	}

}

// **************** Ausgabe vorbereiten ******************************
$s=array();
$outp.="<SELECT name='stg_kz'>";
foreach ($studiengang as $stg)
{
	$outp.="<OPTION onclick=\"window.location.href = '".$_SERVER['PHP_SELF']."?stg_kz=$stg->studiengang_kz&semester=$semester&studiensemester_kurzbz=$studiensemester_kurzbz'\" ".($stg->studiengang_kz==$stg_kz?'selected':'').">$stg->kurzbzlang ($stg->kuerzel) - $stg->bezeichnung</OPTION>";
	//$outp.= '<A href="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg->studiengang_kz.'&semester='.$semester.'">'.$stg->kuerzel.'</A> - ';
	$s[$stg->studiengang_kz]->max_sem=$stg->max_semester;
	$s[$stg->studiengang_kz]->kurzbz=$stg->kurzbzlang;
}
$outp.='</SELECT>';

$outp.="<select name='studiensemester_kurzbz'>\n";
foreach ($ss_arr AS $sts)
{
	if ($studiensemester_kurzbz == $sts)
		$sel = " selected ";
	else
		$sel = '';
	$outp.="				<option value='".$sts."' ".$sel."onclick=\"window.location.href = '".$_SERVER['PHP_SELF']."?stg_kz=$stg_kz&semester=$semester&studiensemester_kurzbz=$sts'\">".$sts."</option>";
}
$outp.="		</select>\n";
$outp.="<BR>Vorr&uuml;ckung von ".$studiensemester_kurzbz_akt." / Semester ".($semester<100?$semester:'alle')." -> ".$studiensemester_kurzbz_zk;
$outp.= '<BR> -- ';
for ($i=0;$i<=$s[$stg_kz]->max_sem;$i++)
	$outp.= '<A href="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&semester='.$i.'&studiensemester_kurzbz='.$studiensemester_kurzbz.'">'.$i.'</A> -- ';
$outp.= '<A href="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&semester=100&studiensemester_kurzbz='.$studiensemester_kurzbz.'">alle</A> -- ';
?>


<html>
<head>
<title>Studenten Vorrueckung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
</head>
<body class="Background_main">
<?php

echo "<H2>Studenten Vorr&uuml;ckung (".$s[$stg_kz]->kurzbz." - ".($semester<100?$semester:'alle')." - ".
	$studiensemester_kurzbz."), DB:".substr(CONN_STRING,strpos(CONN_STRING,'dbname=')+7,
	strpos(CONN_STRING,'user=')-strpos(CONN_STRING,'dbname=')-7)."</H2>";

echo '<form action="" method="POST">';
echo '<table width="70%"><tr><td>';
echo $outp;
echo '</td><td>';
echo '<input type="submit" name="vorr" value="Vorruecken" />';
echo '</td><td>&nbsp;</td></tr></table>';
echo '</form>';

echo "<h3>&Uuml;bersicht</h3>
	<table class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'>
	<thead>
	<tr class='liste'>";

if ($result_std!=0)
{
	$num_rows=pg_num_rows($result_std);
	echo 'Anzahl: '.$num_rows;
	echo "<th class='table-sortable:default'>Nachname</th><th class='table-sortable:default'>Vorname</th><th class='table-sortable:default'>STG</th><th class='table-sortable:default'>Sem</th><th class='table-sortable:default'>Ver</th><th class='table-sortable:default'>Grp</th><th class='table-sortable:default'>Status</th>\n";
	echo "</tr></thead>";
	echo "<tbody>";
	for($i=0;$i<$num_rows;$i++)
	{
		$row=pg_fetch_object($result_std,$i);
		$qry_status="SELECT rolle_kurzbz FROM public.tbl_prestudentrolle JOIN public.tbl_prestudent USING(prestudent_id) WHERE person_id=".myaddslashes($row->person_id)." AND studiengang_kz=".$row->studiengang_kz."  AND studiensemester_kurzbz=".myaddslashes($studiensemester_kurzbz)." ORDER BY datum desc LIMIT 1;";
		if ($result_status=pg_query($conn, $qry_status))
		{
			if($row_status=pg_fetch_object($result_status))
			{
				echo "<tr>";
				echo "<td>$row->nachname</td><td>$row->vorname</td><td>$row->studiengang_kz</td><td>$row->semester_stlv</td><td>$row->verband_stlv</td><td>$row->gruppe_stlv</td><td>$row_status->rolle_kurzbz</td>";
				echo "</tr>\n";
			}
			else 
			{
				echo "<tr>";
				echo "<td>$row->nachname</td><td>$row->vorname</td><td>$row->studiengang_kz</td><td>$row->semester_stlv</td><td>$row->verband_stlv</td><td>$row->gruppe_stlv</td><td></td>";
				echo "</tr>\n";
			}
		}
		else 
		{
			error("Roles not found!");	
		}
	}
}
else
	echo "Kein Eintrag gefunden!";
?>
</tbody>
</table>

<br>
</body>
</html>