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
//* Synchronisiert Fachbereichsdatensaetze von Vilesci DB in PORTAL DB
//*
//*

include('../../vilesci/config.inc.php');
include('../../include/fachbereich.class.php');

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

//fachbereich
$qry = "SELECT * FROM tbl_fachbereich";

if($result = pg_query($conn_vilesci, $qry))
{
	echo nl2br("Fachbereich Sync\n-----------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$fachbereich = new fachbereich($conn);
		$fachbereich->fachbereich_kurzbz	=$row->kurzbz;
		$fachbereich->studiengang_kz	=$row->studiengang_kz;
		$fachbereich->bezeichnung		=$row->bezeichnung;
		$fachbereich->farbe			=$row->farbe;
		$fachbereich->ext_id		=$row->fachbereich_id;
		
		$qry = "SELECT ext_id FROM tbl_fachbereich WHERE ext_id='$fachbereich->ext_id'";
			if($result1 = pg_query($conn, $qry))
			{		
				if(pg_num_rows($result1)>0) //wenn dieser eintrag schon vorhanden ist
				{
					if($row1=pg_fetch_object($result1))
					{
						//Fachbereichsdaten updaten
						$fachbereich->new=false;
						//$fachbereich->fachbereich_kurzbz=$row->fachbereich_kurzbz;
					}
					else 
					{
						$error_log.="fachbereich_id von $row->fachbereich_id konnte nicht ermittelt werden\n";
						$error=true;
					}
				}
				else 
				{
					//Fachbereich neu anlegen
					$fachbereich->new=true;
				}
				
				if(!$error)
					if(!$fachbereich->save())
					{
						$error_log.=$fachbereich->errormsg."\n";
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
	$error_log .= 'Fachbereichsdatensaetze konnten nicht geladen werden';
	
?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - Fachbereiche</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<?php

//echo nl2br($text);
echo nl2br($error_log);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");

?>
</body>
</html>