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
 * Synchronisiert die Lehrverbaende von Vilesci DB in PORTAL DB
 *
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/lehrverband.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die("Connection zur Vilesci Datenbank fehlgeschlagen");

$error_log='';
$text = '';
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

// ***********************************
// * VILESCI->PORTAL - Synchronisation
// ***********************************

$qry = "SELECT studiengang_kz, semester, verband, gruppe FROM tbl_student GROUP BY studiengang_kz, semester, verband, gruppe";

if($result = pg_query($conn_vilesci, $qry))
{
	$text.="Sync der Lehrverbaende\n\n";
	while($row=pg_fetch_object($result))
	{
		$lvb_obj = new lehrverband($conn);
		
		//Lehrverbaende und uebergeordnete Lehrverbaende anlegen sofern diese noch
		//nicht existieren
		if(!$lvb_obj->exists($row->studiengang_kz, $row->semester, '', ''))
		{
			$lvb_obj->studiengang_kz = $row->studiengang_kz;
			$lvb_obj->semester = $row->semester;
			$lvb_obj->verband = ' ';
			$lvb_obj->gruppe = ' ';
			$lvb_obj->aktiv = true;
			
			if(!$lvb_obj->save())
			{
				$error_log.=$lvb_obj->errormsg."\n";
				$anzahl_fehler++;
			}
			else 
				$anzahl_eingefuegt++;
		}
		
		if(trim($row->verband)!='')
		{
			if(!$lvb_obj->exists($row->studiengang_kz, $row->semester, $row->verband, ''))
			{
				$lvb_obj->studiengang_kz = $row->studiengang_kz;
				$lvb_obj->semester = $row->semester;
				$lvb_obj->verband = $row->verband;
				$lvb_obj->gruppe = ' ';
				$lvb_obj->aktiv = true;
				
				if(!$lvb_obj->save())
				{
					$error_log.=$lvb_obj->errormsg."\n";
					$anzahl_fehler++;
				}
				else 
					$anzahl_eingefuegt++;
			}
			
			if(trim($row->gruppe)!='')
			{
				if(!$lvb_obj->exists($row->studiengang_kz, $row->semester, $row->verband, $row->gruppe))
				{					
					$lvb_obj->studiengang_kz = $row->studiengang_kz;
					$lvb_obj->semester = $row->semester;
					$lvb_obj->verband = $row->verband;
					$lvb_obj->gruppe = $row->gruppe;
					$lvb_obj->aktiv = true;
					
					if(!$lvb_obj->save())
					{
						$error_log.=$lvb_obj->errormsg."\n";
						$anzahl_fehler++;
					}
					else
						$anzahl_eingefuegt++;
				}
			}
		}
		/*
		$qry = "INSERT INTO tbl_lehrverband(studiengang_kz, semester, verband, gruppe) VALUES(
		        $row->studiengang_kz, $row->semester, '$row->verband', '$row->gruppe');";
		if(!pg_query($conn, $qry))
		{
			$error_log.= "Fehler beim einfuegen des Datensatzes: $qry";
			$anzahl_fehler++;
		}
		else 
			$anzahl_eingefuegt++;
			*/
	}
}
$text .= "Anzahl Datensaetze Vilesci: ".pg_num_rows($result)."\n";
$text .= "Anzahl eingefuegter Datensaetze: $anzahl_eingefuegt\n";
$text .= "Anzahl der Fehler: $anzahl_fehler\n";
?>
<html>
<head>
<title>Synchro - Vilesci -> Portal - Lehrverbaende</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

echo nl2br($text);
echo nl2br($error_log);

?>
</body>
</html>