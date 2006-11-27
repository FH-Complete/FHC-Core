<?php
/**
 * Synchronisiert Personendatensaetze von Vilesci DB in PORTAL DB
 *
 */
include('../../vilesci/config.inc.php');
include('../../include/fas/person.class.php');
include('../../include/fas/benutzer.class.php');
include('../../include/fas/mitarbeiter.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die("Connection zur Vilesci Datenbank fehlgeschlagen");

$adress='oesi@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
function validate($row)
{
}

/*************************
 * VILESCI-PORTAL - Synchronisation
 */

//Mitarbeiter
$qry = "SELECT * FROM tbl_person join tbl_mitarbeiter using(uid) WHERE personalnummer<>'OFF'";

if($result = pg_query($conn_vilesci, $qry))
{
	$text.="Mitarbeiter Sync\n----------------\n";
	while($row = pg_fetch_object($result))
	{
		$mitarbeiter = new mitarbeiter($conn);
		$qry = "Select count(*) from tbl_benutzer where uid='$row->uid'";
		if($result1 = pg_query($conn, $qry))
		{
			if($row->personalnummer!='')
			{
				$mitarbeiter->sprache='German';
				$mitarbeiter->anrede='';
				$mitarbeiter->titelpost='';
				$mitarbeiter->titelpre=$row->titel;
				$mitarbeiter->nachname=$row->nachname;
				if(!$len=strpos($row->vornamen,' '))
					$len = strlen($row->vornamen);				
				$mitarbeiter->vorname=substr($row->vornamen,0,$len);
				$mitarbeiter->gebdatum=$row->gebdatum;
				$mitarbeiter->gebort=$row->gebort;
				$mitarbeiter->gebzeit=$row->gebzeit;
				$mitarbeiter->foto='';
				$mitarbeiter->anmerkungen=$row->anmerkungen;
				$mitarbeiter->homepage=$row->homepage;
				$mitarbeiter->svnr='';
				$mitarbeiter->ersatzkennzeichen='';
				$mitarbeiter->familienstand='';
				$mitarbeiter->anzahlkinder='';
				$mitarbeiter->aktiv=($row->aktiv=='t'?true:false);
				$mitarbeiter->insertvon='SYNC';
				$mitarbeiter->updatevon=$row->updatevon;
				$mitarbeiter->ext_id='';
				
				$mitarbeiter->uid=$row->uid;
				$mitarbeiter->bnaktiv=$row->aktiv;
				$mitarbeiter->alias=$row->alias;
				
				$mitarbeiter->ausbildungcode='';
				$mitarbeiter->personalnummer=$row->personalnummer;
				$mitarbeiter->kurzbz=$row->kurzbz;
				$mitarbeiter->lektor=($row->lektor=='t'?true:false);
				$mitarbeiter->fixangestellt=($row->fixangestellt=='t'?true:false);
				$mitarbeiter->telefonklappe=$row->telefonklappe;
				
				if(pg_fetch_object($result1)->count>0) //Wenn dieser eintrag schon vorhanden ist
				{
					//Mitarbeiterdaten updaten
					$mitarbeiter->new=false;
					//Person_id ermitteln
					$qry = "Select person_id from tbl_benutzer where uid='$row->uid'";
					if($row2=pg_fetch_object(pg_query($conn,$qry)))
						$mitarbeiter->person_id=$row2->person_id;					
				}
				else 
				{
					//Mitarbeiter neu anlegen
					$mitarbeiter->new=true;
				}
				if(!$mitarbeiter->save())
						$error_log.=$mitarbeiter->errormsg."\n";
			}
			else 
				$error_log .= "$row->nachname ($row->uid) hat keine Personalnummer";
		}
	}
}
else
	$error_log .= 'Mitarbeiterdatensaetze konnten nicht geladen werden';
	
?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - Personen</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

echo nl2br($text);
echo nl2br($error_log);

?>
</body>
</html>
