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
 * Synchronisiert BenutzerGruppen von Vilesci DB in PORTAL DB
 *
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/benutzergruppe.class.php');

$conn=pg_connect(CONN_STRING) or die('Connection zur Portal Datenbank fehlgeschlagen');
$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die('Connection zur Vilesci Datenbank fehlgeschlagen');

$error_log='';
$text = '';
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

// ***********************************
// * VILESCI->PORTAL - Synchronisation
// ***********************************

$qry = 'SELECT uid, UPPER(mailgrp_kurzbz) as gruppe_kurzbz, updateamum, updatevon 
        FROM tbl_personmailgrp 
        UNION 
        SELECT uid, UPPER(einheit_kurzbz) as gruppe_kurzbz, updateamum, updatevon 
        FROM tbl_einheitstudent';

if($result = pg_query($conn_vilesci, $qry))
{
	$text.="\n Sync BenutzerGruppe\n\n";
	while($row = pg_fetch_object($result))
	{
		$bngruppe = new benutzergruppe($conn);
		$bngruppe->gruppe_kurzbz = $row->gruppe_kurzbz;
		$bngruppe->uid = $row->uid;
		$bngruppe->updateamum = $row->updateamum;
		$bngruppe->updatevon = $row->updatevon;
		$bngruppe->insertamum = '';
		$bngruppe->insertvon = '';
		
		$qry = "SELECT count(*) as anz FROM tbl_benutzergruppe WHERE 
		        gruppe_kurzbz='".addslashes($row->gruppe_kurzbz)."' AND uid='".addslashes($row->uid)."'";
		
		if($row1=pg_fetch_object(pg_query($conn,$qry)))
		{
			$new = ($row1->anz>0?false:true);
			
			if(!$bngruppe->save($new))
			{				
				$anzahl_fehler++;
				$error_log .= $bngruppe->errormsg."\n";
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
	$error_log .= "BenutzerGruppen konnten nicht geladen werden\n";
$text.="Anzahl aktualisierte Datensaetze: $anzahl_eingefuegt\n";
$text.="Anzahl der Fehler: $anzahl_fehler\n";
?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - BenutzerGruppe</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

echo nl2br($text);
echo nl2br($error_log);

?>
</body>
</html>