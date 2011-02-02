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
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/berechtigung.class.php');

if (!$db = new basis_db())
	die('Fehler beim Herstellen der Datenbankverbindung');

if (!$conn_wawi = pg_pconnect(CONN_STRING_WAWI)) 
   	die('Es konnte keine Verbindung zum Server aufgebaut werden.   *** File:='.__FILE__.' Line:='.__LINE__."\n");

$error_log='';
$update_log='';
$anzahl=0;
$anzahl_insert=0;
$anzahl_update=0;
$anzahl_delete=0;
$anzahl_fehler=0;
$starttime=date("d.m.Y H:i:s");


checkBerechtigung('wawi/bestellung','Bestellungen verwalten');
checkBerechtigung('wawi/rechnung','Rechnungen verwalten');
checkBerechtigung('wawi/konto','Konten verwalten');
checkBerechtigung('wawi/kostenstelle','Kostenstellen verwalten');
checkBerechtigung('wawi/freigabe','Freigeben von Bestellungen');
checkBerechtigung('wawi/rechnung_freigeben','Freigeben von Rechnungen');
checkBerechtigung('wawi/rechnung_transfer','Setzen des TransferDatums von Rechnungen');

?>
<html>
<head>
<title>Synchro - WaWi -&gt; FAS - Berechtigung</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<?php
/*
 * Berechtigungen holen, Spalten:
 * username_neu, lesen, schreiben, freigeben, verwalten, kostenstelle_id, oe_kurzbz
 * 
 * Direkte Kostenstellenzuordnung
 * UNION
 * Direkte Studiengangszuordnung
 */
$qry="
	SET CLIENT_ENCODING TO UNICODE; 
	
	SELECT 
		username_neu, lesen, schreiben, freigeben, verwalten, kostenstelle_id, null as oe_kurzbz
	FROM 
		public.kostenstelle_benutzer 
		JOIN public.benutzer USING(user_id)
	UNION
	SELECT
		username_neu, lesen, schreiben, freigeben, verwalten, null as kostenstelle_id, oe_kurzbz
	FROM
		public.studiengang_benutzer
		JOIN public.benutzer USING(user_id)
		JOIN public.studiengang USING(studiengang_id)		
	";
/*
 * Wird nicht uebernommen:
 * 
 * Kostenstellenzuordnung über Gruppen
 * UNION
 * Studiengangszuordnung über Gruppen
 * UNION

SELECT 
		username_neu, lesen, schreiben, freigeben, verwalten, kostenstelle_id, null as oe_kurzbz
	FROM
		public.kostenstelle_gruppe
		JOIN public.gruppe_benutzer USING(gruppe_id)
		JOIN public.benutzer USING(user_id)
	UNION
	SELECT
		username_neu, lesen, schreiben, freigeben, verwalten, null as kostenstelle_id, oe_kurzbz
	FROM
		public.studiengang_gruppe
		JOIN public.gruppe_benutzer USING(gruppe_id)
		JOIN public.benutzer USING(user_id)
		JOIN public.studiengang USING(studiengang_id)
	UNION
*/
if($result=pg_query($conn_wawi, $qry))
{

	$anzahl=pg_num_rows($result);
	
	while($row = pg_fetch_object($result))
	{
		if($row->username_neu=='test')
			continue;
		
		if($row->freigeben=='t' && $row->kostenstelle_id!='')
		{
			// wawi/freigabe suid kostenstelle_id
			addBerechtigung($row->username_neu, 'wawi/freigabe', 'suid', null, $row->kostenstelle_id);
		}
		
		$art='';
		if($row->lesen=='t')
			$art='s';
		if($row->schreiben=='t')
			$art.='uid';
			
		if($art!='')
		{
			// wawi/bestellung $art $kostenstelle_id/$oe_kurzbz
			addBerechtigung($row->username_neu, 'wawi/bestellung', $art, $row->oe_kurzbz, $row->kostenstelle_id);
		
			// wawi/rechnung $art $kostenstelle_id/$oe_kurzbz
			addBerechtigung($row->username_neu, 'wawi/rechnung', $art, $row->oe_kurzbz, $row->kostenstelle_id);
			
			// wawi/firma sui Firmenverwaltung fuer alle freischalten die eine berechtigung im wawi haben
			addBerechtigung($row->username_neu, 'wawi/firma', 'sui', null, null);
		}
	}
	
	/**
	 * Permissions aufgrund des Feldes perms
	 */
	 
	//GST
	$qry = "SELECT username_neu FROM public.benutzer WHERE perms='gst'";
	if($result = pg_query($conn_wawi, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			addBerechtigung($row->username_neu, 'wawi/freigabe', 'suid', 'gst', null);
		}
	}
	
	//REK
	$qry = "SELECT username_neu FROM public.benutzer WHERE perms='rek'";
	if($result = pg_query($conn_wawi, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			addBerechtigung($row->username_neu, 'wawi/freigabe', 'suid', 'etw', null);
		}
	}
	
	//GMBH
	$qry = "SELECT username_neu FROM public.benutzer WHERE perms='gmb'";
	if($result = pg_query($conn_wawi, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			addBerechtigung($row->username_neu, 'wawi/freigabe', 'suid', 'gmbh', null);
		}
	}
	
	//Admin
	$qry = "SELECT username_neu FROM public.benutzer WHERE perms='admin'";
	if($result = pg_query($conn_wawi, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			addBerechtigung($row->username_neu, 'wawi/konto', 'suid', 'gst', null);
			addBerechtigung($row->username_neu, 'wawi/kostenstelle', 'suid', 'gst', null);
			addBerechtigung($row->username_neu, 'wawi/bestellung', 'suid', 'gst', null);
			addBerechtigung($row->username_neu, 'wawi/rechnung', 'suid', 'gst', null);
			addBerechtigung($row->username_neu, 'wawi/rechnung_transfer', 'suid', 'gst', null);
			addBerechtigung($row->username_neu, 'wawi/rechnung_freigeben', 'suid', 'gst', null);
			addBerechtigung($row->username_neu, 'wawi/firma', 'suid', null, null);
			addBerechtigung($row->username_neu, 'wawi/budget', 'suid', 'gst', null);
			addBerechtigung($row->username_neu, 'wawi/storno', 'suid', 'gst', null);
		}
	}
	//Mail versenden
	$statistik="Berechtigung Sync\n--------------\n";
	$statistik.="Beginn: ".$starttime." von ".DB_NAME." - Anzahl Einträge: ".$anzahl."\n\n";
	$statistik.="\nEingefügte Datensätze: $anzahl_insert";
	$statistik.="\nGeänderte Datensätze: $anzahl_update";
	$statistik.="\nFehler: $anzahl_fehler\n";
	
	$synced=$statistik.$error_log.$update_log;
	$mail = new mail(MAIL_ADMIN, "vilesci@".DOMAIN, "SYNC Berechtigung von ".DB_NAME, $synced);
	$mail->setReplyTo("vilesci@".DOMAIN);
	if(!$mail->send())
	{
		echo "<font color=\"#FF0000\">Fehler beim Versenden des Durchführungs-Mails!</font><br>";	
	}
}

/**
 * Prueft ob die Berechtigung (Benutzerrolle) vorhanden ist. Wenn nicht wird diese Angelegt
 * @param $username
 * @param $berechtigung_kurbz
 * @param $art
 * @param $oe_kurzbz
 * @param $kostenstelle_id
 */
function addBerechtigung($username, $berechtigung_kurzbz, $art, $oe_kurzbz, $kostenstelle_id)
{
	global $error_log, $anzahl_fehler, $anzahl_insert, $db;
	
	$qry = "SELECT * FROM system.tbl_benutzerrolle 
						WHERE 
							uid='".addslashes($username)."' 
							AND berechtigung_kurzbz='".addslashes($berechtigung_kurzbz)."'";
	if($oe_kurzbz!='')					 
		$qry.="	AND oe_kurzbz='".addslashes($oe_kurzbz)."'";
	if($kostenstelle_id!='')
		$qry.="	AND kostenstelle_id='".addslashes($kostenstelle_id)."'";
	if($art!='')
		$qry.=" AND art='".addslashes($art)."'";
	
	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)==0)
		{
			$rechte = new benutzerberechtigung();
			$rechte->uid = $username;
			$rechte->berechtigung_kurzbz = $berechtigung_kurzbz;
			$rechte->art = $art;
			$rechte->oe_kurzbz = $oe_kurzbz;
			$rechte->kostenstelle_id = $kostenstelle_id;
			$rechte->insertamum = date('Y-m-d H:i:s');
			$rechte->inservon = 'Syncro';
			$rechte->new = true;
			
			if($rechte->save())
			{
				$anzahl_insert++;
			}
			else
			{
				$anzahl_fehler++;
				$error_log.="\n".$rechte->errormsg;
			}
		}
	}
}

/**
 * Prueft ob eine Berechtigung vorhanden ist und legt diese ggf an
 * @param $berechtigung_kurzbz
 * @param $beschreibung
 */
function checkBerechtigung($berechtigung_kurzbz, $beschreibung)
{
	global $error_log, $anzahl_fehler;
	
	$berechtigung = new berechtigung();
	
	if(!$berechtigung->load($berechtigung_kurzbz))
	{
		$berechtigung->berechtigung_kurzbz=$berechtigung_kurzbz;
		$berechtigung->beschreibung=$beschreibung;
		$berechtigung->new = true;
		
		if(!$berechtigung->save())
		{
			$error_log.="\nFehler beim Anlegen der Berechtigung wawi/bestellung";
			$anzahl_fehler++;
		}
	}
}
?>