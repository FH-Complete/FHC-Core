<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Reihungstestdatensaetze von FAS DB in PORTAL DB
//*
//*

require_once('../../../vilesci/config.inc.php');
require_once('../../../include/reihungstest.class.php');


$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_update=0;
$anzahl_fehler=0;
$ausgabe='';
$ausgabe_test='';


function validate($row)
{
}

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>

<html>
<head>
<title>Synchro - FAS -> Vilesci - Reihungstest</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
//nation
$qry = "SELECT * FROM reihungstest ORDER BY datum;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Reihungstest Sync\n-------------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$reihungstest				=new reihungstest($conn);
		$studiengang_kz			='';
		$reihungstest->ort_kurzbz		='';
		$reihungstest->anmerkung		=$row->raum;
		$reihungstest->datum		=$row->datum;
		$reihungstest->uhrzeit		=$row->uhrzeit;
		//$reihungstest->updateamum	=$row->;
		$reihungstest->updatevon		="SYNC";
		//$reihungstest->insertamum	=$row->;
		$reihungstest->insertvon		="SYNC";
		$reihungstest->ext_id		=$row->reihungstest_pk;
	
		$update=false;
		$ausgabe_test='';
		//echo nl2br ($reihungstest->ext_id."\n");

		$qry2="SELECT * FROM tbl_reihungstest WHERE ext_id=".$row->reihungstest_pk.";";
		if($result2 = pg_query($conn, $qry2))
		{
			if(pg_num_rows($result2)>0) //eintrag gefunden
			{
				if($row2=pg_fetch_object($result2))
				{ 
					// update adresse, wenn datensatz bereits vorhanden
					
					if($row2->anmerkung!=$row->raum)
					{
						$update=true;
						if(strlen(trim($ausgabe_test))>0)
						{
							$ausgabe_test.=", Raum: '".$row->raum."'";
						}
						else
						{
							$ausgabe_test="Raum: '".$row->raum."'";
						}
					}
					if($row2->datum!=$row->datum)
					{
						$update=true;
						if(strlen(trim($ausgabe_test))>0)
						{
							$ausgabe_test.=", Datum: '".$row->datum."'";
						}
						else
						{
							$ausgabe_test="Datum: '".$row->datum."'";
						}
					}
					if($row2->uhrzeit!=$row->uhrzeit)
					{
						$update=true;
						if(strlen(trim($ausgabe_test))>0)
						{
							$ausgabe_test.=", Uhrzeit: '".$row->uhrzeit."'";
						}
						else
						{
							$ausgabe_test="Uhrzeit: '".$row->uhrzeit."'";
						}
					}
					
					$reihungstest->new=false;
					$reihungstest->reihungstest_id=$row2->reihungstest_id;
				}
			}
			else 
			{
				// insert, wenn datensatz noch nicht vorhanden
				$reihungstest->new=true;	
			}
		}
		if(!$error)
		{
			if($reihungstest->new || $update)
			{
				if(!$reihungstest->save())
				{
					$error_log.=$reihungstest->errormsg."\n";
					$anzahl_fehler++;
					$ausgabe_test='';
				}
				else 
				{
					if($reihungstest->new)
					{
						$ausgabe.="Reihungstest '$row->raum', '$row->datum' eingefügt!\n";
						$anzahl_eingefuegt++;
					}
					else 
					{
						if($update)
						{
							$ausgabe.="Reihungstest geändert: ".$ausgabe_test." !\n";
							$anzahl_update++;
						}
					}
					//echo "- ";
					//ob_flush();
					//flush();
				}
			}
		}
		//flush();	
	}	
}


//echo nl2br($text);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Geändert: $anzahl_update / Fehler: $anzahl_fehler");
echo nl2br("\n\n".$error_log);
echo nl2br("\n\n".$ausgabe);
$ausgabe="\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Geändert: $anzahl_update / Fehler: $anzahl_fehler\n".$ausgabe;
$ausgabe="Telefonsync:\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Geändert: $anzahl_update / Fehler: $anzahl_fehler\n\n".$ausgabe;
if(strlen(trim($error_log))>0)
{
	mail($adress, 'SYNC-Fehler Reihungstest', $error_log,"From: vilesci@technikum-wien.at");
}
mail($adress, 'SYNC Reihungstest', $ausgabe,"From: vilesci@technikum-wien.at");
?>
</body>
</html>