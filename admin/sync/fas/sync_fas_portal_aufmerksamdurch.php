<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert aufmerksamdurchdatensaetze von FAS DB in PORTAL DB
//*
//*

include('../../../vilesci/config.inc.php');
include('../../../include/aufmerksamdurch.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

function validate($row)
{
}
?>

<html>
<head>
<title>Synchro - FAS -> Portal - Aufmerkamdurch</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
/*************************
 * FAS-PORTAL - Synchronisation
 */


$qry = "SELECT * FROM aufmerksamdurch;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Aufmerksamdurch Sync\n-------------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		echo "- ";
		ob_flush();
		flush();	
			
		$error=false;
		$aufmerksamdurch					=new aufmerksamdurch($conn);
		$aufmerksamdurch->aufmerksamdurch_kurzbz	=substr($row->name,0,8);
		$aufmerksamdurch->beschreibung		=$row->name;
		$aufmerksamdurch->ext_id				=$row->aufmerksamdurch_pk;

		//Insert oder Update
		$qry3="SELECT aufmerksamdurch_kurzbz FROM tbl_aufmerksamdurch WHERE aufmerksamdurch_kurzbz='".$aufmerksamdurch->aufmerksamdurch_kurzbz."';";
		if($result3 = pg_query($conn, $qry3))
		{
			if(pg_num_rows($result3)>0) //eintrag gefunden
			{
				if($row3=pg_fetch_object($result3))
				{ 
					// update , wenn datensatz bereits vorhanden
					$aufmerksamdurch->new=false;
				}
			}
			else 
			{
				// insert, wenn datensatz noch nicht vorhanden
				$aufmerksamdurch->new=true;
			}
		}	
				
		If (!$error)
		{
			if(!$aufmerksamdurch->save())
			{
				$error_log.=$aufmerksamdurch->errormsg."\n";
				$anzahl_fehler++;
			}
			else 
			{
				$anzahl_eingefuegt++;
			}
		}
	}		
}



//echo nl2br($text);
echo nl2br($error_log);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");
$error_log.="\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler";
mail($adress, 'SYNC Aufmerksamdurch', $error_log);
?>
</body>
</html>