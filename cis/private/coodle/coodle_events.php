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
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/ort.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/ical.class.php');
require_once('../../../include/wochenplan.class.php');

if(isset($_POST['id']))
	$id = $_POST['id'];

if(isset($_POST['typ']))
	$typ = $_POST['typ'];

if(isset($_POST['start']))
	$start = $_POST['start'];
if(isset($_POST['end']))
	$end = $_POST['end'];

/*
  Beispiel JSON Event
 [
 	{
 		"id":111,
 		"title":"Event1",
 		"start":"2012-10-10",
 		"url":"http:\/\/yahoo.com\/"
 	},
 	{
 		"id":222,
 		"title":"Event2",
 		"start":"2012-10-20",
 		"end":"2012-10-22",
 		"url":"http:\/\/yahoo.com\/"
 	}
 ]
 */
$events=array();

switch($typ)
{
	case 'Ort':
		// LVPlan/Reservierungen des Raumes holen

		$stdplan = new wochenplan('ort');
		$stdplan->load_data('ort',null,$id);

		while($start<$end)
		{
			if(!date("w",$start))
				$start=jump_day($start,1);

			$stdplan->init_stdplan();
			$datum=$start;
			$start+=604800;	// eine Woche

			// Stundenplan einer Woche laden
			if(!$stdplan->load_week($datum,'stundenplan'))
			{
				die($stdplan->errormsg);
			}

			$result = $stdplan->draw_week_csv('return', LVPLAN_KATEGORIE);
			foreach($result as $row)
			{
				$item['id']=$id.$row['dtstart'].$row['dtend'];
				$item['title']=$id;
				$item['start']=fixDate($row['dtstart']);
				$item['end']=fixDate($row['dtend']);
				$item['allDay']=false;
				$item['editable']=false;
				$events[]=$item;
			}
		}
		break;

	case 'Person':
		//FreeBusy Information holen
		$fp = fopen(APP_ROOT.'cis/public/freebusy.php/'.$id,'r');
		if (!$fp)
		{
			//Load Failed
			break;
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

			//FreeBusy Parsen
			$ical = new ical();
			$ical->parseFreeBusy($doc);

			foreach($ical->dtresult as $row)
			{
				$item['id']=$id.$row['dtstart'].$row['dtend'];
				$item['title']=$id;
				$item['start']=fixDate($row['dtstart']);
				$item['end']=fixDate($row['dtend']);
				$item['allDay']=false;
				$item['editable']=false;
				$events[]=$item;
			}
		}

		break;
	default:
		break;
}
echo json_encode($events);

function fixDate($date)
{
	$jahr = mb_substr($date,0,4);
	$monat = mb_substr($date,4,2);
	$tag = mb_substr($date,6,2);
	$stunde = mb_substr($date,9,2);
	$minute = mb_substr($date,11,2);
	$sekunde = mb_substr($date,13,2);
	return $jahr.'-'.$monat.'-'.$tag.'T'.$stunde.':'.$minute.':'.$sekunde;
}
?>
