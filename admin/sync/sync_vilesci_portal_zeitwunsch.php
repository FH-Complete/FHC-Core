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
 * Synchronisiert die Zeiwuensche von Vilesci DB in PORTAL DB
 *
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/fas/zeitwunsch.class.php');

$conn=pg_connect(CONN_STRING) or die('Connection zur Portal Datenbank fehlgeschlagen');
$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die('Connection zur Vilesci Datenbank fehlgeschlagen');

$error_log='';
$text = '';
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

// ***********************************
// * VILESCI->PORTAL - Synchronisation
// ***********************************

$qry = 'SELECT * FROM tbl_zeitwunsch';

if($result = pg_query($conn_vilesci, $qry))
{
	$text.="Sync der Zeitwuensche\n\n";
	while($row=pg_fetch_object($result))
	{
		$zw = new zeitwunsch($conn);
		$zw->mitarbeiter_uid = $row->uid;
		$zw->stunde = $row->stunde;
		$zw->tag = $row->tag;
		$zw->gewicht = $row->gewicht;
		
		$qry ="SELECT count(*) as anz FROM campus.tbl_zeitwunsch where mitarbeiter_uid='".addslashes($row->uid)."' AND stunde='".addslashes($row->stunde)."' AND tag='".addslashes($row->tag)."';";
		if($row = pg_fetch_object(pg_query($conn, $qry)))
		{
			$zw->new = ($row->anz>0?false:true);
			
			if(!$zw->save())
			{
				$error_log.= "Fehler beim einfuegen des Datensatzes: $qry";
				$anzahl_fehler++;
			}
			else 
				$anzahl_eingefuegt++;
		}
		else
		{
			$this->error_log .= "Fehler beim ermitteln des Zeitwunsches: $qry";
			$anzahl_fehler++;
		}
	}
}
$text .= "Anzahl eingefuegter Datensaetze: $anzahl_eingefuegt\n";
$text .= "Anzahl der Fehler: $anzahl_fehler\n";
?>
<html>
<head>
<title>Synchro - Vilesci -> Portal - Zeitwunsch</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

echo nl2br($text);
echo nl2br($error_log);

?>
</body>
</html>