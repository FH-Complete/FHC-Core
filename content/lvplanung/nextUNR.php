<?php

// *****************************
// Vorschlag fuer UNR liefern
// *****************************

include('../../vilesci/config.inc.php');

$conn = pg_pconnect(CONN_STRING);
if (!$conn) 
	die('no connection');
	
$sql="select max(unr) as max_unr from tbl_lehrveranstaltung";
if(!($erg=pg_exec($conn, $sql_query))) 
{
	$this->errormsg=pg_errormessage($conn);
	return false;
}

$num_rows=pg_numrows($erg);
$result=array();
for($i=0;$i<$num_rows;$i++)
{
	$row=pg_fetch_object($erg,$i);
} 
?>
