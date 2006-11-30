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
 * Synchronisiert Studentendatensaetze von Vilesci DB in PORTAL DB
 *
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/fas/person.class.php');
require_once('../../include/fas/benutzer.class.php');
require_once('../../include/fas/student.class.php');

$conn=pg_connect(CONN_STRING) or die('Connection zur Portal Datenbank fehlgeschlagen');
$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die('Connection zur Vilesci Datenbank fehlgeschlagen');

$error_log='';
$text = '';
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

// ***********************************
// * VILESCI->PORTAL - Synchronisation
// ***********************************

//Studenten
$qry = "SELECT * FROM tbl_person JOIN tbl_student USING(uid) WHERE uid NOT LIKE '\_dummy%' AND uid NOT LIKE '\_Dummy%'";

if($result = pg_query($conn_vilesci, $qry))
{
	$text.="\n Sync Studenten\n\n";
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$student = new student($conn);
		
	
		$student->sprache='German';
		$student->anrede='';
		$student->titelpost='';
		$student->titelpre=$row->titel;
		$student->nachname=$row->nachname;
		if(!$len=strpos($row->vornamen,' '))
		{
			$student->vorname=$row->vornamen;
			$student->vornamen='';
		}
		else
		{
			$student->vorname=substr($row->vornamen,0,$len);
			$student->vornamen=substr($row->vornamen,$len+1,strlen($row->vornamen));
		}
		$student->gebdatum=$row->gebdatum;
		$student->gebort=$row->gebort;
		$student->gebzeit=$row->gebzeit;
		$student->foto='';
		$student->anmerkungen=$row->anmerkungen;
		$student->homepage=$row->homepage;
		$student->svnr='';
		$student->ersatzkennzeichen='';
		$student->familienstand='';
		$student->anzahlkinder='';
		$student->aktiv=($row->aktiv=='t'?true:false);
		$student->insertvon='';
		$student->insertamum='';
		$student->updateamum=$row->updateamum;
		$student->updatevon=$row->updatevon;
		$student->ext_id='';
		
		$student->uid=$row->uid;
		$student->bnaktiv=$row->aktiv;
		$student->alias=$row->alias;
		
		$student->matrikelnr=$row->matrikelnr;
		$student->prestudent_id='';
		$student->studiengang_kz=$row->studiengang_kz;
		$student->semester=$row->semester;
		$student->verband=$row->verband;
		$student->gruppe=$row->gruppe;
		
		$qry = "SELECT person_id FROM tbl_benutzer WHERE uid='$row->uid'";
		if($result1 = pg_query($conn, $qry))
		{		
			if(pg_num_rows($result1)>0) //wenn dieser eintrag schon vorhanden ist
			{
				if($row1=pg_fetch_object($result1))
				{
					//Mitarbeiterdaten updaten
					$student->new=false;
					$student->person_id=$row1->person_id;
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
				$student->new=true;
			}
			
			if(!$error)
				if(!$student->save())
				{
					$error_log.=$student->errormsg."\n";
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
else
	$error_log .= "Studentendatensaetze konnten nicht geladen werden\n";
$text.="Anzahl aktualisierte Datensaetze: $anzahl_eingefuegt\n";
$text.="Anzahl der Fehler: $anzahl_fehler\n";
?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - Studenten</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

echo nl2br($text);
echo nl2br($error_log);

?>
</body>
</html>