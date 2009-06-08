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
 * Synchronisiert Reservierungen von Vilesci DB in PORTAL DB
 *
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/reservierung.class.php');

$conn=pg_connect(CONN_STRING) or die('Connection zur Portal Datenbank fehlgeschlagen');
$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die('Connection zur Vilesci Datenbank fehlgeschlagen');

$error_log='';
$text = '';
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

// ***********************************
// * VILESCI->PORTAL - Synchronisation
// ***********************************

$qry = 'Select * FROM tbl_reservierung';

if($result = pg_query($conn_vilesci, $qry))
{
	$text.="\n Sync Reservierung\n\n";
	while($row = pg_fetch_object($result))
	{
		$reservierung = new reservierung($conn);
		$reservierung->reservierung_id = $row->reservierung_id;
		$reservierung->ort_kurzbz = $row->ort_kurzbz;
		$reservierung->studiengang_kz = $row->studiengang_kz;
		$reservierung->uid = $row->uid;
		$reservierung->stunde = $row->stunde;
		$reservierung->datum = $row->datum;
		$reservierung->titel = $row->titel;
		$reservierung->beschreibung = $row->beschreibung;
		$reservierung->semester = $row->semester;
		$reservierung->verband = $row->verband;
		$reservierung->gruppe = $row->gruppe;
		$reservierung->gruppe_kurzbz = $row->einheit_kurzbz;
		
		$qry = "SELECT count(*) as anz FROM campus.tbl_reservierung where reservierung_id='".addslashes($row->reservierung_id)."'";
		
		if($row1 =pg_fetch_object(pg_query($conn,$qry)))
		{
			$var = ($row1->anz>0?false:true);
			//Wenn reservierung vorhanden ist dann updaten sonst neu einfuegen
			if(!$reservierung->save($var))
			{				
				$anzahl_fehler++;
				$error_log .= $reservierung->errormsg."\n";
			}
			else 
				$anzahl_eingefuegt++;
		}
		else 
		{
			$error_log.='Fehler beim Auslesen';
			$anzahl_fehler++;
		}
	}
}
else
	$error_log .= "Reservierungen konnten nicht geladen werden\n";
$text.="Anzahl aktualisierte Datensaetze: $anzahl_eingefuegt\n";
$text.="Anzahl der Fehler: $anzahl_fehler\n";
?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - Reservierung</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<?php

echo nl2br($text);
echo nl2br($error_log);

?>
</body>
</html>