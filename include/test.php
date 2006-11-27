<?php
include ('../cis2/config.inc.php');
require_once('raumtyp.class.php');
// Verbindung aufbauen
	$conn=pg_pconnect(CONN_STRING) or die ("Unable to connect to SQL-Server");
echo 'test';

$test=new raumtyp($conn);
?>
