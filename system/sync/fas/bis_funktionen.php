<?php
/* Copyright (C) 2007
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */


require_once('../../../vilesci/config.inc.php');
require_once('../sync_config.inc.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$error_log='';
$fehler=0;
$wochendiv=0;
$semester=array(1=>'WS2006', 2=>'SS2007');
$stundensumme=array(array());


function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

?>

<html>
<head>
<title>BIS-Meldung - Funktionen</title>
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

//echo "mitarbeiter_uid / studiengang_kz / studiensemester_kurzbz / semesterstunden / semester / wochen<br>";
$qry="SELECT DISTINCT ON(mitarbeiter_uid) mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter;";
//while-scheife über mitarbeiter
//while-schleife über semester (tbl_lehreinheit)
//aufteilung der sws auf studiengang (tbl_lehrveranstaltung) - tbl_lehreinheitmitarbeiter->semesterstunden/(tbl_semesterwochen->wochen oder 15)
//zuweisung zu eine verwendung des mitarbeiters in tbl_verwendung
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		echo "<br>";
		$qry_erg="SELECT lehre.tbl_lehreinheitmitarbeiter.mitarbeiter_uid, lehre.tbl_lehrveranstaltung.studiengang_kz, lehre.tbl_lehreinheit.studiensemester_kurzbz, lehre.tbl_lehreinheitmitarbeiter.semesterstunden, lehre.tbl_lehrveranstaltung.semester, public.tbl_semesterwochen.wochen 
			FROM lehre.tbl_lehreinheitmitarbeiter join lehre.tbl_lehreinheit USING (lehreinheit_id) 
			JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) 
			JOIN public.tbl_semesterwochen USING(studiengang_kz, semester)
			WHERE lehre.tbl_lehreinheitmitarbeiter.mitarbeiter_uid='".$row->mitarbeiter_uid."' 
				AND (lehre.tbl_lehreinheit.studiensemester_kurzbz='".$semester[1]."' OR lehre.tbl_lehreinheit.studiensemester_kurzbz='".$semester[2]."');";
			//GROUP BY lehre.tbl_lehreinheitmitarbeiter.mitarbeiter_uid, lehre.tbl_lehrveranstaltung.studiengang_kz, lehre.tbl_lehreinheit.studiensemester_kurzbz";
		if($result_erg = pg_query($conn, $qry_erg))
		{
			while($row_erg = pg_fetch_object($result_erg))
			{
				echo "mitarbeiter_uid: '".$row_erg->mitarbeiter_uid. "' studiengang_kz: '".$row_erg->studiengang_kz."' studiensemester_kurzbz: '". $row_erg->studiensemester_kurzbz."' semesterstunden: '".$row_erg->semesterstunden."' semester: '". $row_erg->semester."' semesterwochen: '". $row_erg->wochen."'<br>";
				if($row_erg->wochen==null || $row_erg->wochen<2)
				{
					$wochendiv=15;
				}
				else 
				{
					$wochendiv=$row_erg->wochen;
				}
				if(isset($stundensumme[$row_erg->mitarbeiter_uid][$row_erg->studiengang_kz]))
				{
					$stundensumme[$row_erg->mitarbeiter_uid][$row_erg->studiengang_kz]+=$row_erg->semesterstunden/$wochendiv;
				}
				else 
				{
					$stundensumme[$row_erg->mitarbeiter_uid][$row_erg->studiengang_kz]=$row_erg->semesterstunden/$wochendiv;
				}
				echo '----->$stundensumme['.$row_erg->mitarbeiter_uid.']['.$row_erg->studiengang_kz.'] '.$stundensumme[$row_erg->mitarbeiter_uid][$row_erg->studiengang_kz]."<br>";
				//
				// stunden im selben stg summieren!!!
				//
				$qry_vw="SELECT * FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid='".$row->mitarbeiter_uid."';";
				if($result_vw = pg_query($conn, $qry_vw))
				{
					while($row_vw = pg_fetch_object($result_vw))
					{
						if($row_vw->beginn<=$beginn[$row_erg->studiensemester_kurzbz] AND ($row_vw->ende>=$ende[$row_erg->studiensemester_kurzbz] OR $row_vw->ende==''))
						{
							echo "--------Verwendung(ID/Code):".$row_vw->bisverwendung_id." ".$row_vw->verwendung_code."/".$row_vw->ba1code.$row_vw->ba1code." -- ".$row_vw->beginn."/".$row_vw->ende."   (".$beginn[$row_erg->studiensemester_kurzbz]."/".$ende[$row_erg->studiensemester_kurzbz].")<br>";
							//beginn- und endedatum des semesters mit beginn- und endedatum der verwendungen vergleichen
						}
					}
				}
			}
		}
	}
}

//echo nl2br("Fehler: ".$fehler."\n".$error_log);
//echo nl2br("\n***********************************\nLog: \n".$ausgabe);

//mail($adress, 'Fehler BIS-Funktionen von '.$_SERVER['HTTP_HOST'], "Fehler: ".$fehler."\n".$error_log,"From: vilesci@technikum-wien.at");
//mail($adress, 'BIS-Funktionen von '.$_SERVER['HTTP_HOST'], $ausgabe,"From: vilesci@technikum-wien.at");
?>