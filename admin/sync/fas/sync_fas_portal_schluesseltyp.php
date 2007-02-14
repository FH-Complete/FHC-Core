<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert schluesseltypdatensaetze von FAS DB in PORTAL DB
//*
//*

include('../../../vilesci/config.inc.php');
include('../../../include/betriebsmitteltyp.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at; oesi@technikum-wien.at; pam@technikum-wien.at';
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
<title>Synchro - FAS -> Portal - Schlüsseltyp</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
/*************************
 * FAS-PORTAL - Synchronisation
 */


$qry = "SELECT * FROM schluessel;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Schlüsseltyp Sync\n---------------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		echo "- ";
		ob_flush();
		flush();	
			
		$error=false;
		$betriebsmitteltyp				=new betriebsmitteltyp($conn);
		$betriebsmitteltyp->beschreibung		=$row->name;
		$betriebsmitteltyp->anzahl			=$row->anzahl==''?'0':$row->anzahl;
		$betriebsmitteltyp->kaution			=$row->betrag==''?'0':$row->betrag;

		if($row->name=='Gaderobenschlüssel')
		{
			$betriebsmitteltyp->betriebsmitteltyp='Gaderobe';
		}
		else
		{
			$betriebsmitteltyp->betriebsmitteltyp=$row->name;
		}

		
		$betriebsmitteltyp->new=true;
		if(!$betriebsmitteltyp->save())
		{
			$error_log.=$betriebsmitteltyp->errormsg."\n";
			$anzahl_fehler++;
		}
		else 
		{
			
			//überprüfen, ob sync-eintrag schon vorhanden
			$qryz="SELECT * FROM tbl_syncschluesseltyp WHERE fas_typ='$row->schluessel_pk' AND portal_typ='$betriebsmitteltyp->betriebsmitteltyp'";
			if($resultz = pg_query($conn, $qryz))
			{
				if(pg_num_rows($resultz)==0) //wenn dieser eintrag noch nicht vorhanden ist
				{
					$qry="INSERT INTO tbl_syncschluesseltyp (fas_typ, portal_typ)".
						"VALUES ('".$row->schluessel_pk."', '".$betriebsmitteltyp->betriebsmitteltyp."');";
					$resulti = pg_query($conn, $qry);
				}
			}
			
			$anzahl_eingefuegt++;
		}		
	}
}	


//echo nl2br($text);
echo nl2br($error_log);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");
$error_log.="\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler";
mail($adress, 'SYNC Schluesseltyp', $error_log);
?>
</body>
</html>