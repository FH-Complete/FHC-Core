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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
 */
require_once('../config.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/functions.inc.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

$s=new studiengang($conn);
$s->getAll('typ, kurzbz', false);
$studiengang=$s->result;

$user = get_uid();
// Benutzerdefinierte Variablen laden
echo loadVariables($conn,$user);

// Bezeichnungen fuer Tabellen und Views
$lva_stpl_view=VIEW_BEGIN.'lva_'.$db_stpl_table;
$stpl_table=TABLE_BEGIN.$db_stpl_table;

if (isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
	$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:$_POST['stg_kz']);
else
	$stg_kz=0;
if (isset($_GET['sem']) || isset($_POST['sem']))
	$sem=(isset($_GET['sem'])?$_GET['sem']:$_POST['sem']);
else
	$sem=0;

//*************** im Stundenplan hinzufuegen *************************
if (isset($_GET['insert']))
	if ($_GET['insert']=='true')
	{
		// Variablen checken
		if (isset($_GET['studiengang_kz']))
			$studiengang_kz=$_GET['studiengang_kz'];
		if (isset($_GET['semester']))
			$semester=$_GET['semester'];
		if (isset($_GET['verband']))
			$verband=$_GET['verband'];
		else
			$verband=' ';
		if ($verband=='')
			$verband=' ';
		if (isset($_GET['gruppe']))
			$gruppe=$_GET['gruppe'];
		else
			$gruppe=' ';
		if ($gruppe=='')
			$gruppe=' ';
		if (isset($_GET['gruppe_kurzbz']))
			$gruppe_kurzbz=$_GET['gruppe_kurzbz'];
		else
			$gruppe_kurzbz='';
		if (isset($_GET['leid']))
			$leid=$_GET['leid'];
		else
			$leid=0;
		// Termine holen
		$qry = "SELECT DISTINCT datum, stunde FROM lehre.$stpl_table WHERE lehreinheit_id=$leid";
		//echo $qry.'<BR>';
		if(!$result=pg_query($conn, $qry))
			die ($qry);
		while ($row=pg_fetch_object($result))
		{
			$qry = "SELECT DISTINCT ort_kurzbz FROM lehre.$stpl_table
					WHERE lehreinheit_id=$leid AND datum='$row->datum' AND stunde=$row->stunde;";
			if(!$result_ort=pg_query($conn, $qry))
				die ($qry);
			while ($row_ort=pg_fetch_object($result_ort))
			{
				$qry="INSERT INTO lehre.$stpl_table (datum,stunde,ort_kurzbz,unr,mitarbeiter_uid,studiengang_kz,semester,verband,gruppe,gruppe_kurzbz,lehreinheit_id, insertvon)
						VALUES ('$row->datum', $row->stunde,'$row_ort->ort_kurzbz',$unr,'$lektor_uid',$studiengang_kz,$semester,'$verband','$gruppe',";
				if ($gruppe_kurzbz!='')
					$qry.="'$gruppe_kurzbz',$leid,'LVPlanCheck');";
				else
					$qry.="NULL,$leid,'LVPlanCheck');";
				echo $qry.'<BR>';
				if(!$result_insert=pg_query($conn, $qry))
					die ($qry);
			}
		}
	}

$where=" studiensemester_kurzbz='WS2007'";
if ($semester>0)
	$where.=" AND semester=$semester";
if ($stg_kz>0)
	$where.=" AND studiengang_kz='$stg_kz'";

if(!is_numeric($stg_kz))
	$stg_kz=0;
if(!is_numeric($semester))
	$semester=0;


if(isset($_GET['lvid']) && is_numeric($_GET['lvid']))
{

	//Lehrevz Speichern
	if(isset($_POST['lehrevz']))
	{
		$qry = "UPDATE lehre.tbl_lehrveranstaltung SET lehreverzeichnis='".addslashes($_POST['lehrevz'])."' WHERE lehrveranstaltung_id='".$_GET['lvid']."'";
		if(!pg_query($conn, $qry))
			echo "Fehler beim Speichern!";
		else
			echo "Erfolgreich gespeichert";
	}
}

$sql_query="SELECT *, planstunden-verplant::smallint AS offenestunden
			FROM lehre.$lva_stpl_view JOIN lehre.tbl_lehrform ON $lva_stpl_view.lehrform=tbl_lehrform.lehrform_kurzbz
			WHERE $where AND verplant=0 AND planstunden>0 AND lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.$stpl_table)
			ORDER BY offenestunden DESC, lehrfach, lehrform, semester, verband, gruppe, gruppe_kurzbz;";
//echo $sql_query;
if(!$result_lv=pg_query($conn, $sql_query))
	die ($sql_query);
if(!$result_lv) error("Lehrveranstaltung not found!");
$outp='';
$s=array();
$outp.="<SELECT name='stg_kz'>";
foreach ($studiengang as $stg)
{
	$outp.="<OPTION onclick=\"window.location.href = '".$_SERVER['PHP_SELF']."?stg_kz=$stg->studiengang_kz&semester=$semester'\" ".($stg->studiengang_kz==$stg_kz?'selected':'').">$stg->kuerzel - $stg->bezeichnung</OPTION>";
	//$outp.= '<A href="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg->studiengang_kz.'&sem='.$semester.'">'.$stg->kuerzel.'</A> - ';
	$s[$stg->studiengang_kz]->max_sem=$stg->max_semester;
	$s[$stg->studiengang_kz]->kurzbz=$stg->kurzbzlang;
}
$outp.='</SELECT>';
$outp.= '<BR> -- ';
for ($i=0;$i<=$s[$stg_kz]->max_sem;$i++)
	$outp.= '<A href="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&semester='.$i.'">'.$i.'</A> -- ';
?>

<html>
<head>
<title>Lehrveranstaltung Verwaltung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
</head>
<body class="Background_main">
<?php

echo "<H2>LV-Plan Wartung (".$s[$stg_kz]->kurzbz." - ".$semester.") ($lva_stpl_view)</H2>";

echo '<table width="100%"><tr><td>';
echo $outp;
echo '</td><td>';
echo "<input type='button' onclick='parent.detail.location=\"lehrveranstaltung_details.php?neu=true&stg_kz=$stg_kz&semester=$semester\"' value='Neu'/>";
echo '</td></tr></table>';

echo "<h3>&Uuml;bersicht</h3>
	<table class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'>
	<thead>
	<tr class='liste'>";

if ($result_lv!=0)
{
	$num_rows=pg_num_rows($result_lv);

//  raumtyp raumtypalternativ stundenblockung wochenrythmus semesterstunden  start_kw anmerkung
	echo "<th class='table-sortable:default'>LE-ID</th><th class='table-sortable:default'>UNR</th><th class='table-sortable:default'>Lehrfach</th><th class='table-sortable:default'>Lektor</th>
			<th class='table-sortable:default'>Lehrverband</th><th class='table-sortable:default'>Gruppe</th><th class='table-sortable:default'>SS</th><th class='table-sortable:numeric'>planstunden</th><th class='table-sortable:default'>Verplant</th>\n";
	echo "</tr></thead>";
	echo "<tbody>";
	for($i=0;$i<$num_rows;$i++)
	{
	   $row=pg_fetch_object($result_lv);
	   echo "<tr>";
	   echo "<td align='right'>$row->lehreinheit_id</td><td>$row->unr</td><td>$row->lehrfach-$row->lehrform - $row->lehrfach_bez</td><td>$row->lektor</td>";
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