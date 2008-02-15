<?php
/* Copyright (C) 2008 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Diplomprüfungsdatensaetze von FAS DB in PORTAL DB
//*
//*
//* setzt voraus: tbl_akadgrad, tbl_abschlussbeurteilung
//* benoetigt: tbl_syncperson, tbl_studiengang

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
//tbl_pruefungstyp  checken
if(pg_num_rows(pg_query($conn,"SELECT * FROM lehre.tbl_pruefungstyp where pruefungstyp_kurzbz='Diplom'"))==0)
{
	$qry="INSERT INTO lehre.tbl_pruefungstyp (pruefungstyp_kurzbz, beschreibung) VALUES ('Diplom', 'Diplomprüfung')";
	if (!pg_query($conn, $qry))
	{
		die(pg_last_error($conn));
	}
}
if(pg_num_rows(pg_query($conn,"SELECT * FROM lehre.tbl_pruefungstyp where pruefungstyp_kurzbz='Bachelor'"))==0)
{
	$qry="INSERT INTO lehre.tbl_pruefungstyp (pruefungstyp_kurzbz, beschreibung) VALUES ('Bachelor', 'Bachelorprüfung')";
	if (!pg_query($conn, $qry))
	{
		die(pg_last_error($conn));
	}
}



$error_log='';
$error_log1='';
$error_log2='';
$error_log3='';
$error_log4='';
$error_log5='';
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
$updates=0;
$dublette=0;
$plausi='';
$start='';
$anzahl_person_gesamt=0;
$anzahl_person_gesamt2=0;
$staat=array();

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>
<html>
<head>
<title>Synchro - StPoelten -> Vilesci - Abschlusspruefung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

//*********** Neue Daten holen *****************
$qry="SELECT _vorsitzender, _cxbeurteilungsstufegesamt,_gegenstandtech, _gegenstandnichttech, _pruefertech, _pruefernichttech, chLfdNr, 
	__person, _cxgeschlecht, chtitel, chvorname, chnachname, studiengang_kz,  tbl_studiengang.typ, COALESCE(dapruefungsdat,dapruefteil1dat) as pruefdat FROM sync.stp_person 
	JOIN sync.stp_stgvertiefung ON (_stgvertiefung=__stgvertiefung)
	JOIN public.tbl_studiengang ON (_studiengang=ext_id) 
	WHERE __Person IN (SELECT __person FROM sync.tbl_syncperson) AND (_cxPersonTyp='1' OR _cxPersonTyp='2') 
		AND NOT (_vorsitzender IS NULL AND _pruefertech IS NULL AND _pruefernichttech IS NULL)
		AND COALESCE(dapruefungsdat,dapruefteil1dat) IS NOT NULL;";
//$error_log="Überprüfung Abschlusspruefungsdaten in EXT-DB:\n\n";
$start=date("d.m.Y H:i:s");
echo $start."<br>";
if($result = pg_query($conn, $qry))
{
	$anzahl_person_gesamt=pg_num_rows($result);
	$error_log_ext.="Anzahl der Datensätze: ".$anzahl_person_gesamt."\n";
	echo nl2br($error_log_ext);
	while($row=pg_fetch_object($result))
	{
		//UID ermitteln
		pg_query($conn, "BEGIN");
		$qry_synk="SELECT * FROM public.tbl_student where ext_id=".myaddslashes($row->__person).";";
		if($result_synk=pg_query($conn, $qry_synk))
		{
			if($row_synk=pg_fetch_object($result_synk))
			{
				$uid=$row_synk->student_uid;
			}
			else 
			{
				$error_log1.="\nStudent ".$row->__person." (ext_id) in tbl_student nicht gefunden!";
				$fehler++;
				continue;
			}
		}
		//person_id von vorsitz,pruefer1,pruefer2 ermitteln
		if($row->_vorsitzender==NULL || $row->_vorsitzender=='')
		{
			$vorsitzender=NULL;
		}
		else
		{
			$qry_synk="SELECT * FROM public.tbl_benutzer where ext_id=".myaddslashes($row->_vorsitzender).";";
			if($result_synk=pg_query($conn, $qry_synk))
			{
				if($row_synk=pg_fetch_object($result_synk))
				{
					$vorsitzender=$row_synk->uid;
				}
				else 
				{
					$error_log2.="\nVorsitzender ".$row->_vorsitzender." in tbl_benutzer nicht gefunden!";
					$fehler++;
					continue;
				}
			}
			$qry_synk="SELECT * FROM public.tbl_mitarbeiter where mitarbeiter_uid=".myaddslashes($vorsitzender).";";
			if($result_synk=pg_query($conn, $qry_synk))
			{
				if(pg_num_rows($result_synk)==0)
				{
					$error_log2.="\nVorsitzender ".$vorsitzender." in tbl_mitarbeiter nicht gefunden!";
					$fehler++;
					continue;
				}
			}
		}
		if($row->_pruefertech==NULL || $row->_pruefertech=='')
		{
			$pruefertech=NULL;
		}
		else
		{
			$qry_synk="SELECT * FROM sync.tbl_syncperson where __person=".myaddslashes($row->_pruefertech).";";
			if($result_synk=pg_query($conn, $qry_synk))
			{
				if($row_synk=pg_fetch_object($result_synk))
				{
					$pruefertech=$row_synk->person_id;
				}
				else 
				{
					$error_log3.="\nTechn. Prüfer ".$row->_pruefertech." in tbl_syncperson nicht gefunden!";
					$fehler++;
					continue;
				}
			}
		}
		if($row->_pruefernichttech==NULL || $row->_pruefernichttech=='')
		{
			$pruefernichttech=NULL;
		}
		else
		{
			$qry_synk="SELECT * FROM sync.tbl_syncperson where __person=".myaddslashes($row->_pruefernichttech).";";
			if($result_synk=pg_query($conn, $qry_synk))
			{
				if($row_synk=pg_fetch_object($result_synk))
				{
					$pruefernichttech=$row_synk->person_id;
				}
				else 
				{
					$error_log4.="\nNicht-Techn. Prüfer ".$row->_pruefernichttech." in tbl_syncperson nicht gefunden!";
					$fehler++;
					continue;
				}
			}
		}
		//akad.Grad holen
		$qry_synk="SELECT * FROM lehre.tbl_akadgrad WHERE studiengang_kz=".myaddslashes($row->studiengang_kz)." ORDER BY geschlecht;";
		if($result_synk=pg_query($conn, $qry_synk))
		{
			if(pg_num_rows($result)>0)
			{
				while($row_synk=pg_fetch_object($result_synk))
				{
					if($row_synk->geschlecht==NULL)
					{
						//Bachelor oder Masterstudiengang hat nur einen Eintrag mit geschlecht==NULL
						$akadgrad_id=$row_synk->akadgrad_id;
						break 1;
					}
					else 
					{
						//Diplomstudiengang hat zwei Einträge
						if($row->_cxgeschlecht==1 && $row_synk->geschlecht=='m')
						{
							$akadgrad_id=$row_synk->akadgrad_id;
							break 1;
						}
						if($row->_cxgeschlecht==2 && $row_synk->geschlecht=='w')
						{
							$akadgrad_id=$row_synk->akadgrad_id;
							break 1;
						}
					}
				}
			}
			else 
			{
				$error_log5.="\nAkad.Grad für Stg ".$row->studiengang_kz." nicht gefunden!";
				$fehler++;
				continue;
			}
		}
		/*
		// Konvertieren
		if ($row->_cxgeschlecht==1)
			$row->_cxgeschlecht='m';
		elseif ($row->_cxgeschlecht==2)
			$row->_cxgeschlecht='w';
		else
			$row->_cxgeschlecht='';
			*/
		// Check auf Doppelgaenger
		$qry_dubel="SELECT * FROM lehre.tbl_abschlusspruefung WHERE student_uid='".$uid."' 	AND datum='".$row->pruefdat."';";
		if($result_dubel = pg_query($conn, $qry_dubel))
		{
			if (pg_num_rows($result_dubel)==0)
			{
				//Neue Abschlussprüfung anlegen
				$sql="INSERT INTO lehre.tbl_abschlusspruefung
					(student_uid, vorsitz, pruefer1, pruefer2, pruefer3, abschlussbeurteilung_kurzbz, akadgrad_id, pruefungstyp_kurzbz, datum, sponsion, anmerkung, 
					insertamum,insertvon,updateamum,updatevon, ext_id)
					VALUES
					(".myaddslashes($uid).", ".
					myaddslashes(trim($vorsitzender)).", ".
					myaddslashes(trim($pruefernichttech)).", ".
					myaddslashes(trim($pruefertech)).", 
					NULL, ".
					myaddslashes($row->_cxbeurteilungsstufegesamt).", ".
					myaddslashes($akadgrad_id).", ";
					if($row->typ=='d' || $row->typ=='m')
					{
						$sql.=myaddslashes('Diplom').", ";
					}
					else
					{
						$sql.=myaddslashes('Bachelor').", ";
					}
					$sql.=myaddslashes($row->pruefdat).",  
					NULL, ";
					$sql.=myaddslashes($row->chlfdnr).", 
					now(), 'sync', NULL, NULL, NULL);";
				if(!$result_neu = pg_query($conn, $sql))
				{
					$fehler++;
					$error_log.= $sql."\n<strong>".pg_last_error($conn)." </strong>\n";
					pg_query($conn, "ROLLBACK");
				}
				else
				{
					$ausgabe.="\n------------------------------------\nÜbertragen: ".$row->__person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname);
					$ausgabe.="\n---Abschlussprüfung (".$row->typ."): am ".$row->pruefdat.", Vorsitz:".trim($vorsitzender).", Prüfer: ".$pruefertech." / ".$pruefernichttech;
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
					if($row_dubel->vorsitz!=$vorsitzender && $vorsitzender!=NULL && $vorsitzender!='')
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", vorsitz=".myaddslashes(trim($vorsitzender));
						}
						else 
						{
							$sql="vorsitz=".myaddslashes(trim($vorsitzender));
						}
					}
					if($row_dubel->pruefer1!=$pruefernichttech && $pruefernichttech!=NULL && $pruefernichttech!='')
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", pruefer1=".myaddslashes(trim($pruefernichttech));
						}
						else 
						{
							$sql="pruefer1=".myaddslashes(trim($pruefernichttech));
						}
					}
					if($row_dubel->pruefer2!=$pruefertech && $pruefertech!=NULL && $pruefertech!='')
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", pruefer2=".myaddslashes(trim($pruefertech));
						}
						else 
						{
							$sql="pruefer2=".myaddslashes(trim($pruefertech));
						}
					}
					if($row_dubel->abschlussbeurteilung_kurzbz!=$row->_cxbeurteilungsstufegesamt && $row->_cxbeurteilungsstufegesamt!=NULL && $row->_cxbeurteilungsstufegesamt!='')
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", abschlussbeurteilung_kurzbz=".myaddslashes(trim($row->_cxbeurteilungsstufegesamt));
						}
						else 
						{
							$sql="abschlussbeurteilung_kurzbz=".myaddslashes(trim($row->_cxbeurteilungsstufegesamt));
						}
					}
					
					if(strlen(trim($sql))>0)
					{
						//update nur mit änderungen bei vorsitz,prüfer oder note
						$sql="UPDATE lehre.tbl_abschlusspruefung SET ".$sql." 
						WHERE student_uid='".$uid."' AND datum='".$row->pruefdat."';";
						if(!$result_neu = pg_query($conn, $sql))
						{
							$fehler++;
							$error_log.= "\n".$sql."\n<strong>".pg_last_error($conn)." </strong>\n";
							pg_query($conn, "ROLLBACK");
						}
						else
						{
							$ausgabe.="\n------------------------------------\nÜbertragen: ".$row->__person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname);
							$ausgabe.="\n---Abschlussprüfung (".$row->typ."): am ".$row->pruefdat.", Vorsitz:".trim($vorsitzender).", Prüfer: ".$pruefertech." / ".$pruefernichttech;
							$updates++;
							pg_query($conn, "COMMIT");
						}
					}
					else 
					{
						//kein update da bereits gleich vorhanden
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

echo "Datensätze ohne Prüfungsdatum werden nicht berücksichtigt.";
echo "<br><br>Eingefügt: ".$eingefuegt;
echo "<br>Updates: ".$updates;
echo "<br>bereits vorhanden:      ".$dublette;
echo "<br>Fehler: ".$fehler;
echo "<br><br>";
$error_log=$error_log1.$error_log2.$error_log3.$error_log4.$error_log5.$error_log;
if($error_log=='' )
{
	echo "o.k.<br>";
}
else
{
	echo nl2br($error_log);
}
echo nl2br($ausgabe);

mail($adress, 'SYNC-Fehler StP-Abschlusspruefung von '.$_SERVER['HTTP_HOST'], $error_log,"From: nsc@fhstp.ac.at");

mail($adress, 'SYNC StP-Abschlusspruefung  von '.$_SERVER['HTTP_HOST'], "Sync Abschlussprüfung\n---------------------\n\n"
."Abschlussprüfung: Gesamt: ".$anzahl_person_gesamt." / Eingefügt: ".$eingefuegt." / Updates: ".$updates." / Fehler: ".$fehler." / Doppelt: ".$dublette
."\n\nBeginn: ".$start."\nEnde:   ".date("d.m.Y H:i:s")."\n\n".$ausgabe, "From: nsc@fhstp.ac.at");


?>
</body>
</html>