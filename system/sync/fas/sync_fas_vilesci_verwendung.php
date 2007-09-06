<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert bis-verwendungsdatensaetze von FAS DB in PORTAL DB
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
$update=false;
$verwendung_aktuell='';
$beschart1_aktuell='';
$beschart2_aktuell='';
$semester_aktuell='';
$ext_id_aktuell='';
$beginn=array();
$ende=array();

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>

<html>
<head>
<title>Synchro - FAS -> Vilesci - Verwendung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
$qry="SELECT * FROM public.tbl_studiensemester";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$beginn[$row->ext_id]=$row->start;
		$ende[$row->ext_id]=$row->ende;
	}
}


$qry_ma="SELECT DISTINCT ON(mitarbeiter_fk) * FROM funktion";
if($result_ma = pg_query($conn_fas, $qry_ma))
{
	$anzahl_quelle=pg_num_rows($result_ma);
	while($row_ma = pg_fetch_object($result_ma))
	{
		//mitarbeiter_uid ermitteln
		$qry_uid="SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE ext_id='".$row_ma->mitarbeiter_fk."';";
		if($result_uid = pg_query($conn, $qry_uid))
		{
			if($row_uid = pg_fetch_object($result_uid))
			{
				$mitarbeiter_uid=$row_uid->mitarbeiter_uid;	
			}
			else
			{
				$error_log.="Mitarbeiter mit mitarbeiter_fk '".$row_ma->mitarbeiter_fk."' in tbl_mitarbeiter nicht gefunden!\n";
				$fehler++;
				continue;
			}
		}
		//habilitation aus tabelle mitarbeiter holen
		$qry_hab="SELECT habilitation FROM mitarbeiter WHERE mitarbeiter_pk='".$row_ma->mitarbeiter_fk."';";
		if($result_hab = pg_query($conn_fas, $qry_hab))
		{
			if($row_hab = pg_fetch_object($result_hab))
			{
				$habilitation=$row_hab->habilitation=='J'?true:false;	
			}
			else
			{
				$error_log.="Habilitation von Mitarbeiter '".$row_ma->mitarbeiter_fk."' in  Tabelle mitarbeiter nicht gefunden!";
				$fehler++;
				continue;
			}
		}
		$qry="SELECT * FROM funktion WHERE mitarbeiter_fk='".$row_ma->mitarbeiter_fk."' AND ausmass>0 AND ausmass<6 ORDER BY verwendung, beschart1, beschart2, ausmass, studiensemester_fk;";
		if($result = pg_query($conn_fas, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				if(!($row->verwendung>=1 AND $row->verwendung<=9))
				{
					$error_log.="Verwendungscode ungülig (".$row->verwendung.") bei funktion_pk=".$row->funktion_pk.".\n";
					$fehler++;
					continue;
				}
				if(!($row->hauptberuf>=0 AND $row->hauptberuf<=12))
				{
					$hauptberuf=12;
				}
				else 
				{
					$hauptberuf=$row->hauptberuf;
				}
				if(!($row->beschart1>=1 AND $row->beschart1<=6))
				{
					$error_log.="Beschäftigungsart 1 ungülig (".$row->beschart1.") bei funktion_pk=".$row->funktion_pk.".\n";
					$fehler++;
					continue;
				}
				if(!($row->beschart2>=1 AND $row->beschart2<=2))
				{
					$error_log.="Beschäftigungsart 2 ungülig (".$row->beschart2.") bei funktion_pk=".$row->funktion_pk.".\n";
					$fehler++;
					continue;
				}
				if($verwendung_aktuell=='' || $verwendung_aktuell!=$row->verwendung || $beschart1_aktuell!=$row->beschart1 || $beschart2_aktuell!=$row->beschart2 || $ausmass_aktuell!=$row->ausmass)
				{
					//neue verwendung
					$qry2="INSERT INTO bis.tbl_bisverwendung (ba1code,ba2code,beschausmasscode, verwendung_code, 
						mitarbeiter_uid, hauptberufcode,hauptberuflich,habilitation,beginn,ende,insertamum,insertvon,
						updateamum, updatevon,ext_id) VALUES (".
						myaddslashes($row->beschart1).", ".
						myaddslashes($row->beschart2).", ".
						myaddslashes($row->ausmass).", ".
						myaddslashes($row->verwendung).", ".
						myaddslashes($mitarbeiter_uid).", ".
						myaddslashes($hauptberuf).", ".
						myaddslashes($row->hauptberuflich?'true':'false').", ".
						myaddslashes($habilitation?'true':'false').", ".
						myaddslashes($beginn[$row->studiensemester_fk]).", ".
						myaddslashes($ende[$row->studiensemester_fk]).", ".
						"now(), ".
						"'SYNC', ".
						"now(), ".
						"'SYNC',".
						myaddslashes($row->funktion_pk).");";
					$verwendung_aktuell=$row->verwendung;
					$beschart1_aktuell=$row->beschart1;
					$beschart2_aktuell=$row->beschart2;
					$ausmass_aktuell=$row->ausmass;
					$ext_id_aktuell=$row->funktion_pk;
					$semester_aktuell=$row->studiensemester_fk;
					$ausgabe.="Mitarbeiter :'".$mitarbeiter_uid."' Verwendung: ".$row->verwendung." (".$row->beschart1."/".$row->beschart2.") von '".$beginn[$row->studiensemester_fk]."' bis '".$ende[$row->studiensemester_fk]."' Ausmass: '".$row->ausmass."' angelegt!\n";
				}
				else 
				{
					if($row->studiensemester_fk==$semester_aktuell+1)
					{
						//update mit ende==semesterendedatum
						if(date("d.m.Y")>$ende[$row->studiensemester_fk])
						{
							$qry2="UPDATE bis.tbl_bisverwendung SET ".
								"ende='".$ende[$row->studiensemester_fk]."' ".
								"WHERE ext_id='".$ext_id_aktuell."';";
							$verwendung_aktuell=$row->verwendung;
							$beschart1_aktuell=$row->beschart1;
							$beschart2_aktuell=$row->beschart2;
							$semester_aktuell=$row->studiensemester_fk;
							$ausgabe.="Mitarbeiter :'".$mitarbeiter_uid."' Verwendung: ".$row->verwendung." (".$row->beschart1."/".$row->beschart2.") bis '".$ende[$row->studiensemester_fk]."' erweitert!\n";
						}
						else 
						{
							$qry2="UPDATE bis.tbl_bisverwendung SET ".
								"ende=NULL ".
								"WHERE ext_id='".$ext_id_aktuell."';";
							$verwendung_aktuell=$row->verwendung;
							$beschart1_aktuell=$row->beschart1;
							$beschart2_aktuell=$row->beschart2;
							$semester_aktuell=$row->studiensemester_fk;
							$ausgabe.="Mitarbeiter: '".$mitarbeiter_uid."' Verwendung: ".$row->verwendung." (".$row->beschart1."/".$row->beschart2.") bis '".$ende[$row->studiensemester_fk]."' erweitert!\n";	
						}
					}
					else if ($row->studiensemester_fk>$semester_aktuell+1)
					{
						//insert neue verwendung
						$qry2="INSERT INTO bis.tbl_bisverwendung (ba1code,ba2code,beschausmasscode,verwendung_code,
							mitarbeiter_uid,hauptberufcode,hauptberuflich,habilitation,beginn,ende,insertamum,insertvon,
							updateamum,	updatevon,ext_id) VALUES (".
						myaddslashes($row->beschart1).", ".
						myaddslashes($row->beschart2).", ".
						myaddslashes($row->ausmass).", ".
						myaddslashes($row->verwendung).", ".
						myaddslashes($mitarbeiter_uid).", ".
						myaddslashes($hauptberuf).", ".
						myaddslashes($row->hauptberuflich?'true':'false').", ".
						myaddslashes($habilitation?'true':'false').", ".
						myaddslashes($beginn[$row->studiensemester_fk]).", ".
						myaddslashes($ende[$row->studiensemester_fk]).", ".
						"now(), ".
						"'SYNC', ".
						"now(), ".
						"'SYNC',".
						myaddslashes($row->funktion_pk).");";
						$verwendung_aktuell=$row->verwendung;
						$beschart1_aktuell=$row->beschart1;
						$beschart2_aktuell=$row->beschart2;	
						$ext_id_aktuell=$row->funktion_pk;
						$semester_aktuell=$row->studiensemester_fk;
						$ausgabe.="Mitarbeiter :'".$mitarbeiter_uid."' Verwendung: ".$row->verwendung." (".$row->beschart1."/".$row->beschart2.") von '".$beginn[$row->studiensemester_fk]."' bis '".$ende[$row->studiensemester_fk]."' Ausmass: '".$row->ausmass."' angelegt.\n";
					}
					else 
					{
						//zweite gleiche verwendung im selben semester wird ignoriert
						$qry2="SELECT 1;";
					}
				}
				
				//sql-befehl $qry2 abschicken
				pg_query($conn, 'BEGIN');
				if(!pg_query($conn, $qry2))
				{
					$error=true;
					$error_log.="Fehler beim Schreiben in tbl_bisverwendung:\n".$qry2."'\n";	
					pg_query($conn, 'ROLLBACK');
				}
				else 
				{
					pg_query($conn, 'COMMIT');
				}
			}
		}
		else 
		{
			$error=true;
			$error_log.="Fehler beim Schreiben in tbl_bisverwendung:\n".$qry."'\n";	
		}		
	}
}

echo nl2br("Fehler: ".$fehler."\n".$error_log);
echo nl2br("\n***********************************\nLog: \n".$ausgabe);

mail($adress, 'SYNC-Fehler BIS-Verwendung von '.$_SERVER['HTTP_HOST'], "Fehler: ".$fehler."\n".$error_log,"From: vilesci@technikum-wien.at");
mail($adress, 'SYNC BIS-Verwendung von '.$_SERVER['HTTP_HOST'], $ausgabe,"From: vilesci@technikum-wien.at");
?>