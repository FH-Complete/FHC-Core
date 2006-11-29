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
 * Synchronisiert die Sprache von Vilesci DB in PORTAL DB
 *
 */
require_once('../../vilesci/config.inc.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die("Connection zur Vilesci Datenbank fehlgeschlagen");

$error_log='';
$text = '';
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

// ***********************************
// * VILESCI->PORTAL - Synchronisation
// ***********************************

$qry = "SELECT sprache FROM tbl_sprache";

if($result = pg_query($conn_vilesci, $qry))
{
	$text.="Sync der Sprache\n\n";
	while($row=pg_fetch_object($result))
	{
		$qry = "INSERT INTO tbl_sprache(sprache) VALUES('$row->sprache');";
		if(!pg_query($conn, $qry))
		{
			$error_log.= "Fehler beim einfuegen des Datensatzes: $qry";
			$anzahl_fehler++;
		}
		else 
			$anzahl_eingefuegt++;
	}
}
$text .= "Anzahl eingefuegter Datensaetze: $anzahl_eingefuegt\n";
$text .= "Anzahl der Fehler: $anzahl_fehler\n";
?>
<html>
<head>
<title>Synchro - Vilesci -> Portal - Sprache</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

echo nl2br($text);
echo nl2br($error_log);

?>
</body>
</html>