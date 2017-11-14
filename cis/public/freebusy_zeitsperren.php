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
 * Dieses Script liefert die FreeBusy Informationen für die Zeitsperren
 * 
 * Aufruf: http://www.example.com/cis/public/freebusy_zeitsperren.php/[uid]
 * zB
 * http://www.example.com/cis/public/freebusy_zeitsperren.php/oesi
 */
require_once('../../config/cis.config.inc.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/zeitsperre.class.php');
require_once('../../include/ical.class.php');
require_once('../../include/stunde.class.php');

$uid = mb_substr($_SERVER['PATH_INFO'],1);

$bn = new benutzer();
if(!$bn->load($uid))
	die('User invalid');
	
header("Content-Type: text/calendar; charset=UTF-8");

echo "BEGIN:VCALENDAR\n";
echo "VERSION:2.0\n";
echo "PRODID:-//FH TECHNIKUM WIEN//EN\n";
echo "METHOD:PUBLISH\n";
echo 'ORGANIZER;CN=',$bn->vorname,' ',$bn->nachname,':mailto:',$uid,'@',DOMAIN,"\n";
echo 'DTSTAMP;TZID=Europe/Vienna:',date('Ymd', mktime(date('H'),date('i'),date('s'),date('m'),date('d')-5,date('Y'))),'T',date('Hms'),"\n";
echo 'DTSTART;TZID=Europe/Vienna:',date('Ymd', mktime(0,0,0,date('m'),date('d')-5,date('Y'))),"T000000\n";
echo 'DTEND;TZID=Europe/Vienna:',date('Ymd', mktime(0,0,0,date('m'),date('d')+30,date('Y'))),"T000000\n";
echo 'URL:',APP_ROOT,'cis/public/freebusy_zeitsperren.php/',$uid,"\n";
echo "BEGIN:VFREEBUSY";

// Alle Umfragen holen an denen der User beteiligt ist
$zeitsperre = new zeitsperre();
$zeitsperre->getzeitsperren($uid);

foreach($zeitsperre->result as $row)
{	
	//Start und Ende berechnen
	$stunde = new stunde();
	
	if($row->vonstunde!='')
	{
		$stunde->load($row->vonstunde);
		$vonuhrzeit = $stunde->beginn;
	}
	else
		$vonuhrzeit = '00:00:00';
		
	if($row->bisstunde!='')
	{
		$stunde->load($row->bisstunde);
		$bisuhrzeit = $stunde->ende;
	}
	else
		$bisuhrzeit = '23:59:00';
		
	$date = new DateTime($row->vondatum.' '.$vonuhrzeit);
	$dtstart = $date->format('Ymd\THis');
	$date = new DateTime($row->bisdatum.' '.$bisuhrzeit);
	$dtende = $date->format('Ymd\THis');
	echo "\nFREEBUSY;TZID=Europe/Vienna: $dtstart/$dtende";
}

echo "\nEND:VFREEBUSY";
echo "\nEND:VCALENDAR";
?>