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
//* Synchronisiert Nationendatensaetze von Vilesci DB in PORTAL DB
//*
//*

include('../../../vilesci/config.inc.php');
include('../../../include/nation.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

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
 * FAS-PORTAL - Synchronisation
 */

//nation
$qry = "SELECT * FROM nation";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Nation Sync\n--------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$nation				=new nation($conn);
		$nation->code			=$row->code;
		$nation->entwicklungsstand		=$row->entwland;
		$nation->eu				=$row->euflag=='U'?true:false;
		$nation->ewr				=$row->ewrflag=='W'?true:false;
		$nation->kontinent			=$row->kontinent;
		$nation->kurztext			=$row->kurztext;
		$nation->langtext			=$row->langtext;
		$nation->engltext			=$row->engltext;
		$nation->sperre			=$row->sperre=='J'?true:false;

		if(!$nation->save())
		{
			$error_log.=$nation->errormsg."\n";
			$anzahl_fehler++;
		}
		else 
		{
			$anzahl_eingefuegt++;
		}
	}		
}

?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - Nation</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

//echo nl2br($text);
echo nl2br($error_log);
echo nl2br("\nGesamt: $anzahl_quelle / Eingef�gt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");
$error_log.="\nGesamt: $anzahl_quelle / Eingef�gt: $anzahl_eingefuegt / Fehler: $anzahl_fehler";
mail($adress, 'SYNC Personen', $error_log);
?>
</body>
</html>