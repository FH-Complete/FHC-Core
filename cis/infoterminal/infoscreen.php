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

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="refresh" content="60">
<link href="../../skin/infoscreen.css" rel="stylesheet" type="text/css">
<title>Infoscreen</title>
<style type="text/css">
	html, body, div, iframe 
	{ 
		margin:0; 
		padding:0; 
		height:100%; 
	}
	iframe 
	{ 
		display:block; 
		width:80%; 
		border:none;
	}
  </style>
</head>

<body>';

$ip = $_SERVER["REMOTE_ADDR"];
$infoscreen = new infoscreen();
$i=0;
if($infoscreen->getInfoscreen($ip))
{
	$infoscreen_id = $infoscreen->infoscreen_id;
	$infoscreen->getScreenContent($infoscreen_id);
	foreach($infoscreen->result as $row)
	{
		$i++;
		$content[$i] = $row->content_id;
	}
	//echo "screen: $infoscreen_id";
	//echo "ip: ".$ip;
}

$zuf = rand(0,$i);
//echo 'zuf:'.$zuf.' i:'.$i.'<br>';
//var_dump($content);
//echo 'show content:'.$content[$zuf].' - '.$zuf;
if($zuf!=0)
{
	echo '<center style="height: 100%"><iframe src="../../cms/content.php?content_id='.$content[$zuf].'"></center>';
}
else
{
	// News anzeigen
	echo '
		<table id="inhalt" class="tabcontent">
		  <tr>
		    <td class="tdwidth_left">&nbsp;</td>
		    <td><table class="tabcontent">
		      <tr>
		        <td class="ContentHeader"><font class="ContentHeader">&nbsp;News</font></td>
		      </tr>
		      <tr>
		        <td>&nbsp;</td>
		      </tr>
			  <tr>
			  	<td>
			  	<div id="news">';

  	$news = new news();
  	$news->getnews(MAXNEWSALTER,0,null, false, null, MAXNEWS);
  	$zaehler=0;
  	foreach ($news->result as $row)
  	{
  		$lang='German';
		$content = new content();
		$content->getContent($row->content_id, $lang, null, null, false);
	
		$xml_inhalt = new DOMDocument();
		if($content->content!='')
		{
			$xml_inhalt->loadXML($content->content);
		}

		if($xml_inhalt->getElementsByTagName('verfasser')->item(0))
			$verfasser = $xml_inhalt->getElementsByTagName('verfasser')->item(0)->nodeValue;
		if($xml_inhalt->getElementsByTagName('betreff')->item(0))
			$betreff = $xml_inhalt->getElementsByTagName('betreff')->item(0)->nodeValue;
		if($xml_inhalt->getElementsByTagName('text')->item(0))
			$text = $xml_inhalt->getElementsByTagName('text')->item(0)->nodeValue;
	
  		$zaehler++;
  		//no comment
  		$datum = date('d.m.Y',strtotime(strftime($row->datum)));
  		//DMS Pfad korrigieren damit die Bilder korrekt angezeigt werden
		$text=mb_ereg_replace("dms.php","../../cms/dms.php",$text);
		
		//echo $datum.'&nbsp;'.$row->verfasser.'<br><br><strong>'.$row->betreff.'</strong><br>'.$row->text.'<br><br><br>
		echo '<div class="news">';
		echo '
			<div class="titel">
			<table width="100%">
				<tr>
					<td width="60%" align="left">'.$betreff.'</td>
					<!--<td width="30%" align="center"></td>-->
					<td width="30%" align="right" id="'.$zaehler.'Verfasser">'.$verfasser.' <span style="font-weight: normal">( '.$datum.' )</span></td>
				</tr>
			</table>
			</div>
			<div class="text" id="'.$zaehler.'Text">
			'.$text.'
			</div>
			';
		echo "</div><br />";
	}
	if($zaehler==0)
		echo 'Zur Zeit gibt es keine aktuellen News!';
		  
	echo '
			  </div>
			</td>
			<td>&nbsp;</td>
		  </tr>
	    </table></td>
		<td class="tdwidth_right">&nbsp;</td>
	  </tr>
	</table>';
}
echo '
</body>
</html>';
?>
