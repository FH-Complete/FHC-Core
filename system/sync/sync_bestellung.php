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

require_once('../../config/wawi.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/mail.class.php');
require_once('../../include/datum.class.php');

$db = new basis_db();
$date = new datum(); 

$count_insert = 0;
$count_insert_detail = 0;
$count_insert_status = 0;
$count_update = 0;
$count_update_detail = 0;
$count_update = 0;
$count_delete = 0;
$error_count = 0;
$errormsg = '';
$starttime=date("d.m.Y H:i:s");

$bool_insert = false;
$firma_id = '';

if($con_wawi = pg_connect(CONN_STRING_WAWI))
{
	if(!pg_query($con_wawi, 'SET CLIENT_ENCODING TO UNICODE;'))
	{
		$errormsg .= 'Fehler beim Setzen des Encodings';
	}
	
	$qry ="	SELECT 
			*, 
			public.bestellung_freigegeben.bestellung_id as bestellung_id_freigegeben,
			bestellung.titel as newtitel,
			cbenutzer.username_neu as cusername,
			lbenutzer.username_neu as lusername,			
			kst_benutzer.username_neu as kst_username,
			rek_benutzer.username_neu as rek_username,
			gst_benutzer.username_neu as gst_username,
			gmb_benutzer.username_neu as gmb_username,
			geliefert_benutzer.username_neu as geliefert_username,
			besteller.username_neu as besteller_neu,
		 	public.bestellung.firma_id as firma

			FROM public.bestellung
			
			LEFT JOIN public.bestellung_freigegeben USING (bestellung_id) 
			LEFT JOIN public.benutzer kst_benutzer ON (freigb_kst_user_id=kst_benutzer.user_id)
			LEFT JOIN public.benutzer rek_benutzer ON (freigb_rek_user_id=rek_benutzer.user_id)
			LEFT JOIN public.benutzer gst_benutzer ON (freigb_gst_user_id=gst_benutzer.user_id)
			LEFT JOIN public.benutzer gmb_benutzer ON (freigabe_gmb_user_id=gmb_benutzer.user_id)
			LEFT JOIN public.benutzer geliefert_benutzer ON(geliefert_user_id=geliefert_benutzer.user_id)
			LEFT JOIN public.benutzer cbenutzer ON (public.bestellung.cuser=cbenutzer.user_id)
			LEFT JOIN public.benutzer lbenutzer ON (public.bestellung.luser=lbenutzer.user_id)
			LEFT JOIN public.benutzer besteller ON (public.bestellung.kontaktperson=besteller.user_id)

			ORDER BY bestellung_id DESC";
	
	if($result = pg_query($con_wawi, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			// firma id joinen
			if ( $row->firma != '')
			{
				
				$qry_firma = "SELECT firma_id from public.tbl_firma where ext_id = '$row->firma'";
				if($result_firma = $db->db_query($qry_firma))
				{
					if($row_firma = $db->db_fetch_object($result_firma))
					{
						$firma = $row_firma->firma_id;
					}
				}
				else 
				{
					//fehler aufgetreten
					$error_count++;
					$errormsg.= $qry_firma.' '.pg_last_error($con_wawi); 
				}
			}
			else
			{
				$firma = null; 
			}

			// ob es den eintrag schon gibt.
			$qry = "SELECT  * FROM wawi.tbl_bestellung WHERE bestellung_id=$row->bestellung_id;";
			if($result_check = $db->db_query($qry))
			{
				if($db->db_num_rows($result_check)>0)
				{
					$bool_insert=false;
					if($row_neu = $db->db_fetch_object($result_check))
					{
						//update der bestellung
						if($row->bestellung_id_freigegeben != null)
							$freigegeben = "t";
					 	else 
					 		$freigegeben = "f"; 
					 	
						if($row_neu->besteller_uid != $row->besteller_neu || $row_neu->kostenstelle_id != $row->kostenstelle_id || $row_neu->konto_id != $row->konto_id || $row_neu->firma_id != $firma || $row_neu->bestell_nr != $row->bestellnr ||
							$row_neu->titel != $row->newtitel || $row_neu->bemerkung != $row->bemerkungen || $row_neu->liefertermin != $row->geliefert || $row_neu->updatevon != $row->lusername || 
							 $row_neu->insertvon != $row->cusername || $date->formatDatum($row_neu->updateamum, 'Y-m-d H:i:s') != $date->formatDatum($row->lupdate, 'Y-m-d H:i:s') || 
							 $date->formatDatum($row_neu->insertamum, 'Y-m-d H:i:s') != $date->formatDatum($row->erstellung, 'Y-m-d H:i:s') || $row_neu->freigegeben!=$freigegeben)
						{	
							$qry="UPDATE wawi.tbl_bestellung SET besteller_uid = ".$db->addslashes($row->besteller_neu).", kostenstelle_id = ".$db->addslashes($row->kostenstelle_id).", konto_id = ".$db->addslashes($row->konto_id).", firma_id =
							".$db->addslashes($firma)." ,bestell_nr = ".$db->addslashes($row->bestellnr).", titel = ".$db->addslashes($row->newtitel).", bemerkung = ".$db->addslashes($row->bemerkungen).", freigegeben=".($freigegeben=='t'?'true':'false').", liefertermin= 
							".$db->addslashes($row->geliefert).", updateamum = ".$db->addslashes($row->lupdate).", updatevon = ".$db->addslashes($row->lusername).", insertamum = ".$db->addslashes($row->erstellung).", insertvon =
							".$db->addslashes($row->cusername)." WHERE bestellung_id = ".$db->addslashes($row->bestellung_id).";"; 
					
							if($db->db_query($qry) != true)
							{
								// Fehler
								$error_count++;
								$errormsg.= $qry.' '.$db->db_last_error(); 
							}
							$count_update++;
							$errormsg.= "Update Bestellung_id: ".$row->bestellung_id.'<br>'; 
						}
							
					}
					if($row->freigb_kst != '')
						{
							// gibt es schon die freigabe
							$qry = "SELECT * FROM wawi.tbl_bestellung_bestellstatus where bestellung_id = ".$db->addslashes($row->bestellung_id)." and bestellstatus_kurzbz = 'Freigabe' AND oe_kurzbz is null;";
						//	echo $qry; 
							if($result_check = $db->db_query($qry))
							{
								if($db->db_num_rows($result_check)==0)
								{
									$qry_stati = "INSERT INTO wawi.tbl_bestellung_bestellstatus(bestellung_id, bestellstatus_kurzbz, uid, oe_kurzbz, datum, insertvon, insertamum, updatevon, updateamum)
									VALUES(
									".$db->addslashes($row->bestellung_id).",".$db->addslashes('Freigabe').",".$db->addslashes($row->kst_username).",null,".$db->addslashes($row->freigb_kst).",
									".$db->addslashes($row->cusername).",".$db->addslashes($row->cdate).",".$db->addslashes($row->lusername).",".$db->addslashes($row->lupdate).")";
								
									if($db->db_query($qry_stati) != true)
									{
										// Fehler
										$error_count++;
										$errormsg.= $qry_stati.' '.$db->db_last_error();
									}

									$count_insert_status++;
								}
							}
						}
					
					// Prüfung ob sich Freigabe geändert hat
					if($row->freigb_rek != '')
					{
						// gibt es schon die freigabe
						$qry = "SELECT * FROM wawi.tbl_bestellung_bestellstatus where bestellung_id = ".$db->addslashes($row->bestellung_id)." and bestellstatus_kurzbz = 'Freigabe' and oe_kurzbz = 'etw'";
						if($result_check = $db->db_query($qry))
						{
							if($db->db_num_rows($result_check)==0)
							{
								$qry_stati = "INSERT INTO wawi.tbl_bestellung_bestellstatus(bestellung_id, bestellstatus_kurzbz, uid, oe_kurzbz, datum, insertvon, insertamum, updatevon, updateamum)
								VALUES(
								".$db->addslashes($row->bestellung_id).",".$db->addslashes('Freigabe').",".$db->addslashes($row->rek_username).",'etw',".$db->addslashes($row->freigb_rek).",
								".$db->addslashes($row->cusername).",".$db->addslashes($row->cdate).",".$db->addslashes($row->lusername).",".$db->addslashes($row->lupdate).")";
							
								if($db->db_query($qry_stati) != true)
								{
									// Fehler
									$error_count++;
									$errormsg.= $qry_stati.' '.$db->db_last_error(); 
								}
								$count_insert_status++;
							}
						}
					}
				
					if($row->freigb_gst != '')
					{
						$qry = "SELECT * FROM wawi.tbl_bestellung_bestellstatus where bestellung_id = ".$db->addslashes($row->bestellung_id)." and bestellstatus_kurzbz = 'Freigabe' and oe_kurzbz = 'gst';";
						if($result_check = $db->db_query($qry))
						{
							if($db->db_num_rows($result_check)==0)
							{
						
								$qry_stati = "INSERT INTO wawi.tbl_bestellung_bestellstatus(bestellung_id, bestellstatus_kurzbz, uid, oe_kurzbz, datum, insertvon, insertamum, updatevon, updateamum)
								VALUES(
								".$db->addslashes($row->bestellung_id).",".$db->addslashes('Freigabe').",".$db->addslashes($row->gst_username).",'gst',".$db->addslashes($row->freigb_gst).",
								".$db->addslashes($row->cusername).",".$db->addslashes($row->cdate).",".$db->addslashes($row->lusername).",".$db->addslashes($row->lupdate).")";
							
								if($db->db_query($qry_stati) != true)
								{
									// Fehler
									$error_count++;
									$errormsg.= $qry_stati.' '.$db->db_last_error();
								}
								$count_insert_status++;
							}
						}
					}
				
					if($row->freigabe_gmb != '')
					{
						$qry = "SELECT * FROM wawi.tbl_bestellung_bestellstatus where bestellung_id = ".$db->addslashes($row->bestellung_id)." and bestellstatus_kurzbz = 'Freigabe' and uid = ".$db->addslashes($row->gmb_username)." and oe_kurzbz = 'gmbh'";
						if($result_check = $db->db_query($qry))
						{
							if($db->db_num_rows($result_check)==0)
							{
								$qry_stati = "INSERT INTO wawi.tbl_bestellung_bestellstatus(bestellung_id, bestellstatus_kurzbz, uid, oe_kurzbz, datum, insertvon, insertamum, updatevon, updateamum)
								VALUES(
								".$db->addslashes($row->bestellung_id).",".$db->addslashes('Freigabe').",".$db->addslashes($row->gmb_username).",'gmbh',".$db->addslashes($row->freigabe_gmb).",
								".$db->addslashes($row->cusername).",".$db->addslashes($row->cdate).",".$db->addslashes($row->lusername).",".$db->addslashes($row->lupdate).")";
							
								if($db->db_query($qry_stati) != true)
								{
									// Fehler
									$error_count++;
									$errormsg.= $qry_stati.' '.$db->db_last_error();
								}
								$count_insert_status++;
							}
						}
					}
					
					if($row->geliefert != '')
					{
						$qry = "SELECT * FROM wawi.tbl_bestellung_bestellstatus where bestellung_id = ".$db->addslashes($row->bestellung_id)." and bestellstatus_kurzbz = 'Lieferung' and uid = ".$db->addslashes($row->geliefert_username).";";
						if($result_check = $db->db_query($qry))
						{
							if($db->db_num_rows($result_check)==0)
							{
								$qry_stati = "INSERT INTO wawi.tbl_bestellung_bestellstatus(bestellung_id, bestellstatus_kurzbz, uid, oe_kurzbz, datum, insertvon, insertamum, updatevon, updateamum)
								VALUES(
								".$db->addslashes($row->bestellung_id).",".$db->addslashes('Lieferung').",".$db->addslashes($row->geliefert_username).",null,".$db->addslashes($row->geliefert).",
								".$db->addslashes($row->cusername).",".$db->addslashes($row->cdate).",".$db->addslashes($row->lusername).",".$db->addslashes($row->lupdate).")";
							
								if($db->db_query($qry_stati) != true)
								{
									// Fehler
									$error_count++;
									$errormsg.= $qry_stati.' '.$db->db_last_error();
								}
								$count_insert_status++;
							}
						}
					}
					
					if($row->bestellung != '')
					{
						$qry = "SELECT * FROM wawi.tbl_bestellung_bestellstatus where bestellung_id = ".$db->addslashes($row->bestellung_id)." and bestellstatus_kurzbz = 'Bestellung'";
						if($result_check = $db->db_query($qry))
						{
							if($db->db_num_rows($result_check)==0)
							{
								$qry_stati = "INSERT INTO wawi.tbl_bestellung_bestellstatus(bestellung_id, bestellstatus_kurzbz, uid, oe_kurzbz, datum, insertvon, insertamum, updatevon, updateamum)
								VALUES(
								".$db->addslashes($row->bestellung_id).",".$db->addslashes('Bestellung').",null, null,".$db->addslashes($row->bestellung).",
								 ".$db->addslashes($row->cusername).",".$db->addslashes($row->cdate).",".$db->addslashes($row->lusername).",".$db->addslashes($row->lupdate).")";
							
								if($db->db_query($qry_stati) != true)
								{
									// Fehler
									$error_count++;
									$errormsg.= $qry_stati.' '.$db->db_last_error();
								}	
								$count_insert_status++;
							}				
						}
					}
				}
				else 
				{
					// insert bestellung
					if($row->bestellung_id_freigegeben != null)
						$freigegeben = "true";
					 else 
					 	$freigegeben = "false"; 
					
					// lieferadresse,rechnungsadresse = immer 1 -> public.tbl_adresse id1
					$qry="INSERT INTO wawi.tbl_bestellung(bestellung_id, besteller_uid, kostenstelle_id, konto_id, firma_id, lieferadresse,
					rechnungsadresse, freigegeben, bestell_nr, titel, bemerkung, liefertermin, updateamum, updatevon, insertamum, insertvon, 
					ext_id) VALUES (
					".$db->addslashes($row->bestellung_id).",".$db->addslashes($row->besteller_neu).",".$db->addslashes($row->kostenstelle_id).",
					".$db->addslashes($row->konto_id).",".$db->addslashes($firma).",".$db->addslashes('1').",".$db->addslashes('1').",
					".$freigegeben.",".$db->addslashes($row->bestellnr).",".$db->addslashes($row->newtitel).",".$db->addslashes($row->bemerkungen).",
					".$db->addslashes($row->geliefert).",".$db->addslashes($row->lupdate).",".$db->addslashes($row->lusername).",".$db->addslashes($row->erstellung).",
					".$db->addslashes($row->cusername).",".$db->addslashes($row->bestellung_id).")"; 
					//echo $qry; 
					if($db->db_query($qry) != true)
					{
						// Fehler
						$error_count++;
						$errormsg.= $qry.' '.$db->db_last_error();
					}
					
					$bool_insert=true;
					$count_insert++; 
					
					// insert tbl_bestellung_bestellstatus
					if($row->freigb_kst != '')
					{
						$qry_stati = "INSERT INTO wawi.tbl_bestellung_bestellstatus(bestellung_id, bestellstatus_kurzbz, uid, oe_kurzbz, datum, insertvon, insertamum, updatevon, updateamum)
						VALUES(
						".$db->addslashes($row->bestellung_id).",".$db->addslashes('Freigabe').",".$db->addslashes($row->kst_username).",null,".$db->addslashes($row->freigb_kst).",
						".$db->addslashes($row->cusername).",".$db->addslashes($row->cdate).",".$db->addslashes($row->lusername).",".$db->addslashes($row->lupdate).")";
					
						if($db->db_query($qry_stati) != true)
						{
							// Fehler
							$error_count++;
							$errormsg.= $qry_stati.' '.$db->db_last_error();
						}
						$count_insert_status++;
					}

					if($row->freigb_rek != '')
					{
						$qry_stati = "INSERT INTO wawi.tbl_bestellung_bestellstatus(bestellung_id, bestellstatus_kurzbz, uid, oe_kurzbz, datum, insertvon, insertamum, updatevon, updateamum)
						VALUES(
						".$db->addslashes($row->bestellung_id).",".$db->addslashes('Freigabe').",".$db->addslashes($row->rek_username).",'etw',".$db->addslashes($row->freigb_rek).",
						".$db->addslashes($row->cusername).",".$db->addslashes($row->cdate).",".$db->addslashes($row->lusername).",".$db->addslashes($row->lupdate).")";
						
						if($db->db_query($qry_stati) != true)
						{
							// Fehler
							$error_count++;
							$errormsg.= $qry_stati.' '.$db->db_last_error();
						}
						$count_insert_status++;
					}
					
					if($row->freigb_gst != '')
					{
						$qry_stati = "INSERT INTO wawi.tbl_bestellung_bestellstatus(bestellung_id, bestellstatus_kurzbz, uid, oe_kurzbz, datum, insertvon, insertamum, updatevon, updateamum)
						VALUES(
						".$db->addslashes($row->bestellung_id).",".$db->addslashes('Freigabe').",".$db->addslashes($row->gst_username).",'gst',".$db->addslashes($row->freigb_gst).",
						".$db->addslashes($row->cusername).",".$db->addslashes($row->cdate).",".$db->addslashes($row->lusername).",".$db->addslashes($row->lupdate).")";
					
						if($db->db_query($qry_stati) != true)
						{
							// Fehler
							$error_count++;
							$errormsg.= $qry_stati.' '.$db->db_last_error();
						}
						$count_insert_status++;
					}
					
					if($row->freigabe_gmb != '')
					{
						$qry_stati = "INSERT INTO wawi.tbl_bestellung_bestellstatus(bestellung_id, bestellstatus_kurzbz, uid, oe_kurzbz, datum, insertvon, insertamum, updatevon, updateamum)
						VALUES(
						".$db->addslashes($row->bestellung_id).",".$db->addslashes('Freigabe').",".$db->addslashes($row->gmb_username).",'gmbh',".$db->addslashes($row->freigabe_gmb).",
						".$db->addslashes($row->cusername).",".$db->addslashes($row->cdate).",".$db->addslashes($row->lusername).",".$db->addslashes($row->lupdate).")";
					
						if($db->db_query($qry_stati) != true)
						{
							// Fehler
							$error_count++;
							$errormsg.= $qry_stati.' '.$db->db_last_error();
						}
						$count_insert_status++;
					}
					
					if($row->geliefert != '')
					{
						$qry_stati = "INSERT INTO wawi.tbl_bestellung_bestellstatus(bestellung_id, bestellstatus_kurzbz, uid, oe_kurzbz, datum, insertvon, insertamum, updatevon, updateamum)
						VALUES(
						".$db->addslashes($row->bestellung_id).",".$db->addslashes('Lieferung').",".$db->addslashes($row->geliefert_username).",null,".$db->addslashes($row->geliefert).",
						".$db->addslashes($row->cusername).",".$db->addslashes($row->cdate).",".$db->addslashes($row->lusername).",".$db->addslashes($row->lupdate).")";
					
						if($db->db_query($qry_stati) != true)
						{
							// Fehler
							$error_count++;
							$errormsg.= $qry_stati.' '.$db->db_last_error();
						}
						$count_insert_status++;
					}
					
					if($row->bestellung != '')
					{
						$qry_stati = "INSERT INTO wawi.tbl_bestellung_bestellstatus(bestellung_id, bestellstatus_kurzbz, uid, oe_kurzbz, datum, insertvon, insertamum, updatevon, updateamum)
						VALUES(
						".$db->addslashes($row->bestellung_id).",".$db->addslashes('Bestellung').",null, null,".$db->addslashes($row->bestellung).",
						 ".$db->addslashes($row->cusername).",".$db->addslashes($row->cdate).",".$db->addslashes($row->lusername).",".$db->addslashes($row->lupdate).")";
					
						if($db->db_query($qry_stati) != true)
						{
							// Fehler
							$error_count++;
							$errormsg.= $qry_stati.' '.$db->db_last_error();
						}	
						$count_insert_status++;				
					}
					
				}
			}
			
			// alle bestelldetails zu bestellung holen
			$qry = "SELECT 
						*, benutzer.username_neu as lusername
					FROM 
						public.bestelldetail
						LEFT JOIN public.benutzer ON(bestelldetail.luser=benutzer.user_id) 
					WHERE bestellung_id = $row->bestellung_id";
			if($result_detail = pg_query($con_wawi, $qry))
			{
				while($row = pg_fetch_object($result_detail))
				{
					//echo $row->bestelldetail_id."<br>"; 
					// ob eintrag in neuer Tabelle schon vorhanden ist
					$qry = "SELECT * FROM wawi.tbl_bestelldetail WHERE bestelldetail_id = $row->bestelldetail_id";
					if($bool_insert || $result_check = $db->db_query($qry))
					{
						if($bool_insert || $db->db_num_rows($result_check)==0)
						{		
							// insert bestelldetails
							$qry = "INSERT INTO wawi.tbl_bestelldetail (bestelldetail_id, bestellung_id, position, menge, verpackungseinheit, 
							beschreibung, artikelnummer, preisprove, mwst, erhalten, sort, text, insertamum, insertvon, updateamum, updatevon)
							VALUES (
							".$db->addslashes($row->bestelldetail_id).",".$db->addslashes($row->bestellung_id).",".$db->addslashes($row->pos).",
							".$db->addslashes($row->menge).",".$db->addslashes($row->ve).",".$db->addslashes($row->beschreibung).",
							".$db->addslashes($row->artikelnr).",".$db->addslashes($row->preisve).",".$db->addslashes($row->mwst).",
							".$db->addslashes($row->erhalten).",".$db->addslashes($row->pos).", false ,".$db->addslashes($row->lupdate).",
							".$db->addslashes($row->lusername).",".$db->addslashes($row->lupdate).",".$db->addslashes($row->lusername).")"; 			
							
							//echo $qry; 
							
							if($db->db_query($qry) != true)
							{
								// Fehler
								$error_count++;
								$errormsg.= $qry.' '.$db->db_last_error();
							}	
							$count_insert_detail++;			
						}
						else
						{
							// update bestelldetails			
							if($row_neu = $db->db_fetch_object($result_check))
							{
		
								if($row_neu->bestellung_id != $row->bestellung_id || $row_neu->position != $row->pos || $row_neu->menge != $row->menge  ||$row_neu->verpackungseinheit != $row->ve ||
								$row_neu->beschreibung != $row->beschreibung || $row_neu->artikelnummer != $row->artikelnr || round($row_neu->preisprove,2) != round($row->preisve,2) || $row_neu->mwst != $row->mwst) 
								{	

									//echo $row->preisve."<br>";
									//echo $row_neu->preisprove."<br><br>";
									
									$qry = "UPDATE wawi.tbl_bestelldetail SET position = 
									".$db->addslashes($row->pos).", menge = ".$db->addslashes($row->menge).", verpackungseinheit = ".$db->addslashes($row->ve).", beschreibung =
									".$db->addslashes($row->beschreibung).", artikelnummer = ".$db->addslashes($row->artikelnr).", preisprove =
									".$db->addslashes($row->preisve).", mwst = ".$db->addslashes($row->mwst).", erhalten = ".$db->addslashes($row->erhalten).", sort = 
									".$db->addslashes($row->pos).", text = false, insertamum = ".$db->addslashes($row->lupdate).", insertvon = ".$db->addslashes($row->lusername).", updateamum = 
									".$db->addslashes($row->lupdate).", updatevon = ".$db->addslashes($row->lusername)." WHERE bestelldetail_id = ".$db->addslashes($row->bestelldetail_id).";";
									if($db->db_query($qry) != true)
									{
										// Fehler
										$error_count++;
										$errormsg.= $qry.' '.$db->db_last_error();
									}
									$count_update_detail++;
									$errormsg.= "Update Bestelldetail_id: ".$row->bestelldetail_id.'<br>'; 
								}
							}
						}
					}

				}	
			}				
		} // ende while
	}
	else
	{
		$error_count++;
		$errormsg.= "Verbindung zu Datenbank fehlgeschlagen.";
	}
	
	// delete --> bestellungen die es im wawi nicht mehr gibt
	$qry_delete = "DELETE
				FROM
				wawi.tbl_bestellung 
				WHERE NOT EXISTS (
				SELECT bestellung_id FROM 
				    dblink(
				        '".CONN_STRING_WAWI."'::text,
				        'SELECT bestellung_id FROM public.bestellung WHERE bestellung_id=' || tbl_bestellung.bestellung_id
				          )
				    as foo(bestellung_id integer));";
	
	if($result_delete = $db->db_query($qry_delete))
	{
		$count_delete += $db->db_affected_rows($result_delete);
	}
	else
	{
		$error_count++;
		$errormsg.= "Fehler beim Löschen der Bestellungen! ".$db->db_last_error();
	}
	
	// delete --> bestelldetails die es im wawi nicht mehr gibt
	$qry_delete = "DELETE
				FROM
				wawi.tbl_bestelldetail
				WHERE NOT EXISTS (
				SELECT bestelldetail_id FROM 
				    dblink(
				        '".CONN_STRING_WAWI."'::text,
				        'SELECT bestelldetail_id FROM public.bestelldetail WHERE bestelldetail_id=' || tbl_bestelldetail.bestelldetail_id
				          )
				    as foo(bestelldetail_id integer));";
	
	if($result_delete = $db->db_query($qry_delete))
	{
		$count_delete += $db->db_affected_rows($result_delete);
	}
	else
	{
		$error_count++;
		$errormsg.= "Fehler beim Löschen der Bestelldetails! ".$db->db_last_error();
	}
	
	if ($count_insert >0)
	{
		$max_qry= "SELECT MAX(bestellung_id) as max FROM wawi.tbl_bestellung";
		if($result_max = $db->db_query($max_qry))
		{
			if($row_max = $db->db_fetch_object($result_max))
			{
				$set_qry ="SELECT setval('wawi.seq_bestellung_bestellung_id', $row_max->max)";
				$db->db_query($set_qry);
			}
			else 
			{
				$error_count++;
				$errormsg.= $set_qry.' '.$db->db_last_error();
			}
		}
		else 
		$error_count++;
	}
	
	if ($count_insert_detail >0)
	{
		$max_qry= "SELECT MAX(bestelldetail_id) as max from wawi.tbl_bestelldetail";
		if($result_max = $db->db_query($max_qry))
		{
			if($row_max = $db->db_fetch_object($result_max))
			{
				$set_qry ="SELECT setval('wawi.seq_bestelldetail_bestelldetail_id', $row_max->max)";
				$db->db_query($set_qry);
			}
			else 
			{
				$error_count++;
				$errormsg.= $set_qry.' '.$db->db_last_error();
			}
		}
		else 
		$error_count++;
	}
	
	if ($count_insert_status >0)
	{
		$max_qry= "SELECT MAX(bestellung_bestellstatus_id) as max from wawi.tbl_bestellung_bestellstatus";
		if($result_max = $db->db_query($max_qry))
		{
			if($row_max = $db->db_fetch_object($result_max))
			{
				$set_qry ="SELECT setval('wawi.seq_bestellung_bestellstatus_bestellung_bestellstatus_id', $row_max->max)";
				$db->db_query($set_qry);
			}
			else 
			{
				$error_count++;
				$errormsg.= $set_qry.' '.$db->db_last_error();
			}
		}
		else 
		$error_count++;
	}
	
	echo $errormsg;
	
	$send_msg = "
	Bestellungen Syncro
	Beginn: ".$starttime." von ".DB_NAME."
	$count_update Bestellungen wurden geändert.
	$count_update_detail Bestelldetails wurden geändert.
	$count_insert Bestellungen wurden hinzugefügt.
	$count_insert_detail Bestelldetails wurden hinzugefügt.
	$count_insert_status Bestellstati wurden hinzugefügt. 
	$count_delete Bestellungen wurden gelöscht.
	$error_count Fehler sind dabei aufgetreten. 
	
";
	echo $send_msg;
	
	$mail = new mail(MAIL_ADMIN, 'vilesci@technikum-wien.at', 'WaWi Syncro - Bestellung', $send_msg.$errormsg);
	if(!$mail->send())
		echo 'Fehler beim Senden des Mails';
	else
		echo '<br> Mail verschickt!';
}
?>