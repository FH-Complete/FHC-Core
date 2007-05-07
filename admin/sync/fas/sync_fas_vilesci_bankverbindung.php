<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Bankverbindungsdatensaetze von FAS DB in PORTAL DB
//*
//*

include('../../../vilesci/config.inc.php');
include('../../../include/bankverbindung.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_fehler=0;
$ausgabe="";
$ausgabe_all="";

function validate($row)
{
}
?>

<html>
<head>
<title>Synchro - FAS -> Portal - Bankverbindung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
/*************************
 * FAS-PORTAL - Synchronisation
 */

//nation
$qry = "SELECT * FROM bankverbindung ORDER BY person_fk;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Bankverbindung Sync\n---------------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		/*echo "- ";
		ob_flush();
		flush();*/
			
		$error=false;
		$bankverbindung				=new bankverbindung($conn);
		$bankverbindung->name			=$row->name;
		$bankverbindung->anschrift		=$row->anschrift;
		$bankverbindung->bic			=$row->bic;
		$bankverbindung->blz			=$row->blz;
		$bankverbindung->iban			=$row->iban;
		$bankverbindung->kontonr			=$row->kontonr;
		$bankverbindung->updatevon		="SYNC";
		$bankverbindung->insertvon		="SYNC";
		$bankverbindung->ext_id			=$row->bankverbindung_pk;
		if($row->typ=='1')
		{
			$bankverbindung->typ		='p'; //Privatkonto
			$bankverbindung->verrechnung	=false;
		}
		if($row->typ=='2')
		{
			$bankverbindung->typ		='f'; //Firmenkonto
			$bankverbindung->verrechnung	=false;
		}
		if($row->typ=='11')
		{
			$bankverbindung->typ		='p'; //Privatverrechnungskonto
			$bankverbindung->verrechnung	=true;
		}
		if($row->typ=='12')
		{
			$bankverbindung->typ		='f'; //Firmenverrechnungskonto
			$bankverbindung->verrechnung	=true;
		}
		//Person_id feststellen
		if($row->kontonr!='')
		{
			$qry1="SELECT person_portal FROM sync.tbl_syncperson WHERE person_fas=".$row->person_fk.";";
			if($result1 = pg_query($conn, $qry1))
			{
				if(pg_num_rows($result1)>0) //eintrag gefunden
				{
					if($row1=pg_fetch_object($result1))
					{ 
						$qry2="SELECT bankverbindung_id, ext_id FROM tbl_bankverbindung WHERE ext_id=".$row->bankverbindung_pk.";";
						if($result2 = pg_query($conn, $qry2))
						{
							if(pg_num_rows($result2)>0) //eintrag gefunden
							{
								if($row2=pg_fetch_object($result2))
								{ 
									// update , wenn datensatz bereits vorhanden
									$bankverbindung->person_id=$row1->person_portal;
									$bankverbindung->bankverbindung_id=$row2->bankverbindung_id;
									$bankverbindung->new=false;
									$ausgabe="Bankverbindung aktualisiert: Name '".$bankverbindung->name."', Typ '".$bankverbindung->typ."'.\n";
								}
							}
							else 
							{
								// insert, wenn datensatz noch nicht vorhanden
								$bankverbindung->new=true;
								$bankverbindung->person_id=$row1->person_portal;
								$ausgabe="Bankverbindung eingefügt: Name '".$bankverbindung->name."', Typ '".$bankverbindung->typ."'.\n";
							}
						}
					}
				}
				else 
				{
					$error=true;
					$error_log.="person mit person_fk: $row->person_fk konnte in tbl_syncperson nicht gefunden werden! (".pg_num_rows($result1).")\n";
					$anzahl_fehler++;
				}
			}
			If (!$error)
			{
				if(!$bankverbindung->save())
				{
					$error_log.=$bankverbindung->errormsg."\n";
					$anzahl_fehler++;
				}
				else 
				{
					$anzahl_eingefuegt++;
				}
			}
		}
		$ausgabe_all.= $ausgabe;
		$ausgabe="";
	}		
}



//echo nl2br($text);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");
echo nl2br("\n".$error_log);
if(strlen(trim($error_log))>0)
{
	mail($adress, 'SYNC-Fehler Bankverbindung von '.$_SERVER['HTTP_HOST'], $error_log,"From: vilesci@technikum-wien.at");
}
mail($adress, 'SYNC Bankverbindung von '.$_SERVER['HTTP_HOST'], $ausgabe_all,"From: vilesci@technikum-wien.at");
?>
</body>
</html>