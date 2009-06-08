<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Entwicklungsteam-Datensaetze von FAS DB in VILESCI DB
//*
//*

require_once('../../../vilesci/config.inc.php');
require_once('../sync_config.inc.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

//$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_update=0;
$anzahl_fehler=0;
$update=false;
$ausgabe='';
$ausgabe_et='';

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}
?>

<html>
<head>
<title>Synchro - FAS -> Vilesci - E-Team</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<?php
/*************************
 * FAS - VILESCI - Synchronisation
 */

$qry="SELECT * FROM bis.tbl_entwicklungsteam";
$anzahl_quelle=pg_num_rows(pg_query($conn,$qry));

$qry = "SELECT *, creationdate::timestamp as insertamum 
	FROM funktion 
	WHERE entwicklungsteam='J' AND studiengang_fk>1 AND studiengang_fk<37;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("E-Team-Sync\n-------------\n");
	echo nl2br("E-Team-Synchro Beginn: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");
	//$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		//echo "- ";
		//ob_flush();
		//flush();	
			
		$error=false;
		$update=false;
		$ausgabe_et='';
		$mitarbeiter_uid		 	= '';
		$studiengang_kz 			= '';
		$besqualcode			= $row->besonderequalifikation;
		$beginn 				= '';
		$ende		 			= '';
		$updateamum 			= $row->insertamum;
		$updatevon 				= 'SYNC';
		$insertamum 				= $row->insertamum;
		//$insertvon 				= '';
		$ext_id				= $row->funktion_pk;
		
		//insertvon
		$qrycu="SELECT name FROM benutzer WHERE benutzer_pk='".$row->creationuser."';";
		if($resultcu = pg_query($conn_fas, $qrycu))
		{
			if($rowcu=pg_fetch_object($resultcu))
			{
				$insertvon=$rowcu->name;
			}
		}
		
		if($besqualcode<0 || $besqualcode=='')
		{
			$besqualcode=0;
		}
			
		//mitarbeiter_uid ermitteln
		$qry="SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE ext_id='".$row->mitarbeiter_fk."';";
		if($resultma = pg_query($conn, $qry))
		{
			if(pg_num_rows($resultma)>0)
			{
				if($rowma = pg_fetch_object($resultma))
				{
					$mitarbeiter_uid=$rowma->mitarbeiter_uid;
				}
			}
			else 
			{
				echo nl2br("Mitarbeiter ".$row->mitarbeiter_fk." nicht gefunden.");
				
			}
		}
		else 
		{
			$error_log.="Kein Zugriff auf tbl_mitarbeiter => Mitarbeiter ".$row->mitarbeiter_fk." nicht gefunden.";
			$error=true;
		}
		//studiengang_kz
		$qry="SELECT studiengang_kz FROM public.tbl_studiengang WHERE ext_id='".$row->studiengang_fk."';";
		if($resultstg = pg_query($conn, $qry))
		{
			if(pg_num_rows($resultstg)>0)
			{
				if($rowstg = pg_fetch_object($resultstg))
				{
					$studiengang_kz=$rowstg->studiengang_kz;
				}
			}
			else 
			{
				echo nl2br("Studiengang ".$row->studiengang_fk." nicht gefunden.");
				
			}
		}
		else 
		{
			$error_log.="Kein Zugriff auf tbl_studiengang => Studiengang ".$row->studiengang_fk." nicht gefunden.";
			$error=true;
		}
		
		if(!$error)
		{
			$qry="SELECT * FROM bis.tbl_entwicklungsteam";
			$anzahl_quelle=pg_num_rows(pg_query($conn,$qry));
			$qry="SELECT * FROM bis.tbl_entwicklungsteam WHERE mitarbeiter_uid='".$mitarbeiter_uid."' AND studiengang_kz='".$studiengang_kz."';";
			if($result2=pg_query($conn,$qry))
			{
				if(!pg_num_rows($result2)>0) 
				{
					//einfügen
					$qry="INSERT INTO bis.tbl_entwicklungsteam (mitarbeiter_uid, studiengang_kz, besqualcode, 
						beginn, ende, updateamum, updatevon, insertamum, insertvon, ext_id) VALUES(".
						myaddslashes($mitarbeiter_uid).", ".
						myaddslashes($studiengang_kz).", ".
						myaddslashes($besqualcode).", ".
						myaddslashes($beginn).", ".
						myaddslashes($ende).", ".
						myaddslashes($updateamum).", ".
						myaddslashes($updatevon).", ".
						myaddslashes($insertamum).", ".
						myaddslashes($insertvon).", ".
						myaddslashes($ext_id)." ".
						");";
						$ausgabe.="Mitarbeiter '".$mitarbeiter_uid."' zu Entwicklungsteam von Studiengang: '".$studiengang_kz."' hinzugefügt.\n";
					if(!pg_query($conn,$qry))
					{
						$error_log.= "*****\nFehler beim Speichern des E-Team-Datensatzes von Mitarbeiter '".$mitarbeiter_uid."'\n   ".$qry."\n";
						$anzahl_fehler++;
					}
					else 
					{
						$anzahl_quelle++;
						$anzahl_eingefuegt++;
					}
				}
			}
		}
		
	}
}

echo nl2br("E-Team-Synchro Ende: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");

//echo nl2br($text);
echo nl2br("\n".$error_log);
echo nl2br("\n\nE-Team-Sync:\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler\n".$ausgabe);
$ausgabe="E-Team-Sync:\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler\n\n".$ausgabe;
if(strlen(trim($error_log))>0)
{
	mail($adress, 'SYNC-Fehler E-Team von '.$_SERVER['HTTP_HOST'], $error_log,"From: vilesci@technikum-wien.at");
}
mail($adress, 'SYNC E-Team von '.$_SERVER['HTTP_HOST'], $ausgabe,"From: vilesci@technikum-wien.at");
?>
</body>
</html>