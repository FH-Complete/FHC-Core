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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/**
 * Dieses Script wird verwendet, um zu bereits verplanten LVPlan Eintraegen zusaetzliche
 * Gruppen dazu zu verplanen. (zB fuer Incoming Gruppen die erst spaeter zur LV hinzugefuegt werden)
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

// Benutzerdefinierte Variablen laden
$user = get_uid();
loadVariables($user);

// Berechtigungen pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('lehre/lvplan', null,'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

// Variablen Initialisieren
$studiengang_kz=0;
$lektor_uid=0;
$unr=0;
$semester=0;
$verband=' ';
$gruppe=' ';
$gruppe_kurzbz='';
$leid=0;

$stg_kz=0;
$sem=0;

$insert=false;

if(isset($_GET['stg_kz']))
	$stg_kz = $_GET['stg_kz'];

if(isset($_GET['studiengang_kz']))
	$studiengang_kz = $_GET['studiengang_kz'];

if(isset($_GET['sem']))
	$sem = $_GET['sem'];

if(isset($_GET['semester']))
	$semester = $_GET['semester'];

if(isset($_GET['verband']))
	$verband = $_GET['verband'];

if(isset($_GET['gruppe']))
	$gruppe = $_GET['gruppe'];

if(isset($_GET['leid']))
	$leid = $_GET['leid'];

if(isset($_GET['gruppe_kurzbz']))
	$gruppe_kurzbz = $_GET['gruppe_kurzbz'];

if(isset($_GET['lektor_uid']))
	$lektor_uid = $_GET['lektor_uid'];

if(isset($_GET['unr']))
	$unr = $_GET['unr'];

// Plausib der Variablen
if ($verband=='')
	$verband=' ';
if ($gruppe=='')
	$gruppe=' ';

if(!is_numeric($stg_kz))
	$stg_kz=0;
if(!is_numeric($semester))
	$semester=0;

$insert = (isset($_GET['insert'])?$_GET['insert']:false);
$insert=trim($insert);
$insert=(empty($insert)?false:true);


//	Studiengang lesen
$s=new studiengang();
$s->getAll('typ, kurzbz', false);
$studiengang=$s->result;

// Bezeichnungen fuer Tabellen und Views
$lva_stpl_view=VIEW_BEGIN.'lva_'.$db_stpl_table;
$stpl_table=TABLE_BEGIN.$db_stpl_table;

$outp='';

// Eintrag im Stundenplan hinzufuegen
if ($insert)
{
	// Termine holen
	$qry = "SELECT DISTINCT datum, stunde FROM lehre.$stpl_table WHERE lehreinheit_id=".$db->db_add_param($leid, FHC_INTEGER);

	if(!$result=$db->db_query($qry))
		die ($qry .' '.$db->db_last_error());

	while ($row=$db->db_fetch_object($result))
	{
		$qry = "SELECT
					DISTINCT ort_kurzbz
				FROM
					lehre.".$stpl_table."
				WHERE
					lehreinheit_id=".$db->db_add_param($leid, FHC_INTEGER)."
					AND datum=".$db->db_add_param($row->datum)."
					AND stunde=".$db->db_add_param($row->stunde).";";

		if(!$result_ort=$db->db_query($qry))
			die ("DB Fehler $qry" .' '.$db->db_last_error());

		while ($row_ort=$db->db_fetch_object($result_ort))
		{
			// Pruefen ob der Eintrag schon in der Datenbank vorhanden ist
			// da sonst bei mehrmaligem Refresh der Seite der Eintrag oefter eingetragen wird
			$qry = "SELECT
						1
					FROM
						lehre.$stpl_table
					WHERE datum=".$db->db_add_param($row->datum).
					'	AND stunde='.$db->db_add_param($row->stunde).
					'	AND ort_kurzbz='.$db->db_add_param($row_ort->ort_kurzbz).
					'	AND unr='.$db->db_add_param($unr).
					'	AND mitarbeiter_uid='.$db->db_add_param($lektor_uid).
					'	AND studiengang_kz='.$db->db_add_param($studiengang_kz).
					'	AND semester='.$db->db_add_param($semester).
					'	AND verband='.$db->db_add_param($verband).
					'	AND	gruppe='.$db->db_add_param($gruppe);

			if ($gruppe_kurzbz!='')
				$qry.=' AND gruppe_kurzbz='.$db->db_add_param($gruppe_kurzbz);
			else
				$qry.=' AND gruppe_kurzbz is null';

			if($result_stplcheck=$db->db_query($qry))
			{
				if($db->db_num_rows($result_stplcheck)==0)
				{
					$qry="INSERT INTO lehre.$stpl_table (datum,stunde,ort_kurzbz,unr,mitarbeiter_uid,studiengang_kz,
							semester,verband,gruppe,gruppe_kurzbz,lehreinheit_id, updatevon, insertvon)
							VALUES (".$db->db_add_param($row->datum).",".
							$db->db_add_param($row->stunde).",".
							$db->db_add_param($row_ort->ort_kurzbz).",".
							$db->db_add_param($unr).",".
							$db->db_add_param($lektor_uid).",".
							$db->db_add_param($studiengang_kz).",".
							$db->db_add_param($semester).",".
							$db->db_add_param($verband).",".
							$db->db_add_param($gruppe).",";

					if ($gruppe_kurzbz!='')
						$qry.=$db->db_add_param($gruppe_kurzbz).",";
					else
						$qry.="NULL,";

					$qry.=$db->db_add_param($leid, FHC_INTEGER).",'".$user."','".$user."');";

					if(!$result_insert=$db->db_query($qry))
						die ("DB Fehler $qry" .' '.$db->db_last_error());
				}
				else
				{
					$outp.='<span class="error">Fehlgeschlagen: Eintrag bereits vorhanden</span>';
				}
			}
		}
	}
}

$stsem_obj = new studiensemester();
$studiensemester = $semester_aktuell;

$where=" studiensemester_kurzbz=".$db->db_add_param($studiensemester);

if (!empty($semester))
	$where.=" AND semester=".$db->db_add_param($semester, FHC_INTEGER);

if (!empty($stg_kz))
	$where.=" AND studiengang_kz=".$db->db_add_param($stg_kz, FHC_INTEGER);

$sql_query="SELECT
			*, planstunden-verplant::smallint AS offenestunden
		FROM
			lehre.$lva_stpl_view
			JOIN lehre.tbl_lehrform ON ($lva_stpl_view.lehrform=tbl_lehrform.lehrform_kurzbz)
		WHERE $where
			AND verplant=0
			AND planstunden>0
			AND lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.$stpl_table)
		ORDER BY offenestunden DESC, lehrfach, lehrform, semester, verband, gruppe, gruppe_kurzbz;";

if(!$result_lv=$db->db_query($sql_query))
	die ("DB Fehler $sql_query"  .' '.$db->db_last_error());
if(!$result_lv)
	die("Lehrveranstaltung not found!");

$s=array();
$outp.='<SELECT name="stg_kz" onchange="window.location.href=this.value">';
foreach ($studiengang as $stg)
{
	$outp.="<OPTION value=\"".$_SERVER['PHP_SELF']."?stg_kz=$stg->studiengang_kz&semester=$semester\" ".($stg->studiengang_kz==$stg_kz?'selected':'').">$stg->kuerzel - $stg->bezeichnung</OPTION>";
	$a = new stdClass();
	$a->max_sem=$stg->max_semester;
	$a->kurzbz=$stg->kurzbzlang;
	$s[$stg->studiengang_kz]=$a;
}

$outp.='</SELECT>';
$outp.= '<BR>-- ';
for ($i=0;$i<=$s[$stg_kz]->max_sem;$i++)
	$outp.= '<A href="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&semester='.$i.'">'.$i.'</A> -- ';
?>
<html>
<head>
	<title>LVPlan Wartung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css">
	<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
	<script type="text/javascript">
	$(document).ready(function()
	{
		$("#t1").tablesorter(
		{
			sortList: [[2,1]],
			widgets: ["zebra"]
		});
	});
	</script>
</head>
<body>
<?php

echo "<H2>LV-Plan Wartung (".$s[$stg_kz]->kurzbz." - ".$semester.") ($lva_stpl_view)</H2>";
echo $outp;

echo '<h3>&Uuml;bersicht</h3>
	<table id="t1" class="tablesorter">
	<thead>
	<tr>';

if ($result_lv!=0)
{
	$num_rows=$db->db_num_rows($result_lv);

//  raumtyp raumtypalternativ stundenblockung wochenrythmus semesterstunden  start_kw anmerkung
	echo "<th>LE-ID</th>
			<th>UNR</th>
			<th>Lehrfach</th>
			<th>Lektor</th>
			<th>Lehrverband</th>
			<th>Gruppe</th>
			<th>SS</th>
			<th>planstunden</th>
			<th>Verplant</th>\n";
	echo "</tr></thead>";
	echo "<tbody>";
	for($i=0;$i<$num_rows;$i++)
	{
		$row=$db->db_fetch_object($result_lv);
		echo "<tr>";
		echo "<td align='right'>$row->lehreinheit_id</td>";
		echo "<td>$row->unr</td>";
		echo "<td>$row->lehrfach-$row->lehrform - $row->lehrfach_bez</td>";
		echo "<td>$row->lektor</td>";
		echo "<td>$row->studiengang-$row->semester$row->verband$row->gruppe</td><td>$row->gruppe_kurzbz</td>";
		echo "<td>$row->studiensemester_kurzbz</td>";
		echo "<td>$row->planstunden</td>";
		echo "<td>$row->verplant</td>";
		echo "<td><a href='?insert=true&leid=$row->lehreinheit_id&unr=$row->unr&lektor_uid=$row->lektor_uid&studiengang_kz=$row->studiengang_kz&semester=$row->semester&verband=$row->verband&gruppe=$row->gruppe&gruppe_kurzbz=$row->gruppe_kurzbz&stg_kz=$stg_kz&sem=$sem'>Hinzufuegen</a></td>";
		echo "</tr>\n";
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
