<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Kopiert personen TAbelle von FH-DB StPoelten
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
if (!@pg_query($conn,'SELECT * FROM sync.stp_stgvertiefung LIMIT 1;'))
{
	$sql='CREATE TABLE sync.stp_stgvertiefung (
			__stgvertiefung integer,
			_studiengang integer,
			chbezeichnung varchar(256),
			chkurzbez varchar(12),
			constraint "pk_tbl_sync_stp_stgvertiefung" primary key ("__stgvertiefung"));
		Grant select on sync.stp_stgvertiefung to group "admin";
		Grant update on sync.stp_stgvertiefung to group "admin";
		Grant delete on sync.stp_stgvertiefung to group "admin";
		Grant insert on sync.stp_stgvertiefung to group "admin";';
	if (!@pg_query($conn,$sql))
		echo '<strong>sync.stp_stgvertiefung: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'sync.stp_stgvertiefung wurde angelegt!<BR>';
}

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>
<html>
<head>
<title>Synchro - FHDB -> FH-Complete - PreSyncStgVertiefung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>

<?php
$i=0;

$qry="SELECT __stgvertiefung, _studiengang, chbezeichnung, chkurzbez	FROM stgvertiefung;";
if($result_ext = mssql_query($qry,$conn_ext))
{
	while($row=mssql_fetch_object($result_ext))
	{		
		$qry="INSERT INTO sync.stp_stgvertiefung (__stgvertiefung, _studiengang, chbezeichnung, chkurzbez)
				VALUES ('$row->__stgvertiefung','$row->_studiengang',".($row->chbezeichnung==''?'NULL':"'".$row->chbezeichnung."'").",".($row->chkurzbez==''?'NULL':"'".$row->chkurzbez."'").");";
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