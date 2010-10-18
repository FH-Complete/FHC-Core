<?php
/* Copyright (C) 2010 FH Technikum Wien
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
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/mail.class.php');
require_once('../../include/datum.class.php');

$errormsg = '';
$error_count = 0;
$insert_count =0;
$update_count =0;
$updated_lines ='';
$ausgabe ='';

$date = new datum();
$db = new basis_db();

//Datenbankverbindung zum WaWi Server herstellen
if($con_wawi = pg_connect(CONN_STRING_WAWI))
{
	if(!pg_query($con_wawi, 'SET CLIENT_ENCODING TO UNICODE;'))
	{
		$errormsg .= 'Fehler beim Setzen des Encodings';
	}
	
	//Alle Kostenstellen aus der WaWi Datenbank holen
	$qry = 'SELECT 
				*,
				cbenutzer.username as cusername,
				lbenutzer.username as lusername,
				kostenstelle.lupdate as lkostenupdate,
				kostenstelle.cdate as ckostendate,
				kostenstelle.bezeichnung as kostenbezeichnung,
				kostenstelle.kurzzeichen as kostenkurzzeichen
			FROM
				public.kostenstelle 
				LEFT JOIN public.benutzer cbenutzer ON (cuser=user_id)
				LEFT JOIN public.benutzer lbenutzer ON (kostenstelle.luser=lbenutzer.user_id)
				LEFT JOIN public.studiengang stud ON (kostenstelle.studiengang_id=stud.studiengang_id)
			;';
	
	if($result = pg_query($con_wawi, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			//Dazupassenden Eintrag in der neuen Datenbank suchen
			$qry = "SELECT * from wawi.tbl_kostenstelle WHERE kostenstelle_id = '$row->kostenstelle_id' ;";
			if($result_neu = $db->db_query($qry))
			{
				if($db->db_num_rows($result_neu)>0)
				{
					// Update des Eintrages
					if($row_neu = $db->db_fetch_object($result_neu))
					{
						$update = 'UPDATE wawi.tbl_kostenstelle SET ';
						$bedingung = '';
						
						//Spalten ueberpruefen
						if($row_neu->kostenstelle_id != $row->kostenstelle_id)
						{
							if($bedingung!='')
								$bedingung.=',';
							$bedingung .= " kostenselle_id=".$db->addslashes($row->kostenstelle_id);
							$updated_lines .= "kostenstelle_id von: \"".$row_neu->kostenstelle_id."\" auf: \"".$row->kostenstelle_id."\"\n";
							
							$update_count++;
						}
						
						if($row_neu->oe_kurzbz != $row->oe_kurzbz)
						{
							if($bedingung!='')
								$bedingung.=',';
							$bedingung .= " oe_kurzbz=".$db->addslashes($row->oe_kurzbz);
							$updated_lines .= "oe_kurzbz von: \"".$row_neu->oe_kurzbz."\" auf: \"".$row->oe_kurzbz."\"\n";
							
							$update_count++;
						}
						
						if($row_neu->bezeichnung != $row->kostenbezeichnung)
						{
							if($bedingung!='')
								$bedingung.=',';
							$bedingung .= " bezeichnung=".$db->addslashes($row->kostenbezeichnung);
							$updated_lines .= "beschreibung von: \"".$row_neu->bezeichnung."\" auf: \"".$row->kostenbezeichnung."\"\n";
							
							$update_count++;
						}
						
						if($row_neu->kurzbz != $row->kostenkurzzeichen)
						{
							if($bedingung!='')
								$bedingung.=',';
							$bedingung .= " kurzbz=".$db->addslashes($row->kostenkurzzeichen);
							$updated_lines .= "kurzbz von: \"".$row_neu->kurzbz."\" auf: \"".$row->kostenkurzzeichen."\"\n";
							
							$update_count++;
						}
						
						if($row_neu->budget != $row->budget)
						{
							if($bedingung!='')
								$bedingung.=',';
							$bedingung .= " budget=".$db->addslashes($row->budget);
							$updated_lines .= "budget von: \"".$row_neu->budget."\" auf: \"".$row->budget."\"\n";
							
							$update_count++;
						}
						
						if($row_neu->ext_id != $row->kostenstelle_id)
						{
							if($bedingung!='')
								$bedingung.=',';
							$bedingung .= " ext_id=".$db->addslashes($row->kostenstelle_id);
							$updated_lines .= "ext_id von: \"".$row_neu->ext_id."\" auf: \"".$row->kostenstelle_id."\"\n";
							
							$update_count++;
						}
						
						if($row_neu->kostenstelle_nr != $row->kostenstelle_nr)
						{
							if($bedingung!='')
								$bedingung.=',';
							$bedingung .= " kostenstelle_nr=".$db->addslashes($row->kostenstelle_nr);
							$updated_lines .= "kostenstelle_nr von: \"".$row_neu->kostenstelle_nr."\" auf: \"".$row->kostenstelle_nr."\"\n";
							
							$update_count++;
						}
						if($date->formatDatum($row_neu->insertamum, 'Y-m-d H:i:s') != $date->formatDatum($row->ckostendate, 'Y-m-d H:i:s'))
						{
							if($bedingung!='')
								$bedingung.=',';
							$bedingung .= " insertamum=".$db->addslashes($row->ckostendate);
							$updated_lines .= "insertamum von: \"".$row_neu->insertamum."\" auf: \"".$row->ckostendate."\"\n";
							
							$update_count++;
						}
						
						if($row_neu->insertvon != $row->cusername)
						{
							if($bedingung!='')
								$bedingung.=',';
							$bedingung .= " insertvon=".$db->addslashes($row->cusername);
							$updated_lines .= "insertvon von: \"".$row_neu->insertvon."\" auf: \"".$row->cusername."\"\n";
							
							$update_count++;
						}
						if($date->formatDatum($row_neu->updateamum, 'Y-m-d H:i:s') != $date->formatDatum($row->lkostenupdate, 'Y-m-d H:i:s'))
						{
							if($bedingung!='')
								$bedingung.=',';
							$bedingung .= " updateamum=".$db->addslashes($row->lkostenupdate);
							$updated_lines .= "updateamum von: \"".$row_neu->updateamum."\" auf: \"".$row->lkostenupdate."\"\n";
							
							$update_count++;
						}
						
						if($row_neu->updatevon != $row->lusername)
						{
							if($bedingung!='')
								$bedingung.=',';
							$bedingung .= " updatevon=".$db->addslashes($row->lusername);
							$updated_lines .= "updatevon von: \"".$row_neu->updatevon."\" auf: \"".$row->lusername."\"\n";
							
							$update_count++;
						}
						
						if($updated_lines != '')
							$ausgabe .= "ID ".$row_neu->kostenstelle_id.": ".$updated_lines."\n \n";
							$updated_lines ='';
					}
					
					if ($bedingung !='')
					{
						$update .= $bedingung." WHERE kostenstelle_id =".$row_neu->kostenstelle_id.";";
						//echo "$update <br>";
						$db->db_query($update);
					}
				}
				else
				{
					// Insert neuen Eintrag	
					$insert_qry = 	"INSERT INTO 
									wawi.tbl_kostenstelle
									(kostenstelle_id, oe_kurzbz, bezeichnung, kurzbz, aktiv, budget, updateamum, updatevon, insertamum, insertvon, ext_id, kostenstelle_nr) 
									VALUES (
									".$db->addslashes($row->kostenstelle_id).",".$db->addslashes($row->oe_kurzbz).",".$db->addslashes($row->kostenbezeichnung).",
									".$db->addslashes($row->kostenkurzzeichen).", true, ".$db->addslashes($row->budget).",".$db->addslashes($row->lkostenupdate).",
									".$db->addslashes($row->lusername).",".$db->addslashes($row->ckostendate).",".$db->addslashes($row->cusername).",
									".$db->addslashes($row->kostenstelle_id).",".$db->addslashes($row->kostenstelle_nr).");";
					//echo "$insert_qry <br>";
					$insert_count++;
					if($db->db_query($insert_qry) != true)
						$error_count++;
				}
			}
		}
	}
}
else 
{
	$errormsg .= 'Es konnte keine Verbindung zum WAWI Server aufgebaut werden';
	$error_count++;
}

if ($insert_count >0)
{
	// Sequenz erhöhen
	$max_qry= "SELECT MAX(kostenstelle_id) as max from wawi.tbl_kostenstelle";
	if($result_max = $db->db_query($max_qry))
	{
		if($row_max = $db->db_fetch_object($result_max))
		{
			$set_qry ="SELECT setval('wawi.seq_kostenstelle_kostenstelle_id', $row_max->max)";
			$db->db_query($set_qry);
		}
		else 
		$error_count++;
	}
	else 
	$error_count++;
}

$msg = "
$update_count Datensätze wurden geändert.
$insert_count Datensätze wurden hinzugefügt.
$error_count Fehler sind dabei aufgetreten!

$ausgabe 
";


// Nachricht versenden
$msg.=$errormsg;

$mail = new mail(MAIL_ADMIN, 'vilesci.technikum-wien.at', 'WaWi Syncro - Kostenstelle', $msg);
if(!$mail->send())
	echo 'Fehler beim Senden des Mails';
else
	echo '<br> Mail verschickt!';
	
?>
