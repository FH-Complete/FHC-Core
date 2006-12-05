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
include('../../include/fas/ortraumtyp.class.php');

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

//funktion
$qry = "SELECT * FROM tbl_ortraumtyp";

if($result = pg_query($conn_vilesci, $qry))
{
	echo nl2br("OrtRaumtyp Sync\n------------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$ortraumtyp = new ortraumtyp($conn);
		$ortraumtyp->ort_kurzbz		=$row->ort_kurzbz;
		$ortraumtyp->hierarchie		=$row->hierarchie;
		$ortraumtyp->raumtyp_kurzbz	=$row->raumtyp_kurzbz;
		//$ortraumtyp->insertamum		='';
		$ortraumtyp->insertvon		='SYNC';
		//ortraumtyp->updateamum		='';
		//$ortraumtyp->updatevon		=$row->updatevon;
		
		$qry = "SELECT ort_kurzbz, hierarchie FROM tbl_ortraumtyp WHERE ort_kurzbz = '$row->ort_kurzbz' AND hierarchie = '$row->hierarchie'";
			if($result1 = pg_query($conn, $qry))
			{		
				if(pg_num_rows($result1)>0) //wenn dieser eintrag schon vorhanden ist
				{
					if($row1=pg_fetch_object($result1))
					{
						//OrtRaumtypdaten updaten
						$ortraumtyp->new=false;
					}
					else 
					{
						$error_log.="ort_kurzbz von $row->ort_kurzbz konnte nicht ermittelt werden\n";
						$error=true;
					}
				}
				else 
				{
					//OrtRaumtyp neu anlegen
					$ortraumtyp->new=true;
				}
				
				if(!$error)
					if(!$ortraumtyp->save())
					{
						$error_log.=$ortraumtyp->errormsg."\n";
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
<title>Synchro - Vilesci -> Portal - OrtRaumtyp</title>
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