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
 * Synchronisiert Benutzerberechtigung von Vilesci DB in PORTAL DB
 *
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/fas/benutzerberechtigung.class.php');

$conn=pg_connect(CONN_STRING) or die('Connection zur Portal Datenbank fehlgeschlagen');
$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die('Connection zur Vilesci Datenbank fehlgeschlagen');

$error_log='';
$text = '';
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

// ***********************************
// * VILESCI->PORTAL - Synchronisation
// ***********************************

//Mitarbeiter
$qry = 'Select * FROM tbl_userberechtigung';

if($result = pg_query($conn_vilesci, $qry))
{
	$text.="\n Sync Benutzerberechtigung\n\n";
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$benutzerberechtigung = new benutzerberechtigung($conn);
		$benutzerberechtigung->art = $row->art;
		$benutzerberechtigung->fachbereich_id = $row->fachbereich_id;
		$benutzerberechtigung->studiengang_kz = $row->studiengang_kz;
		$benutzerberechtigung->berechtigung_kurzbz = $row->berechtigung_kurzbz;
		$benutzerberechtigung->uid = $row->uid;
		$benutzerberechtigung->studiensemester_kurzbz = $row->studiensemester_kurzbz;
		$benutzerberechtigung->start = $row->start;
		$benutzerberechtigung->ende = $row->ende;		
		
		//$qry = "SELECT count(*) as anz FROM tbl_feedback WHERE feedback_id='$row->feedback_id'";
		//if($row1 = pg_fetch_object(pg_query($conn, $qry)))
		//{		
		$benutzerberechtigung->new=true;
			
		if(!$benutzerberechtigung->save())
		{
			$error_log.=$benutzerberechtigung->errormsg."\n";
			$anzahl_fehler++;
		}
		else 
			$anzahl_eingefuegt++;
		//}
		//else 
		//	$error_log .= "Fehler beim ermitteln der UID\n";
	}
}
else
	$error_log .= "Berechtigungen konnten nicht geladen werden\n";
$text.="Anzahl aktualisierte Datensaetze: $anzahl_eingefuegt\n";
$text.="Anzahl der Fehler: $anzahl_fehler\n";
?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - Benutzerberechtigung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

echo nl2br($text);
echo nl2br($error_log);

?>
</body>
</html>