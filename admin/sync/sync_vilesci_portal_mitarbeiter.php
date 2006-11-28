<?php
/**
 * Synchronisiert Personendatensaetze von Vilesci DB in PORTAL DB
 *
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/fas/person.class.php');
require_once('../../include/fas/benutzer.class.php');
require_once('../../include/fas/mitarbeiter.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die("Connection zur Vilesci Datenbank fehlgeschlagen");

$error_log='';
$text = '';
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

// ***********************************
// * VILESCI->PORTAL - Synchronisation
// ***********************************

//Mitarbeiter
$qry = "SELECT * FROM tbl_person JOIN tbl_mitarbeiter USING(uid) WHERE personalnummer<>'OFF'";

if($result = pg_query($conn_vilesci, $qry))
{
	$text.="\n Sync Mitarbeiter\n\n";
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$mitarbeiter = new mitarbeiter($conn);
		
		if($row->personalnummer!='')
		{
			$mitarbeiter->sprache='German';
			$mitarbeiter->anrede='';
			$mitarbeiter->titelpost='';
			$mitarbeiter->titelpre=$row->titel;
			$mitarbeiter->nachname=$row->nachname;
			if(!$len=strpos($row->vornamen,' '))
			{
				$student->vorname=$row->vornamen;
				$mitarbeiter->vornamen='';
			}
			else
			{				
				$mitarbeiter->vorname=substr($row->vornamen,0,$len);
				$mitarbeiter->vornamen=substr($row->vornamen,$len+1,strlen($row->vornamen));
			}
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
			$mitarbeiter->insertvon='';
			$mitarbeiter->insertamum='';
			$mitarbeiter->updateamum=$row->updateamum;
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
			
			$qry = "SELECT person_id FROM tbl_benutzer WHERE uid='$row->uid'";
			if($result1 = pg_query($conn, $qry))
			{		
				if(pg_num_rows($result1)>0) //wenn dieser eintrag schon vorhanden ist
				{
					if($row1=pg_fetch_object($result1))
					{
						//Mitarbeiterdaten updaten
						$mitarbeiter->new=false;
						$mitarbeiter->person_id=$row1->person_id;
					}
					else 
					{
						$error_log.="Person_id von $row->uid konnte nicht ermittelt werden\n";
						$error=true;
					}
				}
				else 
				{
					//Mitarbeiter neu anlegen
					$mitarbeiter->new=true;
				}
				
				if(!$error)
					if(!$mitarbeiter->save())
					{
						$error_log.=$mitarbeiter->errormsg."\n";
						$anzahl_fehler++;
					}
					else 
						$anzahl_eingefuegt++;
				else 
					$anzahl_fehler++;
			}
			else 
				$error_log .= "$row->nachname ($row->uid) hat keine Personalnummer\n";
		}
	}
}
else
	$error_log .= 'Mitarbeiterdatensaetze konnten nicht geladen werden\n';
$text.="Anzahl aktualisierte Datensaetze: $anzahl_eingefuegt\n";
$text.="Anzahl der Fehler: $anzahl_fehler\n";
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