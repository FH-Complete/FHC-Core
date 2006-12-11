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
	$qry = "SELECT * FROM tbl_stundenplandev limit 200";
else
	$qry = "SELECT * FROM tbl_stundenplan limit 200";

if($result = pg_query($conn_vilesci, $qry))
{
	if(isset($_GET['dev']))
		$text.="\n Sync StundenplanDEV\n\n";
	else
		$text.="\n Sync Stundenplan\n\n";
		
	while($row = pg_fetch_object($result))
	{
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
		
		//Lehreinheit_id ermitteln
		$qry_le = "SELECT lehreinheit_id_portal FROM tbl_synclehreinheit WHERE lehrveranstaltung_id_vilesci='".addslashes($row->lehrveranstaltung_id)."'";
		if($row_le=pg_fetch_object(pg_query($conn,$qry_le)))
		{
			$lehreinheit_id = $row_le->lehreinheit_id_portal;
		}
		else 
		{			
			$lehreinheit_id='';
		}
		
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
			      	($row->verband!=''?addslashes($row->verband):' ')."','".
			      	(($row->gruppe!='' && $row->gruppe!=0)?addslashes($row->gruppe):' ')."');";
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