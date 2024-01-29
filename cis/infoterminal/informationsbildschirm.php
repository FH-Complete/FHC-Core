<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */

require_once('../../config/cis.config.inc.php');
require_once('../../include/news.class.php');
require_once('../../include/content.class.php');
require_once('../../include/infoscreen.class.php');

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

if(isset($_GET['ipadresse']))
	$ip = $_GET['ipadresse'];
else
{
	if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else
		$ip = $_SERVER["REMOTE_ADDR"];
}

if(isset($_GET['norefresh']))
	$norefresh = true;
else
	$norefresh = false;

$infoscreen = new infoscreen();
$i=-1;
$refreshzeit = 40; // Default Refreshzeit

$infoscreen_id='';
$aktuellerContentIdx=0;

//Cookie erhaelt zusaetzlich die IP im Namen damit bei der Preview keine Konflikte entstehen
$cookie = 'infoscreenContent'.str_replace('-','',str_replace('.','',$ip));

//zuletzt angezeigte Seite des Terminals ermitteln
if(isset($_COOKIE[$cookie]))
{
	$lastinfoscreencontent = $_COOKIE[$cookie];
}
else
{
	$lastinfoscreencontent = -1;
	$aktuellerContentIdx = 0;
}

if($infoscreen->getInfoscreen($ip))
{
	$infoscreen_id = $infoscreen->infoscreen_id;
	$infoscreen->getScreenContent($infoscreen_id);
	foreach($infoscreen->result as $row)
	{
		$i++;
		$content[$i] = $row->content_id;
		$infoscreen_content[$i] = $row->infoscreen_content_id;
		$refreshzeiten[$i] = $row->refreshzeit;
		if($row->infoscreen_content_id==$lastinfoscreencontent)
		{
			$aktuellerContentIdx=$i+1;
		}

	}
}
if($aktuellerContentIdx>$i)
	$aktuellerContentIdx=0;

if(isset($refreshzeiten[$aktuellerContentIdx]) && $refreshzeiten[$aktuellerContentIdx]!='')
	$refreshzeit = $refreshzeiten[$aktuellerContentIdx];

if(isset($infoscreen_content) && isset($infoscreen_content[$aktuellerContentIdx]))
{
	// Cookie enthaelt die zuletzt angezeigte Seite
	setcookie($cookie,$infoscreen_content[$aktuellerContentIdx],time()+3600*24);
}

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
	if (!$norefresh)
	{
		echo '<meta http-equiv="refresh" content="',$refreshzeit,'">';
	}
echo '	<link href="../../skin/infoscreen.css" rel="stylesheet" type="text/css">
';

//Skript fuer den automatischen bildlauf
$scroll= '
<script type="text/javascript" language="JavaScript">

var speed=1 <!--Zeilensprung in px. Wert aendern um Geschwindigkeit zu steuern. Hoeher ist schneller.-->
var currentpos=0,alt=1,curpos1=0,curpos2=-1
function initialize()
	{
	startit()
	}

function scrollwindow()
	{
	if (document.all)
	temp=document.body.scrollTop
	else
	temp=window.pageYOffset
	if (alt==0)
	alt=1
	else
	alt=0
	if (alt==0)
	curpos1=temp
	else
	curpos2=temp
	if (curpos1!=curpos2)
		{
		if (document.all)
		currentpos=document.body.scrollTop+speed
		else
		currentpos=window.pageYOffset+speed
		window.scroll(0,currentpos)
		}
	else
		{
		currentpos=0
		window.scroll(0,currentpos)
		}
	}

function startit()
	{
	setInterval("scrollwindow()",40) <!--Zeit in ms bis zum naechsten Bildwechsel 1000=1sek-->
	}

window.onload=initialize
</script>';
$scroll= "<script>
function scrolldown()
{
	contentframe = document.getElementById('content').contentWindow;
	contentframe.scrollBy(0,1)
	window.setTimeout('scrolldown()',50);
}
window.onload=scrolldown;
</script>
";
//echo $scroll;

echo '
	<title>Informationsbildschirm</title>
</head>
<body>';
echo '<!-- Last content:'.$lastinfoscreencontent.' ID:'.$infoscreen_id.' IP:'.$ip.'-->';
if($infoscreen_id!='' && isset($content[$aktuellerContentIdx]))
{
	echo '<center style="height: 100%"><iframe id="content" src="../../cms/content.php?content_id='.$content[$aktuellerContentIdx].'" ></iframe></center>';
}
else
{
	echo '	<table style="width: 100%; height: 100%">
				<tr>
					<td style="height: 80%; vertical-align: center; text-align: center">
						<img style="height: 900px" src="../../skin/styles/'.EXT_FKT_PATH.'/logo_200x400.png" />
					</td>
				</tr>
				<tr>
					<td style="height: 20%; vertical-align: bottom; text-align: right; color: #CCCCCC; padding: 50px">'.$ip.'</td>
				</tr>
			</table>
';
}
echo '
</body>
</html>';
?>
