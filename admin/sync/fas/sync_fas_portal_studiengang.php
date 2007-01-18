<?php
/* Copyright (C) 2007 Technikum-Wien
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
//* Synchronisiert Studiengangsdatensaetze von FAS DB in PORTAL DB
//*
//*

include('../../../vilesci/config.inc.php');
include('../../../include/studiengang.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur Vilesci Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

function validate($row)
{
}

/*************************
 * VILESCI-PORTAL - Synchronisation
 */

//studiengang
$qry = "SELECT * FROM studiengang";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Studiengang Sync\n-----------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		
		$error=false;
		$studiengang = new studiengang($conn);
		$studiengang->studiengang_kz		=$row->kennzahl;
		$studiengang->kurzbz			=$row->emailkuerzel;
		$studiengang->kurzbzlang			='';
		$studiengang->bezeichnung		=$row->name;
		$studiengang->english			=$row->program_name;
		$studiengang->typ				='';
		$studiengang->farbe			='';
		$studiengang->email			='';
		$studiengang->max_verband		='';
		$studiengang->max_semester		='';
		$studiengang->max_gruppe		='';
		$studiengang->erhalter_kz			='5';
		$studiengang->bescheid			=$row->bescheid;
		$studiengang->bescheidbgbl1		=$row->bescheidbgbl1;
		$studiengang->bescheidbgbl2		=$row->bescheidbgbl2;
		$studiengang->bescheidgz			=$row->bescheidgz;
		$studiengang->bescheidvom		=$row->bescheidvom;
		$studiengang->organisationsform		='';
		$studiengang->titelbescheidvom		=$row->titelbescheidvom;			
		$studiengang->ext_id			=$row->studiengang_pk;
		
		If($row->organisationsform=='1')
		{
			$studiengang->organisationsform='n'; //normal
		}
		If($row->organisationsform=='2')
		{
			$studiengang->organisationsform='b'; //berufsbegleitend
		}
		If($row->organisationsform=='4')
		{
			$studiengang->organisationsform='z'; //zielgruppenspezifisch
		}
		if($row->studiengangsart=='1')
		{
			$studiengang->typ='b';
		}
		if($row->studiengangsart=='2')
		{
			$studiengang->typ='m';
		}
		if($row->studiengangsart=='3')
		{
			$studiengang->typ='d';
		}
		
		$qry = "SELECT * FROM tbl_studiengang WHERE studiengang_kz='$row->kennzahl'";
		if($result1 = pg_query($conn, $qry))
		{		
			if(pg_num_rows($result1)>0) //wenn dieser eintrag schon vorhanden ist
			{
				if($row1=pg_fetch_object($result1))
				{
					//Studiengangsdaten updaten
					$studiengang->farbe			=$row1->farbe;
					$studiengang->email			=$row1->email;
					$studiengang->max_verband		=$row1->max_verband;
					$studiengang->max_semester		=$row1->max_semester;
					$studiengang->max_gruppe		=$row1->max_gruppe;
					$studiengang->kurzbzlang			=$row1->kurzbzlang;
					$studiengang->new=false;
				}
				else 
				{
					$error_log.="studiengang_kz von $row->studiengang_kz konnte nicht ermittelt werden\n";
					$error=true;
				}
			}
			else 
			{
				//Studiengang neu anlegen
				$studiengang->new=true;
			}
			
			if(!$error)
				if(!$studiengang->save())
				{
					$error_log.=$studiengang->errormsg."\n";
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
	$error_log .= 'Studiengangsdatensaetze konnten nicht geladen werden';
	
?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - Studiengang</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

//echo nl2br($text);
echo nl2br($error_log);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");

?>
</body>
</html>