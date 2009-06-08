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
 * Synchronisiert Gruppen von Vilesci DB in PORTAL DB
 *
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/gruppe.class.php');

$conn=pg_connect(CONN_STRING) or die('Connection zur Portal Datenbank fehlgeschlagen');
$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die('Connection zur Vilesci Datenbank fehlgeschlagen');

$error_log='';
$text = '';
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

// ***********************************
// * VILESCI->PORTAL - Synchronisation
// ***********************************

$qry = 'SELECT einheit_kurzbz as gruppe_kurzbz, studiengang_kz, bezeichnung, semester, typ, mailgrp_kurzbz, 
               bezeichnung as mailgrp_beschreibung, true as sichtbar , true as aktiv, null as updateamum, 
               null as updatevon, null as insertamum, null as insertvon  
        FROM tbl_einheit WHERE mailgrp_kurzbz is null
        UNION 
        SELECT mailgrp_kurzbz as gruppe_kurzbz, studiengang_kz, beschreibung as bezeichnung, null as semester, 
               null as typ, mailgrp_kurzbz, beschreibung as mailgrp_beschreibung, sichtbar, aktiv, null as updateamum, 
               null as updatevon, null as insertamum, null as insertvon 
        FROM tbl_mailgrp';

if($result = pg_query($conn_vilesci, $qry))
{
	$text.="\n Sync Gruppe\n\n";
	while($row = pg_fetch_object($result))
	{
		$gruppe = new gruppe($conn);
		$gruppe->gruppe_kurzbz = strtoupper($row->gruppe_kurzbz);
		$gruppe->studiengang_kz = $row->studiengang_kz;
		$gruppe->bezeichnung = substr($row->bezeichnung,0,32);
		$gruppe->semester = $row->semester;
		$gruppe->sort = $row->typ;
		$gruppe->mailgrp = ($row->mailgrp_kurzbz!=''?true:false);
		$gruppe->beschreibung = $row->mailgrp_beschreibung;
		$gruppe->sichtbar = ($row->sichtbar=='t'?true:false);
		$gruppe->aktiv = ($row->aktiv=='t'?true:false);
		$gruppe->updateamum = $row->updateamum;
		$gruppe->updatevon = $row->updatevon;
		$gruppe->insertamum = $row->insertamum;
		$gruppe->insertvon = $row->insertvon;
		
		$qry = "SELECT count(*) as anz FROM tbl_gruppe where gruppe_kurzbz='".addslashes($row->gruppe_kurzbz)."'";
		
		if($row1=pg_fetch_object(pg_query($conn,$qry)))
		{
			$new = ($row1->anz>0?false:true);
			
			if(!$gruppe->save($new))
			{				
				$anzahl_fehler++;
				$error_log .= $gruppe->errormsg."\n";
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
	$error_log .= "Gruppen konnten nicht geladen werden\n";
$text.="Anzahl aktualisierte Datensaetze: $anzahl_eingefuegt\n";
$text.="Anzahl der Fehler: $anzahl_fehler\n";
?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - Gruppe</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<?php

echo nl2br($text);
echo nl2br($error_log);

?>
</body>
</html>