<?php
/* Copyright (C) 2006 Technikum-Wien
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

//$adress='ruhan@technikum-wien.at';
$adress='fas_sync@technikum-wien.at';

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
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");
$error_log.="Sync Nation:\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler";
mail($adress, 'SYNC Nation', $error_log,"From: vilesci@technikum-wien.at");
?>
</body>
</html>