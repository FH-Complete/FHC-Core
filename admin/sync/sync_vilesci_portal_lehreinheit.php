<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Synchronisiert die Lehreinheit von Vilesci DB in PORTAL DB
 * Ablauf:
 
 LEHREINHEIT IST BEREITS EINGEFUEGT (IN SYNCTAB)?
 	JA
 		//update ist nicht implementiert
 	NEIN
 		GLEICHE LEHREINHEIT MIT ANDERER GRUPPE (ABER GLEICHER LEKTOR) BEREITS VORHANDEN?
 			JA
 				ZU SYNCTAB HINZUFUEGEN
 				GRUPPE HINZUFUEGEN
 			NEIN
 				LEHREINHEIT ANLEGEN
 				ZU SYNCTAB HINZUFUEGEN
 				GRUPPE HINZUFUEGEN
 				LEKTOR HINZUFUEGEN
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/lehreinheit.class.php');
require_once('../../include/lehreinheitgruppe.class.php');
require_once('../../include/lehreinheitmitarbeiter.class.php');
require_once('../../include/lehrverband.class.php');
require_once('../../include/gruppe.class.php');

$conn=pg_connect(CONN_STRING) or die('Connection zur Portal Datenbank fehlgeschlagen');
$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die('Connection zur Vilesci Datenbank fehlgeschlagen');

$error_log='';
$text = '';
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

function gruppezuweisen($id,$studiengang_kz, $semester, $verband, $gruppe, $einheit_kurzbz)
{
	global $error_log,$conn;
	
	if($einheit_kurzbz=='')
	{
		$lehrverband  = new lehrverband($conn);
		if(!$lehrverband->exists($studiengang_kz, $semester, $verband, $gruppe))
		{
			$lehrverband->studiengang_kz=$studiengang_kz;
			$lehrverband->semester=$semester;
			$lehrverband->verband=$verband;
			$lehrverband->gruppe=$gruppe;
			if($lehrverband->save())
				$error=false;
			else 
			{
				$error_log .=$lehrverband->errormsg."\n";
				$error=true;
			}
		}
		else 
			$error=false;
	}
	else 
	{
		$gruppe2 = new gruppe($conn);
		if(!$gruppe2->exists(strtoupper($einheit_kurzbz)))
		{
			$gruppe2->studiengang_kz=$studiengang_kz;
			$gruppe2->semester=$semester;
			$gruppe2->bezeichnung='';
			$gruppe2->typ='';
			$gruppe2->sichtbar=false;
			$gruppe2->aktiv=false;
			$gruppe2->gruppe_kurzbz=strtoupper($einheit_kurzbz);
			$gruppe2->mailgrp=false;
			if($gruppe2->save(true))
				$error=false;
			else 
			{
				$error_log.=$gruppe2->errormsg."\n";
				$error=true;
			}
		}
		else
			$error=false;
	}
	
	if(!$error)
	{
		//Gruppe Zuweisen
		$gruppe1 = new lehreinheitgruppe($conn);
		$gruppe1->lehreinheit_id = $id;
		$gruppe1->studiengang_kz = $studiengang_kz;
		$gruppe1->semester = $semester;
		$gruppe1->verband = $verband;
		$gruppe1->gruppe = $gruppe;
		$gruppe1->gruppe_kurzbz = strtoupper($einheit_kurzbz);
		if($gruppe1->save(true))
			return true;
		else 
		{
			$error_log .= $gruppe1->errormsg."\n";
			return false;
		}
	}
	else 
		return false;
}
// ***********************************
// * VILESCI->PORTAL - Synchronisation
// ***********************************
$error = false;

$qry = 'SELECT * FROM tbl_lehrveranstaltung';

if($result = pg_query($conn_vilesci, $qry))
{
	$text.="\n Sync Lehreinheiten\n\n";
	
	//Schauen ob Sync table vorhanden ist
	$qry = "SELECT 1 FROM public.tbl_synclehreinheit;";
	if(!pg_query($conn,$qry))
	{
		//Die synctabelle wird benoetigt um die Verbindung der LehrveranstaltungsID(Vilesci) und der
		//LehreinheitID(Portal) Festzuhalten um beim Syncro vom Stundenplan die richitge Zuordnung zu haben.
		//-> kann nicht ueber ext_id vorgenommen werden da manche Lehrveranstaltungen keinen eigenen
		//   Lehreinheiteneintrag haben sondern nur die Gruppe zu einer anderen Lehreinheit hinzugefuegt wird.
		//Sync Table anlegen
		$qry = "CREATE TABLE public.tbl_synclehreinheit(
		          lehrveranstaltung_id_vilesci integer,
		          lehreinheit_id_portal integer,
		          PRIMARY KEY(lehrveranstaltung_id_vilesci, lehreinheit_id_portal)
		        );";
		if(!pg_query($conn,$qry))
		{
			$error=true;
			$error_log = 'SyncTable konnte nicht erstellt werden';
			$anzahl_fehler++;
		}
	}
	
	if(!$error)
	{
		while($row = pg_fetch_object($result))
		{
			$error=false;
			$lehreinheit = new lehreinheit($conn);
			//Nachschauen ob diese Lehreinheit bereits synchronisiert wurde
			$qry = "SELECT lehreinheit_id_portal FROM public.tbl_synclehreinheit WHERE lehrveranstaltung_id_vilesci='".addslashes($row->lehrveranstaltung_id)."'";
			
			if($result1=pg_query($conn, $qry))
			{
				if(pg_num_rows($result1)>0) //Lehreinheit ist bereits vorhanden
				{
					//BereitsSyncronisiert
					//WORKING
				}
				else //Lehreinheit neu anlegen
				{					
					$lehreinheit->ext_id = $row->lehrveranstaltung_id;
					//Lehrveranstaltungsnummer aus LF ermitteln
					$qry = "SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung WHERE ext_id='".addslashes($row->lehrfach_nr)."'";
					if($row1 = pg_fetch_object(pg_query($conn, $qry)))
					{
						//Wenn alles gleich ist ausser die Gruppe dann wird nur die gruppe zur LE hinzugefuegt
						$qry = "SELECT * FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehreinheitmitarbeiter using(lehreinheit_id) WHERE 
						          lehrveranstaltung_id='$row1->lehrveranstaltung_id' AND
						          studiensemester_kurzbz='$row->studiensemester_kurzbz' AND
						          lehrfach_id='$row->lehrfach_nr' AND
						          lehrform_kurzbz='$row->lehrform_kurzbz' AND
						          stundenblockung='$row->stundenblockung' AND
						          wochenrythmus='$row->wochenrythmus' AND
						          start_kw ".($row->start_kw!=''?"='$row->start_kw'":'is null')." AND
						          raumtyp='$row->raumtyp' AND
						          raumtypalternativ='$row->raumtypalternativ' AND
						          unr ".($row->unr!=''?"='$row->unr'":'is null')." AND
						          lvnr ".($row->lvnr!=''?"='$row->lvnr'":'is null');
						if($result2 = pg_query($conn,$qry))
						{
							if(pg_num_rows($result2)>0)
							{
								//Lehreinheit vorhanden. Es muss nur noch der Gruppeneintrag eingetragen werden
								if($row_val = pg_fetch_object($result2))
								{	
									if(gruppezuweisen($row_val->lehreinheit_id, $row->studiengang_kz, $row->semester,$row->verband, $row->gruppe, $row->einheit_kurzbz))
									{
										$qry = "INSERT INTO public.tbl_synclehreinheit(lehrveranstaltung_id_vilesci, lehreinheit_id_portal) 
										        VALUES('".$row->lehrveranstaltung_id."','".$row_val->lehreinheit_id."');";
										if(pg_query($conn,$qry))
										{
											$anzahl_eingefuegt++;
										}
									}
									else 
									{										
										$anzahl_fehler++;
									}
								}
								else 
								{
									$error_log .= 'Fehler beim Select: '.$qry."\n";
									$anzahl_fehler++;
								}
							}
							else 
							{
								//Neue Lehreinheit anlegen
								$lehreinheit->lehrveranstaltung_id = $row1->lehrveranstaltung_id;
								$lehreinheit->studiensemester_kurzbz = $row->studiensemester_kurzbz;
								$lehreinheit->lehrfach_id = $row->lehrfach_nr;
								$lehreinheit->lehrform_kurzbz = $row->lehrform_kurzbz;
								$lehreinheit->stundenblockung = $row->stundenblockung;
								$lehreinheit->wochenrythmus = $row->wochenrythmus;
								$lehreinheit->start_kw = $row->start_kw;
								$lehreinheit->raumtyp = $row->raumtyp;
								$lehreinheit->raumtypalternativ = $row->raumtypalternativ;
								$lehreinheit->lehre = true;
								$lehreinheit->anmerkung = $row->anmerkung;
								$lehreinheit->unr = $row->unr;
								$lehreinheit->lvnr = $row->lvnr;
								$lehreinheit->sprache = 'German';
								$lehreinheit->updateamum = '';
								$lehreinheit->updatevon = '';
								$lehreinheit->insertamum = '';
								$lehreinheit->insertvon = '';
								$lehreinheit->ext_id = '';
								
								//Datensatz Speichern
								pg_query($conn,'BEGIN');
								
								if(!$lehreinheit->save(true))
								{
									$error_log .= $lehreinheit->errormsg."\n";
									$anzahl_fehler++;									
								}
								else
								{
									//ID aus der Sequenz auslesen
									$qry = "SELECT currval('lehre.tbl_lehreinheit_lehreinheit_id_seq') as id";
									if($row_val = pg_fetch_object(pg_query($conn, $qry)))
									{
										//Beide IDS in die SyncTab einfuegen
										$qry = "INSERT INTO public.tbl_synclehreinheit(lehrveranstaltung_id_vilesci, lehreinheit_id_portal) 
										        VALUES('".$row->lehrveranstaltung_id."','".$row_val->id."');";
										if(pg_query($conn,$qry))
										{
											if(gruppezuweisen($row_val->id, $row->studiengang_kz, $row->semester, $row->verband, $row->gruppe, $row->einheit_kurzbz))
											{
												//Lektor Zuweisen
												$lektor = new lehreinheitmitarbeiter($conn);
												$lektor->lehreinheit_id = $row_val->id;
												$lektor->mitarbeiter_uid = $row->lektor;
												$lektor->semesterstunden = $row->semesterstunden;
												$lektor->planstunden = $row->semesterstunden;
												$lektor->lehrfunktion_kurzbz ='lektor';
												$lektor->stundensatz = '';
												$lektor->faktor = 1;
												$lektor->anmerkung = '';
												$lektor->ext_id = $row->fas_id;
													
												if($lektor->save(true))
												{
													pg_query($conn,'COMMIT');
													$anzahl_eingefuegt++;
												}
												else 
												{
													pg_query($conn,'ROLLBACK');
													$error_log .= $lektor->errormsg."\n";
													$anzahl_fehler++;
												}
											}
											else 
											{
												$anzahl_fehler++;
												pg_query($conn,'ROLLBACK');
												
											}											
										}
										else
										{
											pg_query($conn,'ROLLBACK');
											$anzahl_fehler++;
											$error_log .='Fehler beim Insert in die SyncTab '.$qry."\n";
										}
									}
									else 
									{
										pg_query($conn,'ROLLBACK');
										$anzahl_fehler++;
										$error_log .= 'Fehler beim Auslesen der Sequence: '.$qry."\n";
									}
								}
							}
						}
						else
						{
							$anzahl_fehler++;
							$error_log.='Fehler beim Select: '.$qry."\n";
						}
					}
					else
					{
						$error_log .= "Lehrveranstaltungsnummer zu Lehrfach $row->lehrfach_nr konnte nicht ermittelt werden\n";
						$anzahl_fehler++;
						$error=true;
					}
				}
			}
			else
				$error_log .= "Fehler beim Auslesen der Lehreinheiten: $qry\n";
		}
	}
}
else
	$error_log .= "Lehrformen konnten nicht geladen werden\n";
$text.="Anzahl aktualisierte Datensaetze: $anzahl_eingefuegt\n";
$text.="Anzahl der Fehler: $anzahl_fehler\n";
?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - Lehreinheiten</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

echo nl2br($text);
echo nl2br($error_log);

?>
</body>
</html>