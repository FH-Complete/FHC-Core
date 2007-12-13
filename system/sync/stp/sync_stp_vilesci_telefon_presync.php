<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Kopiert telefon Tabelle von FH-DB StPoelten
//*
//*

require_once('sync_config.inc.php');

$starttime=time();
$conn=pg_connect(CONN_STRING)
	or die("Connection zur FH-Complete Datenbank fehlgeschlagen");
if (!$conn_ext=mssql_connect (STPDB_SERVER, STPDB_USER, STPDB_PASSWD))
	die('Fehler beim Verbindungsaufbau!');
mssql_select_db(STPDB_DB, $conn_ext);

/*
__Telefon 		(int)
chBemerkung 	(char)
chVorwahl 		(char)
chNummer 		(char)
chKlappe 		(char)
_Person 		(int)
boStandard 		(bit) 
*/

// Sync-Tabelle fuer telefon checken
if (!@pg_query($conn,'SELECT * FROM sync.stp_telefon LIMIT 1;'))
{
	$sql='CREATE TABLE sync.stp_telefon (
			__telefon integer,
			_person integer,
			chbemerkung varchar(64),
			chvorwahl varchar(16),
			chnummer varchar(32),
			chklappe varchar(16), 
			bostandard boolean,
		constraint "pk_tbl_sync_stp_telefon" primary key ("__telefon"));
		Grant select on sync.stp_telefon to group "admin";
		Grant update on sync.stp_telefon to group "admin";
		Grant delete on sync.stp_telefon to group "admin";
		Grant insert on sync.stp_telefon to group "admin";';
	if (!@pg_query($conn,$sql))
		echo '<strong>sync.stp_telefon: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'sync.stp_telefon wurde angelegt!<BR>';
}

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>
<html>
<head>
<title>Synchro - FHDB -> FH-Complete - PreSyncTelefon</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>

<?php
if (!@pg_query($conn,'DELETE FROM sync.stp_telefon;'))
	echo '<strong>sync.stp_telefon: '.pg_last_error($conn).' </strong><BR>';
else
	echo 'sync.stp_telefon wurde geleert!<BR>';

echo 'Daten werden geholt!<BR>';

$i=0;

$qry="SELECT __Telefon, _Person, chVorwahl, chNummer, chKlappe, chBemerkung, boStandard	FROM telefon;";
if($result_ext = mssql_query($qry,$conn_ext))
{
	while($row=mssql_fetch_object($result_ext))
	{
		$qry="INSERT INTO sync.stp_telefon (__telefon, _person, chvorwahl, chnummer, chklappe, chbemerkung, bostandard)
			VALUES ('$row->__Telefon','$row->_Person', ".
			($row->chVorwahl==''?'NULL':"'".addslashes($row->chVorwahl)."'").", ".
			($row->chNummer==''?'NULL':"'".$row->chNummer."'").", ".
			($row->chKlappe==''?'NULL':"'".$row->chKlappe."'").", ".
			($row->chBemerkung==''?'NULL':"'".$row->chBemerkung."'").", ".
			"'$row->boStandard');";
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