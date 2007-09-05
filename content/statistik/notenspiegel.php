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
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/note.class.php');
require_once('../../include/lehrveranstaltung.class.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Connecten zur Datenbank');

$user = get_uid();
loadVariables($conn, $user);

if(!isset($_GET['studiengang_kz']))
	die('Falsche Parameteruebergabe');
else 
	$studiengang_kz = $_GET['studiengang_kz'];

$semester = isset($_GET['semester'])?$_GET['semester']:'';


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Lehreinheit</title>
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<style type="text/css">
td, th
{
	border: 1px solid black;
	text-align: center;
}
</style>
</head>
<body class="Background_main">

<?php
$stg = new studiengang($conn);
$stg->load($studiengang_kz);

echo "<h2>Notenspiegel $stg->kuerzel $semester</h2>";

$student = new student($conn);
$result_student = $student->getStudents($studiengang_kz,$semester,null,null,null, $semester_aktuell);

$lehrveranstaltung = new lehrveranstaltung($conn);
$lehrveranstaltung->load_lva($studiengang_kz, $semester, null, null, true);

$noten = new note($conn);
$noten->getAll();
$noten_arr = array();
$noten_farben = array();

foreach ($noten->result as $row)
{
	$noten_arr[$row->note]=$row->anmerkung;
	$noten_farben[$row->note]=$row->farbe;
}

echo '<table class="liste" style="border: 1px solid black" cellspacing="0"><tr class="liste"><th>Nr</th><th>Name</th><th>Personenkennzeichen</th>';
foreach ($lehrveranstaltung->lehrveranstaltungen as $row_lva)
{
	echo "<th>$row_lva->bezeichnung</th>";
}
echo '<th>Notendurchschnitt</td>';
echo '</tr>';
$i=0;
$anzahl_lv=array();
$summe_lv=array();

foreach ($result_student as $row_student)
{
	$i++;
	echo "<tr><td>$i</td><td>$row_student->nachname $row_student->vorname</td><td>$row_student->matrikelnr</td>";
	
	$noten = array();
	$qry = "SELECT * FROM lehre.tbl_zeugnisnote WHERE student_uid='$row_student->uid' AND studiensemester_kurzbz='$semester_aktuell'";
	if($result = pg_query($conn, $qry))
		while($row = pg_fetch_object($result))
			$noten[$row->lehrveranstaltung_id] = $row->note;
	
	$anzahl=0;
	$summe=0;
	foreach ($lehrveranstaltung->lehrveranstaltungen as $row_lva)
	{
		if(isset($noten[$row_lva->lehrveranstaltung_id]))
		{
			if($noten[$row_lva->lehrveranstaltung_id]=='5')
				echo "<td style='background-color: red'>".$noten_arr[$noten[$row_lva->lehrveranstaltung_id]]."</td>";
			else
				echo "<td>".$noten_arr[$noten[$row_lva->lehrveranstaltung_id]]."</td>";
			if(is_numeric($noten_arr[$noten[$row_lva->lehrveranstaltung_id]]))
			{
				if(!isset($summe_lv[$row_lva->lehrveranstaltung_id]))
				{
					$summe_lv[$row_lva->lehrveranstaltung_id]=0;
					$anzahl_lv[$row_lva->lehrveranstaltung_id]=0;
				}
				$summe_lv[$row_lva->lehrveranstaltung_id] += $noten[$row_lva->lehrveranstaltung_id];
				$anzahl_lv[$row_lva->lehrveranstaltung_id]++;
				$summe+=$noten[$row_lva->lehrveranstaltung_id];
				$anzahl++;
			}
		}
		else 
			echo '<td style="background-color: lightgreen">&nbsp;</td>';
	}
	if($anzahl!=0)
		$schnitt = $summe/$anzahl;
	else
		$schnitt=0;
	echo "<td>".($schnitt==0?'&nbsp;':sprintf("%.2f", $schnitt))."</td>";
	echo '</tr>';
}

echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td>Notendurchschnitt</td>';
$summe_schnitt=0;
$anzahl_schnitt=0;
foreach ($lehrveranstaltung->lehrveranstaltungen as $row_lva)
{
	if(isset($summe_lv[$row_lva->lehrveranstaltung_id]))
	{
		if($anzahl_lv[$row_lva->lehrveranstaltung_id]!=0)
			$schnitt = $summe_lv[$row_lva->lehrveranstaltung_id]/$anzahl_lv[$row_lva->lehrveranstaltung_id];
		else 
			$schnitt = 0;
	}
	else
		$schnitt=0;
	if($schnitt!=0)
	{
		$summe_schnitt +=$schnitt;
		$anzahl_schnitt++;
	}
	echo "<td>".($schnitt==0?'&nbsp;':sprintf("%.2f",$schnitt))."</td>";
}

if($anzahl_schnitt!=0)
	$schnitt = $summe_schnitt/$anzahl_schnitt;
else 
	$schnitt=0;
echo "<td>".($schnitt==0?'&nbsp;':sprintf("%.2f",$schnitt))."</td>";

echo '</table>';
?>
</body>
</html>