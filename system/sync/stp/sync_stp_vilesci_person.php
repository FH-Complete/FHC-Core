<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Personendatensaetze von FAS DB in PORTAL DB
//*
//*
//* setzt voraus: sync von sync.stp_person, sync.stp_staat
//* benoetigt: tbl_syncperson

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
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_update=0;
$anzahl_fehler=0;
$plausi='';
$staat=array();

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>
<html>
<head>
<title>Synchro - StPoelten -> Vilesci - Person</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>

<?php
//Array für Nationen erzeugen
$qry_staat="SELECT __staat, chkurzbez from sync.stp_staat";
if($result_staat = pg_query($conn, $qry_staat))
{
	while($row_staat = pg_fetch_object($result_staat))
	{
		$staat[$row_staat->__staat]=$row_staat->chkurzbez;
	}
}

//*********** Neue Daten holen *****************
$qry='SELECT __Person,_Staatsbuerger,_GebLand,Briefanrede,chTitel,chNachname,chVorname,daGebDat,chGebOrt,chAdrBemerkung,chHomepage,chSVNr,chErsatzKZ,_cxFamilienstand,_cxGeschlecht,inKinder
		FROM sync.stp_person
		WHERE _cxGeschlecht!=3 AND _cxPersonTyp!=5
			AND __Person NOT IN (SELECT __Person FROM sync.tbl_syncperson) LIMIT 10;';

$error_log_ext="Überprüfung Personendaten in EXT-DB:\n\n";

if($result = pg_query($conn, $qry))
{
	$error_log_ext.="Anzahl der Datensätze: ".pg_num_rows($result)."\n";
	echo nl2br($error_log_ext);
	while($row=pg_fetch_object($result))
	{
		if ($row->_cxgeschlecht==1)
			$row->_cxgeschlecht='m';
		elseif ($row->_cxgeschlecht==2)
			$row->_cxgeschlecht='w';
		else
			$row->_cxgeschlecht='';
		if($row->_staatsbuerger==NULL)
		{
			$error_log1.="\nKeine Staatsbürgerschaft eingetragen";
			$error=true;
		}
		if($row->_gebland==NULL)
		{
			$error_log1.="\nKein Geburtsland eingetragen";
			$error=true;
		}
		if($error)
		{
			$error_log.="\n*****\n".$row->chtitel." ".$row->chnachname.", ".$row->chvorname." :".$error_log1;
			$error_log1='';
			$error=false;
			continue;
		}
		// Check auf Doppelgaenger
		if ($row->chsvnr!='' || $row->dagebdat!='' )
		{
			$sql="SELECT * FROM public.tbl_person
				WHERE
					(svnr='$row->chsvnr' AND svnr!='' AND svnr IS NOT NULL)
					OR (ersatzkennzeichen='.myaddslashes($row->chersatzkz).' AND ersatzkennzeichen!='' AND ersatzkennzeichen IS NOT NULL) 
					OR (nachname=".myaddslashes($row->chnachname)." AND ".myaddslashes($row->chnachname)."!='' AND vorname=".myaddslashes($row->chvorname)."AND ".myaddslashes($row->chvorname)."!='' AND gebdatum=".myaddslashes($row->dagebdat)." AND gebdatum IS NOT NULL)";
			if($result_dubel = pg_query($conn, $sql))
			{
				if (pg_num_rows($result_dubel)==0)
				{
					//Neue Person anlegen
					pg_query($conn, "BEGIN");
					$sql="INSERT INTO public.tbl_person
							(staatsbuergerschaft,geburtsnation,sprache,anrede,titelpost,titelpre,nachname,vorname,
							vornamen,gebdatum,gebort,gebzeit,foto,anmerkung,homepage,svnr,ersatzkennzeichen,
							familienstand,geschlecht,anzahlkinder,aktiv,insertamum,insertvon,updateamum,updatevon,
							ext_id)
							VALUES
							(".myaddslashes($staat[$row->_staatsbuerger]).", ".
							myaddslashes($staat[$row->_gebland]).", ".
							"NULL, ".
							myaddslashes($row->briefanrede).", ".
							"NULL, ".
							myaddslashes($row->chtitel).", ".
							myaddslashes($row->chnachname).", ".
							myaddslashes($row->chvorname).", ".
							"NULL, ".
							myaddslashes($row->dagebdat).", ".
							myaddslashes($row->chgebort).", ".
							"NULL, ".
							"NULL, ".
							myaddslashes($row->chadrbemerkung).", ".
							myaddslashes($row->chhomepage).", ".
							myaddslashes($row->chsvnr).", ".
							myaddslashes($row->chersatzkz).", ".
							myaddslashes($row->_cxfamilienstand).", ".
							myaddslashes($row->_cxgeschlecht).", ".
							myaddslashes($row->inkinder).", ".
							"TRUE, now(), 'sync', now(), 'sync', ".
							myaddslashes($row->__person).");";
					if(!$result_neu = pg_query($conn, $sql))
					{
						$error_log.= $sql."\n<strong>".pg_last_error($conn)." </strong>\n";
						pg_query($conn, "ROLLBACK");
					}
					else 
					{
						//Eintrag Synctabelle
						$qry_seq = "SELECT currval('public.tbl_person_person_id_seq') AS id;";
						if($row_seq=pg_fetch_object(pg_query($conn,$qry_seq)))
						{
							$person_id=$row_seq->id;
						}
						else
						{
							$error=true;
							$error_log.='Person-Sequence konnte nicht ausgelesen werden\n';
						}
						if(!$error)
						{
							$qryz="SELECT * FROM sync.tbl_syncperson WHERE __person='$row->__person' AND person_id='$person_id'";
							if($resultz = pg_query($conn, $qryz))
							{
								if(pg_num_rows($resultz)==0) //wenn dieser eintrag noch nicht vorhanden ist
								{
									$qry='INSERT INTO sync.tbl_syncperson (__person, person_id)'.
										'VALUES ('.$row->__person.', '.$person_id.');';
									$resulti = pg_query($conn, $qry);
								}
								$ausgabe.="\n------------------\nÜbertragen: ".$row->chtitel." ".$row->chnachname.", ".$row->chvorname." / ".$row->_staatsbuerger.", ".$row->_gebland;	
								pg_query($conn, "COMMIT");
							}
							else 
							{
								$error_log.= $sql."\n<strong>".pg_last_error($conn)." </strong>\n";
								pg_query($conn, "ROLLBACK");	
							}
						}
						else 
						{
							pg_query($conn, "ROLLBACK");
						}
						
					}
				}
			}
		}
	}
}
echo "<br><br>";
echo nl2br($error_log);
echo nl2br($ausgabe);

?>
</body>
</html>