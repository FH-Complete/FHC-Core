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
 * Authors: Karl Burkhart	<burkhart@technikum-wien.at>
 */

if(isset($_REQUEST['projekt']))
	$projekt_kurzbz = $_REQUEST['projekt'];
else
	die ('Kein Projekt übergeben!');
	
if(isset($_REQUEST['studienjahr']))
	$studienjahr = $_REQUEST['studienjahr'];
else
	die ('Kein Studienjahr übergeben');
	
if(isset($_REQUEST['ansicht']))
	$ansicht = $_REQUEST['ansicht'];
else
	die('es wurde keine Ansicht mitübergeben');

// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/projektphase.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/datum.class.php');

$projektphasen = new projektphase(); 
if(!$projektphasen->getProjektphasen($projekt_kurzbz))
	die('Kein gültiges Projekt übergeben');
	
$datum = new datum();
$widthPerWeek = 16;
$startX = 270;
$startY = 70;
// KW in der 28.12 liegt ist lezte KW 
$datum_gesamt = $studienjahr.'-12-28';
$timestamp_gesamt = $datum->mktime_fromdate($datum_gesamt);	
$kw_gesamt = date('W',$timestamp_gesamt);  

// kommt auf Anzahl der Phasen an
$height = (count($projektphasen->result)) * 50; 

// Zeichne Kalenderjahr -> beginnend mit KW 1
if($ansicht=='kalenderjahr')
{
	echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="no"?>
	<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 20010904//EN"
	"http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">';
	
	echo '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
	width="100%" height="100%">
	<rect x="'.$startX.'" y="'.$startY.'" width="'.($kw_gesamt*$widthPerWeek).'" height="'.$height.'"
	style="color:#000000;fill:none;stroke:#e1e1e1;stroke-width:1;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-opacity:1;stroke-dasharray:none;stroke-dashoffset:0;marker:none;visibility:visible;display:inline;overflow:visible;enable-background:accumulate" />';
	
	// Überschriften
	echo'<text x="30%" y="40" style="font-size:33px">Kalenderjahr: '.$studienjahr.'</text>';
	echo'<text x="'.($startX-10).'" y="'.($startY-5).'" style="font-size:13px" text-anchor="end"> KW:</text>';

	// Zeichne Raster
	for($i=1; $i<=$kw_gesamt; $i++)
	{
		$x1 = $startX + $i*$widthPerWeek;	
		echo '<line x1="'.$x1.'" y1="'.$startY.'" x2="'.$x1.'" y2="'.($height+$startY).'" style="stroke:#e1e1e1; stroke-width:2px;" />';
		if($i%2 == 1)
			echo '<text x="'.($x1-$widthPerWeek).'" y="'.($startY-5).'" style="font-size:13px">'.$i.'</text>';
		
	}
	
	// aktuelle KW markieren
	$timestamp_now = time();
	$kw_now = kalenderwoche($timestamp_now);
	$year_now=date("Y",$timestamp_now);
	if($year_now == $studienjahr)
	{
		$x = (($startX +($kw_now*$widthPerWeek))-$widthPerWeek/2); 
		echo '<line x1="'.$x.'" y1="'.($startY-20).'" x2="'.$x.'" y2="'.($height+$startY+20).'" style="stroke:red; stroke-width:4px;" />';
	}
	
	$i=0;
	foreach($projektphasen->result as $phase)
	{
		$width = 0;
		$x = 0;
		// wenn kein start oder ende angegeben -> nichts zeichnen -> width=0
		if($phase->start != '' && $phase->ende != '')
		{
			$timestamp_beginn = $datum->mktime_fromdate($phase->start);
			$timestamp_end = $datum->mktime_fromdate($phase->ende);
			$kw_beginn = kalenderwoche($timestamp_beginn);
			$kw_end = kalenderwoche($timestamp_end);
			// kw soll bei 0 zu zeichnen beginnen
			$kw_beginn = $kw_beginn -1; 
			$kw_end = $kw_end -1;
			
			$year_beginn=date("Y",$timestamp_beginn);
			$year_end=date("Y",$timestamp_end);
		
			// phase beginnt und endet im Jahr
			if($year_end == $year_beginn && $year_beginn == $studienjahr)
			{
				$width = ($kw_end - $kw_beginn+1)*$widthPerWeek;
				$x = ($startX+$kw_beginn*$widthPerWeek);
			}
				// endet im nächsten jahr
			else if($year_beginn == $studienjahr && $year_end > $year_beginn)
			{
				$width = ($kw_gesamt - $kw_beginn)*$widthPerWeek;
				$x = ($startX+$kw_beginn*$widthPerWeek);
			}
				// geht über gesamtes jahr
			else if($year_beginn < $studienjahr && $year_end > $studienjahr)
			{
				$width = ($kw_gesamt*$widthPerWeek);
				$x = $startX;
			}
				// beginnt im vorigen und endet im aktuellen
			else if($year_beginn < $studienjahr && $year_end == $studienjahr)
			{
					$width = ($kw_end)*$widthPerWeek;
					$x = $startX;
			}
		}
	
		// zeichne balken
		echo '<rect x="'.$x.'" y="'.($startY+10+$i*50).'" width ="'.$width.'" height ="30" fill="blue" stroke="black" />';
		echo'<text x="'.($startX-10).'" y="'.($startY+30+$i*50).'" style="font-size:15px" text-anchor="end">'.$phase->bezeichnung.'</text>';
		$i++;
	}
	
	echo '</svg>';
}
else if($ansicht == 'studienjahr')
{
	echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="no"?>
	<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 20010904//EN"
	"http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">';
	echo '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
	width="100%" height="100%">';
	
	// Überschriften
	echo'<text x="30%" y="40" style="font-size:33px">Studienjahr: '.$studienjahr.'/'.($studienjahr+1).'</text>';
	echo'<text x="'.($startX-10).'" y="'.($startY-5).'" style="font-size:13px" text-anchor="end"> KW:</text>';
	
	// WS 
	$year_old = $studienjahr.'-09-01';
	$timestamp_old = $datum->mktime_fromdate($year_old);
	$kw_old = kalenderwoche($timestamp_old);
	
	// SS
	$year_new = ($studienjahr+1).'-09-01';
	$timestamp_new = $datum->mktime_fromdate($year_new);
	$kw_new = kalenderwoche($timestamp_new);
	
	// gesamtanzahl der KWs im Studienjahr
	$y = 0;
	// Zeichne Raster studienjahr WS
	for($i=$kw_old; $i<=$kw_gesamt; $i++)
	{
		$x1 = ($startX +$y*$widthPerWeek);
		echo '<line x1="'.$x1.'" y1="'.$startY.'" x2="'.$x1.'" y2="'.($height+$startY).'" style="stroke:#e1e1e1; stroke-width:2px;" />';
		if($y%2 == 0)
			echo '<text x="'.$x1.'" y="'.($startY-5).'" style="font-size:13px">'.$i.'</text>';
		$y++;
	}
	// Zeichne Raster studienjahr SS
	for($i=1; $i<=$kw_new; $i++)
	{
		$x1 = ($startX +$y*$widthPerWeek);
		echo '<line x1="'.$x1.'" y1="'.$startY.'" x2="'.$x1.'" y2="'.($height+$startY).'" style="stroke:#e1e1e1; stroke-width:2px;" />';
		if($y%2 == 0)
			echo '<text x="'.$x1.'" y="'.($startY-5).'" style="font-size:13px">'.$i.'</text>';
		$y++;
	}
	echo '<rect x="'.$startX.'" y="'.$startY.'" width="'.($y*$widthPerWeek).'" height="'.$height.'"
	style="color:#000000;fill:none;stroke:#e1e1e1;stroke-width:1;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-opacity:1;stroke-dasharray:none;stroke-dashoffset:0;marker:none;visibility:visible;display:inline;overflow:visible;enable-background:accumulate" />';
	
	$i=0;
	foreach($projektphasen->result as $phase)
	{
		$width = 0;
		$x = 0;
		// wenn kein start oder ende angegeben -> nichts zeichnen -> width=0
		if($phase->start != '' && $phase->ende != '')
		{
			$timestamp_beginn = $datum->mktime_fromdate($phase->start);
			$timestamp_end = $datum->mktime_fromdate($phase->ende);
			$kw_beginn = kalenderwoche($timestamp_beginn);
			$kw_end = kalenderwoche($timestamp_end);
			$kw_beginn = $kw_beginn; 
			$kw_end = $kw_end;
			
			$startSS = $kw_gesamt-$kw_old;
			
			$year_beginn=date("Y",$timestamp_beginn);
			$year_end=date("Y",$timestamp_end);
			$test = 0; 
			// phase beginnt und endet im WS 
			if($year_end == $year_beginn && $year_beginn == $studienjahr && $kw_beginn >= $kw_old)
			{
				$width = ($kw_end - $kw_beginn + 1)*$widthPerWeek;
				$x = ($startX+($kw_beginn-$kw_old)*$widthPerWeek);
				$test = 1; 
			}
				// phase beginnt und endet im SS 
			if($year_end == $year_beginn && $year_beginn == $studienjahr+1 && $kw_beginn >= 1 && $kw_end <=$kw_new)
			{
				if($kw_end == 1)// es kann auch sein dass 31.12 des kalenderjahres schon in der 1. KW liegt
					$kw_end =$kw_new; 
				$width = ($kw_end - $kw_beginn + 1)*$widthPerWeek;
				$x = ($startX+($kw_beginn+$startSS)*$widthPerWeek);
				$test = 2; 
			}
				// phase beginnt im WS und endet im SS
			else if($year_beginn == $studienjahr && $year_end == $studienjahr+1 && $kw_beginn >= $kw_old && $kw_end <= $kw_new)
			{
				$width = ($kw_gesamt - $kw_beginn + $kw_end + 1)*$widthPerWeek;		
				$x = ($startX+($kw_beginn-$kw_old)*$widthPerWeek);
				$test = 3; 
			}
				// geht über gesamtes studienjahr
			else if($year_beginn == $studienjahr && $kw_beginn <= $kw_old && (($year_end == $studienjahr+1 && $kw_end >= $kw_new) || $year_end > $studienjahr+1))
			{
				$width = $y*$widthPerWeek;
				$x = $startX; 
				$test = 4; 
			}
				// geht über gesamtes studienjahr
			else if($year_beginn < $studienjahr && $year_end > $studienjahr+1)
			{
				$width = $y*$widthPerWeek;
				$x = $startX; 
				$test = 5; 
			}
				// beginnt früher und endet im aktuellen WS
			else if((($year_beginn == $studienjahr && $kw_beginn < $kw_old) || ($year_beginn < $studienjahr)) && ($year_end == $studienjahr && $kw_end >= $kw_old))
			{

				$width = ($kw_end - $kw_old + 1)*$widthPerWeek;
				$x = $startX;
				$test = 6; 
			}
				// beginnt früher und endet im aktuellen SS
			else if((($year_beginn == $studienjahr && $kw_beginn < $kw_old) || ($year_beginn < $studienjahr)) && ($year_end == $studienjahr+1 && $kw_end <= $kw_new))
			{
				if($kw_end == 1) // es kann auch sein dass 31.12 des kalenderjahres schon in der 1. KW liegt
					$kw_end =$kw_new; 
				$width = ($kw_gesamt - $kw_old + $kw_end + 1)*$widthPerWeek;
				$x = $startX;
				$test = 7; 
			}
				// beginnt im aktuellen WS und endet nach Studienjahr im aktuellen Kalenderjahr
			else if(($year_beginn == $studienjahr && $kw_beginn >= $kw_old) && ($year_end == $studienjahr+1 && $kw_end > $kw_new))
			{
				$width = ($kw_gesamt - $kw_beginn + $kw_new + 1)*$widthPerWeek;
				$x = ($startX+($kw_beginn-$kw_old)*$widthPerWeek);
				$test = 8; 
			}
				// beginnt im aktuellen WS und endet nach Studienjahr und nach aktuellem Kalenderjahr
			else if(($year_beginn == $studienjahr && $kw_beginn > $kw_old) && ($year_end > $studienjahr+1))
			{
				$width = ($kw_gesamt - $kw_beginn + $kw_new + 1)*$widthPerWeek;
				$x = ($startX+($kw_beginn-$kw_old)*$widthPerWeek);
				$test = 9; 
			}
				// beginnt im aktuellen SS und endet nach Studienjahr im aktuellen Kalenderjahr
			else if(($year_beginn == $studienjahr+1 && $kw_beginn <= $kw_new) && ($year_end == $studienjahr+1 && ($kw_end > $kw_new || $kw_end == 1))) // da 31.123
			{
				$width = ($y-$kw_beginn - $startSS)*$widthPerWeek;
				$x = ($startX+($kw_beginn+$startSS)*$widthPerWeek);
				$test = 10; 
			}
				// beginnt im aktuellen SS und endet nach Studienjahr und nach aktuellem Kalenderjahr
			else if(($year_beginn == $studienjahr+1 && $kw_beginn <= $kw_new) && ($year_end > $studienjahr+1))
			{
				$width = ($y-$kw_beginn - $startSS)*$widthPerWeek;
				$x = ($startX+($kw_beginn+$startSS)*$widthPerWeek);
				$test = 11; 
			}
		}
	
		// zeichne balken
		echo "test:".$test.$phase->bezeichnung."jahr".$studienjahr;
		echo '<rect x="'.$x.'" y="'.($startY+10+$i*50).'" width ="'.$width.'" height ="30" fill="blue" stroke="black" />';
		echo'<text x="'.($startX-10).'" y="'.($startY+30+$i*50).'" style="font-size:15px" text-anchor="end">'.$phase->bezeichnung.'</text>';
		$i++;
	}
	
	// aktuelle KW markieren
	$timestamp_now = time();
	$kw_now = kalenderwoche($timestamp_now);
	$year_now=date("Y",$timestamp_now);
	if($year_now == $studienjahr && $kw_now > $kw_old)
	{
		$x = (($startX +(($kw_now-$kw_old)*$widthPerWeek))-$widthPerWeek/2); 
		echo '<line x1="'.$x.'" y1="'.($startY-20).'" x2="'.$x.'" y2="'.($height+$startY+20).'" style="stroke:red; stroke-width:4px;" />';
	}
	else if($year_now == $studienjahr+1 && $kw_now < $kw_new)
	{
		$x = (($startX +(($kw_now+$kw_gesamt - $kw_old +1 )*$widthPerWeek))-$widthPerWeek/2); 
		echo '<line x1="'.$x.'" y1="'.($startY-20).'" x2="'.$x.'" y2="'.($height+$startY+20).'" style="stroke:red; stroke-width:4px;" />';
	}
	echo '</svg>';
}
?>
