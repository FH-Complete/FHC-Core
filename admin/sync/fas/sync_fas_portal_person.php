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

//set_time_limit(60);

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
?>
<?php
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
		$person->anzahlkinder=$row->anzahlderkinder;
		$person->staatsbuergerschaft=$row->staatsbuergerschaft;
		$person->geschlecht=strtolower($row->geschlecht);
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
				$qry="SELECT person_fas, person_portal FROM public.tbl_syncperson WHERE person_fas='$row->person_pk'";
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
						$qry="SELECT person_id FROM public.tbl_person 
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
									//echo nl2br("update3 von ".$row->uid.", ".$row->familienname."\n");
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
					$qryz="SELECT person_fas FROM tbl_syncperson WHERE person_fas='$row->person_pk' AND person_portal='$person->person_id'";
					if($resultz = pg_query($conn, $qryz))
					{
						if(pg_num_rows($resultz)==0) //wenn dieser eintrag noch nicht vorhanden ist
						{
							$qry='INSERT INTO tbl_syncperson (person_fas, person_portal)'.
								'VALUES ('.$row->person_pk.', '.$person->person_id.');';
							pg_query($conn, $qry);
						}
					}
					$anzahl_eingefuegt++;
					echo "- ";
					ob_flush();
					flush();
				}
			}
			else 
			{
				$anzahl_fehler++;
			}
		}
	}
	echo nl2br("abgeschlossen\n\n");
}
else
	$error_log .= 'Personendatensaetze konnten nicht geladen werden';
	


//echo nl2br($text);
echo nl2br("\n".$error_log);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");

?>
</body>
</html>