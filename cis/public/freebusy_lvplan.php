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
 * Dieses Script liefert die FreeBusy Informationen
 * 
 * Aufruf: http://www.example.com/cis/public/freebusy.php/[uid]
 * zB
 * http://www.example.com/cis/public/freebusy.php/oesi
 */
require_once('../../config/cis.config.inc.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/wochenplan.class.php');

$uid = mb_substr($_SERVER['PATH_INFO'],1);

$bn = new benutzer();
if(!$bn->load($uid))
	die('User invalid');
	
if(check_lektor($uid))
	$type='lektor';
else
	$type='student';
	
	
header("Content-Type: text/calendar; charset=UTF-8");

echo "BEGIN:VCALENDAR\n";
echo "VERSION:2.0\n";
echo "PRODID:-//FHCOMPLETE//EN\n";
echo "METHOD:PUBLISH\n";
echo "BEGIN:VFREEBUSY\n";
echo 'ORGANIZER;CN=',$bn->vorname,' ',$bn->nachname,':mailto:',$uid,'@',DOMAIN,"\n";
echo 'DTSTAMP;TZID=Europe/Vienna:',date('Ymd', mktime(date('H'),date('i'),date('s'),date('m'),date('d')-5,date('Y'))),'T',date('Hms'),"\n";
echo 'DTSTART;TZID=Europe/Vienna:',date('Ymd', mktime(0,0,0,date('m'),date('d')-5,date('Y'))),"T000000\n";
echo 'DTEND;TZID=Europe/Vienna:',date('Ymd', mktime(0,0,0,date('m'),date('d')+30,date('Y'))),"T000000\n";
echo 'URL:',APP_ROOT,'cis/public/freebusy_lvplan.php/',$uid,"\n";

// Stundenplanobjekt erzeugen
$stdplan = new wochenplan($type);
$stdplan->crlf="\n";

// Zusaetzliche Daten laden
if(!$stdplan->load_data($type,$uid))
{
	die($stdplan->errormsg);
}

$begin = mktime(0,0,0,date('m'),date('d')-5,date('Y'));
$ende = mktime(0,0,0,date('m'),date('d')+30,date('Y'));
$db_stpl_table = 'stundenplan';
$i=0;
// Kalender erstellen
while($begin<$ende)
{
$i++;
	if(!date("w",$begin))
		$begin=jump_day($begin,1);
	
	$stdplan->init_stdplan();
	$datum=$begin;
	$begin+=604800;	// eine Woche

	// Stundenplan einer Woche laden
	if(!$stdplan->load_week($datum,$db_stpl_table))
	{
		die($stdplan->errormsg);
	}
	$stdplan->draw_week_csv('freebusy', LVPLAN_KATEGORIE);
}

echo "\nEND:VFREEBUSY";
echo "\nEND:VCALENDAR";
?>