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

//*
//* Synchronisiert LVInfodatensaetze von Vilesci DB nach PORTAL DB
//*
//*

include('../../vilesci/config.inc.php');
include('../../include/fas/lvinfo.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die("Connection zur Vilesci Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

function validate($row)
{
}

/*************************
 * VILESCI-PORTAL - Synchronisation
 */

//lvinfo
$qry = "SELECT * FROM tbl_lvinfo";

if($result = pg_query($conn_vilesci, $qry))
{
	echo nl2br("LVInfo Sync\n-------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$lvinfo = new lvinfo($conn);
		$lvinfo->lvinfo_id			=$row->lvinfo_id;
		$lvinfo->lehrziele			=$row->lehrziele;
		$lvinfo->lehrinhalte			=$row->lehrinhalte;
		$lvinfo->voraussetzungen		=$row->voraussetzungen;
		$lvinfo->unterlagen			=$row->unterlagen;
		$lvinfo->pruefungsordnung		=$row->pruefungsordnung;
		$lvinfo->anmerkungen		=$row->anmerkungen;
		//$lvinfo->kurzbz			=$row->kurzbz;
		$lvinfo->lehrformen			=$row->lehrformen;
		$lvinfo->genehmigt			=($row->genehmigt=='t'?true:false);
		$lvinfo->aktiv				=($row->aktiv=='t'?true:false);
		$lvinfo->sprache			=$row->sprache;
		$lvinfo->lehrveranstaltung_nr	=$row->lehrfach_nr;
		//$funktion->insertamum		='';
		$funktion->insertvon			='SYNC';
		//$funktion->updateamum		='';
		//$funktion->updatevon		=$row->updatevon;
		
		$qry = "SELECT lvinfo_id FROM tbl_lvinfo WHERE lvinfo_id='$lvinfo->lvinfo_id'";
			if($result1 = pg_query($conn, $qry))
			{		
				if(pg_num_rows($result1)>0) //wenn dieser eintrag schon vorhanden ist
				{
					if($row1=pg_fetch_object($result1))
					{
						//Funktionsdaten updaten
						$lvinfo->new=false;
						$lvinfo->lvinfo_id=$row->lvinfo_id;
					}
					else 
					{
						$error_log.="lvinfo_id von <b>$row->lvinfo_id</b> konnte nicht ermittelt werden\n";
						$error=true;
					}
				}
				else 
				{
					//LVInfo neu anlegen
					$lvinfo->new=true;
					
					$qry = "SELECT lehrveranstaltung_nr FROM tbl_lehrveranstaltung WHERE ext_id = '$row->lehrfach_nr' ;";
					if ($result2 = pg_query($conn, $qry))
					{					
						if(pg_num_rows($result2)>0)
						{
							if ($row2=pg_fetch_object($result2))
							{
								$lvinfo->lehrverantaltung_nr = $row2->lehrveranstaltung_nr;
							}
						}
						else 
						{
							$error_log.= "Lehrveranstaltung <b>".$row->lehrfach_nr."</b> nicht gefunden\n";
							$error=true;
						}
					}
				}
				
				if(!$error)
					if(!$lvinfo->save())
					{
						$error_log.=$lvinfo->errormsg."\n";
						$anzahl_fehler++;
					}
					else 
						$anzahl_eingefuegt++;
				else 
					$anzahl_fehler++;
			}	
	}
	echo nl2br("abgeschlossen\n\n");
}
else
	$error_log .= 'Funktiondatensaetze konnten nicht geladen werden';
	
?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - LVInfo</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

//echo nl2br($text);
echo nl2br($error_log);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");

?>
</body>
</html>