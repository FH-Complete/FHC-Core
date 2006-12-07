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
//* Synchronisiert Funktiondatensaetze von Vilesci DB in PORTAL DB
//*
//*

include('../../vilesci/config.inc.php');
include('../../include/fas/ferien.class.php');

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

//ferien
$qry = "SELECT * FROM tbl_ferien";

if($result = pg_query($conn_vilesci, $qry))
{
	echo nl2br("Ferien Sync\n------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$ferien = new ferien($conn);
		$ferien->bezeichnung	=$row->bezeichnung;
		$ferien->studiengang_kz	=$row->studiengang_kz;
		$ferien->vondatum		=$row->vondatum;
		$ferien->bisdatum		=$row->bisdatum;
		
		$qry = "SELECT bezeichnung, studiengang_kz FROM tbl_ferien WHERE bezeichnung='$row->bezeichnung' AND studiengang_kz='$row->studiengang_kz'";
			if($result1 = pg_query($conn, $qry))
			{		
				if(pg_num_rows($result1)>0) //wenn dieser eintrag schon vorhanden ist
				{
					if($row1=pg_fetch_object($result1))
					{
						//Feriendaten updaten
						$ferien->new=false;
						
					}
					else 
					{
						$error_log.="bezeichnung von $row->studiengang_kz, $row->bezeichnung konnte nicht ermittelt werden\n";
						$error=true;
					}
				}
				else 
				{
					//Ferien neu anlegen
					$ferien->new=true;
				}
				
				if(!$error)
				{
					if(!$ferien->save())
					{
						$error_log.=$ferien->errormsg."\n";
						$anzahl_fehler++;
					}
					else 
					{
						$anzahl_eingefuegt++;
					}
				}
				else 
					$anzahl_fehler++;
			}	
	}
	echo nl2br("abgeschlossen\n\n");
}
else
	$error_log .= 'Feriendatensaetze konnten nicht geladen werden';
	
?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - Ferien</title>
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