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
$ausgabe1='';
$ausgabe2='';
$ausgabe_all='';
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
$qry="SELECT DISTINCT ON(mitarbeiter_uid) mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter ;";
//WHERE mitarbeiter_uid='balog'


if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$qry_erg="SELECT lehre.tbl_lehreinheitmitarbeiter.mitarbeiter_uid, lehre.tbl_lehrveranstaltung.studiengang_kz, lehre.tbl_lehreinheit.studiensemester_kurzbz, lehre.tbl_lehreinheitmitarbeiter.semesterstunden, lehre.tbl_lehrveranstaltung.semester, public.tbl_semesterwochen.wochen 
			FROM lehre.tbl_lehreinheitmitarbeiter join lehre.tbl_lehreinheit USING (lehreinheit_id) 
			JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) 
			JOIN public.tbl_semesterwochen USING(studiengang_kz, semester)
			WHERE lehre.tbl_lehreinheitmitarbeiter.mitarbeiter_uid='".$row->mitarbeiter_uid."' 
			AND (lehre.tbl_lehreinheit.studiensemester_kurzbz='".$semester[1]."' OR lehre.tbl_lehreinheit.studiensemester_kurzbz='".$semester[2]."');";
			//GROUP BY lehre.tbl_lehreinheitmitarbeiter.mitarbeiter_uid, lehre.tbl_lehrveranstaltung.studiengang_kz, lehre.tbl_lehreinheit.studiensemester_kurzbz";
		if($result_erg = pg_query($conn, $qry_erg))
		{
			if(pg_num_rows($result_erg)>0)
			{
				while($row_erg = pg_fetch_object($result_erg))
				{	
					//$ausgabe1.= "mitarbeiter_uid: '".$row_erg->mitarbeiter_uid. "' studiengang_kz: '".$row_erg->studiengang_kz."' studiensemester_kurzbz: '". $row_erg->studiensemester_kurzbz."' semesterstunden: '".$row_erg->semesterstunden."' semester: '". $row_erg->semester."' semesterwochen: '". $row_erg->wochen."'\n";
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
					//$ausgabe1.='$stundensumme['.$row_erg->mitarbeiter_uid.']['.$row_erg->studiengang_kz.'] '.$stundensumme[$row_erg->mitarbeiter_uid][$row_erg->studiengang_kz][$row_erg->studiensemester_kurzbz]."\n";			
				}
				//schleife über alle stg, in denen stunden vorhanden sind
				$qry_stg="SELECT studiengang_kz FROM public.tbl_studiengang WHERE studiengang_kz>1 AND studiengang_kz<999;";
				if($result_stg = pg_query($conn, $qry_stg))
				{
					while($row_stg = pg_fetch_object($result_stg))
					{
						//echo "stg: ".$row_stg->studiengang_kz."<br>";
						if(isset($stundensumme[$row->mitarbeiter_uid][$row_stg->studiengang_kz]))
						{
							//echo "stg mit inhalt: ".$row_stg->studiengang_kz."<br>";
							$qry_vw="SELECT * FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid='".$row->mitarbeiter_uid."'".
								" AND beginn<='".$beginn[$semester[1]]."' AND (ende::varchar is null OR ende>='".$ende[$semester[2]]."');";
							if($result_vw = pg_query($conn, $qry_vw))
							{
								if(pg_num_rows($result_vw)>=1)
								{
									while($row_vw = pg_fetch_object($result_vw))
									{
										$qry_ins='';
										//$ausgabe2="Stundensumme in qry: ".$stundensumme[$row_erg->mitarbeiter_uid][$row_stg->studiengang_kz]."\n";
										$qry_ins="INSERT INTO bis.tbl_bisfunktion (bisverwendung_id, studiengang_kz, sws, updateamum, updatevon, insertvon, insertamum, ext_id)      
											     VALUES (".myaddslashes($row_vw->bisverwendung_id).", ".myaddslashes($row_stg->studiengang_kz).", ".myaddslashes($stundensumme[$row->mitarbeiter_uid][$row_stg->studiengang_kz]).", "."now(), "."'SYNC', "."now(), "."'SYNC', "."NULL".");";
										
										$ausgabe2.= "Verwendung von Mitarbeiter ".$row->mitarbeiter_uid." (ID/Code):".$row_vw->bisverwendung_id." ".$row_vw->verwendung_code."/".$row_vw->ba1code." -- ".$row_vw->beginn."/".$row_vw->ende."   (".$beginn[$semester[1]]."/".$ende[$semester[2]].")\n-----".$qry_ins."\n";	
									}
								}
								else 
								{
									while($row_vw = pg_fetch_object($result_vw))
									{
										$error_log.=$ausgabe2."#####Verwendung(ID/Code):".$row_vw->bisverwendung_id." ".$row_vw->verwendung_code."/".$row_vw->ba1code." -- ".$row_vw->beginn."/".$row_vw->ende."   (".$beginn[$semester[1]]."/".$ende[$semester[2]].")\n";	
									}
								}
							}
						}
					}
				}
			}
		}
		
		$ausgabe_all.=$ausgabe1." ".$ausgabe2;
		$ausgabe1='';
		$ausgabe2='';
	}
}

//echo nl2br("Fehler: ".$fehler."\n".$error_log);
//echo nl2br("\n***********************************\nLog: \n".$ausgabe);
echo nl2br("Log:\n".$ausgabe_all);
echo nl2br("Fehler:\n".$error_log);

//mail($adress, 'Fehler BIS-Funktionen von '.$_SERVER['HTTP_HOST'], "Fehler: ".$fehler."\n".$error_log,"From: vilesci@technikum-wien.at");
//mail($adress, 'BIS-Funktionen von '.$_SERVER['HTTP_HOST'], $ausgabe,"From: vilesci@technikum-wien.at");
?>