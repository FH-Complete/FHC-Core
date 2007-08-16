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
	require_once('../../include/functions.inc.php');
	require_once('../../include/studiengang.class.php');
	require_once('../../include/reihungstest.class.php');
	require_once('../../include/ort.class.php');
	require_once('../../include/datum.class.php');
	
	if (!$conn = pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	$user = get_uid();
	$datum_obj = new datum();
	$stg_kz = (isset($_GET['stg_kz'])?$_GET['stg_kz']:'');
	$reihungstest_id = (isset($_GET['reihungstest_id'])?$_GET['reihungstest_id']:'');
	$neu = (isset($_GET['neu'])?true:false);
	$stg_arr = array();
	$error = false;
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Reihungstest</title>
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
</head>
<body class="Background_main">
<h2>Reihungstest - Verwaltung</h2>
<?php

	if(isset($_POST['speichern']))
	{
		$reihungstest = new reihungstest($conn);
		
		if(isset($_POST['reihungstest_id']) && $_POST['reihungstest_id']!='')
		{
			if(!$reihungstest->load($_POST['reihungstest_id']))
				die($reihungstest->errormsg);
			$reihungstest->new = false;
		}
		else 
		{
			$reihungstest->new=true;
			$reihungstest->insertvon = $user;
			$reihungstest->insertamum = date('Y-m-d H:i:s');
		}
		
		if($_POST['datum']!='' && !$datum_obj->checkDatum($_POST['datum']))
		{
			echo 'Datum ist ungueltig';
			$error = true;
		}
		if($_POST['uhrzeit']!='' && !$datum_obj->checkUhrzeit($_POST['uhrzeit']))
		{
			echo 'Uhrzeit ist ungueltig:'.$_POST['uhrzeit'];
			$error = true;
		}
		
		if(!$error)
		{
			$reihungstest->studiengang_kz = $_POST['studiengang_kz'];
			$reihungstest->ort_kurzbz = $_POST['ort_kurzbz'];
			$reihungstest->anmerkung = $_POST['anmerkung'];
			$reihungstest->datum = $_POST['datum'];
			$reihungstest->uhrzeit = $_POST['uhrzeit'];
			$reihungstest->updateamum = date('Y-m-d H:i:s');
			$reihungstest->udpatevon = $user;
			
			if($reihungstest->save())
			{
				echo 'Daten wurden erfolgreich gespeichert';
				$reihungstest_id = $reihungstest->reihungstest_id;
				$stg_kz = $reihungstest->studiengang_kz;
			}
			else 
			{
				echo 'Fehler beim Speichern der Daten: '.$reihungstest->errormsg;
			}
		}
	}
	echo '<br><table width="100%"><tr><td>';
	
	//Studiengang DropDown
	$studiengang = new studiengang($conn);
	$studiengang->getAll('typ, kurzbz', false);
		
	echo "<SELECT name='studiengang' onchange='window.location.href=this.value'>";
	if($stg_kz==-1)
		$selected='selected';
	else 
		$selected='';
	
	echo "<OPTION value='".$_SERVER['PHP_SELF']."?stg_kz=-1&reihungstest_id=$reihungstest_id' $selected>Alle Studiengaenge</OPTION>";
	foreach ($studiengang->result as $row) 
	{
		$stg_arr[$row->studiengang_kz] = $row->kuerzel;
		if($stg_kz=='')
			$stg_kz=$row->studiengang_kz;
		if($row->studiengang_kz==$stg_kz)
			$selected='selected';
		else 
			$selected='';
			
		echo "<OPTION value='".$_SERVER['PHP_SELF']."?stg_kz=$row->studiengang_kz&reihungstest_id=$reihungstest_id' $selected>$row->kuerzel</OPTION>";
	}
	echo "</SELECT>";
	
	//Reihungstest DropDown
	$reihungstest = new reihungstest($conn);
	if($stg_kz==-1)
		$reihungstest->getAll();
	else
		$reihungstest->getReihungstest($stg_kz);
	
	echo "<SELECT name='reihungstest' onchange='window.location.href=this.value'>";
	foreach ($reihungstest->result as $row) 
	{
		if($reihungstest_id=='')
			$reihungstest_id=$row->reihungstest_id;
		if($row->reihungstest_id==$reihungstest_id)
			$selected='selected';
		else
			$selected='';
			
		echo "<OPTION value='".$_SERVER['PHP_SELF']."?stg_kz=$stg_kz&reihungstest_id=$row->reihungstest_id' $selected>$row->datum $row->uhrzeit $row->ort_kurzbz $row->anmerkung</OPTION>";
	}
	echo "</SELECT></td>";
	echo "<td align='right'><INPUT type='button' value='Neuen Reihungstesttermin anlegen' onclick='window.location.href=\"".$_SERVER['PHP_SELF']."?stg_kz=$stg_kz&neu=true\"' >";
	
	echo "</td></tr></table><br><br>";
	
	$reihungstest = new reihungstest($conn);
	
	if(!$neu)
	{
		if(!$reihungstest->load($reihungstest_id))
			die('Reihungstest existiert nicht: '.$reihungstest_id);
	}
	else 
	{
		if($stg_kz!=-1 && $stg_kz!='')
			$reihungstest->studiengang_kz = $stg_kz;
	}
	
	echo '<HR>';
	echo "<FORM method='POST'>";
	echo "<input type='hidden' value='$reihungstest->reihungstest_id' name='reihungstest_id' />";
	
	//Studiengang DropDown
	echo "<table><tr><td>Studiengang</td><td><SELECT name='studiengang_kz'>";
	if($reihungstest->studiengang_kz=='')
		$selected = 'selected';
	else 
		$selected = '';
		
	echo "<OPTION value='' $selected>-- keine Auswahl --</OPTION>";
	foreach ($studiengang->result as $row)
	{
		if($row->studiengang_kz==$reihungstest->studiengang_kz)
			$selected = 'selected';
		else 
			$selected = '';
		
		echo "<OPTION value='$row->studiengang_kz' $selected>$row->kuerzel</OPTION>";
	}
	echo "</SELECT></TD></TR>";
	
	echo "<tr><td>Ort</td><td><SELECT name='ort_kurzbz'>";
	
	if($reihungstes->ort_kurzbz=='')
		$selected = 'selected';
	else 
		$selected = '';
	echo "<OPTION value='' $selected>-- keine Auswahl --</OPTION>";	
	
	$ort = new ort($conn);
	$ort->getAll();
	
	foreach ($ort->result as $row) 
	{
		if($row->ort_kurzbz==$reihungstest->ort_kurzbz)
			$selected='selected';
		else 
			$selected='';
		
		echo "<OPTION value='$row->ort_kurzbz' $selected>$row->ort_kurzbz</OPTION";
	}
	echo '</SELECT></td></tr>';
	echo '<tr><td>Anmerkung</td><td><input type="input" name="anmerkung" value="'.$reihungstest->anmerkung.'"></td></tr>';
	echo '<tr><td>Datum</td><td><input type="input" name="datum" value="'.$datum_obj->convertISODate($reihungstest->datum).'"></td></tr>';
	echo '<tr><td>Uhrzeit</td><td><input type="input" name="uhrzeit" value="'.$reihungstest->uhrzeit.'"></td></tr>';
	echo '<tr><td></td><td><input type="submit" name="speichern" value="Speichern"></td></tr>';
	echo '</table>';
	echo '</FORM>';
	
	echo '<HR>';	
	
	
	$qry = "SELECT *, (SELECT kontakt FROM tbl_kontakt WHERE kontakttyp='email' AND person_id=tbl_prestudent.person_id ORDER BY zustellung LIMIT 1) as email FROM public.tbl_prestudent JOIN public.tbl_person USING(person_id) WHERE reihungstest_id='$reihungstest_id' ORDER BY nachname, vorname";
	$mailto = '';
	if($result = pg_query($conn, $qry))
	{
		echo 'Anzahl: '.pg_num_rows($result);
		
		echo "<table class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'><thead><tr class='liste'><th class='table-sortable:default'>Vorname</th><th class='table-sortable:default'>Nachname</th><th class='table-sortable:default'>Studiengang</th><th class='table-sortable:default'>Geburtsdatum</th><th>EMail</th></tr></thead><tbody>";
		while($row = pg_fetch_object($result))
		{
			echo "
				<tr>
					<td>$row->vorname</td>
					<td>$row->nachname</td>
					<td>".$stg_arr[$row->studiengang_kz]."</td>
					<td>".$datum_obj->convertISODate($row->gebdatum)."</td>
					<td><a href='mailto:$row->email'>$row->email</a></td>
				</tr>";
			
			$mailto.= ($mailto!=''?',':'').$row->email;
		}
		echo "</tbody></table>";
		echo "<br><a href='mailto:$mailto'>Mail an alle senden</a>";
	}
?>

</body>
</html>