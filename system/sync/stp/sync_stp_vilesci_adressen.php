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
$updates=0;
$plausi='';
$start='';
$staat=array();
$person_id='';

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
$qry="(SELECT __person as _person, chtitel, chnachname, chvorname, _cxbundesland, 
	chstrasse, chhausnr,  chplz,  chort, _staat,  chadrbemerkung, NULL as bostandardadr, NULL as boheimatadr, 'h' as typ 
	FROM sync.stp_person WHERE  __Person IN (SELECT ext_id FROM public.tbl_person))
	UNION
	(SELECT _person, titelpre, nachname, vorname, NULL, 
	chstrasse, chhausnr,  chplz,  chort, _staat,  chbemerkung, bostandardadr, boheimatadr, 'n' as typ 
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
		if($row->chort=='' || $row->chort==NULL)
		{
			$error_log1.="\nKein Ort eingetragen";
			$cont=true;
			$error=true;
		}
		if($row->chplz=='' || $row->chplz==NULL)
		{
			$error_log1.="\nKeine Postleitzahl eingetragen";
			$cont=true;
			$error=true;
		}
		if($row->chstrasse=='' || $row->chstrasse==NULL)
		{
			$error_log1.="\nKeine Straße eingetragen";
			$cont=true;
			$error=true;
		}
		/*if($row->_cxbundesland=='' || $row->_cxbundesland==NULL)
		{
			$error_log1.="\nKein Bundesland eingetragen";
			$error=true;
		}*/
		if(!isset($staat[$row->_staat]))
		{
			$error_log1.="\nStaat-Nr.: '".$row->_staat."' in sync.stp_staat nicht gefunden";
			$error=true;
			$cont=true;
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
		//Person ermitteln
		pg_query($conn, "BEGIN");
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
		
		// Check auf Doppelgaenger
		$qry_dubel="SELECT * FROM public.tbl_adresse
			WHERE person_id='".$person_id."' 
			AND trim(strasse)='".trim(trim(addslashes($row->chstrasse))." ".trim($row->chhausnr))."' 
			AND plz='".$row->chplz."' AND trim(ort)='".trim(addslashes($row->chort))."';";
		if($result_dubel = pg_query($conn, $qry_dubel))
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
					myaddslashes(trim($row->chort)).", ".
					myaddslashes(trim($row->chort)).", ".
					myaddslashes($staat[$row->_staat]).", ".
					myaddslashes($row->typ).", ".
					($row->boheimatadr?'true':'false').", ".
					($row->bostandardadr?'true':'false').", 
					NULL, 	now(), 'sync', now(), 'sync', NULL);";
				if(!$result_neu = pg_query($conn, $sql))
				{
					$fehler++;
					$error_log.= $sql."\n<strong>".pg_last_error($conn)." </strong>\n";
					pg_query($conn, "ROLLBACK");
				}
				else
				{
					$ausgabe.="\n------------------------------------\nÜbertragen: ".$row->_person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname);
					$ausgabe.="\n---Adresse: ".trim(trim($row->chstrasse))." ".trim($row->chhausnr).", ".$row->chplz.", ".$row->chort.", ".$staat[$row->_staat].", Typ: ".$row->typ;
					$ausgabe.="\n---------Heimatadresse: '".($row->boheimatadr?'Ja':'Nein')."', Standardadresse: '".($row->bostandardadr?'Ja':'Nein')."',\n---------Anmerkung: '".$row->chadrbemerkung."';";
					$eingefuegt++;
					pg_query($conn, "COMMIT");
				}
			}
			else
			{
				if($row_dubel=pg_fetch_object($result_dubel))
				{
					//Update
					$sql='';
					if($row_dubel->name != $row->chadrbemerkung
						&& $row->chadrbemerkung!='' && $row->chadrbemerkung!=NULL)
					{
						$sql="name = ".myaddslashes($row->chadrbemerkung);
					}
					if($row_dubel->strasse!=trim(trim($row->chstrasse)." ".trim($row->chhausnr)) 
						&& trim(trim($row->chstrasse))!='' && trim(trim($row->chstrasse))!=NULL 
						&& trim($row->chhausnr)!='' && trim($row->chhausnr)!=NULL)
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", strasse=".myaddslashes(trim(trim($row->chstrasse)." ".trim($row->chhausnr)));
						}
						else 
						{
							$sql="strasse=".myaddslashes(trim(trim($row->chstrasse)." ".trim($row->chhausnr)));
						}
					}
					if($row_dubel->plz!=$row->chplz
						&& $row->chplz!='' && $row->chplz!=NULL)
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", plz='".$row->chplz."'";
						}
						else 
						{
							$sql="plz='".$row->chplz."'";
						}
					}
					if($row_dubel->ort!=$row->chort
						&& $row->chort!='' && $row->chort!=NULL)
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", ort='".$row->chort."'";
						}
						else 
						{
							$sql="ort='".$row->chort."'";
						}
					}
					if($row_dubel->gemeinde!=$row->chort
						&& $row->chort!='' && $row->chort!=NULL)
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", gemeinde='".$row->chort."'";
						}
						else 
						{
							$sql="gemeinde='".$row->chort."'";
						}
					}
					if($row_dubel->nation!=$staat[$row->_staat]
						&& $staat[$row->_staat]!='' && $staat[$row->_staat]!=NULL)
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", nation='".$staat[$row->_staat]."'";
						}
						else 
						{
							$sql="nation='".$staat[$row->_staat]."'";
						}
					}
					if($row_dubel->typ!=$row->typ && $row_dubel->typ!='h'
						&& $row->typ!='' && $row->typ!=NULL )
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", typ='".$row->typ."'";
						}
						else 
						{
							$sql="typ='".$row->typ."'";
						}
					}
					
					
					if(strlen(trim($sql))>0)
					{
						$sql="UPDATE public.tbl_adresse SET ".$sql." 
						WHERE person_id='".$person_id."' 
						AND trim(strasse)='".trim(trim(addslashes($row->chstrasse))." ".trim($row->chhausnr))."' 
						AND plz='".$row->chplz."' AND trim(ort)='".trim(addslashes($row->chort))."';";
						if(!$result_neu = pg_query($conn, $sql))
						{
							$fehler++;
							$error_log.= "\n".$sql."\n<strong>".pg_last_error($conn)." </strong>\n";
							pg_query($conn, "ROLLBACK");
						}
						else
						{
							$ausgabe.="\n------------------------------------\nGeändert: ".$row->_person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname);
							$ausgabe.="\n---Adresse: ".$row_dubel->strasse.", ".$row_dubel->plz.", ".$row_dubel->ort.", ".$row_dubel->nation.", Typ: ".$row_dubel->typ;
							$ausgabe.="\n---Heimatadresse: '".($row_dubel->heimatadresse?'Ja':'Nein')."', Zustelladresse: '".($row_dubel->zustelladresse?'Ja':'Nein')."',\n---name: '".$row_dubel->name."'\n".$sql;
							$updates++;
							pg_query($conn, "COMMIT");
						}
					}
					else 
					{
						//$ausgabe.="\n------------------------------------\nGeändert: ".$row->_person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname);
						//$ausgabe.="\n---Adresse: ".$row_dubel->strasse.", ".$row_dubel->plz.", ".$row_dubel->ort.", ".$row_dubel->nation.", Typ: ".$row_dubel->typ;
						//$ausgabe.="\n--->bereits vorhanden. keine Änderung.";
						$dublette++;
					}
				}
			}
		}
		else
		{
			$fehler++;
			$error_log.= "\n".$sql."\n<strong>".pg_last_error($conn)." </strong>\n";
			pg_query($conn, "ROLLBACK");
		}
	}
}
else
{
	$fehler++;
	echo "<br>".$qry."<br><strong>".pg_last_error($conn)." </strong><br>";
}



echo "<br><br>Eingefügt:  ".$eingefuegt;
echo "<br>Updates:  ".$updates;
echo "<br>Doppelt:   ".$dublette; 
echo "<br>Fehler:       ".$fehler;
//echo "<br><br>Summe:     ".($eingefuegt+$updates+$dublette+$fehler);

echo "<br><br>";
if($error_log=='' && $log_updates=='')
{
	echo "o.k.<br>";
}
else
{
	//echo nl2br($log_updates);
	echo nl2br($error_log);
}
echo nl2br($ausgabe);

mail($adress, 'SYNC-Fehler StP-Adresse von '.$_SERVER['HTTP_HOST'], $error_log,"From: nsc@fhstp.ac.at");

mail($adress, 'SYNC StP-Adresse  von '.$_SERVER['HTTP_HOST'], "Sync Person\n------------\n\n"
."Personen: Gesamt: ".$anzahl_person_gesamt." / Eingefügt: ".$eingefuegt." / Updates: ".$updates." / Fehler: ".$fehler
."\n\nBeginn: ".$start."\nEnde:    ".date("d.m.Y H:i:s")."\n\n".$ausgabe.$log_updates, "From: nsc@fhstp.ac.at");


?>
</body>
</html>