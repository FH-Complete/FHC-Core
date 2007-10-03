<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Überprüfung der Verwendungsdatensätze im FASonline
//*
//*

require_once('../../../vilesci/config.inc.php');
require_once('../sync_config.inc.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

//$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$fehler=0;

$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_update=0;
$anzahl_fehler=0;
$ausgabe='';
$error_log_fas='';
$update=false;

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

?>

<html>
<head>
<title>BIS-Meldung - Überprüfung von Verwendungen</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

$qry="SELECT * FROM public.tbl_studiensemester";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$beginn[$row->studiensemester_kurzbz]=$row->start;
		$ende[$row->studiensemester_kurzbz]=$row->ende;
	}
}
$qryall="SELECT DISTINCT ON(mitarbeiter_uid) * FROM bis.tbl_bisverwendung ORDER by mitarbeiter_uid;";
if($resultall = pg_query($conn, $qryall))
{
	while($rowall=pg_fetch_object($resultall))
	{
		$i=0;
		$qry="SELECT * FROM bis.tbl_bisverwendung JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid) JOIN public.tbl_person USING(person_id)
			WHERE tbl_benutzer.aktiv=TRUE AND (ende>now() OR ende IS NULL) AND mitarbeiter_uid='".$rowall->mitarbeiter_uid."';";
		if($result = pg_query($conn, $qry))
		{
			if(pg_num_rows($result)>1)
			{
				while($row=pg_fetch_object($result))
				{
					if($i==0)
					{
						echo "<br><u>Mitarbeiter(in): ".$row->nachname." ".$row->vorname." hat ".pg_num_rows($result)." aktuelle Verwendungen:</u><br>";
						$i++;
					}
					echo "Verwendung Code ".$row->verwendung_code.", Beschäftigungscode ".$row->ba1code.", ".$row->ba2code.", mit Ausmaß ".$row->beschausmasscode.", ".$row->beginn." - ".$row->ende."<br>";
				}
			}
		}
	}
}
?>