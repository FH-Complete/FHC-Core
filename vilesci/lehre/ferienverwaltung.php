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
 *          Manfred Kindl	<manfred.kindl@technikum-wien.at>
 */
/**
 * Ferienverwaltung
 * 
 */
	require_once('../../config/vilesci.config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/studiengang.class.php');
	require_once('../../include/ort.class.php');
	require_once('../../include/datum.class.php');
	require_once('../../include/benutzerberechtigung.class.php');
	require_once('../../include/pruefling.class.php');
	require_once('../../include/person.class.php');
	require_once('../../include/prestudent.class.php');
	
	if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');
	
	$user = get_uid();
	$datum_obj = new datum();
	$stg_kz = (isset($_GET['stg_kz'])?$_GET['stg_kz']:'-2');
	$action = (isset($_GET['action'])?$_GET['action']:'');
	$bezeichnung=(isset($_REQUEST['bezeichnung'])?$_REQUEST['bezeichnung']:'');
	$stg_arr = array();
	
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
	
	if(!$rechte->isBerechtigt('admin'))
		die('Sie haben keine Berechtigung fuer diese Seite');
	
	$studiengang = new studiengang();
	$studiengang->getAll('typ, kurzbz', false);
		
	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//DE" "http://www.w3.org/TR/html4/strict.dtd">
				<html>
				<head>
					<title>Ferienverwaltung</title>
					<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
					<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
					<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
					<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
				</head>
				<body class="Background_main">
				<h2>Ferienverwaltung</h2>';
		
	// Speichern eines Ferieneintrags
	if(isset($_POST['speichern']))
	{
		$sql_query="SELECT bezeichnung FROM lehre.tbl_ferien WHERE bezeichnung='".$_POST['bezeichnung']."';";
		$db->db_num_rows($db->db_query($sql_query));		
		//echo $sql_query;
			
		//Formulardaten pruefen
		if(!$datum_obj->checkDatum($_POST['vondatum']) || !$datum_obj->checkDatum($_POST['bisdatum']))
		{
			echo '<span class="input_error">Datum ist ungültig. Das Datum muss im Format DD.MM.JJJJ eingegeben werden<br></span>';
			$stg_kz = $_POST['studiengang_kz'];
		}
		elseif($_POST['vondatum']>$_POST['bisdatum'])
		{
			echo '<span class="input_error"><i>Datum bis</i> darf nicht kleiner als <i>Datum von</i> sein<br></span>';
			$stg_kz = $_POST['studiengang_kz'];
		}
		elseif($_POST['bezeichnung']=='')
		{
			echo '<span class="input_error">Geben Sie eine Bezeichnung ein<br></span>';
			$stg_kz = $_POST['studiengang_kz'];
		}
		elseif($db->db_num_rows($db->db_query($sql_query))!=0)
		{
			echo '<span class="input_error">Die Bezeichnung existiert bereits. Geben Sie eine andere Bezeichnung ein.<br></span>';
			$stg_kz = $_POST['studiengang_kz'];
		}
		elseif($_POST['studiengang_kz']=='')
		{
			echo '<span class="input_error">Wählen Sie einen Studiengang aus<br></span>';
			$stg_kz = $_POST['studiengang_kz'];
		}
		else
		{
			$sql_query="INSERT INTO lehre.tbl_ferien (studiengang_kz, bezeichnung, vondatum, bisdatum) VALUES(
			'".$_POST['studiengang_kz']."',
			".$db->db_add_param($_POST['bezeichnung']).",
			'".$datum_obj->formatDatum($_POST['vondatum'],'Y-m-d')."',
			'".$datum_obj->formatDatum($_POST['bisdatum'],'Y-m-d')."');";
			//echo $sql_query;
			$db->db_query($sql_query);
			$stg_kz = $_POST['studiengang_kz'];
		}
		$neu=false;
	}
	//Löschen von Ferieneinträgen
	if($action=='delete')
	{
		if(!$rechte->isBerechtigt('admin', null, 'suid'))
			die('Sie haben keine Berechtigung zum löschen von Ferien');
			
		$sql_query = "DELETE FROM lehre.tbl_ferien WHERE bezeichnung='$bezeichnung' AND studiengang_kz='$stg_kz'";
		$result = $db->db_query($sql_query);
		if ($db->db_affected_rows($result)==1)
		 echo '<span class="insertok">Eintrag erfolgreich gelöscht</span><br>';
		else 
		 echo '<span class="input_error">Fehler! Eintrag konnte nicht gelöscht werden</span><br>';
	}
	echo '<br><table width="100%"><tr><td>';
	
	//Dropdown Auswahl Studiengang
	echo "<SELECT name='studiengang' id='studiengang' onchange='window.location.href=this.value'>";
	if($stg_kz==-2)
		$selected='selected';
	else 
		$selected='';	
	echo "<OPTION value='".$_SERVER['PHP_SELF']."' $selected>-- keine Auswahl --</OPTION>";
	
	if($stg_kz==-1)
			$selected='selected';
		else 
			$selected='';
	echo "<OPTION value='".$_SERVER['PHP_SELF']."?stg_kz=-1' $selected>Alle Studiengänge</OPTION>";
	foreach ($studiengang->result as $row) 
	{
		$stg_arr[$row->studiengang_kz] = $row->kuerzel;
		if($stg_kz=='')
			$stg_kz=$row->studiengang_kz;
		if($row->studiengang_kz==$stg_kz)
			$selected='selected';
		else 
			$selected='';
			
		echo "<OPTION value='".$_SERVER['PHP_SELF']."?stg_kz=$row->studiengang_kz' $selected>".$db->convert_html_chars($row->kuerzel)."</OPTION>";
	}
	echo "</SELECT>";
	echo "<INPUT type='button' value='Anzeigen' onclick='window.location.href=document.getElementById(\"studiengang\").value;'>";
	echo "</td></tr></table><br>";
			
	if($stg_kz!=-1 && $stg_kz!='')
		$db->studiengang_kz = $stg_kz;
	$db->vondatum = date('Y-m-d');
	$db->bisdatum = date('Y-m-d');

	//Formular zum Bearbeiten von Ferieneinträgen
	echo '<HR>';
	echo "<FORM method='POST' action='".$_SERVER['PHP_SELF']."'?stg_kz='.$row->studiengang_kz.'>";
	echo "<input type='hidden' value='$stg_kz' name='studiengang' />";
	echo "<table>";
	
		//Studiengang DropDown
		echo "<tr><td>Studiengang</td><td><SELECT name='studiengang_kz'>";
		if($row->studiengang_kz=='')
			$selected = 'selected';
		else 
			$selected = '';
			
		echo "<OPTION value='' $selected>-- keine Auswahl --</OPTION>";
		foreach ($studiengang->result as $row)
		{
			if($row->studiengang_kz==$stg_kz)
				$selected = 'selected';
			else 
				$selected = '';
			
			echo "<OPTION value='$row->studiengang_kz' $selected>".$db->convert_html_chars($row->kuerzel)."</OPTION>";
		}
	echo "</SELECT></TD></TR>";
	echo '<tr><td>Bezeichnung</td><td><input type="text" name="bezeichnung" value="" size="68" maxlength="64"></td></tr>';
	echo '<tr><td>Datum von</td><td><input type="text" name="vondatum" value="'.$datum_obj->convertISODate($db->vondatum).'"></td></tr>';
	echo '<tr><td>Datum bis</td><td><input type="text" name="bisdatum" value="'.$datum_obj->convertISODate($db->bisdatum).'"></td></tr>';	
	echo '<tr><td></td><td><input type="submit" name="speichern" value="Speichern"></td></tr>';
	echo '</table>';
	echo '</FORM>';	
	echo '<HR>';
	
	//Liste der eingetragenen Ferien
	if($stg_kz!='')
	{		
		$qry="SELECT * FROM lehre.tbl_ferien ";
		if ($stg_kz!=-1)
			$qry.=" WHERE studiengang_kz='".$stg_kz."'";
					
		$qry.=" ORDER BY vondatum DESC;";	
		//echo $qry;
		
		if($result = $db->db_query($qry))
		{
			echo 'Anzahl: '.$db->db_num_rows($result);
			$pruefling = new pruefling();
			
			echo "<table class='liste table-autosort:# table-stripeclass:alternate table-autostripe'>
					<thead>
					<tr class='liste'>
						<th class='table-sortable:default'>Studiengang</th>
						<th class='table-sortable:default'>Datum von</th>
						<th class='table-sortable:default'>Datum bis</th>
						<th class='table-sortable:default'>Bezeichnung</th>
					</tr>
					<tr class='liste'>
						<th>&nbsp;</th>
						<th></th>
						<th></th>
						<th>Filter <input name='filter' size='30' onkeyup='Table.filter(this,this)'></th>
					</tr>
					</thead>
					<tbody>";
			while($row = $db->db_fetch_object($result))
			{
				echo '
					<tr>
						<td>'.$db->convert_html_chars($stg_arr[$row->studiengang_kz]).'</td>
						<td>'.$db->convert_html_chars($row->vondatum).'</td>
						<td>'.$db->convert_html_chars($row->bisdatum).'</td>
						<td>'.$db->convert_html_chars($row->bezeichnung).'</td>
						<td><a href='.$_SERVER["PHP_SELF"].'?action=delete&stg_kz='.$row->studiengang_kz.'&bezeichnung='.urlencode($row->bezeichnung).'>delete</a></td>
					</tr>';
			}
			echo "</tbody></table>";
		}
	}
	echo '
			</body>
			</html>';
?>