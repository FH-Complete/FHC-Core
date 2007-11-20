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
//* setzt voraus: sync von sync.stp_person
//* benoetigt: tbl_syncperson

require_once('sync_config.inc.php');

$starttime=time();
$conn=pg_connect(CONN_STRING)
	or die("Connection zur FH-Complete Datenbank fehlgeschlagen");

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
		echo '<strong>sync.stp_person: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'sync.stp_person wurde angelegt!<BR>';
}



$error_log='';
$error_log_ext='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_update=0;
$anzahl_fehler=0;
$plausi='';


/*************************
 * FAS-PORTAL - Synchronisation
 */
?>
<html>
<head>
<title>Synchro - FAS -> Vilesci - Person</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>

<?php
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
		// Check auf Doppelgaenger
		if ($row->chsvnr!='' || $row->dagebdat!='' )
		{
			$sql="SELECT * FROM public.tbl_person
				WHERE
					(svnr='$row->chsvnr' AND svnr!='' AND svnr IS NOT NULL)
					OR (nachname='$row->chnachname' AND '$row->chnachname'!='' AND vorname='$row->chvorname' AND '$row->chvorname'!='' AND gebdatum='$row->dagebdat' AND gebdatum IS NOT NULL)";
			if($result_dubel = pg_query($conn, $sql))
			{
				if (pg_num_rows($result_dubel)==0)
				{
					//Neue Person anlegen
					$sql="INSERT INTO public.tbl_person
							(staatsbuergerschaft,geburtsnation,sprache,anrede,titelpost,titelpre,nachname,vorname,vornamen,gebdatum,gebort,gebzeit,foto,anmerkung,homepage,svnr,ersatzkennzeichen,familienstand,geschlecht,anzahlkinder,aktiv,insertamum,insertvon,updateamum,updatevon,ext_id)
							VALUES
							($row->_staatsbuerger,$row->_gebland,NULL,'$row->briefanrede',NULL,'$row->chtitel','$row->chnachname','$row->chvorname',NULL,'$row->dagebdat','$row->chgebort',NULL,NULL,'$row->chadrbemerkung','$row->chhomepage','$row->chsvnr','$row->chersatzkz',$row->_cxfamilienstand,'$row->_cxgeschlecht',$row->inkinder,TRUE,now(),'sync',now(),'sync',NULL);";
					if(!$result_neu = pg_query($conn, $sql))
						echo $sql.'<BR>'.pg_last_error($conn).' </strong><BR>';
				}
			}
		}
	}
}



/*mail($adress, 'Plausicheck von Personen von '.$_SERVER['HTTP_HOST'], $error_log_fas,"From: vilesci@technikum-wien.at");
$error_log_fas='';
exit;

$qry = "SELECT * FROM person WHERE person_pk AS person1 NOT IN (
SELECT
p1.person_pk AS person1, p1.familienname AS familienname1, p1.vorname AS vorname1, p1.vornamen AS vornamen1, p1.geschlecht AS geschlecht1,
p1.gebdat AS gebdat1, p1.gebort AS gebort1, p1.staatsbuergerschaft AS staatsbuergerschaft1, p1.familienstand AS familienstand1,
p1.svnr AS svnr1, p1. ersatzkennzeichen  AS ersatzkennzeichen1, p1.anrede AS anrede1, p1.anzahlderkinder AS anzahlderkinder1,
p1.bismelden AS bismelden1, p1.titel AS titel1,  p1.uid AS uid1, p1.gebnation AS gebnation1, p1.postnomentitel AS postnomentitel1,
p2.person_pk AS person2, p2.familienname AS familienname2, p2.vorname AS vorname2, p2.vornamen AS vornamen2, p2.geschlecht AS geschlecht2,
p2.gebdat AS gebdat2, p2.gebort AS gebort2, p2.staatsbuergerschaft AS staatsbuergerschaft2, p2.familienstand AS familienstand2,
p2.svnr AS svnr2, p2. ersatzkennzeichen  AS ersatzkennzeichen2, p2.anrede AS anrede2, p2.anzahlderkinder AS anzahlderkinder2,
p2.bismelden AS bismelden2, p2.titel AS titel2,  p2.uid AS uid2, p2.gebnation AS gebnation2, p2.postnomentitel AS postnomentitel2
FROM person AS p1, person AS p2 WHERE
((p1.gebdat=p2.gebdat AND p1.familienname=p2.familienname AND p1.vorname=p2.vorname)
OR ((p1.ersatzkennzeichen=p2.ersatzkennzeichen AND p1.ersatzkennzeichen<>'') OR (p1.svnr=p2.svnr AND p1.svnr<>'')))
AND (p1.person_pk <> p2.person_pk)
AND (p1.familienname<>p2.familienname OR p1.vorname<>p2.vorname OR p1.vornamen<>p2.vornamen OR p1.geschlecht<>p2.geschlecht OR p1.gebdat<>p2.gebdat OR p1.staatsbuergerschaft<> p2.staatsbuergerschaft OR p1.familienstand<>p2.familienstand OR p1.svnr<>p2.svnr OR p1.ersatzkennzeichen<>p2.ersatzkennzeichen OR p1.anrede<>p2.anrede OR p1.anzahlderkinder<>p2.anzahlderkinder OR p1.titel<>p2.titel OR p1.gebnation<>p2.gebnation OR p1.postnomentitel<> p2.postnomentitel)
);";
if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Person Sync\n-------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$person=new person($conn);

		$person->geburtsnation=$row->gebnation;
		$person->anrede=trim($row->anrede);
		$person->titelpost=trim($row->postnomentitel);
		$person->titelpre=trim($row->titel);
		$person->nachname=trim($row->familienname);
		$person->vorname=trim($row->vorname);
		$person->vornamen=trim($row->vornamen);
		$person->gebdatum=$row->gebdat;
		$person->gebort=$row->gebort;
		$person->anmerkungen=$row->bemerkung;
		$person->svnr=trim($row->svnr);
		$person->ersatzkennzeichen=trim($row->ersatzkennzeichen);
		$person->familienstand=$row->familienstand;
		$person->anzahlkinder=$row->anzahlderkinder;
		$person->staatsbuergerschaft=$row->staatsbuergerschaft;
		$person->geschlecht=strtolower($row->geschlecht);
		$person->ext_id=$row->person_pk;
		$person->aktiv=true;
		$person->updatevon='SYNC';
		$person->insertvon='SYNC';


		if($row->familienstand==1)
		{
			$person->familienstand='l';
		}
		elseif($row->familienstand==2)
		{
			$person->familienstand='v';
		}
		elseif($row->familienstand==3)
		{
			$person->familienstand='g';
		}
		elseif($row->familienstand==4)
		{
			$person->familienstand='w';
		}
		else
		{
			$person->familienstand=null;
		}
		if ($person->geschlecht=='')
		{
			$person->geschlecht='m';
		}

		$error=false;

		$qry="SELECT person_id FROM public.tbl_benutzer WHERE uid='$row->uid'";
		if($resultu = pg_query($conn, $qry))
		{
			if(pg_num_rows($resultu)>0 && $row->uid!='') //wenn dieser eintrag schon vorhanden ist
			{
				if($rowu=pg_fetch_object($resultu))
				{
					//update
					$person->person_id=$rowu->person_id;
					$person->new=false;
				}
				else
				{
					$error=true;
					$error_log.="benutzer von $row->uid konnte nicht ermittelt werden\n";
				}
			}
			else
			{
				$qry="SELECT person_fas, person_portal FROM sync.tbl_syncperson WHERE person_fas='$row->person_pk'";
				if($result1 = pg_query($conn, $qry))
				{
					if(pg_num_rows($result1)>0) //wenn dieser eintrag schon vorhanden ist
					{
						if($row1=pg_fetch_object($result1))
						{
							//update
							$person->person_id=$row1->person_portal;
							$person->new=false;
						}
						else
						{
							$error=true;
							$error_log.="person von $row->person_pk konnte nicht ermittelt werden\n";
						}
					}
					else
					{
						//vergleich svnr und ersatzkennzeichen
						$qry="SELECT * FROM public.tbl_person
							WHERE ('$row->svnr' is not null AND '$row->svnr' <> '' AND svnr = '$row->svnr')
							OR ('$row->ersatzkennzeichen' is not null AND '$row->ersatzkennzeichen' <> '' AND ersatzkennzeichen = '$row->ersatzkennzeichen')";
						if($resultz = pg_query($conn, $qry))
						{
							if(pg_num_rows($resultz)>0) //wenn dieser eintrag schon vorhanden ist
							{
								if($rowz=pg_fetch_object($resultz))
								{
									$person->new=false;
									$person->person_id=$rowz->person_id;

								}
								else
								{
									$error=true;
									$error_log.="person mit svnr: $row->svnr bzw. ersatzkennzeichen: $row->ersatzkennzeichen konnte nicht ermittelt werden (".pg_num_rows($resultz).")\n";
								}
							}
							else
							{
								//insert
								$person->new=true;
								//echo nl2br("insert von ".$row->uid.", ".$row->familienname."\n");
							}
						}
					}
				}
			}

			if(!$error)
			{
				if(!$person->save())
				{
					$error_log.=$person->errormsg."\n";
					$anzahl_fehler++;
				}
				else
				{
					//überprüfen, ob eintrag schon vorhanden
					$qryz="SELECT person_fas FROM sync.tbl_syncperson WHERE person_fas='$row->person_pk' AND person_portal='$person->person_id'";
					if($resultz = pg_query($conn, $qryz))
					{
						if(pg_num_rows($resultz)==0) //wenn dieser eintrag noch nicht vorhanden ist
						{
							$qry='INSERT INTO sync.tbl_syncperson (person_fas, person_portal)'.
								'VALUES ('.$row->person_pk.', '.$person->person_id.');';
							pg_query($conn, $qry);
						}
					}
					if ($person->new)
					{
						$anzahl_eingefuegt++;
					}
					else
					{
						$anzahl_update++;
					}
					//echo "- ";
					//ob_flush();
					//flush();
				}
			}
			else
			{
				$anzahl_fehler++;
			}
		}
	}
	echo nl2br("\nabgeschlossen\n\n");
}
else
	$error_log .= '\nPersonendatensaetze konnten nicht geladen werden\n';



//echo nl2br($text);
echo nl2br("\nLog:\n".$error_log);
echo nl2br("\n\nGesamt FAS: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Geändert: $anzahl_update / Fehler: $anzahl_fehler");
$error_log="Person Sync\n-------------\n\nGesamt FAS: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Geändert: $anzahl_update / Fehler: $anzahl_fehler\n\n".$error_log;
mail($adress, 'SYNC Personen', $error_log,"From: vilesci@technikum-wien.at");
*/
?>
</body>
</html>