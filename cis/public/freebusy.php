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
require_once('../../include/freebusy.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/ical.class.php');

$uid = mb_substr($_SERVER['PATH_INFO'],1);

$bn = new benutzer();
if(!$bn->load($uid))
	die('User invalid');
	
$freebusy = new freebusy();
$freebusy->getFreeBusy($uid);
header("Content-Type: text/calendar; charset=UTF-8");

echo "BEGIN:VCALENDAR\n";
echo "VERSION:2.0\n";
echo "PRODID:-//FH TECHNIKUM WIEN//EN\n";
echo "METHOD:PUBLISH\n";
echo 'ORGANIZER;CN=',$bn->vorname,' ',$bn->nachname,':mailto:',$uid,'@',DOMAIN,"\n";
echo 'DTSTAMP:',date('Ymd', mktime(date('H'),date('i'),date('s'),date('m'),date('d')-5,date('Y'))),'T',date('Hms'),"Z\n";
echo 'DTSTART:',date('Ymd', mktime(0,0,0,date('m'),date('d')-5,date('Y'))),"T000000Z\n";
echo 'DTEND:',date('Ymd', mktime(0,0,0,date('m'),date('d')+30,date('Y'))),"T000000Z\n";
echo 'URL:',APP_ROOT,'cis/public/freebusy.php/',$uid,"\n";

$ical = new ical();

foreach($freebusy->result as $row)
{
	if($row->aktiv)
	{
		$fp = fopen($row->url,'r');
		if (!$fp) 
		{
		    echo "$errstr ($errno)<br />\n";
		}
		else 
		{
			$doc = '';
		    while (!feof($fp)) 
		    {
		        $line = fgets($fp);
		        $doc.=$line;
		    }
		    fclose($fp);
		    
		    $ical->importFreeBusy($doc, $row->freebusytyp_kurzbz);
		}
	}
}

//Pers. LVplan
$fp = fopen(APP_ROOT.'cis/public/freebusy_lvplan.php/'.$uid,'r');
if (!$fp) 
{
    echo "$errstr ($errno)<br />\n";
}
else 
{
	$doc = '';
    while (!feof($fp)) 
    {
        $line = fgets($fp);
        $doc.=$line;
    }
    fclose($fp);
    
    $ical->importFreeBusy($doc, 'LVPLAN');
}
echo $ical->getFreeBusy();
echo "\nEND:VCALENDAR";
?>