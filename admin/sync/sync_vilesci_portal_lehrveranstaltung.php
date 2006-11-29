<?php
/**
 * Synchronisiert Lehrveranstaltungsdatensaetze von Vilesci DB in PORTAL DB
 *
 */
include('../../vilesci/config.inc.php');
include('../../include/fas/lehrveranstaltung.class.php');
include('../../include/fas/fachbereich.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die("Connection zur Vilesci Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
function validate($row)
{
}

/*************************
 * VILESCI-PORTAL - Synchronisation
 */

//Mitarbeiter
$qry = "SELECT * FROM tbl_lehrfach limit 5";

if($result = pg_query($conn_vilesci, $qry))
{
	$text.="Lehrveranstaltung Sync\n----------------------\n";
	while($row = pg_fetch_object($result))
	{
		$lehrveranstaltung = new lehrveranstaltung($conn);
		$lehrveranstaltung->studiengang_kz=$row->studiengang_kz;
		$lehrveranstaltung->bezeichnung=$row->bezeichnung;
		$lehrveranstaltung->kurzbz=$row->kurzbz;
		$lehrveranstaltung->semester=$row->semester;
		$lehrveranstaltung->ects=$row->ects;
		$lehrveranstaltung->semesterstunden=0;
		$lehrveranstaltung->gemeinsam='false';
		$lehrveranstaltung->anmerkung='';
		$lehrveranstaltung->lehre=($row->aktiv=='t'?true:false);
		$lehrveranstaltung->lehreverzeichnis=$row->lehrevz;
		$lehrveranstaltung->aktiv=($row->aktiv=='t'?true:false);
		$lehrveranstaltung->planfaktor='1.0';
		$lehrveranstaltung->planlektoren='1';
		$lehrveranstaltung->planpersonalkosten='80';
		//$lehrveranstaltung->insertamum='';
		$lehrveranstaltung->insertvon='SYNC';
		//$lehrveranstaltung->updateamum='';
		//$lehrveranstaltung->updatevon=$row->updatevon;
		$lehrveranstaltung->ext_id='';
		$lehrveranstaltung->new=true;
		
		if(!$lehrveranstaltung->save())
				$error_log.=$lehrveranstaltung->errormsg."\n";
	
	}
	$text.="abgeschlossen";
}
else
	$error_log .= 'Lehrveranstaltungsdatensaetze konnten nicht geladen werden';
	
?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - Lehrveranstaltungen</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

echo nl2br($text);
echo nl2br($error_log);

?>
</body>
</html>