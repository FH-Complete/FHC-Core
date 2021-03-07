<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 */
/**
 * Dieses Script liefert die iCal Informationen für die vorreservierten Coodle Termine
 * 
 * Aufruf: http://www.example.com/cis/public/ical_coodle.php/[uid]
 */
require_once('../../config/cis.config.inc.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/coodle.class.php');
require_once('../../include/ical.class.php');

$uid = mb_substr($_SERVER['PATH_INFO'],1);

$bn = new benutzer();
if(!$bn->load($uid))
	die('User invalid');
	
header("Content-Type: text/calendar; charset=UTF-8");

echo "BEGIN:VCALENDAR\n";
echo "VERSION:2.0\n";
echo "PRODID:-//FH TECHNIKUM WIEN//EN\n";
//echo "METHOD:PUBLISH\n";
//echo 'ORGANIZER;CN=',$bn->vorname,' ',$bn->nachname,':mailto:',$uid,'@',DOMAIN,"\n";
//echo 'DTSTAMP;TZID=Europe/Vienna:',date('Ymd', mktime(date('H'),date('i'),date('s'),date('m'),date('d')-5,date('Y'))),'T',date('Hms'),"\n";
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
//echo 'URL:',APP_ROOT,'cis/public/ical_coodle.php/',$uid,"\n";

// Alle Umfragen holen an denen der User beteiligt ist
$umfragen = new coodle();
$umfragen->getCoodleFromUser($uid);
$i = 0;
if (count($umfragen->result) > 0)
{
	//echo "BEGIN:VEVENT";
}
foreach($umfragen->result as $umfrage)
{
	if($umfrage->coodle_status_kurzbz=='laufend')
	{
		// Bei laufenden Umfragen werden alle angekreuzten Vorschlaege zur FreeBusy hinzugefuegt
		$ressource = new coodle();
		if($ressource_id = $ressource->RessourceExists($umfrage->coodle_id, $uid))
		{	
			// Terminvorschlaege laden die angekreuzt wurden
			$termine = new coodle();
			$termine->getRessourceTermin($umfrage->coodle_id, $ressource_id);
			foreach($termine->result as $termin)
			{
				//Start und Ende berechnen
				$date = new DateTime($termin->datum.' '.$termin->uhrzeit);
				$dtstart = $date->format('Ymd\THis');
				$interval =new DateInterval('PT'.$umfrage->dauer.'M');
				$date->add($interval);
				$uhrzeit_ende = $date->format('H:i:s');
				$dtende = $date->format('Ymd\THis');

				echo "\nBEGIN:VEVENT";
				echo "\nUID:Coodle_Terminoption".$dtstart."_".$dtende."";
				echo "\nSUMMARY:Coodle Terminoption";
				echo "\nDTSTART;TZID=Europe/Vienna:$dtstart";
				echo "\nDTEND;TZID=Europe/Vienna:$dtende";
				echo "\nTRANSP:OPAQUE";
				echo "\nEND:VEVENT";

				$i++;
			}
		}
	}
	// Bei abgeschlossene Umfragen wird ein iCal File mitgeschickt,
	// deshalb müssen diese Termine nicht berücksichtigt werden
	/*elseif($umfrage->coodle_status_kurzbz=='abgeschlossen')
	{
		// Bei abgeschlossenen Umfragen wird nur mehr der ausgewaehlte Termin zur FreeBusy hinzugefuegt
		$termin = new coodle();
		$coodle_termin_id=$termin->getTerminAuswahl($umfrage->coodle_id);
		if($termin->loadTermin($coodle_termin_id))
		{
			//Start und Ende berechnen
			$date = new DateTime($termin->datum.' '.$termin->uhrzeit);
			$dtstart = $date->format('Ymd\THis');
			$interval =new DateInterval('PT'.$umfrage->dauer.'M');
			$date->add($interval);
			$uhrzeit_ende = $date->format('H:i:s');
			$dtende = $date->format('Ymd\THis');

			echo "\nBEGIN:VEVENT";
			echo "\nSUMMARY:Coodle Terminzusage";
			echo "\nDTSTART;TZID=Europe/Vienna: $dtstart";
			echo "\nDTEND;TZID=Europe/Vienna: $dtende";
			echo "\nTRANSP:OPAQUE";
			echo "\nEND:VEVENT";
		}
	}*/
	// stornierte Umfragen werden nicht beruecksichtigt
}

echo "\nEND:VCALENDAR";
?>