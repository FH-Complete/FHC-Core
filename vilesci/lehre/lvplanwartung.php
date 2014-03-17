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
		require_once('../../config/vilesci.config.inc.php');
		require_once('../../include/basis_db.class.php');
		if (!$db = new basis_db())
				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
		
		require_once('../../include/studiengang.class.php');
		require_once('../../include/functions.inc.php');
		require_once('../../include/studiensemester.class.php');

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
	
// POST/GET Parameter uebernehmen 
	if (isset($_GET))
	{
		while (list ($tmp_key, $tmp_val) = each ($_GET)) 
		{
			$$tmp_key=$tmp_val;
		}	
			
	}
	else if (isset($_POST))
	{
		while (list ($tmp_key, $tmp_val) = each ($_POST)) 
		{
			$$tmp_key=$tmp_val;
		}	
	}
// Plausib der Variablen
	if ($verband=='')
		$verband=' ';
	if ($gruppe=='')
		$gruppe=' ';
	
	if(!is_numeric($stg_kz))
		$stg_kz=0;
	if(!is_numeric($semester))
		$semester=0;
		
	$insert=trim($insert);
		$insert=(empty($insert)?false:true);
			
			
//	Studiengang lesen 
	$s=new studiengang();
	$s->getAll('typ, kurzbz', false);
	$studiengang=$s->result;

// Benutzerdefinierte Variablen laden
	$user = get_uid();
	loadVariables($user);


// Bezeichnungen fuer Tabellen und Views
	$lva_stpl_view=VIEW_BEGIN.'lva_'.$db_stpl_table;
	$stpl_table=TABLE_BEGIN.$db_stpl_table;


//*************** im Stundenplan hinzufuegen *************************
	if ($insert)
	{
		// Termine holen
		$qry = "SELECT DISTINCT datum, stunde FROM lehre.$stpl_table WHERE lehreinheit_id=".$leid;
		//echo $qry.'<BR>';
		if(!$result=$db->db_query($qry))
				die ($qry .' '.$db->db_last_error());
			
		while ($row=$db->db_fetch_object($result))
		{
			$qry = "SELECT DISTINCT ort_kurzbz FROM lehre.".$stpl_table."
					WHERE lehreinheit_id=$leid AND datum='$row->datum' AND stunde=$row->stunde;";
			if(!$result_ort=$db->db_query($qry))
				die ("DB Fehler $qry" .' '.$db->db_last_error());
			while ($row_ort=$db->db_fetch_object($result_ort))
			{
				$qry="INSERT INTO lehre.$stpl_table (datum,stunde,ort_kurzbz,unr,mitarbeiter_uid,studiengang_kz,semester,verband,gruppe,gruppe_kurzbz,lehreinheit_id, insertvon)
						VALUES ('".$row->datum."', $row->stunde,'$row_ort->ort_kurzbz',$unr,'".$lektor_uid."',$studiengang_kz,$semester,'$verband','$gruppe',";
				if ($gruppe_kurzbz!='')
					$qry.="'$gruppe_kurzbz',$leid,'LVPlanCheck');";
				else
					$qry.="NULL,$leid,'LVPlanCheck');";
				if(!$result_insert=$db->db_query($qry))
					die ("DB Fehler $qry" .' '.$db->db_last_error());
			}
		}
	}

	$stsem_obj = new studiensemester();
/*	if(date("m")>=1 || date("m")<=2)
	{
		$stsem_obj->getNextStudiensemester();
		$studiensemester = $stsem_obj->studiensemester_kurzbz;
	}
	else  */
		$studiensemester = $semester_aktuell;
	$where=" studiensemester_kurzbz='".$studiensemester."'";
	if (!empty($semester))
		$where.=" AND semester=$semester";
	if (!empty($stg_kz))
		$where.=" AND studiengang_kz='$stg_kz'";

		
	if(isset($_GET['lvid']) && is_numeric($_GET['lvid']))
	{
		//Lehrevz Speichern
		if(isset($_POST['lehrevz']))
		{
			$qry = "UPDATE lehre.tbl_lehrveranstaltung SET lehreverzeichnis='".addslashes($_POST['lehrevz'])."' WHERE lehrveranstaltung_id='".$_GET['lvid']."'";
			if(!$db->db_query($qry))
					echo "Fehler beim Speichern! " .' '.$db->db_last_error();
			else
					echo "Erfolgreich gespeichert";
		}
	} 

	$sql_query="SELECT *, planstunden-verplant::smallint AS offenestunden
			FROM lehre.$lva_stpl_view JOIN lehre.tbl_lehrform ON $lva_stpl_view.lehrform=tbl_lehrform.lehrform_kurzbz
			WHERE $where AND verplant=0 AND planstunden>0 AND lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.$stpl_table)
			ORDER BY offenestunden DESC, lehrfach, lehrform, semester, verband, gruppe, gruppe_kurzbz;";
	//echo $sql_query;
	if(!$result_lv=$db->db_query($sql_query))
		die ("DB Fehler $sql_query"  .' '.$db->db_last_error());
	if(!$result_lv) 
		die("Lehrveranstaltung not found!");
		
	$outp='';
	$s=array();
	$outp.="<SELECT name='stg_kz'>";
	foreach ($studiengang as $stg)
	{
		$outp.="<OPTION onclick=\"window.location.href = '".$_SERVER['PHP_SELF']."?stg_kz=$stg->studiengang_kz&semester=$semester'\" ".($stg->studiengang_kz==$stg_kz?'selected':'').">$stg->kuerzel - $stg->bezeichnung</OPTION>";
		//$outp.= '<A href="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg->studiengang_kz.'&sem='.$semester.'">'.$stg->kuerzel.'</A> - ';
                $a = new stdClass();
                $a->max_sem=$stg->max_semester;
                $a->kurzbz=$stg->kurzbzlang;
                $s[$stg->studiengang_kz]=$a;
//		$s[$stg->studiengang_kz]->max_sem=$stg->max_semester;
//		$s[$stg->studiengang_kz]->kurzbz=$stg->kurzbzlang;
	}
	$outp.='</SELECT>';
	$outp.= '<BR> -- ';
	for ($i=0;$i<=$s[$stg_kz]->max_sem;$i++)
		$outp.= '<A href="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&semester='.$i.'">'.$i.'</A> -- ';
?>
<html>
<head>
<title>Lehrveranstaltung Verwaltung</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
	$num_rows=$db->db_num_rows($result_lv);

//  raumtyp raumtypalternativ stundenblockung wochenrythmus semesterstunden  start_kw anmerkung
	echo "<th class='table-sortable:default'>LE-ID</th><th class='table-sortable:default'>UNR</th><th class='table-sortable:default'>Lehrfach</th><th class='table-sortable:default'>Lektor</th>
			<th class='table-sortable:default'>Lehrverband</th><th class='table-sortable:default'>Gruppe</th><th class='table-sortable:default'>SS</th><th class='table-sortable:numeric'>planstunden</th><th class='table-sortable:default'>Verplant</th>\n";
	echo "</tr></thead>";
	echo "<tbody>";
	for($i=0;$i<$num_rows;$i++)
	{
	   $row=$db->db_fetch_object($result_lv);
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