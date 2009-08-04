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
/*
 * Script zum Kopieren einer Kreuzerltool Uebung zu einer anderen Lehreinheit
 * (zB fuer die Uebernahme der Uebungen aus dem Vorjahr)
 */
	require_once('../../../../config/cis.config.inc.php');
// ------------------------------------------------------------------------------------------
//	Datenbankanbindung 
// ------------------------------------------------------------------------------------------
	require_once('../../../../include/basis_db.class.php');
	if (!$db = new basis_db())
			die('Fehler beim Herstellen der Datenbankverbindung');

require_once('../../../../include/functions.inc.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/studiengang.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/lehreinheit.class.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/uebung.class.php');
require_once('../../../../include/beispiel.class.php');
require_once('../../../../include/datum.class.php');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
$error=0;

if(!$rechte->isBerechtigt('admin'))
	die('Sie haben keine Rechte für diese Seite');

if(isset($_GET['uebung_id_source']))
	$uebung_id_source=$_GET['uebung_id_source'];
else 
	$uebung_id_source='';

if(isset($_GET['lehreinheit_id_target']))
	$lehreinheit_id_target=$_GET['lehreinheit_id_target'];
else 
	$lehreinheit_id_target='';
	
if($uebung_id_source!='' && $lehreinheit_id_target!='')
{
	$copy_insert = 0;
	$copy_update = 0;	
	$copy_insert_bsp = 0;
	$copy_update_bsp = 0;
	if (!is_numeric($uebung_id_source) or !is_numeric($lehreinheit_id_target))
		echo "<span class='error'>Übung und Lehreinheit muss ausgewählt sein!</span>";
	else
	{
		$ueb_1 = new uebung($uebung_id_source);
		$lehreinheit_id=$ueb_1->lehreinheit_id;
		$nummer_source = $ueb_1->nummer;
		$qry = "SELECT * from campus.tbl_uebung where nummer = '".$nummer_source."' and lehreinheit_id = '".$lehreinheit_id_target."'";
		//echo $qry;
		if($result1 = $db->db_query($qry))	
		{
			if ($db->db_num_rows($result1) >0)
			{
				$row1 = $db->db_fetch_object($result1);		
				$ueb_1_target =new uebung($row1->uebung_id);
				$ueb_1_target->new = false;
				$new = null;
				$ueb_1_target->insertamum = null;
				$ueb_1_target->insertvon = null;
				$ueb_1_target->updateamum = date('Y-m-d H:i:s');
				$ueb_1_target->updatevon = $user;
				$copy_update++;
			}
			else
			{
				$ueb_1_target =new uebung();
				$ueb_1_target->new = true;
				$new = true;
				$ueb_1_target->insertamum = date('Y-m-d H:i:s');
				$ueb_1_target->insertvon = $user;
				$ueb_1_target->updateamum = null;
				$ueb_1_target->updatevon = null;
				$copy_insert++;
			}
			$ueb_1_target->gewicht = $ueb_1->gewicht;
			$ueb_1_target->punkte = null;
			$ueb_1_target->angabedatei=null;
			$ueb_1_target->freigabevon = null;
			$ueb_1_target->freigabebis = null;
			$ueb_1_target->abgabe = false;
			$ueb_1_target->beispiele = false;
			$ueb_1_target->statistik = false;
			$ueb_1_target->maxstd = null;
			$ueb_1_target->maxbsp=null;
			$ueb_1_target->liste_id=null;
			$ueb_1_target->bezeichnung = $ueb_1->bezeichnung;
			$ueb_1_target->positiv = $ueb_1->positiv;
			$ueb_1_target->defaultbemerkung = $ueb_1->defaultbemerkung;
			$ueb_1_target->lehreinheit_id = $lehreinheit_id_target;
			$ueb_1_target->nummer = $nummer_source;
					
			if (!$ueb_1_target->save($new))
			{
				$error = 1;
				echo "<span class='error'>Hauptübung konnte nicht kopiert werden!</span>";
			}
				
			else
			{
				// Subübungen durchlaufen			
				$error = 0;
				$ueb_2 = new uebung();

				$ueb_2->load_uebung($lehreinheit_id,2,$uebung_id_source);
				
				$ueb_2anzahl = count($ueb_2->uebungen);
				if ($ueb_2anzahl >0)			
				{
					foreach ($ueb_2->uebungen as $subrow)
					{
															
						$nummer_source2 = $subrow->nummer;
						$qry2 = "SELECT * from campus.tbl_uebung where nummer = '".$nummer_source2."' and lehreinheit_id = '".$lehreinheit_id_target."'";
						$result2 = $db->db_query($qry2);
						if ($db->db_num_rows($result2) >0)
						{
							$row2 = $db->db_fetch_object($result2);		
							$ueb_2_target =new uebung($row2->uebung_id);
							$ueb_2_target->new = false;
							$new = null;
							$ueb_2_target->insertamum = null;
							$ueb_2_target->insertvon = null;
							$ueb_2_target->updateamum = date('Y-m-d H:i:s');
							$ueb_2_target->updatevon = $user;
							$copy_update++;
						}
						else
						{
							$ueb_2_target =new uebung();
							$ueb_2_target->new = true;
							$new = true;
							$ueb_2_target->insertamum = date('Y-m-d H:i:s');
							$ueb_2_target->insertvon = $user;
							$ueb_2_target->updateamum = null;
							$ueb_2_target->updatevon = null;
							$copy_insert++;
						}
						$ueb_2_target->gewicht = $subrow->gewicht;
						$ueb_2_target->punkte = $subrow->punkte;
						$ueb_2_target->angabedatei=null;
						$ueb_2_target->freigabevon = $subrow->freigabevon;
						$ueb_2_target->freigabebis = $subrow->freigabebis;
						$ueb_2_target->abgabe = $subrow->abgabe;
						$ueb_2_target->beispiele = $subrow->beispiele;
						$ueb_2_target->statistik = $subrow->statistik;
						$ueb_2_target->maxstd = $subrow->maxstd;
						$ueb_2_target->maxbsp=$subrow->maxbsp;
						$ueb_2_target->liste_id=$ueb_1_target->uebung_id;
						$ueb_2_target->bezeichnung = $subrow->bezeichnung;
						$ueb_2_target->positiv = $subrow->positiv;
						$ueb_2_target->defaultbemerkung = $subrow->defaultbemerkung;
						$ueb_2_target->lehreinheit_id = $lehreinheit_id_target;
						$ueb_2_target->nummer = $nummer_source2;
								
						if (!$ueb_2_target->save($new))
						{
							$error = 1;
							echo "<span class='error'>Übung konnte nicht kopiert werden!</span>";
						}
						
						//angabedatei syncen
						if ($subrow->angabedatei != "")
						{	
							$angabedatei_source = $subrow->angabedatei;
							$angabedatei_target = makeUploadName($db,'angabe', $lehreinheit_id, $ueb_2_target->uebung_id, $stsem);
							$angabedatei_target .= ".".mb_substr($angabedatei_source, mb_strrpos($angabedatei_source, '.',0) + 1);
							echo $angabedatei_source."->".$angabedatei_target."<br>";
							exec("cp ".BENOTUNGSTOOL_PATH."angabe/".$angabedatei_source." ".BENOTUNGSTOOL_PATH."angabe/".$angabedatei_target);
							$angabeupdate = "update campus.tbl_uebung set angabedatei = '".$angabedatei_target."' where uebung_id = '".$ueb_2_target->uebung_id."'";
							$db->db_query($angabeupdate);
						}
										
						if (($error == 0) and $ueb_2_target->beispiele)
						{
							// beispiele synchronisieren
							$bsp_obj = new beispiel();
							$bsp_obj->load_beispiel($subrow->uebung_id);
							foreach ($bsp_obj->beispiele as $bsp)
							{
								$nummer_source_bsp = $bsp->nummer;
								$qrybsp = "SELECT * from campus.tbl_beispiel where nummer = '".$nummer_source_bsp."' and uebung_id = '".$ueb_2_target->uebung_id."'";
								$resultbsp = $db->db_query($qrybsp);
			
								if ($db->db_num_rows($resultbsp) >0)
								{
									$rowbsp = $db->db_fetch_object($resultbsp);		
									$bsp_target =new beispiel($rowbsp->beispiel_id);
									$bsp_target->new = false;
									$new = null;
									$bsp_target->insertamum = null;
									$bsp_target->insertvon = null;
									$bsp_target->updateamum = date('Y-m-d H:i:s');
									$bsp_target->updatevon = $user;
									$copy_update_bsp++;
								}
								else
								{
									$bsp_target =new beispiel();
									$bsp_target->new = true;
									$new = true;
									$bsp_target->insertamum = date('Y-m-d H:i:s');
									$bsp_target->insertvon = $user;
									$bsp_target->updateamum = null;
									$bsp_target->updatevon = null;
									$copy_insert_bsp++;
								}
								$bsp_target->uebung_id = $ueb_2_target->uebung_id;
								$bsp_target->nummer = $nummer_source_bsp;
								$bsp_target->bezeichnung = $bsp->bezeichnung;
								$bsp_target->punkte = $bsp->punkte;
								
								if (!$bsp_target->save($new))
								{
									$error = 1;
									echo "<span class='error'>Beispiele konnten nicht angelegt werden</span>";							
								}
								
								//Notenschlüssel synchronisieren
								$clear = "delete from campus.tbl_notenschluesseluebung where uebung_id = '".$ueb_1_target->uebung_id."'";
								$db->db_query($clear);
								
								$qry_ns_source = "SELECT * from campus.tbl_notenschluesseluebung where uebung_id = '".$uebung_id_source."'";
								$result_ns_source = $db->db_query($qry_ns_source);
								while($row_ns = $db->db_fetch_object($result_ns_source))
								{
									$ns_insert = "INSERT INTO campus.tbl_notenschluesseluebung values ('".$ueb_1_target->uebung_id."','".$row_ns->note."', '".$row_ns->punkte."')";
									$db->db_query($ns_insert);					
								}					
											
							}									
						}
							
					}
				}
			}
			
		}
		else
			echo "<span class='error'>Fehler beim Datenbankzugriff!</span>";
			
		if ($error == 0)
		{
			echo "Übung erfolgreich kopiert! (Ü: ".$copy_insert."/".$copy_update."; B: ".$copy_insert_bsp."/".$copy_update_bsp.")";
			echo '<br><br><a href="'.$_SERVER['PHP_SELF'].'" class="Item">noch eine Übung Kopieren</a>';
		}
			
	}
}
else 
{
	echo '
		<h1>Kopieren von Übungen in eine andere Lehreinheit</h1>
		Script zum Kopieren einer Übung in eine beliebige Lehreinheit:<br><br>
		<form action="'.$_SERVER['PHP_SELF'].'" method="GET">
			ÜbungID die Kopiert werden soll: <input type="text" name="uebung_id_source"><br>
			Lehreinheit_id in welche diese Übung kopiert werden soll: <input type="text" name="lehreinheit_id_target"><br>
			<input type="submit" value="Kopieren">
		</form>';
}