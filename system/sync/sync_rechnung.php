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
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
/**
 * Syncronisiert die Aufteilung von WaWi DB in FHComplete DB
 */
require_once('../../config/wawi.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/mail.class.php');
	
if (!$db = new basis_db())
	die('Fehler beim Herstellen der Datenbankverbindung');

if (!$conn_wawi = pg_pconnect(CONN_STRING_WAWI)) 
   	die('Es konnte keine Verbindung zum Server aufgebaut werden.   *** File:='.__FILE__.' Line:='.__LINE__."\n");
			
$error_log='';
$update_log='';
$anzahl_gesamt=0;
$anzahl_insert=0;
$anzahl_update=0;
$anzahl_delete=0;
$anzahl_fehler=0;

?>
<html>
<head>
<title>Synchro - WaWi -&gt; FAS - Rechnungen</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<?php
$qry="
	SET CLIENT_ENCODING TO UNICODE; 
	SELECT 
		*, rechnung.lupdate, bn_update.username_neu as updatevon
	FROM 
		rechnung
		LEFT JOIN benutzer bn_update ON(rechnung.luser=bn_update.user_id)
	ORDER BY r_id;
	";
if($result=pg_query($conn_wawi, $qry))
{

	$anzahl_gesamt=pg_num_rows($result);
	
	while($row = pg_fetch_object($result))
	{
		//check, ob rechnung bereits übertragen
		$qry_check="SELECT * FROM wawi.tbl_rechnung WHERE rechnung_id='".addslashes($row->r_id)."'";
		if($result_check=$db->db_query($qry_check))
		{
			if($db->db_num_rows($result_check)>0)
			{
				$update = false;
				//Rechnung vorhanden
				if($row_check = $db->db_fetch_object($result_check))
				{
					if($row_check->bestellung_id!=$row->bestellung_id)
					{
						$update = true;
						$update_log.="\nBestellung_id von $row_check->bestellung_id auf $row->bestellung_id geändert";
					}
					if($row_check->buchungsdatum!=$row->buchungsdatum)
					{
						$update=true;
						$update_log.="\nBuchungsdatum von $row_check->buchungsdatum auf $row->buchungsdatum geändert";
					}
					if($row_check->rechnungsnr!=$row->rechnungsnr)
					{
						$update=true;
						$update_log.="\nRechnungsnr von $row_check->rechnungsnr auf $row->rechnungsnr geändert";
					}
					if($row_check->transfer_datum!=$row->transfer_datum)
					{
						$update=true;
						$update_log.="\nTransferDatum von $row_check->transfer_datum auf $row->transfer_datum geändert";
					}
					if($row_check->buchungstext!=$row->buchungstext)
					{
						$update=true;
						$update_log.="\nBuchungstext von $row_check->buchungstext auf $row->buchungstext geändert";
					}
					if($row_check->updatevon!=$row->updatevon)
					{
						$update=true;
						$update_log.="\nUpdatevon von $row_check->updatevon auf $row->updatevon geändert";
					}
					if($row_check->updateamum!=$row->lupdate)
					{
						$update=true;
						$update_log.="\nUpdateamum von $row_check->updateamum auf $row->lupdate geändert";
					}
					
					if($update)
					{
						$qry = "UPDATE wawi.tbl_rechnung SET 
								bestellung_id=".$db->addslashes($row->bestellung_id).",
								buchungsdatum=".$db->addslashes($row->buchungsdatum).",
								rechnungsnr=".$db->addslashes($row->rechnungsnr).",
								transfer_datum=".$db->addslashes($row->transfer_datum).",
								buchungstext=".$db->addslashes($row->buchungstext).",
								updatevon=".$db->addslashes($row->updatevon).",
								updateamum=".$db->addslashes($row->lupdate)."
								WHERE rechnung_id='".addslashes($row->r_id)."'";
						if($db->db_query($qry))
						{
							$anzahl_update++;
						}
						else
						{
							$error_log.="\nFehler beim Update: $qry";
							$anzahl_fehler++;
						}
					}
					
					//Rechnungsbetrag
					for($i=1;$i<=3;$i++)
					{
						$mwst = 'mwst'.$i;
						$betrag = 'betrag'.$i;
						
						if($row->$mwst!='')
						{
							$qry = "SELECT * FROM wawi.tbl_rechnungsbetrag 
									WHERE rechnung_id=".$db->addslashes($row->r_id)." AND mwst=".$db->addslashes($row->$mwst);
							if($result_rbetrag = $db->db_query($qry))
							{
								if($row_rbetrag = $db->db_fetch_object($result_rbetrag))
								{
									//Update
									if($row_rbetrag->betrag!=$row->$betrag)
									{
										$qry = "UPDATE wawi.tbl_rechnungsbetrag 
												SET betrag=".$db->addslashes($row->$betrag)." 
												WHERE rechnungsbetrag_id=".$db->addslashes($row_rbetrag->rechnungsbetrag_id);
										if($db->db_query($qry))
										{
											$anzahl_update++;
											$update_log.="\nBetrag von $row_rbetrag->betrag auf ".$row->$betrag." geändert bei Mwst ".$row->$mwst;
										}
									}
								}
								else
								{
									//Insert
									$qry = "INSERT INTO wawi.tbl_rechnungsbetrag(rechnung_id, mwst, betrag) VALUES(".
											$db->addslashes($row->r_id).",".
											$db->addslashes($row->$mwst).",".
											$db->addslashes($row->$betrag).");";
									if($db->db_query($qry))
									{
										$anzahl_insert++;
										$update_log.="\nRechnungsbetrag hinzugefügt Mwst $row->$mwst Betrag $row->$betrag";
									}
								}
							}
							else 
							{
								$anzahl_fehler++;
								$error_log.="\nFehler beim Select: $qry";
							}
						}
					}
					
					//Loeschen von ueberfluessigen Rechnungsbetraegen
					$qry="DELETE FROM wawi.tbl_rechnungsbetrag 
						WHERE mwst NOT IN(".$db->addslashes($row->mwst1).",".$db->addslashes($row->mwst2).",".$db->addslashes($row->mwst3).") 
						AND rechnung_id=".$db->addslashes($row->r_id);
					if($result_delete = $db->db_query($qry))
					{
						$rechnungsbetrag_delete = $db->db_affected_rows($result_delete);
						if($rechnungsbetrag_delete>0)
						{
							$update_log.="\n $rechnungsbetrag_delete Rechnungsbetrag Einträge gelöscht";
							$anzahl_delete+=$rechnungsbetrag_delete;
						}
					}	
					else 
					{
						$anzahl_fehler++;
						$error_log.="\nFehler bei Delete: $qry";
					}					
				}
			}
			else 
			{
				//Aufteilung nicht vorhanden
				$qry="INSERT INTO wawi.tbl_rechnung(rechnung_id, bestellung_id, rechnungstyp_kurzbz, 
						buchungsdatum, rechnungsnr, rechnungsdatum, transfer_datum, buchungstext, 
						freigegeben, freigegebenamum, freigegebenvon, insertamum, insertvon, 
						updateamum, updatevon) VALUES("
						.$db->addslashes($row->r_id).","
						.$db->addslashes($row->bestellung_id).","
						."'Zahlung',"
						.$db->addslashes($row->buchungsdatum).","
						.$db->addslashes($row->rechnungsnr).","
						.$db->addslashes($row->rechnungsdatum).","
						.$db->addslashes($row->transfer_datum).","
						.$db->addslashes($row->buchungstext).","
						."true,"
						.$db->addslashes($row->lupdate).","
						.$db->addslashes($row->updatevon).","
						.$db->addslashes($row->lupdate).","
						.$db->addslashes($row->updatevon).","
						.$db->addslashes($row->lupdate).","
						.$db->addslashes($row->updatevon).");";
				if($row->mwst1!='')
					$qry.="INSERT INTO wawi.tbl_rechnungsbetrag(rechnung_id, mwst, betrag) VALUES(".$db->addslashes($row->r_id).",".$db->addslashes($row->mwst1).",".$db->addslashes($row->betrag1).");";
				if($row->mwst2!='')
					$qry.="INSERT INTO wawi.tbl_rechnungsbetrag(rechnung_id, mwst, betrag) VALUES(".$db->addslashes($row->r_id).",".$db->addslashes($row->mwst2).",".$db->addslashes($row->betrag2).");";
				if($row->mwst3!='')
					$qry.="INSERT INTO wawi.tbl_rechnungsbetrag(rechnung_id, mwst, betrag) VALUES(".$db->addslashes($row->r_id).",".$db->addslashes($row->mwst3).",".$db->addslashes($row->betrag3).");";
				
				if($db->db_query($qry))
				{
					$anzahl_insert++;
				}
				else
				{
					$error_log.="\nFehler beim Insert: $qry";
					$anzahl_fehler++;
				}					
			}
		}
		else
		{
			$error_log.="\nFehler bei Select: $qry_check";
			$anzahl_fehler++;
		}
	}

	$qry = "
		SELECT setval('wawi.seq_rechnung_rechnung_id',(SELECT max(rechnung_id) FROM wawi.tbl_rechnung));
		SELECT setval('wawi.seq_rechnungsbetrag_rechnungsbetrag_id',(SELECT max(rechnungsbetrag_id) FROM wawi.tbl_rechnungsbetrag));
		";
	if(!$db->db_query($qry))
	{
		$error_log.="\nFehler beim Update der Sequence";
		$anzahl_fehler++;
	}
	//Mail versenden
	$statistik="Rechnung Sync\n--------------\n";
	$statistik.="Beginn: ".date("d.m.Y H:i:s")." von ".DB_NAME." - Anzahl Einträge: ".$anzahl_gesamt."\n\n";
	$statistik.="\nEingefügte Datensätze: $anzahl_insert";
	$statistik.="\nGeänderte Datensätze: $anzahl_update";
	$statistik.="\nFehler: $anzahl_fehler\n";
	
	$synced=$statistik.$error_log.$update_log;
	$mail = new mail(MAIL_ADMIN, "vilesci@".DOMAIN, "SYNC Rechnung von ".DB_NAME, $synced);
	$mail->setReplyTo("vilesci@".DOMAIN);
	if(!$mail->send())
	{
		echo "<font color=\"#FF0000\">Fehler beim Versenden des Durchführungs-Mails!</font><br>";	
	}
}
?>