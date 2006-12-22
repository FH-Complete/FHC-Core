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
/**
 * Synchronisiert tbl_personlvstudiensemester von Vilesci DB in PORTAL DB
 *
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/lehrverband.class.php');
require_once('../../include/gruppe.class.php');

$conn=pg_connect(CONN_STRING) or die('Connection zur Portal Datenbank fehlgeschlagen');
$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die('Connection zur Vilesci Datenbank fehlgeschlagen');

$error_log='';
$text = '';
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

function myaddslashes($var)
{
		return ($var!=''?"'".addslashes($var)."'":'null');
}
// ***********************************
// * VILESCI->PORTAL - Synchronisation
// ***********************************
if(isset($_GET['dev']))
	$dev=true;
else
	$dev=false;
	
if($dev)
	$qry = "SELECT * FROM tbl_stundenplandev";
else
	$qry = "SELECT * FROM tbl_stundenplan";

if($result = pg_query($conn_vilesci, $qry))
{
	if(isset($_GET['dev']))
		$text.="\n Sync StundenplanDEV\n\n";
	else
		$text.="\n Sync Stundenplan\n\n";
		
	while($row = pg_fetch_object($result))
	{
		if($row->verband==0)
			$row->verband=' ';
			
		if($row->einheit_kurzbz=='')
		{
			//Lehrverbandsgruppe
			$lvb_obj = new lehrverband($conn);
			
			if(!$lvb_obj->exists($row->studiengang_kz, $row->semester, $row->verband, $row->gruppe))
			{				
				$lvb_obj->studiengang_kz = $row->studiengang_kz;
				$lvb_obj->semester = $row->semester;
				$lvb_obj->verband = $row->verband;
				$lvb_obj->gruppe = $row->gruppe;
				$lvb_obj->aktiv = false;
				if(!$lvb_obj->save())
				{
					$error_log .= $lvb_obj->errormsg."\n";
					$anzahl_fehler++;
				}
			}
		}
		else
		{
			//Spezialgruppe
			$grp_obj = new gruppe($conn);
			
			if(!$grp_obj->exists($row->einheit_kurzbz))
			{
				$grp_obj->gruppe_kurzbz = $row->einheit_kurzbz;
				$grp_obj->studiengang_kz = $row->studiengang_kz;
				$grp_obj->semester = $row->semester;
				$grp_obj->mailgrp = false;
				$grp_obj->sichtbar = false;
				$grp_obj->aktiv = false;
				$grp_obj->new = true;
				
				//Bei Spezialgruppen keinen Verband/Gruppe angeben
				$row->verband=' ';
				$row->gruppe=' ';
				
				if(!$grp_obj->save())
				{
					$error_log.=$grp_obj->errormsg;
					$anzahl_fehler++;
				}
			}
		}
		
		//Lehreinheit_id ermitteln
		if($row->lehrveranstaltung_id!='')
		{
			$qry_le = "SELECT lehreinheit_id_portal FROM tbl_synclehreinheit WHERE lehrveranstaltung_id_vilesci='".addslashes($row->lehrveranstaltung_id)."'";
			if($row_le=pg_fetch_object(pg_query($conn,$qry_le)))
			{
				$lehreinheit_id = $row_le->lehreinheit_id_portal;
			}
			else 
			{			
				$lehreinheit_id='';
			}
		}
		else 
			$lehreinheit_id='';
			
		if($dev)
			$qry = "INSERT INTO lehre.tbl_stundenplandev(stundenplandev_id,";
		else
			$qry = 'INSERT INTO campus.tbl_stundenplan(stundenplan_id,';
			
		$qry.='unr, mitarbeiter_uid, datum, stunde, ort_kurzbz, gruppe_kurzbz, titel, 
		       anmerkung, fix, updateamum, updatevon, lehreinheit_id, 
		       studiengang_kz, semester, verband, gruppe) VALUES(';
		
		if($dev)
			$qry.="'".$row->stundenplandev_id."'";
		else 
			$qry.="'".$row->stundenplan_id."'";		
			
		//fix fuer fehlerhafte Lehrverbaende
		
		if(trim($row->semester)!='')
			$verb=$row->verband;
		else 
			$verb=' ';
		if(trim($verb)!='')
			$gruppe=$row->gruppe;
		else 
			$gruppe=' ';
			
		$qry.=",".myaddslashes($row->unr).",".
					myaddslashes($row->uid).",".
					myaddslashes($row->datum).",".
			      	myaddslashes($row->stunde).",".
			      	myaddslashes($row->ort_kurzbz).",".
			      	myaddslashes($row->einheit_kurzbz).",".
			      	myaddslashes($row->titel).",".
			      	myaddslashes($row->anmerkung).",".
			      	($row->fix=='t'?'true':'false').",'".
			      	addslashes($row->updateamum)."','".
			      	addslashes($row->updatevon)."',".
			      	myaddslashes($lehreinheit_id).",".
			      	myaddslashes($row->studiengang_kz).",".
			      	myaddslashes($row->semester).",'".
			      	($verb!=''?addslashes($verb):' ')."','".
			      	(($gruppe!='' && $gruppe!=0)?addslashes($gruppe):' ')."');";
			if(pg_query($conn,$qry))
			{
				$anzahl_eingefuegt++;
			}
			else 
			{
				$anzahl_fehler++;
				$error_log.= 'Fehler beim Einfuegen: '.$qry;
			}
	}
}
else
	$error_log .= "Stundenplan konnten nicht geladen werden\n";

$text.="Anzahl Datensaetze Vilesci: ".pg_num_rows($result)."\n";	
$text.="Anzahl aktualisierte Datensaetze: $anzahl_eingefuegt\n";
$text.="Anzahl der Fehler: $anzahl_fehler\n";
?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - Stundenplan</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

echo nl2br($text);
echo nl2br($error_log);

?>
</body>
</html>