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

require('../config.inc.php');

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
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
</head>
<body>
<br><H1>BIS-Verwendungen werden &uuml;berpr&uuml;ft</H1><br><br>
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
//1 - aktive mitarbeiter mit keiner verwendung oder mehr als einer aktuellen verwendung
$qryall='SELECT uid,nachname,vorname, count(bisverwendung_id)  FROM campus.vw_mitarbeiter LEFT OUTER JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid) WHERE aktiv AND (ende>now() OR ende IS NULL) GROUP BY uid,nachname,vorname HAVING count(bisverwendung_id)!=1 ORDER by nachname,vorname;';
if($resultall = pg_query($conn, $qryall))
{
	$num_rows_all=pg_num_rows($resultall);
	echo "<H2>Bei $num_rows_all aktiven Mitarbeitern sind die aktuellen Verwendungen nicht plausibel</H2>";
	while($rowall=pg_fetch_object($resultall))
	{
		$i=0;
		$qry="SELECT * FROM bis.tbl_bisverwendung JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid) JOIN public.tbl_person USING(person_id)
			WHERE tbl_benutzer.aktiv=TRUE AND (ende>now() OR ende IS NULL) AND mitarbeiter_uid='".$rowall->uid."';";
		if($result = pg_query($conn, $qry))
		{
			$num_rows=pg_num_rows($result);
			if($num_rows>1)
			{
				while($row=pg_fetch_object($result))
				{
					if($i==0)
					{
						echo "<br><u>Aktiv(e) Mitarbeiter(in) ".$row->nachname." ".$row->vorname." hat ".$num_rows." aktuelle Verwendungen:</u><br>";
						$i++;
					}
					echo "Verwendung Code ".$row->verwendung_code.", Beschäftigungscode ".$row->ba1code.", ".$row->ba2code.", mit Ausmaß ".$row->beschausmasscode.", ".$row->beginn." - ".$row->ende."<br>";
				}
			}
			elseif($num_rows==0)
				echo "<br><u>Aktiv(e) Mitarbeiter(in): ".$rowall->nachname." ".$rowall->vorname." hat ".$num_rows." aktuelle Verwendungen:</u><br>";
		}
	}
}
//2 - aktive mitarbeiter mit keiner aktuellen verwendung
$qryall='SELECT uid,nachname,vorname, count(bisverwendung_id) FROM campus.vw_mitarbeiter LEFT OUTER JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid) WHERE aktiv AND NOT ende>now() AND NOT ende IS NULL AND uid NOT IN (SELECT uid FROM campus.vw_mitarbeiter LEFT OUTER JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid) WHERE aktiv AND (ende>now() OR ende IS NULL)) GROUP BY uid,nachname,vorname ORDER by nachname,vorname;';
if($resultall = pg_query($conn, $qryall))
{
	$num_rows_all=pg_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all aktiven Mitarbeitern sind keine aktuellen Verwendungen eingetragen</H2>";
	while($rowall=pg_fetch_object($resultall))
	{
		$i=0;
		$qry="SELECT * FROM bis.tbl_bisverwendung JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid) JOIN public.tbl_person USING(person_id)
			WHERE tbl_benutzer.aktiv=TRUE AND mitarbeiter_uid='".$rowall->uid."';";
		if($result = pg_query($conn, $qry))
		{
			$num_rows=pg_num_rows($result);
			while($row=pg_fetch_object($result))
			{
				if($i==0)
				{
					echo "<br><u>Aktiv(e) Mitarbeiter(in) ".$rowall->nachname." ".$rowall->vorname." hat keine aktuelle Verwendungen:</u><br>";
					$i++;
				}
				echo "Verwendung Code ".$row->verwendung_code.", Beschäftigungscode ".$row->ba1code.", ".$row->ba2code.", mit Ausmaß ".$row->beschausmasscode.", ".$row->beginn." - ".$row->ende."<br>";
			}
		}
	}
}

//3 - nicht aktive mitarbeiter mitarbeiter mit aktueller verwendung
$qryall='SELECT uid,nachname,vorname FROM campus.vw_mitarbeiter 
	JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid) 
	WHERE aktiv=false AND (ende>now() OR ende IS NULL) 
	GROUP BY uid,nachname,vorname
	ORDER by nachname,vorname;';



if($resultall = pg_query($conn, $qryall))
{
	$num_rows_all=pg_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all nicht aktiven Mitarbeitern sind die aktuellen Verwendungen nicht plausibel</H2>";
	while($rowall=pg_fetch_object($resultall))
	{
		$i=0;
		$qry="SELECT * FROM bis.tbl_bisverwendung 
			WHERE (ende>now() OR ende IS NULL) AND mitarbeiter_uid='".$rowall->uid."';";
		if($result = pg_query($conn, $qry))
		{
			$num_rows=pg_num_rows($result);
			while($row=pg_fetch_object($result))
			{
				if($i==0)
				{
					echo "<br><u>Nicht aktive(r) Mitarbeiter(in) ".$rowall->nachname." ".$rowall->vorname." hat ".$num_rows." aktuelle Verwendungen:</u><br>";
					$i++;
				}
				echo "Verwendung Code ".$row->verwendung_code.", Beschäftigungscode ".$row->ba1code.", ".$row->ba2code.", mit Ausmaß ".$row->beschausmasscode.", ".$row->beginn." - ".$row->ende."<br>";
			}
		}
	}
}
//4 - wenn verwendung=1,5,6 dann sollte hauptberuf=j sein - check
$qryall="SELECT uid,nachname,vorname FROM campus.vw_mitarbeiter 
	JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid) 
	WHERE verwendung_code IN ('1','5','6') AND hauptberuflich=false 
	GROUP BY uid,nachname,vorname
	ORDER by nachname,vorname,uid;";
if($resultall = pg_query($conn, $qryall))
{
	$num_rows_all=pg_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all Mitarbeitern sind die Eintragungen 'hauptberuflich' nicht plausibel</H2>";
	while($rowall=pg_fetch_object($resultall))
	{
		$i=0;
		$qry="SELECT * FROM bis.tbl_bisverwendung 
			WHERE verwendung_code IN ('1','5','6') AND hauptberuflich=false AND mitarbeiter_uid='".$rowall->uid."';";
		if($result = pg_query($conn, $qry))
		{
			$num_rows=pg_num_rows($result);
			while($row=pg_fetch_object($result))
			{
				if($i==0)
				{
					echo "<br><u>Mitarbeiter(in) ".$rowall->nachname." ".$rowall->vorname.":</u><br>";
					$i++;
				}
				echo "Verwendung Code ".$row->verwendung_code.", hauptberuflich ".$row->hauptberuflich.", Beschäftigungscode ".$row->ba1code.", ".$row->ba2code.", mit Ausmaß ".$row->beschausmasscode.", ".$row->beginn." - ".$row->ende."<br>";
			}
		}
	}
}
//5 - stimmt beschausmasscode mit vertragsstunden überein?

?>