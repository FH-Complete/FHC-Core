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
__EMail (int)
chBemerkung (char)
chAdresse (char)
_Person (int)
boFHWeb (bit)
boAutoEMail (bit) 
*/

// Sync-Tabelle fuer email checken
if (!@pg_query($conn,'SELECT * FROM sync.stp_email LIMIT 1;'))
{
	$sql='CREATE TABLE sync.stp_email (
			__email integer,
			_person integer,
			chbemerkung varchar(64),
			chadresse varchar(128),
			boFHWeb varchar(1),
			boAutoEmail varchar(1),
		constraint "pk_tbl_sync_stp_email" primary key ("__email"));
		Grant select on sync.stp_email to group "admin";
		Grant update on sync.stp_email to group "admin";
		Grant delete on sync.stp_email to group "admin";
		Grant insert on sync.stp_email to group "admin";';
	if (!@pg_query($conn,$sql))
		echo '<strong>sync.stp_email: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'sync.stp_email wurde angelegt!<BR>';
}

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>
<html>
<head>
<title>Synchro - FHDB -> FH-Complete - PreSyncEMail/title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>

<?php
if (!@pg_query($conn,'DELETE FROM sync.stp_email;'))
	echo '<strong>sync.stp_email: '.pg_last_error($conn).' </strong><BR>';
else
	echo 'sync.stp_email wurde geleert!<BR>';

echo 'Daten werden geholt!<BR>';

$i=0;

$qry="SELECT __Email, _Person, chAdresse, chBemerkung, boFHWeb, boAutoEmail 	FROM email WHERE chAdresse!='' AND chAdresse IS NOT NULL;";
if($result_ext = mssql_query($qry,$conn_ext))
{
	while($row=mssql_fetch_object($result_ext))
	{
		$qry="INSERT INTO sync.stp_email ( __email, _person, chadresse, chbemerkung, bofhweb, boautoemail)
			VALUES ('$row->__Email','$row->_Person', ".
			($row->chAdresse==''?'NULL':"'".addslashes($row->chAdresse)."'").", ".
			($row->chBemerkung==''?'NULL':"'".$row->chBemerkung."'").", ".
			"'$row->boFHWeb','$row->boAutoEmail');";
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