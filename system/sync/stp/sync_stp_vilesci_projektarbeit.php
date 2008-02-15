<?php
/* Copyright (C) 2008 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Projektarbeitsdatensaetze von FAS DB in PORTAL DB
//*
//*
//* setzt voraus: tbl_raumtyp, tbl_fachbereich, tbl_betreuerart
//* benoetigt: tbl_syncperson, tbl_studiengang

require_once('sync_config.inc.php');
require_once('../../../include/functions.inc.php');

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
$error_log2='';
$error_log3='';
$error_log4='';
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
$fehler1=0;
$fehler2=0;
$updates=0;
$dublette=0;
$plausi='';
$start='';
$ende='';
$anzahl_person_gesamt=0;
$anzahl_person_gesamt2=0;
$projekttyp_kurzbz='';
$studiensemester_kurzbz='';
$projektarbeit_id='';
$b1angelegt=0;
$b2angelegt=0;

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>
<html>
<head>
<title>Synchro - StPoelten -> Vilesci - Projektabeiten</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

//*********** Neue Daten holen *****************
$qry="SELECT chthema, chthemaengl, _cxbeurteilungsstufediplarbeit, COALESCE(dapruefungsdat,dapruefteil1dat) as pruefdat, _personlb, _personlb2, 
	__person, _cxgeschlecht, chtitel, chvorname, chnachname, studiengang_kz,  tbl_studiengang.typ, max_semester FROM sync.stp_person 
	JOIN sync.stp_stgvertiefung ON (_stgvertiefung=__stgvertiefung)
	JOIN public.tbl_studiengang ON (_studiengang=ext_id) 
	WHERE __Person IN (SELECT __person FROM sync.tbl_syncperson) AND (_cxPersonTyp='1' OR _cxPersonTyp='2') 
		AND chthema IS NOT NULL AND chthema!='' 
		AND COALESCE(dapruefungsdat,dapruefteil1dat) IS NOT NULL;";
//$error_log="Überprüfung Projektarbeitsdaten in EXT-DB:\n\n";
$start=date("d.m.Y H:i:s");
echo $start."<br>";
if($result = pg_query($conn, $qry))
{
	$anzahl_person_gesamt=pg_num_rows($result);
	$error_log_ext.="Anzahl der Datensätze: ".$anzahl_person_gesamt."\n";
	echo nl2br($error_log_ext);
	while($row=pg_fetch_object($result))
	{
		if($row->typ=='d' || $row->typ=='m')
		{
			$projekttyp_kurzbz='Diplom';
			$kurzbz='DA';
			$bezeichnung_engl='master´s thesis';
		}
		else
		{
			$projekttyp_kurzbz='Bachelor';
			$kurzbz='BA';
			$bezeichnung_engl='bachelor´s thesis';
		}
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
				$error_log1.="\nStudent ".$row->__person." (ext_id) ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname)." in tbl_student nicht gefunden!";
				$fehler++;
				pg_query($conn, "ROLLBACK");
				continue;
			}
		}
		//LEHRVERANSTALTUNG, LEHRFACH UND LEHREINHEIT ANLEGEN BZW. AUSWÄHLEN
		$qry_lv="SELECT * FROM lehre.tbl_lehrveranstaltung WHERE studiengang_kz=".myaddslashes($row->studiengang_kz)." AND bezeichnung=".myaddslashes($projekttyp_kurzbz.'arbeit')." AND semester=".myaddslashes($row->max_semester);
		if($result_lv = pg_query($conn, $qry_lv))
		{
			if (pg_num_rows($result_lv)>0)
			{
				//LV vorhanden
				if($row_lv=pg_fetch_object($result_lv))
				{
					$lehrveranstaltung_id=$row_lv->lehrveranstaltung_id;
				}
			}
			else 
			{
				//LV anlegen
				$ins_lv="INSERT INTO lehre.tbl_lehrveranstaltung (kurzbz, bezeichnung, studiengang_kz, semester, sprache, ects, semesterstunden, anmerkung, lehre, lehreverzeichnis,
				aktiv, planfaktor, planlektoren, planpersonalkosten, plankostenprolektor, koordinator, sort, zeugnis, projektarbeit, lehrform_kurzbz, bezeichnung_english, 
				insertamum, insertvon, updateamum, updatevon, ext_id) VALUES (".
				myaddslashes($kurzbz).", ".
				myaddslashes($projekttyp_kurzbz.'arbeit').", ".
				myaddslashes($row->studiengang_kz).", ".
				myaddslashes($row->max_semester).", ".
				"'German', ".
				myaddslashes(24).", ".
				myaddslashes(0).", 
				NULL, 
				TRUE, ".
				myaddslashes(strtolower($kurzbz)).", 
				
				TRUE, ".
				myaddslashes(1).", ".
				myaddslashes(0).", ".
				myaddslashes(0).", ".
				myaddslashes(0).", 
				NULL, 
				NULL, 
				TRUE, 
				TRUE, 
				'BE', ".
				myaddslashes($bezeichnung_engl).", 
				
				now(), 'sync', NULL, NULL, NULL);";
				if(pg_query($conn, $ins_lv))
				{
					echo "<br>Lehrveranstaltung ".$projekttyp_kurzbz.'arbeit, '.$row->max_semester.".Semester in Studiengang ".$row->studiengang_kz." angelegt!";
					//Sequenz auslesen
					$qry_seq = "SELECT currval('lehre.tbl_lehrveranstaltung_lehrveranstaltung_id_seq') AS id;";
					if($row_seq=pg_fetch_object(pg_query($conn,$qry_seq)))
					{
						$lehrveranstaltung_id=$row_seq->id;
					}
					else
					{
						$fehler++;
						pg_query($conn, "ROLLBACK");
						$error_log.='LV-Sequence konnte nicht ausgelesen werden\n';
						continue;
					}
					
				}
				else 
				{
					pg_query($conn, "ROLLBACK");
					exit("<br>Konnte Lehrveranstaltung nicht anlegen!<br>".$ins_lv);
				}
			}
		}
		//Lehrfach anlegen bzw. aufrufen
		$qry_lf="SELECT * FROM lehre.tbl_lehrfach WHERE studiengang_kz=".myaddslashes($row->studiengang_kz)." AND bezeichnung=".myaddslashes($projekttyp_kurzbz.'arbeit')." AND semester=".myaddslashes($row->max_semester).";";
		if($result_lf = pg_query($conn, $qry_lf))
		{
			if (pg_num_rows($result_lf)>0)
			{
				//LF vorhanden
				if($row_lf=pg_fetch_object($result_lf))
				{
					$lehrfach_id=$row_lf->lehrfach_id;
				}
			}
			else 
			{
				//LF anlegen
				$ins_lf="INSERT INTO lehre.tbl_lehrfach(studiengang_kz, fachbereich_kurzbz, kurzbz, bezeichnung, farbe, aktiv, semester, sprache,
				insertamum, insertvon, updateamum, updatevon, ext_id) VALUES (".
				myaddslashes($row->studiengang_kz).", ".
				myaddslashes('Dummy').", ".
				myaddslashes($kurzbz).", ".
				myaddslashes($projekttyp_kurzbz.'arbeit').", 
				NULL, 
				TRUE, ".
				myaddslashes($row->max_semester).", 
				'German', 
				now(), 'sync', NULL, NULL, NULL);";
				");";
				if(pg_query($conn, $ins_lf))
				{
					echo "<br>Lehrfach ".$projekttyp_kurzbz.'arbeit'.", Studiengang ".$row->studiengang_kz." im Semester ".$row->max_semester." angelegt!";
					//Sequenz auslesen
					$qry_seq = "SELECT currval('lehre.tbl_lehrfach_lehrfach_id_seq') AS id;";
					if($row_seq=pg_fetch_object(pg_query($conn,$qry_seq)))
					{
						$lehrfach_id=$row_seq->id;
					}
					else
					{
						$fehler++;
						$error_log.='LF-Sequence konnte nicht ausgelesen werden\n';
						pg_query($conn, "ROLLBACK");
						continue;
					}						
				}
				else 
				{
					pg_query($conn, "ROLLBACK");
					exit("<br>Konnte Lehrfach nicht anlegen!<br>".$ins_lf);
				}
			}
		}
			
			
		//Lehreinheit anlegen bzw. aufrufen
		$studiensemester_kurzbz=getStudiensemesterFromDatum($conn, $row->pruefdat, false);
		$qry_le="SELECT * FROM lehre.tbl_lehreinheit WHERE lehrveranstaltung_id=".myaddslashes($lehrveranstaltung_id)." AND studiensemester_kurzbz=".myaddslashes($studiensemester_kurzbz);
		if($result_le = pg_query($conn, $qry_le))
		{
			if (pg_num_rows($result_le)>0)
			{
				//LE vorhanden
				if($row_le=pg_fetch_object($result_le))
				{
					$lehreinheit_id=$row_le->lehreinheit_id;
				}
			}
			else 
			{
				//LE anlegen
				$ins_le="INSERT INTO lehre.tbl_lehreinheit (lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id, lehrform_kurzbz, stundenblockung,
				wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, anmerkung, unr, lvnr,
				insertamum, insertvon, updateamum, updatevon, ext_id) VALUES (".
				myaddslashes($lehrveranstaltung_id).", ".
				myaddslashes($studiensemester_kurzbz).", ".
				myaddslashes($lehrfach_id).", ".
				myaddslashes('0').", ".
				myaddslashes(1).", ".
				myaddslashes(1).", 
				NULL, ".
				myaddslashes('Dummy').", ".
				myaddslashes('Dummy').", ".
				myaddslashes('German').", 
				TRUE, 
				NULL, 
				NULL, 
				NULL, 
				
				now(), 'sync', NULL, NULL, NULL);";
				");";
				if(pg_query($conn, $ins_le))
				{
					echo "<br>Lehreinheit für LV ".$lehrveranstaltung_id." im Studiensemester ".$studiensemester_kurzbz." angelegt!";
					//Sequenz auslesen
					$qry_seq = "SELECT currval('lehre.tbl_lehreinheit_lehreinheit_id_seq') AS id;";
					if($row_seq=pg_fetch_object(pg_query($conn,$qry_seq)))
					{
						$lehreinheit_id=$row_seq->id;
					}
					else
					{
						$fehler++;
						$error_log.='LE-Sequence konnte nicht ausgelesen werden\n';
						pg_query($conn, "ROLLBACK");
						continue;
					}						
				}
				else 
				{
					pg_query($conn, "ROLLBACK");
					$fehler++;
					exit("<br>Konnte Lehreinheit nicht anlegen!<br>".$ins_le);
				}
				
			}
		}
		
		
		// Check auf Doppelgaenger
		$qry_dubel="SELECT * FROM lehre.tbl_projektarbeit WHERE student_uid=".myaddslashes($uid)." AND titel=".myaddslashes($row->chthema).";";
		if($result_dubel = pg_query($conn, $qry_dubel))
		{
			if (pg_num_rows($result_dubel)==0)
			{
				//Neue Abschlussprüfung anlegen
				$sql="INSERT INTO lehre.tbl_projektarbeit
					(projekttyp_kurzbz, titel, lehreinheit_id, student_uid, firma_id, note, punkte, beginn, ende, faktor, 
					freigegeben, gesperrtbis, stundensatz, gesamtstunden, themenbereich, anmerkung,  
					insertamum,insertvon,updateamum,updatevon, ext_id)
					VALUES (".
					myaddslashes($projekttyp_kurzbz).", ".
					myaddslashes($row->chthema).", ".
					myaddslashes($lehreinheit_id).", ".
					myaddslashes($uid).", 
					NULL, ".
					myaddslashes($row->_cxbeurteilungsstufediplarbeit).", 
					NULL, 
					NULL, ".
					myaddslashes($row->pruefdat).", 
					NULL, NULL, NULL, NULL, NULL, NULL, NULL, 
					now(), 'sync', NULL, NULL, ".
					myaddslashes($row->__person).");";
				if(!$result_neu = pg_query($conn, $sql))
				{
					$fehler++;
					$error_log.= $sql."\n<strong>".pg_last_error($conn)." </strong>\n";
					pg_query($conn, "ROLLBACK");
					continue;
				}
				else
				{
					$ausgabe.="\n------------------------------------\nÜbertragen: ".$row->__person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname);
					$ausgabe.="\n---Projektarbeit (".$projekttyp_kurzbz."): Note: ".$row->_cxbeurteilungsstufediplarbeit.", Titel: ".$row->chthema;
					$eingefuegt++;
					pg_query($conn, "COMMIT");
					//Sequenz auslesen
					$qry_seq = "SELECT currval('lehre.tbl_projektarbeit_projektarbeit_id_seq') AS id;";
					if($row_seq=pg_fetch_object(pg_query($conn,$qry_seq)))
					{
						$projektarbeit_id=$row_seq->id;
					}
					else
					{
						$fehler++;
						$error_log.='Projektarbeit-Sequence konnte nicht ausgelesen werden\n';
						pg_query($conn, "ROLLBACK");
						continue;
					}	
				}
			}
			else
			{
				if($row_dubel=pg_fetch_object($result_dubel))
				{
					//Update?
					$sql='';
					if($row_dubel->projekttyp_kurzbz!=$projekttyp_kurzbz && $projekttyp_kurzbz!=NULL && $projekttyp_kurzbz!='')
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", projekttyp_kurzbz=".myaddslashes($projekttyp_kurzbz);
						}
						else 
						{
							$sql="projekttyp_kurzbz=".myaddslashes($projekttyp_kurzbz);
						}
					}
					if($row_dubel->lehreinheit_id!=$lehreinheit_id && $lehreinheit_id!=NULL && $lehreinheit_id!='')
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", lehreinheit_id=".myaddslashes($lehreinheit_id);
						}
						else 
						{
							$sql="lehreinheit_id=".myaddslashes($lehreinheit_id);
						}
					}
					if($row_dubel->note!=$row->_cxbeurteilungsstufediplarbeit && $row->_cxbeurteilungsstufediplarbeit!=NULL && $row->_cxbeurteilungsstufediplarbeit!='')
					{
						if(strlen(trim($sql))>0)
						{
							$sql.=", note=".myaddslashes($row->_cxbeurteilungsstufediplarbeit);
						}
						else 
						{
							$sql="note=".myaddslashes($row->_cxbeurteilungsstufediplarbeit);
						}
					}
					
					
					if(strlen(trim($sql))==1)
					{
						//update nur mit änderungen 
						$sql="UPDATE lehre.tbl_projektarbeit SET ".$sql." 
						WHERE student_uid='".$uid."' AND titel='".$row->chthema."';";
						if(!$result_neu = pg_query($conn, $sql))
						{
							$fehler++;
							$error_log.= "\n".$sql."\n<strong>".pg_last_error($conn)." </strong>\n";
							pg_query($conn, "ROLLBACK");
							continue;
						}
						else
						{
							$ausgabe.="\n------------------------------------\nGeändert: ".$row->__person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname);
							$ausgabe.="\n---Projektarbeit (".$projekttyp_kurzbz."): Note: ".$row->_cxbeurteilungsstufediplarbeit.", Titel: ".$row->chthema;
							$updates++;
							pg_query($conn, "COMMIT");
						}
					}
					else 
					{
						//kein update da bereits gleich vorhanden
						$dublette++;
					}
					$projektarbeit_id=$row_dubel->projektarbeit_id;
				}
			}
		}
		else
		{
			$fehler++;
			$error_log.= "\n".$sql."\n<strong>".pg_last_error($conn)." </strong>\n";
			pg_query($conn, "ROLLBACK");
			continue;
		}
		//Betreuer anlegen _personlb, _personlb2
		if($row->_personlb==NULL || $row->_personlb=='')
		{
			$betreuer1=NULL;
		}
		else
		{
			$qry_synk="SELECT * FROM sync.tbl_syncperson where __person=".myaddslashes($row->_personlb).";";
			if($result_synk=pg_query($conn, $qry_synk))
			{
				if($row_synk=pg_fetch_object($result_synk))
				{
					$betreuer1=$row_synk->person_id;
				}
				else 
				{
					$qry_err="SELECT chtitel, chvorname, chnachname FROM sync.stp_person 
						WHERE __person=".myaddslashes($row->_personlb).";";
					if($result_err=pg_query($conn,$qry_err))
					{
						if($row_err=pg_fetch_object($result_err))
						{
							$error_log3.="\nBetreuer1 ".$row->_personlb.", ".trim($row_err->chtitel)." ".trim($row_err->chnachname).", ".trim($row_err->chvorname)." in tbl_syncperson nicht gefunden!";
						}
						else 
						{
							$error_log3.="\nBetreuer1 ".$row->_personlb." in tbl_syncperson nicht gefunden!";						
						}
					}
					$fehler1++;
					$betreuer1=NULL;
				}
			}
		}
		if($row->_personlb2==NULL || $row->_personlb2=='')
		{
			$betreuer2=NULL;
		}
		else
		{
			$qry_synk="SELECT * FROM sync.tbl_syncperson where __person=".myaddslashes($row->_personlb2).";";
			if($result_synk=pg_query($conn, $qry_synk))
			{
				if($row_synk=pg_fetch_object($result_synk))
				{
					$betreuer2=$row_synk->person_id;
				}
				else 
				{
					$qry_err="SELECT chtitel, chvorname, chnachname FROM sync.stp_person 
						WHERE __person=".myaddslashes($row->_personlb2).";";
					if($result_err=pg_query($conn,$qry_err))
					{
						if($row_err=pg_fetch_object($result_err))
						{
							$error_log4.="\nBetreuer2 ".$row->_personlb2.", ".trim($row_err->chtitel)." ".trim($row_err->chnachname).", ".trim($row_err->chvorname)." in tbl_syncperson nicht gefunden!";
						}
						else 
						{
							$error_log4.="\nBetreuer2 ".$row->_personlb2." in tbl_syncperson nicht gefunden!";						
						}
					}
					$fehler2++;
					$betreuer2=NULL;
				}
			}
		}
		if($betreuer1!=NULL)
		{
			$qry_dubel="SELECT * FROM lehre.tbl_projektbetreuer WHERE person_id=".myaddslashes($betreuer1)." AND projektarbeit_id=".myaddslashes($projektarbeit_id).";";
			if($result_dubel = pg_query($conn, $qry_dubel))
			{
				if (pg_num_rows($result_dubel)==0)
				{
					//Betreuer1 anlegen
					$qry_ins="INSERT INTO lehre.tbl_projektbetreuer (person_id, projektarbeit_id, note, faktor, name, punkte, stundensatz, betreuerart_kurzbz, stunden, 
					insertamum, insertvon, updateamum, updatevon, ext_id) VALUES (".
					myaddslashes($betreuer1).", ".
					myaddslashes($projektarbeit_id).", 
					NULL, ".
					myaddslashes(1).", 
					NULL, 
					NULL, 
					NULL, ".
					myaddslashes('Erstbegutachter').", 
					NULL, 
					now(), 'sync', NULL, NULL, ".
					myaddslashes($row->_personlb).");";
					");";
					if(!pg_query($conn, $qry_ins))
					{
						$fehler1++;
						$error_log3.="\nBetreuer1 ".$betreuer1." konnte für Projektarbeit ID ".$projektarbeit_id." nicht eingetragen werden!";
					}
					else 
					{
						$b1angelegt++;
						$ausgabe.="\nBetreuer1 (".$betreuer1.") wurde für Projektarbeit ID ".$projektarbeit_id." angelegt.";
					}
				}
				else 
				{
					//Betreuer1 bereits vorhanden	
				}
			}
		}
		if($betreuer2!=NULL)
		{
			$qry_dubel="SELECT * FROM lehre.tbl_projektbetreuer WHERE person_id=".myaddslashes($betreuer2)." AND projektarbeit_id=".myaddslashes($projektarbeit_id).";";
			if($result_dubel = pg_query($conn, $qry_dubel))
			{
				if (pg_num_rows($result_dubel)==0)
				{
					//Betreuer2 anlegen
					$qry_ins="INSERT INTO lehre.tbl_projektbetreuer (person_id, projektarbeit_id, note, faktor, name, punkte, stundensatz, betreuerart_kurzbz, stunden, 
					insertamum, insertvon, updateamum, updatevon, ext_id) VALUES (".
					myaddslashes($betreuer2).", ".
					myaddslashes($projektarbeit_id).", 
					NULL, ".
					myaddslashes(1).", 
					NULL, 
					NULL, 
					NULL, ".
					myaddslashes('Zweitbegutachter').", 
					NULL, 
					now(), 'sync', NULL, NULL, ".
					myaddslashes($row->_personlb2).");";
					");";
					if(!pg_query($conn, $qry_ins)) 
					{
						$fehler2++;
						$error_log4.="\nBetreuer2 ".$betreuer2." konnte für Projektarbeit ID ".$projektarbeit_id." nicht eingetragen werden!";
					}
					else
					{
						$b2angelegt++;
						$ausgabe.="\nBetreuer2 (".$betreuer2.") wurde für Projektarbeit ID ".$projektarbeit_id." angelegt.";
					}
				}
				else 
				{
					//Betreuer2 bereits vorhanden	
				}
			}
		}
	}
}
else
{
	$fehler++;
	echo "<br>".$qry."<br><strong>".pg_last_error($conn)." </strong><br>";
}
echo "<br><br><b>Projektarbeiten:</b>";
echo "<br>Eingefügt: ".$eingefuegt;
echo "<br>Updates: ".$updates;
echo "<br>bereits vorhanden:      ".$dublette;
echo "<br>Fehler: ".$fehler;
echo "<br>-----------------------------------";
echo "<br>Betreuer1: ".$b1angelegt." / Fehler: ".$fehler1;
echo "<br>Betreuer2: ".$b2angelegt." / Fehler: ".$fehler2;
$ende=date("d.m.Y H:i:s");
echo "<br><br>".$ende."<br>";
echo "<br>";
$error_log=$error_log1.$error_log2."\n--------------\n".$error_log3.$error_log4."\n--------------\n".$error_log;
if($error_log=='' )
{
	echo "o.k.<br>";
}
else
{
	echo nl2br($error_log);
}
echo nl2br("\n\n".$ausgabe);

mail($adress, 'SYNC-Fehler StP-Projektarbeit von '.$_SERVER['HTTP_HOST'], $error_log,"From: nsc@fhstp.ac.at");

mail($adress, 'SYNC StP-Projektarbeit  von '.$_SERVER['HTTP_HOST'], "Sync Projektarbeit\n------------------\n\n"
."Projektarbeiten: Gesamt: ".$anzahl_person_gesamt." / Eingefügt: ".$eingefuegt." / Updates: ".$updates." / Fehler: ".$fehler." / Bereits vorhanden: ".$dublette
."\n\nBeginn: ".$start."\nEnde:   ".date("d.m.Y H:i:s")."\n\n".$ausgabe, "From: nsc@fhstp.ac.at");


?>
</body>
</html>