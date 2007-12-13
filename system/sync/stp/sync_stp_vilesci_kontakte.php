<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Kontaktdatensaetze von FAS DB in PORTAL DB
//*
//*
//* setzt voraus: sync von sync.stp_person
//* benoetigt: tbl_syncperson, sync.stp_telefon, sync.stp_email

require_once('sync_config.inc.php');

$starttime=time();
$conn=pg_connect(CONN_STRING)
	or die("Connection zur FH-Complete Datenbank fehlgeschlagen");

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

// Sync-Tabelle fuer Personen checken
if (!@pg_query($conn,'SELECT * FROM sync.tbl_syncperson LIMIT 1;'))
{
	$sql='CREATE TABLE sync.tbl_syncperson
			(
				person_id	integer NOT NULL,
				__Person	integer NOT NULL
			);
			Grant select on sync.tbl_syncperson to group "admin";
			Grant update on sync.tbl_syncperson to group "admin";
			Grant delete on sync.tbl_syncperson to group "admin";
			Grant insert on sync.tbl_syncperson to group "admin";';
	if (!@pg_query($conn,$sql))
		echo '<strong>sync.tbl_syncperson: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'sync.tbl_syncperson wurde angelegt!<BR>';
}



$error_log='';
$error_log1='';
$error_log_ext='';
$ausgabe="";
$text = '';
$error = '';
$cont='';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_update=0;
$anzahl_fehler=0;
$eingefuegt1=0;
$eingefuegt2=0;
$fehler=0;
$fehler1=0;
$fehler2=0;
$dublette1=0;
$dublette2=0;
$updates1=0;
$updates2=0;
$plausi='';
$start='';
$tel='';
$person_id='';

/*************************
 * StP-PORTAL - Synchronisation
 */
?>
<html>
<head>
<title>Synchro - StPoelten -> Vilesci - Kontakte</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>

<?php


/*
chTelBemerkung	char
chVorwahl		char
chNummer		char
chKlappe		char

chEMailAdresse	char
chEMailBemerkung	char
boEMailFHWeb	bit

__Telefon 		(int)
chBemerkung 	(char)
chVorwahl 		(char)
chNummer 		(char)
chKlappe 		(char)
_Person 		(int)
boStandard 		(bit) 

__EMail (int)
chBemerkung (char)
chAdresse (char)
_Person (int)
boFHWeb (bit)
boAutoEMail (bit)
*/


//*********** Neue Daten holen *****************
$qry="(SELECT __person as _person, chtelbemerkung, chvorwahl, chnummer, chklappe, null as bostandard,
	chemailadresse, chemailbemerkung, null as boautoemail, 
	chtitel, chnachname, chvorname, _cxbundesland  
	FROM sync.stp_person WHERE  __Person IN (SELECT ext_id FROM public.tbl_person) 
	AND (chnummer!='' AND chnummer  IS NOT NULL AND chemailadresse!='' AND chemailadresse  IS NOT NULL))
	UNION
	(SELECT _person, chbemerkung, chvorwahl, chnummer, chklappe, bostandard, 
	null, null, null, 
	titelpre, nachname, vorname, NULL 
	FROM sync.stp_telefon JOIN public.tbl_person ON(_person=ext_id) WHERE  _Person IN (SELECT ext_id FROM public.tbl_person))
	UNION
	(SELECT _person, null, null, null, null, null,
	chadresse, chbemerkung, boautoemail,
	titelpre, nachname, vorname, NULL 
	FROM sync.stp_email JOIN public.tbl_person ON(_person=ext_id) WHERE  _Person IN (SELECT ext_id FROM public.tbl_person))
	ORDER BY _Person;";

$error_log_ext="Überprüfung Kontaktdaten in EXT-DB:\n\n";
$start=date("d.m.Y H:i:s");
echo $start."<br>";
if($result = pg_query($conn, $qry))
{
	$anzahl_person_gesamt=pg_num_rows($result);
	$error_log_ext.="Anzahl der Datensätze: ".$anzahl_person_gesamt."\n";
	echo nl2br($error_log_ext);
	while($row=pg_fetch_object($result))
	{
		$cont='';
		if(($row->chnummer=='' || $row->chnummer==NULL) && ($row->chemailadresse=='' || $row->chemailadresse==NULL))
		{
			$error_log1.="\nDatensatz ohne Telefonnummer und E-Mail-Adresse!";
			$cont=true;
			$error=true;
		}
		if($error)
		{
			$error_log.="\n*****\n".$row->_person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname).": ".$error_log1;
			$error_log1='';
			$error=false;
			if($cont)
			{
				$fehler++;
				$error_log.="\n-->nicht eingetragen";
				continue;
			}
		}
		$tel=trim($row->chvorwahl).trim($row->chnummer).trim($row->chklappe);
		//Person ermitteln
		//pg_query($conn, "BEGIN");
		$qry_synk="SELECT * FROM sync.tbl_syncperson where __person=".$row->_person.";";
		if($result_synk=pg_query($conn, $qry_synk))
		{
			if($row_synk=pg_fetch_object($result_synk))
			{
				$person_id=$row_synk->person_id;
			}
			else 
			{
				$error_log.="Person ".$row->_person." in tbl_syncperson nicht gefunden!";
				$fehler++;
				continue;
			}
		}
		
		// telefon
		if((trim($row->chvorwahl).trim($row->chnummer).trim($row->chklappe))!='' && $row->chnummer!=NULL)
		{
			$qry_dubel="SELECT * FROM public.tbl_kontakt
				WHERE person_id='".$person_id."' 
				AND kontakttyp='telefon' AND kontakt='".trim($row->chvorwahl).trim($row->chnummer).trim($row->chklappe)."';";
			if($result_dubel = pg_query($conn, $qry_dubel))
			{
				if (pg_num_rows($result_dubel)==0)
				{
					//Neue Telefonnummer anlegen
					$sql="INSERT INTO public.tbl_kontakt
						(person_id, kontakttyp, anmerkung, kontakt, zustellung, 
						insertamum,insertvon,updateamum,updatevon, ext_id)
						VALUES
						(".myaddslashes($person_id).", 
						'telefon', ".
						myaddslashes($row->chtelbemerkung).", ".
						myaddslashes(trim($row->chvorwahl).trim($row->chnummer).trim($row->chklappe)).", 
						false, 
						now(), 'sync', now(), 'sync', NULL);";
					if(!$result_neu = pg_query($conn, $sql))
					{
						$fehler1++;
						$error_log.= $sql."\n<strong>".pg_last_error($conn)." </strong>\n";
					}
					else
					{
						$ausgabe.="\n------------------------------------\nÜbertragen: ".$row->_person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname);
						$ausgabe.="\n---Telefon: ".trim($row->chvorwahl).trim($row->chnummer).trim($row->chklappe).", ".$row->chtelbemerkung;
						$eingefuegt1++;
					}
				}
				else 
				{
					$dublette1++;
						
				}
			}
			else
			{
				$fehler1++;
				$error_log.= "\n".$sql."\n<strong>".pg_last_error($conn)." </strong>\n";
			}
		}
		// email
		if((trim($row->chemailadresse))!='' && $row->chemailadresse!=NULL)
		{
			$qry_dubel="SELECT * FROM public.tbl_kontakt
				WHERE person_id='".$person_id."' 
				AND kontakttyp='email' AND kontakt='".trim($row->chemailadresse)."';";
			if($result_dubel = pg_query($conn, $qry_dubel))
			{
				if (pg_num_rows($result_dubel)==0)
				{
					//Neue Telefonnummer anlegen
					$sql="INSERT INTO public.tbl_kontakt
						(person_id, kontakttyp, anmerkung, kontakt, zustellung, 
						insertamum,insertvon,updateamum,updatevon, ext_id)
						VALUES
						(".myaddslashes($person_id).", 
						'email', ".
						myaddslashes($row->chemailbemerkung).", ".
						myaddslashes(trim($row->chemailadresse)).", 
						false, 
						now(), 'sync', now(), 'sync', NULL);";
					if(!$result_neu = pg_query($conn, $sql))
					{
						$fehler2++;
						$error_log.= $sql."\n<strong>".pg_last_error($conn)." </strong>\n";
					}
					else
					{
						$ausgabe.="\n------------------------------------\nÜbertragen: ".$row->_person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname);
						$ausgabe.="\n---Email: ".trim($row->chemailadresse).", ".$row->chemailbemerkung;
						$eingefuegt2++;
					}
				}
				else 
				{
					$dublette2++;
						
				}
			}
			else
			{
				$fehler2++;
				$error_log.= "\n".$sql."\n<strong>".pg_last_error($conn)." </strong>\n";
			}
		}
	}
}
else
{
	$fehler++;
	echo "<br>".$qry."<br><strong>".pg_last_error($conn)." </strong><br>";
}


echo "<br><b>Telefon: </b>";
echo "<br>Eingefügt:  ".$eingefuegt1;
echo "<br>Updates:  ".$updates1;
echo "<br>Doppelt:   ".$dublette1; 
echo "<br>Fehler:     ".$fehler1;
echo "<br><br><b>E-Mail: </b>";
echo "<br>Eingefügt:  ".$eingefuegt2;
echo "<br>Updates:  ".$updates2;
echo "<br>Doppelt:   ".$dublette2; 
echo "<br>Fehler:     ".$fehler2;
echo "<br><br>allg. Fehler:       ".$fehler;
//echo "<br><br>Summe:     ".($eingefuegt+$updates+$dublette+$fehler);

echo "<br><br>";
if($error_log=='')
{
	echo "o.k.<br>";
}
else
{
	//echo nl2br($log_updates);
	echo nl2br($error_log);
}
echo nl2br($ausgabe);
/*
mail($adress, 'SYNC-Fehler StP-Adresse von '.$_SERVER['HTTP_HOST'], $error_log,"From: nsc@fhstp.ac.at");

mail($adress, 'SYNC StP-Adresse  von '.$_SERVER['HTTP_HOST'], "Sync Person\n------------\n\n"
."Personen: Gesamt: ".$anzahl_person_gesamt." / Eingefügt: ".$eingefuegt." / Updates: ".$updates." / Fehler: ".$fehler
."\n\nBeginn: ".$start."\nEnde:    ".date("d.m.Y H:i:s")."\n\n".$ausgabe.$log_updates, "From: nsc@fhstp.ac.at");
*/

?>
</body>
</html>