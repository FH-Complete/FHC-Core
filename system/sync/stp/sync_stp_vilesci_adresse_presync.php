<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Kopiert adressen Tabelle von FH-DB StPoelten
//*
//*

require_once('sync_config.inc.php');

$starttime=time();
$conn=pg_connect(CONN_STRING)
	or die("Connection zur FH-Complete Datenbank fehlgeschlagen");
if (!$conn_ext=mssql_connect (STPDB_SERVER, STPDB_USER, STPDB_PASSWD))
	die('Fehler beim Verbindungsaufbau!');
mssql_select_db(STPDB_DB, $conn_ext);



// Sync-Tabelle fuer Personen checken
if (!@pg_query($conn,'SELECT * FROM sync.stp_adresse LIMIT 1;'))
{
	$sql='CREATE TABLE sync.stp_adresse (
			__adresse integer,
			_person integer,
			chstrasse varchar(256),
			chhausnr varchar(32),
			chplz varchar(16),
			chort varchar(256), 
			_staat integer, 
			chbemerkung varchar(256),
			bostandardadr boolean,
			boheimatadr boolean, 
		constraint "pk_tbl_sync_stp_adresse" primary key ("__adresse"));
		Grant select on sync.stp_adresse to group "admin";
		Grant update on sync.stp_adresse to group "admin";
		Grant delete on sync.stp_adresse to group "admin";
		Grant insert on sync.stp_adresse to group "admin";';
	if (!@pg_query($conn,$sql))
		echo '<strong>sync.stp_adresse: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'sync.stp_adresse wurde angelegt!<BR>';
}

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>
<html>
<head>
<title>Synchro - FHDB -> FH-Complete - PreSyncAdresse</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>

<?php
if (!@pg_query($conn,'DELETE FROM sync.stp_adresse;'))
	echo '<strong>sync.stp_adresse: '.pg_last_error($conn).' </strong><BR>';
else
	echo 'sync.stp_adresse wurde geleert!<BR>';

echo 'Daten werden geholt!<BR>';

$i=0;

$qry="SELECT __Adresse, _Person, chStrasse, chHausNr, chPLZ, chOrt, _Staat, 
	chBemerkung, boStandardAdr, boHeimatAdr 	FROM adresse;";
if($result_ext = mssql_query($qry,$conn_ext))
{
	while($row=mssql_fetch_object($result_ext))
	{
		$qry="INSERT INTO sync.stp_adresse (__adresse, _person, chstrasse, chhausnr, chplz, chort, _staat, chbemerkung,
			bostandardadr, boheimatadr)
			VALUES ('$row->__Adresse','$row->_Person', ".
			($row->chStrasse==''?'NULL':"'".addslashes($row->chStrasse)."'").", ".
			($row->chHausNr==''?'NULL':"'".$row->chHausNr."'").", ".
			($row->chPLZ==''?'NULL':"'".$row->chPLZ."'").", ".
			($row->chOrt==''?'NULL':"'".addslashes($row->chOrt)."'").", ".
			($row->_Staat==''?'NULL':"'".$row->_Staat."'").", ".
			($row->chBemerkung==''?'NULL':"'".$row->chBemerkung."'").", ".
			"'$row->boStandardAdr','$row->boHeimatAdr');";
		if(!$result = pg_query($conn, $qry))
		{
			echo $qry.'<BR>'.pg_last_error($conn).' </strong><BR>';
		}

		echo '.';
		flush();

		$i++;
	}
}
else
	echo 'Fehler';//mssql_lasterror($conn_ext);

	echo '<BR>Time elapsed: '.(time()-$starttime).' seconds!';

?>
</body>
</html>