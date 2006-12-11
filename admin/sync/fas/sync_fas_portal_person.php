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

//*
//* Synchronisiert Personendatensaetze von FAS DB in PORTAL DB
//*
//*

include('../../../vilesci/config.inc.php');
include('../../../include/person.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
//$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die("Connection zur Vilesci Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>
<html>
<head>
<title>Synchro - FAS -> Portal - Person</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
//person
$qry = "SELECT * FROM person";
if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Person Sync\n-------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$person=new person($conn);
		$person->geburtsnation=$row->gebnation;
		$person->anrede=$row->anrede;
		$person->titelpost=$row->postnomentitel;
		$person->titelpre=$row->titel;
		$person->nachname=$row->familienname;
		$person->vorname=$row->vorname;
		$person->vornamen=$row->vornamen;
		$person->gebdatum=$row->gebdat;
		$person->gebort=$row->gebort;
		$person->anmerkungen=$row->bemerkung;
		$person->svnr=$row->svnr;
		$person->ersatzkennzeichen=$row->ersatzkennzeichen;
		$person->familienstand=$row->familienstand;
		$person->staatsbuergerschaft=$row->staatsbuergerschaft;
		$person->geschlecht=$row->geschlecht;
		$person->ext_id=$row->person_pk;
		$person->aktiv=true;
					
		if ($row->familienstand==0)
		{
			$person->familienstand=null;
		}
		elseif($row->familienstand==1)
		{
			$person->familienstand='l';
		}
		elseif($row->familienstand==2)
		{
			$person->familienstand=='v';
		}
		elseif($row->familienstand==3)
		{
			$person->familienstand=='g';
		}
		elseif($row->familienstand==4)
		{
			$person->familienstand=='w';
		}
		$error=false;
		$qry="SELECT ext_id FROM public.tbl_person WHERE ext_id='$row->person_pk'";
		if($result1 = pg_query($conn, $qry))
		{
			if(pg_num_rows($result1)>0) //wenn dieser eintrag schon vorhanden ist
			{
				if($row1=pg_fetch_object($result1))
				{
					//update
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
				//insert
				$person->new=true;

			}
			if(!$error)
				if(!$person->save())
				{
					$error_log.=$person->errormsg."\n";
					$anzahl_fehler++;
				}
				else 
					$anzahl_eingefuegt++;
			else 
				$anzahl_fehler++;
		}
	}
	echo nl2br("abgeschlossen\n\n");
}
else
	$error_log .= 'Personendatensaetze konnten nicht geladen werden';
	


//echo nl2br($text);
echo nl2br($error_log);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");

?>
</body>
</html>