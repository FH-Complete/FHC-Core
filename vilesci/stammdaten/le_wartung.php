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
	require_once('../../include/lehrveranstaltung.class.php');
	require_once('../../include/lehreinheit.class.php');
	require_once('../../include/studiengang.class.php');
	require_once('../../include/studiensemester.class.php');
		
	if (!$conn = pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	$user = get_uid();
	
	$stg_kz = (isset($_GET['stg_kz'])?$_GET['stg_kz']:'');
	$semester = (isset($_GET['semester'])?$_GET['semester']:'');
	$stsem = (isset($_GET['stsem'])?$_GET['stsem']:'');
	$check = (isset($_GET['check'])?true:false);
	
	//Wenn mitcheck=true ist, dann werden in der Tabelle (gefiltert nach Studiengang/Semester/Stsem) 
	//nur die Eintraege mit zusammenpassenden UNRs angezeigt
	if(isset($_GET['mitcheck']) && $_GET['mitcheck']=='false')
		$mitcheck = false;
	else 
		$mitcheck = true;
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Lehreinheit</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body class="Background_main">
<h2>Lehreinheiten Zusammenlegen</h2>
<?php

	echo "<table width='100%'><tr><td>";
	
	//Studiengang DropDown
	echo "Studiengang: <SELECT name='stg_kz' onchange='window.location.href=this.value'>";
	
	$stg = new studiengang($conn);
	$stg->getAll('typ, kurzbz', false);
	
	
	foreach ($stg->result as $row)
	{
		if($stg_kz=='')
			$stg_kz=$row->studiengang_kz;
		echo "<OPTION value=\"".$_SERVER['PHP_SELF']."?stg_kz=$row->studiengang_kz&semester=$semester&mitcheck=".($mitcheck?'true':'false')."&stsem=$stsem\" ".($row->studiengang_kz==$stg_kz?'selected':'').">$row->kuerzel - $row->bezeichnung</OPTION>";
		$s[$row->studiengang_kz]=$row->max_semester;
	}
	
	echo '</SELECT>';

	//Semester DropDown
	if($semester>$s[$stg_kz])
		$semester = $s[$stg_kz];
	
	echo " Semester: <SELECT name='semester' onchange='window.location.href=this.value'>";
	for ($i=0;$i<=$s[$stg_kz];$i++)
	{
		if($semester=='')
			$semester=$i;
		echo "<OPTION value=\"".$_SERVER['PHP_SELF']."?stg_kz=$stg_kz&semester=$i&mitcheck=".($mitcheck?'true':'false')."&stsem=$stsem\" ".($i==$semester?'selected':'').">$i</OPTION>";
	}
	echo '</SELECT>';
	
	//Studiensemester DropDown
	$studiensem = new studiensemester($conn);
	if($stsem=='')
		$stsem = $studiensem->getAktorNext();
	$studiensem->getAll();
	echo " StSem: <SELECT name='stsem' onchange='window.location.href=this.value'>";
	foreach ($studiensem->studiensemester as $row)
	{
		echo "<OPTION value=\"".$_SERVER['PHP_SELF']."?stg_kz=$stg_kz&semester=$semester&mitcheck=".($mitcheck?'true':'false')."&stsem=$row->studiensemester_kurzbz\" ".($row->studiensemester_kurzbz==$stsem?'selected':'').">$row->studiensemester_kurzbz</OPTION>";
	}
	echo '</SELECT>';
	echo 'Mit Check?<input type="checkbox" name="mitcheck" onclick="window.location.href = \''.$_SERVER['PHP_SELF']."?stg_kz=$stg_kz&semester=$semester&stsem=$stsem&mitcheck=".($mitcheck?'false':'true')."'\" ".($mitcheck?'checked':'').'>';
	echo '</td><td align="right" style="font-size: small;">';
	echo '<a href="'.$_SERVER['PHP_SELF'].'?check=true">CHECK</a>';
	echo '</td></tr></table>';
	
	
	if(isset($_POST['zusammenlegen']))
	{		
		$le_id_bleibt = $_POST['radio_bleibt'];
		$le_id_delete = $_POST['radio_delete'];
		
		if($le_id_bleibt!=$le_id_delete)
		{
			//unr beider Lehreinheiten ermitteln
			$qry = "SELECT (SELECT unr FROM lehre.tbl_lehreinheit WHERE lehreinheit_id='$le_id_bleibt') as unr_bleibt, 
						   (SELECT unr FROM lehre.tbl_lehreinheit WHERE lehreinheit_id='$le_id_delete') as unr_delete";

			if($result = pg_query($conn, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					//Wenn beide UNRs gleich sind -> zusammenlegen
					if($row->unr_bleibt==$row->unr_delete)
					{
						
						echo "LV ".$_POST['radio_delete']." wird geloescht - LV ".$_POST['radio_bleibt']." bleibt bestehen<br>";
									
						/*		
						- Eintraege aus tbl_lehreinheitmitarbeiter Loeschen die nicht uebernommen werden sollen
						
						- Mitarbeiter die in beiden Tabellen vorkommen werden gemerged
						
						- Gruppen die in beiden Lehreinheiten vorkommen werden geloescht
						
						-Lehreinheit_id's umbiegen in den Tabellen
							tbl_stundenplan
							tbl_stundenplandev
							tbl_lehreinheitgruppe
							tbl_legesamtnote
							tbl_notenschluessel
							tbl_uebung
							tbl_projektarbeit
							tbl_pruefung
							tbl_lehreinheitmitarbeiter
							tbl_synclehreinheit
						*/
						
						$error = false;
						pg_query($conn, 'BEGIN');
						
						//Mitarbeiter loeschen die nicht uebernommen werden
						foreach($_POST as $key=>$wert)
						{
							if(mb_strstr($key, 'check_'))
							{
								$arr = split('_',$key);
								$qry = "DELETE FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id='$arr[1]' AND mitarbeiter_uid='$arr[2]'";
								pg_query($conn, $qry);
								echo $qry.'<br>';
							}
						}
						
						//Mitarbeiter die in beiden Lehreinheiten geich sind werden gemerged
						$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id='$le_id_delete'";
						if($result_delete = pg_query($conn, $qry))
						{
							while($row_delete = pg_fetch_object($result_delete))
							{
								$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id='$le_id_bleibt' AND mitarbeiter_uid='$row_delete->mitarbeiter_uid'";
								if($result_bleibt = pg_query($conn, $qry))
								{
									if($row_bleibt = pg_fetch_object($result_bleibt))
									{
										echo "Lehreinheitmitarbeiter $row_bleibt->mitarbeiter_uid wird gemerged<br>";
										if($row_delete->lehrfunktion_kurzbz==$row_bleibt->lehrfunktion_kurzbz)
										{
											if($row_bleibt->semesterstunden!='' && $row_bleibt->semesterstunden!='0')
												$semesterstunden = $row_bleibt->semesterstunden;
											elseif($row_delete->semesterstunden!='' && $row_delete->semesterstunden!='0')
												$semesterstunden = $row_delete->semesterstunden;
											else 
												$semesterstunden = '0';
												
											if($row_bleibt->planstunden!='' && $row_bleibt->planstunden!='0')
												$planstunden = $row_bleibt->planstunden;
											elseif($row_delete->planstunden!='' && $row_delete->planstunden!='0')
												$planstunden = $row_delete->planstunden;
											else 
												$planstunden = '0';
											
											if($row_bleibt->stundensatz!='' && $row_bleibt->stundensatz!='0')
												$stundensatz = $row_bleibt->stundensatz;
											elseif($row_delete->stundensatz!='' && $row_delete->stundensatz!='0')
												$stundensatz = $row_delete->stundensatz;
											else 
												$stundensatz = '0';
												
											if($row_bleibt->faktor!='' && $row_bleibt->faktor!='0')
												$faktor = $row_bleibt->faktor;
											elseif($row_delete->faktor!='' && $row_delete->faktor!='0')
												$faktor = $row_delete->faktor;
											else 
												$faktor = '0';
												
											$anmerkung = $row_delete->anmerkung.' '.$row_bleibt->anmerkung;
											
											if($row_delete->bismelden=='t' || $row_bleibt->bismelden=='t')
												$bismelden=true;
											else 	
												$bismelden=false;
												
											$updateamum = date('Y-m-d H:i:s');
											$updatevon = $user;
											
											$qry = "UPDATE lehre.tbl_lehreinheitmitarbeiter SET
													semesterstunden='".addslashes($semesterstunden)."',
													planstunden='".addslashes($planstunden)."',
													stundensatz='".addslashes($stundensatz)."',
													faktor='".addslashes($faktor)."',
													anmerkung='".addslashes($anmerkung)."',
													bismelden=".($bismelden?'true':'false').",
													updateamum='$updateamum',
													updatevon='$updatevon'
													WHERE lehreinheit_id='$row_bleibt->lehreinheit_id' AND mitarbeiter_uid='$row_bleibt->mitarbeiter_uid'";
											pg_query($conn, $qry);
											echo $qry.'<br>';
											
											$qry = "DELETE FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id='$row_delete->lehreinheit_id' AND mitarbeiter_uid='$row_delete->mitarbeiter_uid'";
											pg_query($conn, $qry);
											echo $qry.'<br>';
										}
										else 
										{
											echo "Kann die Daten von Person $row_bleibt->mitarbeiter_uid nicht mergen da sie eine unterschiedliche Lehrfunktion haben";
											$error = true;
										}							
									}						
								}
							}
						}
						
						if(!$error)
						{
							//Gruppen die in beiden Lehreinheiten gleich sind werden geloescht
							$qry = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id='$le_id_bleibt'";
							if($result_bleibt = pg_query($conn, $qry))
							{
								while($row_bleibt = pg_fetch_object($result_bleibt))
								{
									$qry = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id='$le_id_delete' AND studiengang_kz='$row_bleibt->studiengang_kz' AND semester='$row_bleibt->semester' AND (verband='$row_bleibt->verband' ".($row_bleibt->verband==''?' OR verband is null':'').") AND (gruppe='$row_bleibt->gruppe'".($row_bleibt->gruppe==''?' OR gruppe is null':'').") AND (gruppe_kurzbz='$row_bleibt->gruppe_kurzbz'".($row_bleibt->gruppe_kurzbz==''?' OR gruppe_kurzbz is null':'').")";
									if($result_gruppe = pg_query($conn, $qry))
									{
										if($row_gruppe = pg_fetch_object($result_gruppe))
										{
											$qry = "DELETE FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheitgruppe_id='$row_gruppe->lehreinheitgruppe_id'";
											pg_query($conn, $qry);
											echo $qry.'<br>';
										}
									}
								}
							}
						}
						
						if(!$error)
						{
							$qry = "UPDATE lehre.tbl_stundenplan SET lehreinheit_id='$le_id_bleibt' WHERE lehreinheit_id='$le_id_delete';\n";
							$qry .= "UPDATE lehre.tbl_stundenplandev SET lehreinheit_id='$le_id_bleibt' WHERE lehreinheit_id='$le_id_delete';\n";
							$qry .= "UPDATE lehre.tbl_lehreinheitgruppe SET lehreinheit_id='$le_id_bleibt' WHERE lehreinheit_id='$le_id_delete';\n";
							$qry .= "UPDATE campus.tbl_legesamtnote SET lehreinheit_id='$le_id_bleibt' WHERE lehreinheit_id='$le_id_delete';\n";
							$qry .= "UPDATE campus.tbl_notenschluessel SET lehreinheit_id='$le_id_bleibt' WHERE lehreinheit_id='$le_id_delete';\n";
							$qry .= "UPDATE campus.tbl_uebung SET lehreinheit_id='$le_id_bleibt' WHERE lehreinheit_id='$le_id_delete';\n";
							$qry .= "UPDATE lehre.tbl_projektarbeit SET lehreinheit_id='$le_id_bleibt' WHERE lehreinheit_id='$le_id_delete';\n";
							$qry .= "UPDATE lehre.tbl_pruefung SET lehreinheit_id='$le_id_bleibt' WHERE lehreinheit_id='$le_id_delete';\n";
							$qry .= "UPDATE lehre.tbl_lehreinheitmitarbeiter SET lehreinheit_id='$le_id_bleibt' WHERE lehreinheit_id='$le_id_delete';\n";
							pg_query($conn, $qry);
							echo nl2br($qry);

							//Wenn der Synclehreinheit Eintrag schon existiert dann den anderen loeschen sonst umbiegen
							$qry = "SELECT * FROM sync.tbl_synclehreinheit WHERE lehreinheit_id='$le_id_bleibt' AND lehreinheit_pk in(SELECT lehreinheit_pk FROM sync.tbl_synclehreinheit WHERE lehreinheit_id='$le_id_delete')";
			
							if($result = pg_query($conn, $qry))
							{
								if(pg_numrows($result)==0)
								{
									$qry = "UPDATE sync.tbl_synclehreinheit SET lehreinheit_id='$le_id_bleibt' WHERE lehreinheit_id='$le_id_delete';";
								}
								else 
									$qry = "DELETE FROM sync.tbl_synclehreinheit WHERE lehreinheit_id='$le_id_bleibt' AND lehreinheit_pk in(SELECT lehreinheit_pk FROM sync.tbl_synclehreinheit WHERE lehreinheit_id='$le_id_delete');";
								
								pg_query($conn, $qry);
								echo $qry.'<br>';
							}
							$qry = "DELETE FROM lehre.tbl_lehreinheit WHERE lehreinheit_id='$le_id_delete'\n";
							pg_query($conn, $qry);
							echo nl2br($qry);
						
							pg_query($conn, 'COMMIT');
						}
						else 
						{
							pg_query($conn, 'ROLLBACK');
						}
					}
					else 
						echo "Die UNR beider Lehreinheiten muss gleich sein damit die beiden LE zusammengefuegt werden koennen";
				}
			}
		}
		else 
		{
			echo "Es wurde 2 mal die gleiche Lehreinheit ausgewählt";
		}
	}
	
	
	
	echo '<br><br><h3>Das wird geloescht:</h3>';

	if($check)
		$qry = "SELECT distinct a.* FROM lehre.tbl_lehreinheit a, lehre.tbl_lehreinheit b WHERE a.lehreinheit_id!=b.lehreinheit_id AND a.unr=b.unr AND a.unr!=0 AND a.studiensemester_kurzbz=b.studiensemester_kurzbz ORDER BY unr DESC LIMIT 10";
	else
	{
		if($mitcheck)
			$qry = "SELECT a.* FROM (Select * FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) WHERE studiengang_kz='$stg_kz' AND semester='$semester' AND studiensemester_kurzbz='$stsem') as a, lehre.tbl_lehreinheit as b WHERE a.lehreinheit_id!=b.lehreinheit_id AND a.unr=b.unr AND a.studiensemester_kurzbz=b.studiensemester_kurzbz ORDER BY unr DESC";
		else
			$qry = "SELECT * FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) WHERE studiengang_kz='$stg_kz' AND semester='$semester' AND studiensemester_kurzbz='$stsem'";
	}

	echo "<form method='POST' action='".$_SERVER['PHP_SELF']."?stg_kz=$stg_kz&semester=$semester&stsem=$stsem".($check?'&check=true':'')."'>";
	//Obere Tabelle
	draw_table($qry, true);
	
	echo '<input type="submit" name="zusammenlegen" value="Zusammenlegen">';
	echo '<br><br><h3>Das bleibt:</h3>';
	
	//Untere Tabelle
	draw_table($qry, false);
	
	echo "</form>";
	
	function draw_table($qry, $delete)
	{
		global $conn;
		
		echo '<table class="liste"><tr><th>LE_id</th><th>LV_id</th><th>StSem</th><th>LF_id</th><th>LForm</th><th>Blockung</th><th>WR</th>
		                 <th>StartKW</th><th>Raumtyp</th><th>RaumtypAlt</th><th>lehre</th><th>unr</th><th>lvnr</th></tr>';
		
		if($result = pg_query($conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				echo '<tr class="liste1">';
				echo "<td><input type='radio' name='radio_".($delete?'delete':'bleibt')."' value='$row->lehreinheit_id'>$row->lehreinheit_id</td><td>$row->lehrveranstaltung_id</td><td>$row->studiensemester_kurzbz</td>
					  <td>$row->lehrfach_id</td><td>$row->lehrform_kurzbz</td><td>$row->stundenblockung</td>
					  <td>$row->wochenrythmus</td><td>$row->start_kw</td><td>$row->raumtyp</td>
					  <td>$row->raumtypalternativ</td><td>".($row->lehre=='t'?'Ja':'Nein')."</td>
					  <td>$row->unr</td><td>$row->lvnr</td></tr>";
				
				//Liste der zugehoerigen Mitarbeiter
				$qry_ma = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id='$row->lehreinheit_id'";
				if($result_ma = pg_query($conn, $qry_ma))
				{
					while($row_ma = pg_fetch_object($result_ma))
					{
						echo "<tr><td></td><td><input type='checkbox' name='check_".$row->lehreinheit_id."_".$row_ma->mitarbeiter_uid."'>$row_ma->mitarbeiter_uid</td><td>$row_ma->lehrfunktion_kurzbz</td>
								  <td>$row_ma->semesterstunden</td><td>$row_ma->planstunden</td><td>$row_ma->stundensatz</td>
								  <td>$row_ma->faktor</td><td>$row_ma->anmerkung</td><td>".($row_ma->bismelden?'Ja':'Nein')."</td></tr>";
					}
				}
			}
		}
		echo '</table>';
	}
?>

</body>
</html>