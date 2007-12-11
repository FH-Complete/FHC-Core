<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Adressendatensaetze von FAS DB in PORTAL DB
//*
//*
//* setzt voraus: sync von sync.stp_person, sync.stp_staat
//* benoetigt: tbl_syncperson, sync.stp_adresse

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
$eingefuegt=0;
$fehler=0;
$dublette=0;
$updtaes=0;
$plausi='';
$start='';
$staat=array();

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>
<html>
<head>
<title>Synchro - StPoelten -> Vilesci - Adresse</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>

<?php

//Array für Nationen erzeugen
$qry_staat="SELECT __staat, chkurzbez FROM sync.stp_staat";
if($result_staat = pg_query($conn, $qry_staat))
{
	while($row_staat = pg_fetch_object($result_staat))
	{
		$staat[$row_staat->__staat]=$row_staat->chkurzbez;
	}
}
else
{
	echo "<br>".$qry_staat."<br><strong>".pg_last_error($conn)." </strong><br>";
}

/*
Tabelle: adresse
__Adresse		int
_Person		int
chStrasse		char
chHausNr		char
chPLZ			char
chOrt			char
_Staat			int
chBemerkung		char
boStandardAdr	bit
boHeimatAdr		bit

_cxbundesland 	integer 		
chstrasse 		character varying(256) 		
chhausnr 		character varying(256) 		
chplz 			character varying(256) 		
chort 			character varying(256) 		
_staat 			integer 		
chadrbemerkung 	character varying(256) 
*/

//Array für Nationen erzeugen
$qry_staat="SELECT __staat, chkurzbez FROM sync.stp_staat";
if($result_staat = pg_query($conn, $qry_staat))
{
	while($row_staat = pg_fetch_object($result_staat))
	{
		$staat[$row_staat->__staat]=$row_staat->chkurzbez;
	}
}
else
{
	echo "<br>".$qry_staat."<br><strong>".pg_last_error($conn)." </strong><br>";
}

//*********** Neue Daten holen *****************
$qry="(SELECT __Person as _Person, chTitel, chNachname, chVorname, _cxBundesland, 
	chStrasse, chHausNr,  chPLZ,  chOrt, _Staat,  chAdrBemerkung, NULL as boStandardAdr, NULL as boHeimatAdr, 'h' as typ 
	FROM sync.stp_person WHERE  __Person IN (SELECT ext_id FROM public.tbl_person))
	UNION
	(SELECT _Person, titelpre, nachname, vorname, NULL, 
	chStrasse, chHausNr,  chPLZ,  chOrt, _Staat,  chBemerkung, boStandardAdr, boHeimatAdr, 'n' as typ 
	FROM sync.stp_adresse JOIN public.tbl_person ON(_person=ext_id) WHERE  _Person IN (SELECT ext_id FROM public.tbl_person))
	ORDER BY _Person;";

$error_log_ext="Überprüfung Adressendaten in EXT-DB:\n\n";
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
		if($row->_chort=='' || $row->_chort==NULL)
		{
			$error_log1.="\nKein Ort eingetragen";
			$error=true;
		}
		if($row->_chplz=='' || $row->_chplz==NULL)
		{
			$error_log1.="\nKeine Postleitzahl eingetragen";
			$error=true;
		}
		if($row->_chstrasse=='' || $row->_chstrasse==NULL)
		{
			$error_log1.="\nKeine Straße eingetragen";
			$error=true;
		}
		if($row->_cxbundesland=='' || $row->_cxbundesland==NULL)
		{
			$error_log1.="\nKein Bundesland eingetragen";
			$error=true;
		}
		if($error)
		{
			$error_log.="\n*****\n".$row->__person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname).": ".$error_log1;
			$error_log1='';
			$error=false;
			if($cont)
			{
				$fehler++;
				continue;
			}
		}
		//Person ermitteln
		pg_query($conn, "BEGIN");
		$qry_synk="SELECT * FROM sync.tbl_syncperson where __person=".$row->__person.";";
		if($result_synk=pg_query($conn, $qry_synk))
		{
			if($row_synk=pg_fetch_object($result_synk))
			{
				$person_id=$row_synk.person_id;
			}
			else 
			{
				$error_log.="Person ".$row->__person." in tbl_syncperson nicht gefunden!";
				$fehler++;
				continue;
			}
		}
		
		// Check auf Doppelgaenger
		$sql="SELECT * FROM public.tbl_adresse
			WHERE person_id='".$person_id."' 
			AND trim(strasse)='".trim(trim(myaddslashes($row->chstrasse))." ".trim($row->chhausnr))."' 
			AND plz='".$row->chplz."' AND trim(ort)='".trim(myaddslashes($row->chort))."';";
		if($result_dubel = pg_query($conn, $sql))
		{
			if (pg_num_rows($result_dubel)==0)
			{
				//Neue Adresse anlegen
				$sql="INSERT INTO public.tbl_adresse
					(person_id, name, strasse, plz, ort, gemeinde, nation, typ, heimatadresse, zustelladresse, firma_id, 
					insertamum,insertvon,updateamum,updatevon, ext_id)
					VALUES
					(".myaddslashes($person_id).", ".
					myaddslashes($row->chadrbemerkung).", ".
					myaddslashes(trim(trim($row->chstrasse))." ".trim($row->chhausnr)).", ".
					myaddslashes($row->chplz).", ".
					myaddslashes(trim($row->ort)).", ".
					myaddslashes(trim($row->ort)).", ".
					myaddslashes($staat[$row->_staat]).", ".
					myaddslashes($row->typ).", ".
					($boheimatadr?'true':'false').", ".
					($bostandardadr?'true':'false').", 
					NULL, 	now(), 'sync', now(), 'sync', NULL);";
				if(!$result_neu = pg_query($conn, $sql))
				{
					$error_log.= $sql."\n<strong>".pg_last_error($conn)." </strong>\n";
					pg_query($conn, "ROLLBACK");
				}
				else
				{
					$eingefuegt++;
					pg_query($conn, "COMMIT");
				}
			}
			else
			{
				if($row_dubel=pg_fetch_object($result_dubel))
				{
					//Update
					if(name != myaddslashes($row->chadrbemerkung))
					{
						$sql="name = ".myaddslashes($row->chadrbemerkung);
					}
					if(strasse!=trim(trim($row->chstrasse)." ".trim($row->chhausnr)))
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", strasse=".trim(trim($row->chstrasse)." ".trim($row->chhausnr));
						}
						else 
						{
							$sql="strasse=".trim(trim($row->chstrasse)." ".trim($row->chhausnr));
						}
					}
					if(plz!=$row->chplz)
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", plz=".$row->chplz;
						}
						else 
						{
							$sql="plz=".$row->chplz;
						}
					}
					if(ort!=$row->chort)
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", ort=".$row->chort;
						}
						else 
						{
							$sql="ort=".$row->chort;
						}
					}
					if(gemeinde!=$row->chort)
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", gemeinde=".$row->chort;
						}
						else 
						{
							$sql="gemeinde=".$row->chort;
						}
					}
					if(nation!=$staat[$row->_staat])
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", nation=".$staat[$row->_staat];
						}
						else 
						{
							$sql="nation=".$staat[$row->_staat];
						}
					}
					if(typ!=$row->typ)
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", typ=".$row->typ;
						}
						else 
						{
							$sql="typ=".$row->typ;
						}
					}
					
					
					if(strlen(trim($sql))>0)
					{
						$sql="UPDATE public.tbl_adresse SET ".$sql." WHERE person_id='".$person_id."';";
						if(!$result_neu = pg_query($conn, $sql))
						{
							$error_log.= $sql."\n<strong>".pg_last_error($conn)." </strong>\n";
							pg_query($conn, "ROLLBACK");
						}
						else
						{
							$updates++;
							pg_query($conn, "COMMIT");
						}
					}
				}
			}
		}
		else
		{
			$error_log.= "\n".$sql."\n<strong>".pg_last_error($conn)." </strong>\n";
			pg_query($conn, "ROLLBACK");
		}
	}
}
else
{
	echo "<br>".$qry."<br><strong>".pg_last_error($conn)." </strong><br>";
}



echo "<br><br>Eingefügt:  ".$eingefuegt;
echo "<br>Updates:  ".$updates;
echo "<br>Fehler:       ".$fehler;
echo "<br><br>";
if($error_log=='' && $log_updates=='')
{
	echo "o.k.<br>";
}
else
{
	echo nl2br($log_updates);
	echo nl2br($error_log);
}
echo nl2br($ausgabe);

mail($adress, 'SYNC-Fehler StP-Adresse von '.$_SERVER['HTTP_HOST'], $error_log,"From: vilesci@technikum-wien.at");

mail($adress, 'SYNC StP-Adresse  von '.$_SERVER['HTTP_HOST'], "Sync Person\n------------\n\n"
."Personen: Gesamt: ".$anzahl_person_gesamt." / Eingefügt: ".$eingefuegt." / Updates: ".$updates." / Fehler: ".$fehler
."\n\nBeginn: ".$start."\nEnde:    ".date("d.m.Y H:i:s")."\n\n".$ausgabe.$log_updates, "From: vilesci@technikum-wien.at");


?>
</body>
</html>