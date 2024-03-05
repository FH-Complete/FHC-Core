<?php
/* Copyright (C) 2024 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 * 			Manuela Thamer 			<manuela.thamer@technikum-wien.at>
 */
/**
 * Dieses Script liefert Zeitverfügbarkeiten und -sperren des übergebenen Parameters (uid, foreign_uid oder oekurz_bz)
 *
 * Aufruf: link + [params]
 * link: URL:',APP_ROOT'/fhcomplete/cis/public/ical_zeitsperren.php/cipher_encryption/
 * [params]
 * uid=oesi (Termine: "ZEITSPERRE" bzw."VERFUEGBAR", Bezeichnungen der Zeitsperren)
 * oe_kurzbz=SystemEntwicklungCore (Termine: "ZEITSPERRE" bzw."VERFUEGBAR", "NAME MITARBEITER")
 * foreign_uid=oesi (Termine: "ZEITSPERRE" bzw."VERFUEGBAR", "NAME MITARBEITER")
 * Beispielaufruf php
 * echo "<a href=" . APP_ROOT. "cis/public/ical_zeitsperren.php/cipher_encryption/".encryptData('uid=oesi',ZEITSPERREN_CYPHER_KEY).">Beispielaufruf <a/>";
 */
require_once('../../config/cis.config.inc.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/zeitsperre.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/benutzerfunktion.class.php');

$params = mb_substr($_SERVER['PATH_INFO'],1);
$paramsArray = explode('/',$params);

if ($paramsArray[0] == 'cipher_encryption')
{
	$string = decryptData($paramsArray[1],ZEITSPERREN_CYPHER_KEY);
}
$delimiter = "=";

$position = strpos($string, $delimiter);
if ($position !== false)
{
	$substring = substr($string, 0, $position);
}
else
{
	die('invalid Parameterformat');
}

$private = ($substring == 'uid') ? true : false;

if($substring == 'uid' || $substring == 'foreign_uid')
{
	$uid =  substr($string, $position + 1);
	$bn = new benutzer();
	if(!$bn->load($uid))
		die('uid' . $uid . ' not found');
	$filename = $uid;
}
elseif($substring == 'oe_kurzbz')
{
	$oe = substr($string, $position + 1);

	//check if valid Oe
	$bf = new benutzerfunktion();
	if ($bf->getOeFunktionen($oe, 'oezuordnung'))
	{
		$uidArr = array();

		foreach ($bf->result as $uid) {
			$uidArr[] = $uid->uid;
		}

		if ($uidArr == null)
			die('oe_kurzbz not found');
		else
			$uid = $uidArr;
		$filename = $oe;
	}
}
else
{
	die('Parameter not valid');
}

header("Content-Type: text/calendar; charset=UTF-8");
header("Content-disposition: filename=".$filename."_Zeitsperren_Verfuegbarkeiten.ics");

echo "BEGIN:VCALENDAR\n";
echo "VERSION:2.0\n";
echo "PRODID:-//FH TECHNIKUM WIEN//EN\n";
echo 'DTSTART;TZID=Europe/Vienna:',date('Ymd', mktime(0,0,0,date('m'),date('d')-5,date('Y'))),"T000000\n";
echo 'DTEND;TZID=Europe/Vienna:',date('Ymd', mktime(0,0,0,date('m'),date('d')+30,date('Y'))),"T000000\n";
echo "BEGIN:VTIMEZONE
TZID:Europe/Vienna
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
DTSTART:19810329T020000
TZNAME:GMT+02:00
TZOFFSETTO:+0200
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
DTSTART:19961027T030000
TZNAME:GMT+01:00
TZOFFSETTO:+0100
END:STANDARD
END:VTIMEZONE\n";

//echo 'URL:',APP_ROOT,'cis/public/ical_zeitsperren.php/',$uid,"\n";

//Zeitsperren des Users (der User) laden
$zeitsperre = new zeitsperre();
$zeitsperre->getZeitsperrenForIcal($uid);

foreach ($zeitsperre->result as $z)
{
	$typ = $z->zeitsperretyp_kurzbz;
	$name = trim($z->vorname . " " . $z->nachname);
	$titel = $typ == 'ZVerfueg' ? "VERFUEGBAR" : "ZEITSPERRE";
	$bezeichnung = $private ? ($typ . " " . $z->bezeichnung) : $name;
	$dateVon = new DateTime($z->von);
	$dtstart = $dateVon->format('Ymd\THis');
	$dateBis = new DateTime($z->bis);
	$dtend = $dateBis->format('Ymd\THis');

	echo "BEGIN:VEVENT\r\n"
		."SUMMARY: ". $titel."\r\n"
		."DESCRIPTION: ". $bezeichnung."\r\n"
		."DTSTART;TZID=Europe/Vienna:".$dtstart."\r\n"
		."DTEND;TZID=Europe/Vienna:".$dtend."\r\n"
		."TRANSP:OPAQUE\r\n"
		."END:VEVENT\r\n";
}
echo "END:VCALENDAR\r\n";
?>