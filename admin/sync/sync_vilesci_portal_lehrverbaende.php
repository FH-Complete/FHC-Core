<?php
/**
 * Synchronisiert die Lehrverbaende von Vilesci DB in PORTAL DB
 *
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/fas/person.class.php');
require_once('../../include/fas/benutzer.class.php');
require_once('../../include/fas/mitarbeiter.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die("Connection zur Vilesci Datenbank fehlgeschlagen");

$error_log='';
$text = '';
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

// ***********************************
// * VILESCI->PORTAL - Synchronisation
// ***********************************

$qry = "SELECT studiengang_kz, semester, verband, gruppe FROM tbl_student GROUP BY studiengang_kz, semester, verband, gruppe";

if($result = pg_query($conn_vilesci, $qry))
{
	$text.="Sync der Lehrverbaende\n\n";
	while($row=pg_fetch_object($result))
	{
		$qry = "INSERT INTO tbl_lehrverband(studiengang_kz, semester, verband, gruppe) VALUES(
		        $row->studiengang_kz, $row->semester, '$row->verband', '$row->gruppe');";
		if(!pg_query($conn, $qry))
		{
			$error_log.= "Fehler beim einfuegen des Datensatzes: $qry";
			$anzahl_fehler++;
		}
		else 
			$anzahl_eingefuegt++;
	}
}
$text .= "Anzahl eingefuegter Datensaetze: $anzahl_eingefuegt\n";
$text .= "Anzahl der Fehler: $anzahl_fehler\n";
?>
<html>
<head>
<title>Synchro - Vilesci -> Portal - Personen</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

echo nl2br($text);
echo nl2br($error_log);

?>
</body>
</html>