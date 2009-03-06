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
/*
Vorrückung aller AKTIVEN Studenten.
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

$ausbildungssemester=0;
$s=new studiengang($conn);
$s->getAll('typ, kurzbz', true);
$studiengang=$s->result;

//Einlesen der studiensemester in einen Array
$ss = new studiensemester($conn);
$ss->getAll();
foreach($ss->studiensemester as $studiensemester)
{
	$ss_arr[] = $studiensemester->studiensemester_kurzbz;
}

$user = get_uid();

//Übergabeparameter
//studiengang
if (isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
{
	$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:$_POST['stg_kz']);
}
else
{
	$stg_kz=0;
}
//semester anzeige
if (isset($_GET['semester']) || isset($_POST['semester']))
{
	$semester=(isset($_GET['semester'])?$_GET['semester']:$_POST['semester']);
}
else
{
	$semester=100;
}
//semester vorrückung
if (isset($_GET['semesterv']) || isset($_POST['semesterv']))
{
	$semesterv=(isset($_GET['semesterv'])?$_GET['semesterv']:$_POST['semesterv']);
}
else
{
	$semesterv=100;
}
//angezeigtes studiensemester
if (isset($_GET['studiensemester_kurzbz']) || isset($_POST['studiensemester_kurzbz']))
{
	$studiensemester_kurzbz=(isset($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:$_POST['studiensemester_kurzbz']);
}
else
{
	$studiensemester_kurzbz=$ss->getakt();
}
//ausgangssemester für vorrückung
if (isset($_GET['studiensemester_kurzbz_akt']) || isset($_POST['studiensemester_kurzbz_akt']))
{
	$studiensemester_kurzbz_akt=(isset($_GET['studiensemester_kurzbz_akt'])?$_GET['studiensemester_kurzbz_akt']:$_POST['studiensemester_kurzbz_akt']);
}
else
{
	$studiensemester_kurzbz_akt=$studiensemester_kurzbz;
}
//zielsemester für vorrückung
if (isset($_GET['studiensemester_kurzbz_zk']) || isset($_POST['studiensemester_kurzbz_zk']))
{
	$studiensemester_kurzbz_zk=(isset($_GET['studiensemester_kurzbz_zk'])?$_GET['studiensemester_kurzbz_zk']:$_POST['studiensemester_kurzbz_zk']);
}
else
{
	$studiensemester_kurzbz_zk=$ss->getNextFrom($studiensemester_kurzbz_akt);
}



if(!is_numeric($stg_kz))
{
	$stg_kz=0;
}
//semester=100 bedeutet die Auswahl aller Semester
if(!is_numeric($semester))
{
	$semester=100;
}

//Einlesen der maximalen, regulären Dauer der Studiengänge in einen Array
$qry_stg="SELECT * FROM public.tbl_studiengang";
if ($result_stg=pg_query($conn, $qry_stg))
{
	while($row_stg=pg_fetch_object($result_stg))
	{
		$max[$row_stg->studiengang_kz]=$row_stg->max_semester;
	}
}	
	
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
{
	error("Studenten not found!");
}
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
			AND studiensemester_kurzbz='$studiensemester_kurzbz_akt'";
	if($semester<100)
	{
		$sql_query.="AND tbl_studentlehrverband.semester='$semesterv' "; //semester = 100 wählt alle aus
	}
	$sql_query.="ORDER BY semester, nachname";
	
	//echo $sql_query;
	if (!$result_std=pg_query($conn, $sql_query))
	{
		error("Studenten not found!");
	}
	$next_ss=$studiensemester_kurzbz_zk;
	while($row=pg_fetch_object($result_std))
	{
		//aktuelle Rolle laden
		$qry_status="SELECT * FROM public.tbl_prestudentrolle JOIN public.tbl_prestudent USING(prestudent_id) 
		WHERE person_id=".myaddslashes($row->person_id)." 
		AND studiengang_kz=".$row->studiengang_kz."  
		AND studiensemester_kurzbz=".myaddslashes($studiensemester_kurzbz_akt)." 
		ORDER BY datum desc, tbl_prestudentrolle.insertamum desc, tbl_prestudentrolle.ext_id desc LIMIT 1;";
		if ($result_status=pg_query($conn, $qry_status))
		{
			if($row_status=pg_fetch_object($result_status))
			{
				//Studenten im letzten Semester bleiben dort, wenn aktiv
				if($row->semester_stlv>=$max[$stg_kz] || $row->semester_stlv==0)
				{
					$s=$row->semester_stlv;
				}
				else
				{
					$s=$row->semester_stlv+1;
				}
				if($row_status->ausbildungssemester>=$max[$stg_kz] || $row_status->rolle_kurzbz=="Unterbrecher" || $row_status->rolle_kurzbz=="Incoming")
				{
					$ausbildungssemester=$row_status->ausbildungssemester;
				}
				else 
				{
					$ausbildungssemester=$row_status->ausbildungssemester+1;
				}
				//Lehrverbandgruppe anlegen, wenn noch nicht vorhanden
				$qry_lvb="SELECT * FROM public.tbl_lehrverband 
				WHERE studiengang_kz=".myaddslashes($row->studiengang_kz)." AND semester=".myaddslashes($s)."
				AND verband=".myaddslashes($row->verband_stlv)." AND gruppe=".myaddslashes($row->gruppe_stlv).";";
				if(pg_num_rows(pg_query($conn, $qry_lvb))<1)
				{
					$lvb_ins="INSERT INTO public.tbl_lehrverband (studiengang_kz, semester, verband, gruppe, aktiv, bezeichnung, ext_id) VALUES (".
					myaddslashes($row->studiengang_kz).", ".
					myaddslashes($s).", ".
					myaddslashes($row->verband_stlv).", ".
					myaddslashes($row->gruppe_stlv).", 
					TRUE, NULL, NULL);";
					if (!$r=pg_query($conn, $lvb_ins))
						die(pg_last_error($conn));
				}
				//Überprüfen ob Eintrag schon vorhanden
				$qry_chk="SELECT * FROM public.tbl_studentlehrverband 
						WHERE student_uid=".myaddslashes($row->student_uid)." 
						AND studiensemester_kurzbz=".myaddslashes($next_ss).";";
				$sql='';
				if(pg_num_rows(pg_query($conn, $qry_chk))<1)
				{
					//Eintragen der neuen Gruppe
					$sql="INSERT INTO tbl_studentlehrverband (student_uid, studiensemester_kurzbz, studiengang_kz, semester, verband, gruppe, updateamum, updatevon, insertamum, insertvon, ext_id) 
						VALUES ('$row->student_uid','$next_ss','$row->studiengang_kz',
						'$s','$row->verband_stlv','$row->gruppe_stlv',NULL,NULL,now(),'$user',NULL);";
				}
				$qry_chk="SELECT * FROM public.tbl_prestudentrolle
						WHERE prestudent_id=".myaddslashes($row->prestudent_id)." 
						AND studiensemester_kurzbz=".myaddslashes($next_ss).";";
				if(pg_num_rows(pg_query($conn, $qry_chk))<1)
				{
					//Eintragen des neuen Status
					$sql.="INSERT INTO tbl_prestudentrolle (prestudent_id, rolle_kurzbz, studiensemester_kurzbz, ausbildungssemester, datum, insertamum, insertvon, updateamum, updatevon, ext_id, orgform_kurzbz)
					VALUES ($row->prestudent_id, '$row_status->rolle_kurzbz', '$next_ss',
						$ausbildungssemester, now(), now(), '$user',
					NULL, NULL, NULL, ".myaddslashes($row_status->orgform_kurzbz).");";
				}
				if($sql!='')
				{
					if (!$r=pg_query($conn, $sql))
					{
						die(pg_last_error($conn)."<br>".$sql);
					}
				}
			}
		}
	}

}

// **************** Ausgabe vorbereiten ******************************
$s=array();
$outp.="-----Anzeige------------------------------------------------------------------------------------------------------------------------------------------";
$outp.="<br>Studiengang: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<SELECT name='stg_kz'>";
//Auswahl Studiengang
foreach ($studiengang as $stg)
{
	$outp.="<OPTION onclick=\"window.location.href = '".$_SERVER['PHP_SELF']."?stg_kz=$stg->studiengang_kz&semester=$semester&semesterv=$semesterv&studiensemester_kurzbz=$studiensemester_kurzbz&studiensemester_kurzbz_akt=$studiensemester_kurzbz_akt&studiensemester_kurzbz_zk=$studiensemester_kurzbz_zk'\" ".($stg->studiengang_kz==$stg_kz?'selected':'').">$stg->kurzbzlang ($stg->kuerzel) - $stg->bezeichnung</OPTION>";
	//$outp.= '<A href="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg->studiengang_kz.'&semester='.$semester.'">'.$stg->kuerzel.'</A> - ';
	$s[$stg->studiengang_kz]->max_sem=$stg->max_semester;
	$s[$stg->studiengang_kz]->kurzbz=$stg->kurzbzlang;
}
$outp.='</SELECT>';
//Auswahl angezeigtes Studiensemester
$outp.="<br>Angezeigtes Studiensemester: <select name='studiensemester_kurzbz'>\n";
foreach ($ss_arr AS $sts)
{
	if ($studiensemester_kurzbz == $sts)
		$sel = " selected ";
	else
		$sel = '';
	$outp.="				<option value='".$sts."' ".$sel."onclick=\"window.location.href = '".$_SERVER['PHP_SELF']."?stg_kz=$stg_kz&semester=$semester&semesterv=$semesterv&studiensemester_kurzbz=$sts&studiensemester_kurzbz_akt=$studiensemester_kurzbz_akt&studiensemester_kurzbz_zk=$studiensemester_kurzbz_zk'\">".$sts."</option>";
}
$outp.="		</select>";
$outp.= '<BR>Ausbildungssemester der Anzeige: -- ';
for ($i=0;$i<=$s[$stg_kz]->max_sem;$i++)
{
	$outp.= '<A href="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&semester='.$i.'&semesterv='.$semesterv.'&studiensemester_kurzbz='.$studiensemester_kurzbz.'&studiensemester_kurzbz_akt='.$studiensemester_kurzbz_akt.'&studiensemester_kurzbz_zk='.$studiensemester_kurzbz_zk.'">'.$i.'</A> -- ';
}
$outp.= '<A href="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&semesterv='.$semesterv.'&semester=100&studiensemester_kurzbz='.$studiensemester_kurzbz.'&studiensemester_kurzbz_akt='.$studiensemester_kurzbz_akt.'&studiensemester_kurzbz_zk='.$studiensemester_kurzbz_zk.'">alle</A> -- ';

//Auswahl Studiensemester von dem weg vorgerückt werden soll
$outp.="<br>-----Vorr&uuml;ckung Studiengang ".$s[$stg_kz]->kurzbz."----------------------------------------------------------------------------------------------------------";
$outp.="<br>Ausgangs-Studiensemester: &nbsp;&nbsp;&nbsp;&nbsp;<select name='studiensemester_kurzbz_akt'>\n";
foreach ($ss_arr AS $sts2)
{
	if ($studiensemester_kurzbz_akt == $sts2)
		$sel2 = " selected ";
	else
		$sel2 = '';
	$outp.="				<option value='".$sts2."' ".$sel2."onclick=\"window.location.href = '".$_SERVER['PHP_SELF']."?stg_kz=$stg_kz&semester=$semester&semesterv=$semesterv&studiensemester_kurzbz=$studiensemester_kurzbz&studiensemester_kurzbz_akt=$sts2&studiensemester_kurzb_zk=$studiensemester_kurzbz_zk'\">".$sts2."</option>";
}
$outp.="		</select>\n";
$outp.= '<BR>Ausgangs-Ausbildungssemester: &nbsp;&nbsp;-- ';
for ($j=0;$j<=$s[$stg_kz]->max_sem;$j++)
{
	$outp.= '<A href="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&semester='.$semester.'&semesterv='.$j.'&studiensemester_kurzbz='.$studiensemester_kurzbz.'&studiensemester_kurzbz_akt='.$studiensemester_kurzbz_akt.'&studiensemester_kurzbz_zk='.$studiensemester_kurzbz_zk.'">'.$j.'</A> -- ';
}
$outp.= '<A href="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&semester='.$semester.'&semesterv=100&studiensemester_kurzbz='.$studiensemester_kurzbz.'&studiensemester_kurzbz_akt='.$studiensemester_kurzbz_akt.'&studiensemester_kurzbz_zk='.$studiensemester_kurzbz_zk.'">alle</A> -- ';
//Auswahl Studiensemester in das vorgerückt werden soll
$outp.="<br>Ziel-Studiensemester: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select name='studiensemester_kurzbz_zk'>\n";
foreach ($ss_arr AS $sts3)
{
	if ($studiensemester_kurzbz_zk == $sts3)
		$sel3 = " selected ";
	else
		$sel3 = '';
	$outp.="				<option value='".$sts3."' ".$sel3."onclick=\"window.location.href = '".$_SERVER['PHP_SELF']."?stg_kz=$stg_kz&semester=$semester&semesterv=$semesterv&studiensemester_kurzbz=$studiensemester_kurzbz&studiensemester_kurzbz_akt=$studiensemester_kurzbz_akt&studiensemester_kurzbz_zk=$sts3'\">".$sts3."</option>";
}
$outp.="		</select>\n";
$outp.="<BR>Vorr&uuml;ckung von ".$studiensemester_kurzbz_akt." / ".($semesterv<100?$semesterv.".":'alle')." Semester  -> ".$studiensemester_kurzbz_zk;

//Aufbau Ausgabe
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
//Überschrift
echo "<H2>Studenten Vorr&uuml;ckung (".$s[$stg_kz]->kurzbz." - ".($semester<100?$semester:'alle')." - ".
	$studiensemester_kurzbz."), DB:".substr(CONN_STRING,strpos(CONN_STRING,'dbname=')+7,
	strpos(CONN_STRING,'user=')-strpos(CONN_STRING,'dbname=')-7)."</H2>";

echo '<form action="" method="POST">';
echo '<table width="70%"><tr><td>';
//Ausgabe der Auswahl
echo $outp;
echo '</td><td>';
echo '<br><br><br><input type="submit" name="vorr" value="Vorruecken" />';
echo '</td><td>&nbsp;</td></tr></table>';
echo '</form>';
//Überschrift Anzeige
echo "<h3>&Uuml;bersicht (".$studiensemester_kurzbz."/".($semester<100?$semester.".":'alle')." Semester )</h3>
	<table class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'>
	<thead>
	<tr class='liste'>";
//Anzeige Tabelle
if ($result_std!=0)
{
	$num_rows=pg_num_rows($result_std);
	echo 'Anzahl: '.$num_rows;
	echo "<th class='table-sortable:default'>Nachname</th><th class='table-sortable:default'>Vorname</th><th class='table-sortable:default'>STG</th><th class='table-sortable:default'>Sem</th><th class='table-sortable:default'>Ver</th><th class='table-sortable:default'>Grp</th><th class='table-sortable:default'>Status</th><th class='table-sortable:default'>AusbSem</th>\n";
	echo "</tr></thead>";
	echo "<tbody>";
	for($i=0;$i<$num_rows;$i++)
	{
		$row=pg_fetch_object($result_std,$i);
		$qry_status="SELECT rolle_kurzbz, ausbildungssemester FROM public.tbl_prestudentrolle 
			JOIN public.tbl_prestudent USING(prestudent_id) WHERE person_id=".myaddslashes($row->person_id)." 
			AND studiengang_kz=".$row->studiengang_kz."  
			AND studiensemester_kurzbz=".myaddslashes($studiensemester_kurzbz)." 
			ORDER BY datum desc, tbl_prestudentrolle.insertamum desc, tbl_prestudentrolle.ext_id desc LIMIT 1;";
		if ($result_status=pg_query($conn, $qry_status))
		{
			if($row_status=pg_fetch_object($result_status))
			{
				echo "<tr>";
				echo "<td>$row->nachname</td><td>$row->vorname</td><td>$row->studiengang_kz</td><td>$row->semester_stlv</td><td>$row->verband_stlv</td><td>$row->gruppe_stlv</td><td>$row_status->rolle_kurzbz</td><td>$row_status->ausbildungssemester</td>";
				echo "</tr>\n";
			}
			else 
			{
				echo "<tr>";
				echo "<td>$row->nachname</td><td>$row->vorname</td><td>$row->studiengang_kz</td><td>$row->semester_stlv</td><td>$row->verband_stlv</td><td>$row->gruppe_stlv</td><td></td><td></td>";
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