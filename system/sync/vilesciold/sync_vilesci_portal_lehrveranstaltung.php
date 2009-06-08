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
 * Synchronisiert Lehrveranstaltungsdatensaetze von Vilesci DB in PORTAL DB
 *
 */
include('../../vilesci/config.inc.php');
include('../../include/lehrveranstaltung.class.php');
include('../../include/fachbereich.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die("Connection zur Vilesci Datenbank fehlgeschlagen");

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

//Lehrveranstaltung
$qry = "SELECT * FROM tbl_lehrfach";

if($result = pg_query($conn_vilesci, $qry))
{
	$anzahl_quelle=pg_num_rows($result);
	$text.="Lehrveranstaltung Sync\n-----------------------\n";
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$lehrveranstaltung = new lehrveranstaltung($conn);
		$lehrveranstaltung->studiengang_kz	=$row->studiengang_kz;
		$lehrveranstaltung->bezeichnung		=$row->bezeichnung;
		$lehrveranstaltung->kurzbz			=$row->kurzbz;
		$lehrveranstaltung->semester		=$row->semester;
		$lehrveranstaltung->ects			=$row->ects;
		$lehrveranstaltung->semesterstunden	=0;
		$lehrveranstaltung->anmerkung		='';
		$lehrveranstaltung->lehre			=($row->lehre=='t'?true:false);
		$lehrveranstaltung->lehreverzeichnis	=$row->lehrevz;
		$lehrveranstaltung->aktiv			=($row->aktiv=='t'?true:false);
		$lehrveranstaltung->planfaktor		='1.0';
		$lehrveranstaltung->planlektoren		='1';
		$lehrveranstaltung->planpersonalkosten	='80';
		$lehrveranstaltung->sprache = ($row->sprache!=''?$row->sprache:'German');
		//$lehrveranstaltung->insertamum		='';
		$lehrveranstaltung->insertvon		='SYNC';
		//$lehrveranstaltung->updateamum	='';
		//$lehrveranstaltung->updatevon		=$row->updatevon;
		$lehrveranstaltung->ext_id			=$row->lehrfach_nr;
		//$lehrveranstaltung->new			=true;

		$qry = "SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung WHERE ext_id='$lehrveranstaltung->ext_id'";
			if($result1 = pg_query($conn, $qry))
			{
				if(pg_num_rows($result1)>0) //wenn dieser eintrag schon vorhanden ist
				{
					if($row1=pg_fetch_object($result1))
					{
						//Lehrveranstaltungsdaten updaten
						$lehrveranstaltung->new=false;
						$lehrveranstaltung->lehrveranstaltung_id=$row1->lehrveranstaltung_id;
					}
					else
					{
						$error_log.="lehrveranstaltung_id von $row->lehrveranstaltung_id konnte nicht ermittelt werden\n";
						$error=true;
					}
				}
				else
				{
					//Lehrveranstaltung neu anlegen
					$lehrveranstaltung->new=true;
				}

				if(!$error)
					if(!$lehrveranstaltung->save())
					{
						$error_log.=$lehrveranstaltung->errormsg."\n";
						$anzahl_fehler++;
					}
					else
						$anzahl_eingefuegt++;
				else
					$anzahl_fehler++;
			}
	}
	$text.="abgeschlossen";
}
else
	$error_log .= 'Lehrveranstaltungsdatensaetze konnten nicht geladen werden';

?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - Lehrveranstaltungen</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<?php

echo nl2br($text);
echo nl2br($error_log);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");

?>
</body>
</html>