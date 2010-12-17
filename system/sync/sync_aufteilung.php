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
$anzahl_aufteilungen=0;
$anzahl_insert=0;
$anzahl_update=0;
$anzahl_delete=0;
$anzahl_fehler=0;
$starttime=date("d.m.Y H:i:s");

?>
<html>
<head>
<title>Synchro - WaWi -&gt; FAS - Aufteilung</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<?php
$qry="
	SET CLIENT_ENCODING TO UNICODE; 
	SELECT 
		*, aufteilung.lupdate, aufteilung.cdate, bn_create.username_neu as insertvon, bn_update.username_neu as updatevon
	FROM 
		aufteilung
		JOIN studiengang USING(studiengang_id)
		LEFT JOIN benutzer bn_update ON(aufteilung.luser=bn_update.user_id)
		LEFT JOIN benutzer bn_create ON(aufteilung.luser=bn_create.user_id)
	WHERE studiengang_id<>20
	ORDER BY aufteilung_id
	";
if($result=pg_query($conn_wawi, $qry))
{

	$anzahl_aufteilungen=pg_num_rows($result);
	
	while($row = pg_fetch_object($result))
	{
		//check, ob firma bereits übertragen
		$qry_check="SELECT * FROM wawi.tbl_aufteilung WHERE aufteilung_id='".addslashes($row->aufteilung_id)."'";
		if($result_check=$db->db_query($qry_check))
		{
			if($db->db_num_rows($result_check)>0)
			{
				$update = false;
				//Aufteilung vorhanden - Änderungen im WaWi?
				if($row_check = $db->db_fetch_object($result_check))
				{
					if($row_check->bestellung_id!=$row->bestellung_id)
					{
						$update = true;
						$update_log.="\nBestellung_id von $row_check->bestellung_id auf $row->bestellung_id geändert";
					}
					if($row_check->oe_kurzbz!=$row->oe_kurzbz)
					{
						$update=true;
						$update_log.="\nOrganisationseinheit von $row_check->oe_kurzbz auf $row->oe_kurzbz geändert";
					}
					if($row_check->anteil!=$row->anteil)
					{
						$update=true;
						$update_log.="\nAnteil von $row_check->anteil auf $row->anteil geändert";
					}
					if($row_check->updatevon!=$row->username_neu)
					{
						$update=true;
						$update_log.="\nUpdateVon von $row_check->updatevon auf $row->username_neu geändert";
					}
					
					if($update)
					{
						$qry = "UPDATE wawi.tbl_aufteilung SET 
								bestellung_id=".$db->addslashes($row->bestellung_id).",
								oe_kurzbz=".$db->addslashes($row->oe_kurzbz).",
								anteil=".$db->addslashes($row->anteil).",
								updatevon=".$db->addslashes($row->updatevon).",
								updateamum=".$db->addslashes($row->lupdate)."
								WHERE aufteilung_id='".addslashes($row->aufteilung_id)."'";
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
				}
			}
			else 
			{
				//Aufteilung nicht vorhanden
				$qry="INSERT INTO wawi.tbl_aufteilung(aufteilung_id, bestellung_id, oe_kurzbz, anteil, 
						insertamum, insertvon, updateamum, updatevon) VALUES("
						.$db->addslashes($row->aufteilung_id).","
						.$db->addslashes($row->bestellung_id).","
						.$db->addslashes($row->oe_kurzbz).","
						.$db->addslashes($row->anteil).","
						.$db->addslashes($row->cdate).","
						.$db->addslashes($row->insertvon).","
						.$db->addslashes($row->lupdate).","
						.$db->addslashes($row->updatevon).");";
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

	if(!$db->db_query("SELECT setval('wawi.seq_aufteilung_aufteilung_id',(SELECT max(aufteilung_id) FROM wawi.tbl_aufteilung));"))
	{
		$error_log.="\nFehler beim Update der Sequence";
		$anzahl_fehler++;
	}
	//Mail versenden
	$statistik="Aufteilung Default Sync\n--------------\n";
	$statistik.="Beginn: ".$starttime." von ".DB_NAME." - Anzahl Einträge: ".$anzahl_aufteilungen."\n\n";
	$statistik.="\nEingefügte Datensätze: $anzahl_insert";
	$statistik.="\nGeänderte Datensätze: $anzahl_update";
	$statistik.="\nFehler: $anzahl_fehler\n";
	
	$synced=$statistik.$error_log.$update_log;
	$mail = new mail(MAIL_ADMIN, "vilesci@".DOMAIN, "SYNC Aufteilung Default von ".DB_NAME, $synced);
	$mail->setReplyTo("vilesci@".DOMAIN);
	if(!$mail->send())
	{
		echo "<font color=\"#FF0000\">Fehler beim Versenden des Durchführungs-Mails!</font><br>";	
	}
}
?>