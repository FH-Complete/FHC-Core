<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Gruppendatensaetze von FAS DB in PORTAL DB
//*
//*

include('../../../vilesci/config.inc.php');


$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

//$adress='ruhan@technikum-wien.at';
$adress='fas_sync@technikum-wien.at';

$error_log='';
$error_log_fas='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_update=0;
$anzahl_fehler=0;
$ausgabe='';
$ausgabe_all='';
$update=false;

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
<title>Synchro - FAS -> Vilesci - Gruppe</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
//nation
$qry="SELECT * FROM gruppe WHERE typ='4' OR typ='5' Or typ='6' ORDER BY gruppe_pk;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Gruppe Sync\n-------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$gruppe_kurzbz			="";
		$studiengang_kz			="";
		$semester				="";
		$bezeichnung			="";
		$beschreibung			="";
		$sichtbar				=TRUE;
		$lehre					=TRUE;
		$aktiv					=TRUE;
		$sort					=NULL;
		$mailgrp				=FALSE;
		$generiert				=FALSE;
		$updateamum			="";
		$updatevon				="SYNC";
		$insertamum				="";
		$insertvon				="SYNC";
		$ext_id				="";
		
		$verband				="";
		$gruppe				="";
		$untergruppe				="";
		if($row->obergruppe_fk!='0')
		{
			$qry1="SELECT * FROM gruppe WHERE gruppe_pk='".$row->obergruppe_fk."';";
			if($result1 = pg_query($conn_fas, $qry1))
			{
				if($row1=pg_fetch_object($result1))
				{ 
					if($row1->obergruppe_fk!='0')
					{
						$qry2="SELECT * FROM gruppe WHERE gruppe_pk='".$row1->obergruppe_fk."';";
						if($result2 = pg_query($conn_fas, $qry2))
						{
							if($row2=pg_fetch_object($result2))
							{ 
								if($row2->obergruppe_fk!='0')
								{
									$qry3="SELECT * FROM gruppe WHERE gruppe_pk='".$row2->obergruppe_fk."';";
									if($result3 = pg_query($conn_fas, $qry3))
									{
										if($row3=pg_fetch_object($result3))
										{
											$semester=$row3->name;
											$verband=$row2->name;
											$gruppe=$row1->name;
											$untergruppe=$row->name;
										}
									}
									else 
									{
										$error=true;
										$error_log="Zugriff auf Tabelle gruppe ist fehlgeschlagen.\n";
									}	
								}
								else 
								{
									$semester=$row2->name;
									$verband=$row1->name;
									$gruppe=$row->name;
								}
							}
						}
						else 
						{
							$error=true;
							$error_log="Zugriff auf Tabelle Gruppe fehlgeschlagen.\n";
						}
					}
					else 
					{
						$semester=$row1->name;
						$verband=$row->name;
						$gruppe=' ';		
					}
				}
			}
			else 
			{
				$error=true;
				$error_log="Zugriff auf Tabelle gruppe fehlgeschlagen.\n";
			}
		}
		else 
		{
			$semester=$row->name;
			$verband=' ';
			$gruppe=' ';
		}
		$ext_id=$row->gruppe_pk;
		
//echo nl2br($semester."/".$verband."/".$gruppe."/".$untergruppe."--".$ext_id."\n");
//continue;
		$qry2="SELECT * FROM studiengang WHERE studiengang_pk='".$row1->studiengang_fk."';";
		if($result2 = pg_query($conn_fas, $qry2))
		{
			if($row2=pg_fetch_object($result2))
			{
				$studiengang_kz=$row2->kennzahl;
			}
			else 
			{
				$error_log.="Studiengang mit studiengang_pk='".$row1->studiengang_fk."' nicht gefunden.\n";
				$error=true;
			}
		}
		else 
		{
			$error=true;
			$error_log="Fehler beim Zugriff auf Tabelle studiengang.\n";
		}
		$qry2="SELECT * FROM public.tbl_studiengang WHERE studiengang_kz='".$studiengang_kz."';";
		if($result2 = pg_query($conn, $qry2))
		{
			if($row2=pg_fetch_object($result2))
			{
				$gruppe_kurzbz=strtoupper($row2->typ).strtoupper($row2->kurzbz)."-".strtoupper(trim($semester)).strtoupper(trim($verband)).strtoupper(trim($gruppe)).strtoupper(trim($untergruppe));
				if(strlen($gruppe_kurzbz)>16)
				{
					$verband=substr($verband,0,strlen($verband)-(strlen($gruppe_kurzbz)-16));
					$gruppe_kurzbz=strtoupper($row2->typ).strtoupper($row2->kurzbz)."-".strtoupper(trim($semester)).strtoupper(trim($verband)).strtoupper(trim($gruppe)).strtoupper(trim($untergruppe));
				}
			}
			else 
			{
				$error_log.="Studiengang mit studiengang_kz='".$studiengang_kz."' nicht gefunden.\n";
				$error=true;
			}
		}
		else 
		{
			$error=true;
			$error_log="Fehler beim Zugriff auf Tabelle tbl_studiengang.\n";
		}
		$bezeichnung=$gruppe_kurzbz;
		$beschreibung=$bezeichnung;
		if(!$error)
		{
			$qry2="SELECT * FROM public.tbl_gruppe WHERE gruppe_kurzbz='".$gruppe_kurzbz."' AND studiengang_kz='".$studiengang_kz."';";
			if($result2 = pg_query($conn, $qry2))
			{
				if($row2=pg_fetch_object($result2))
				{		
					//Eintrag bereits vorhanden - Eintragung in Sync-Tabelle
					$anzahl_update++;
					$qrysync="SELECT * FROM sync.tbl_syncgruppe WHERE fas_gruppe='".$ext_id."' AND vilesci_gruppe='".$gruppe_kurzbz."';";
					if($resultsync = pg_query($conn, $qrysync))
					{
						$ausgabe="Gruppe in Vilesci bereits vorhanden.\n";
						$qryupd="UPDATE public.tbl_gruppe SET ext_id='".$ext_id."' WHERE gruppe_kurzbz='".$gruppe_kurzbz."' AND studiengang_kz='".$studiengang_kz."';";
						if($resultupd = pg_query($conn, $qryupd))
						{
							if($rowsync=pg_fetch_object($resultsync))
							{
								//Sync-Eintrag bereits vorhanden
								$qryinss="INSERT INTO sync.tbl_syncgruppe (fas_gruppe, vilesci_gruppe) VALUES ('".$ext_id."','".$rowsync->vilesci_gruppe."');";
								$ausgabe.="---Sync-Eintrag 1: FAS-'".$ext_id."', Vilesci-'".$rowsync->vilesci_gruppe."'.\n";
							}
							else 
							{
								//Sync-Eintrag nicht vorhanden
								$qryinss="INSERT INTO sync.tbl_syncgruppe (fas_gruppe, vilesci_gruppe) VALUES ('".$ext_id."','".$gruppe_kurzbz."');";
								$ausgabe.="---Sync-Eintrag 2: FAS-'".$ext_id."', Vilesci-'".$gruppe_kurzbz."'.\n";
							}
							if(!(pg_query($conn, $qryinss)))
							{
								$error=true;
								$error_log="Eintrag in Tabelle tbl_syncgruppe fehlgeschlagen: ".$qryinss."\n";
							}
						}
						else 
						{
							$error=true;
							$error_log="Update in Tabelle tbl_gruppe fehlgeschlagen: ".$qryupd."\n";
						}
					}
				}
				else 
				{
					//Eintrag noch nicht vorhanden - Insert und Eintragung in Sync-Tabelle
					$qryinsg="INSERT INTO public.tbl_gruppe (gruppe_kurzbz, studiengang_kz, semester, bezeichnung, beschreibung, sichtbar, lehre, aktiv, sort, mailgrp, generiert, updateamum, updatevon, insertamum, insertvon, ext_id) VALUES(".
						myaddslashes($gruppe_kurzbz).", ".
						myaddslashes($studiengang_kz).", ".
						myaddslashes($semester).", ".
						myaddslashes($bezeichnung).", ".
						myaddslashes($beschreibung).", ".
						($sichtbar?'true':'false').", ".
						($lehre?'true':'false').", ".
						($aktiv?'true':'false').", ".
						myaddslashes($sort).", ".
						($mailgrp?'true':'false').", ".
						($generiert?'true':'false').", 
						now(), 
						'SYNC', 
						now(),
						'SYNC', ".
						$ext_id.");";
						
					if($resultinsg = pg_query($conn, $qryinsg))
					{
						$anzahl_eingefuegt++;
						$ausgabe="Gruppe mit gruppe_pk='".$ext_id."' eingetragen als: Gruppe='".$gruppe_kurzbz."', Stg.='".$studiengang_kz."'.\n";
						$qryinss="INSERT INTO sync.tbl_syncgruppe (fas_gruppe, vilesci_gruppe) VALUES ('".$ext_id."','".$gruppe_kurzbz."');";
						
						if(!(pg_query($conn, $qryinss)))
						{
							$error=true;
							$error_log="Eintrag in Tabelle tbl_syncgruppe fehlgeschlagen: ".$qryinss.";\n";
						}
						else 
						{
							$ausgabe.="---Sync-Eintrag: FAS-'".$ext_id."', Vilesci-'".$gruppe_kurzbz."'.\n";
						}
					}
					else 
					{
						$error=true;
						$error_log="Eintrag in Tabelle tbl_gruppe fehlgeschlagen: ".$qryinsg."\n";
					}
				}
			}
			else 
			{
				$error=true;
				$error_log="Fehler bei Zugriff auf Tabelle tbl_gruppe";
			}
		}
		else 
		{
			$anzahl_fehler++;
		}
		$ausgabe_all.=$ausgabe;		
	}
}	
			

//echo nl2br($text);
echo nl2br("\nGruppe\nGruppe: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Nicht eingefügt: $anzahl_update / Fehler: $anzahl_fehler\n\n");
echo nl2br($error_log_fas."\n\n");
echo nl2br ($ausgabe_all);
$ausgabe="\nGruppe\nGruppe: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Nicht eingefügt: $anzahl_update / Fehler: $anzahl_fehler."
."\n\n".$ausgabe_all;
 

if(strlen(trim($error_log_fas))>0)
{
	mail($adress, 'SYNC-Fehler Gruppe von '.$_SERVER['HTTP_HOST'], $error_log_fas,"From: vilesci@technikum-wien.at");
}
mail($adress, 'SYNC Gruppe von '.$_SERVER['HTTP_HOST'], $ausgabe,"From: vilesci@technikum-wien.at");

?>
</body>
</html>