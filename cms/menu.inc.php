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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Dieses File enthaelt Hilfsklassen zur Anzeige des CMS-Menues
 * 
 * mit drawSubmenu($id) wird das enstprechende Menue gezeichnet.
 */
require_once(dirname(__FILE__).'/../include/functions.inc.php');
require_once(dirname(__FILE__).'/../include/content.class.php');

//Parameter fuer Redirect URLS
$params = array();
foreach($_REQUEST as $key=>$value)
	$params[$key]=$value;

//Parameter fuer Include Addons
$includeparams = array();

/**
 * Zeichnet einen Menueeintrag aus dem CMS System
 * 
 * @param $content_id
 */
function drawSubmenu($content_id)
{
	global $sprache;
	$content = new content();
	$sprache = getSprache();
	
	$arr = $content->getMenueArray($content_id, $sprache, true);
	foreach ($arr as $row)
	{
		drawEntry($row);
	}
}

/**
 * Prueft ob der Menueeintrag Submenues hat
 * 
 * @param $item Menue Array
 * @return boolean
 */
function EntryHasChilds($item)
{
	foreach($item as $row)
	{
		if(is_array($row) && isset($row['name']))
			return true;
	}
	
	return false;
}

/**
 * Zeichnet den Menueeintrag samt Untermenues
 * @param $item Menue Array
 */
function drawEntry($item)
{
	if(EntryHasChilds($item))
	{
		echo '
		<tr>
			<td class="tdwidth10" nowrap>&nbsp;</td>
			<td class="tdwrap">';
		if($item['template']=='include')
			IncludeMenuAddon($item['content_id']);
		elseif($item['template']=='redirect')
			Redirect($item['content_id'], $item['name'], $item['content_id']);
		else
			DrawLink($item['link'], 'content', $item['name'], $item['content_id']);
		
		echo '
			<table class="tabcontent" id="Content'.$item['content_id'].'" style="display: '.($item['open']=='true'?'visible':'none').'">';
		foreach($item as $row)
		{
			if(is_array($row) && isset($row['name']))
			{
				drawEntry($row);
			}
		}	
		echo '
				</table>
			</td>
		</tr>';
	}
	else
	{
		echo '
		<tr>
		  	<td class="tdwidth10" nowrap>&nbsp;</td>
			<td class="tdwrap">';
		if($item['template']=='include')
			IncludeMenuAddon($item['content_id']);
		elseif($item['template']=='redirect')
			Redirect($item['content_id'], $item['name']);
		else
			DrawLink($item['link'],'content',$item['name']);
			
		echo '
			</td>
		</tr>';
	}
}

/**
 * Zeichnet einen normalen Menue Link
 * @param $link URL
 * @param $target Target
 * @param $name Anzeigename
 * @param $content_id Wenn die Content_id uebergeben wird, oeffnet der Klick das Submenu
 */
function DrawLink($link, $target, $name, $content_id=null)
{
	if($target=='')
		$target='content';
	
	if(!is_null($content_id))
		$class = 'class="MenuItem" onClick="js_toggle_container(\'Content'.$content_id.'\');"';
	else
		$class='class="Item"';
	
	echo '<a '.$class.' href="'.$link.'" target="'.$target.'"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;'.$name.'</a>';
	
}

/**
 * Redirects sind Links Seiten ausserhalb des CMS
 * die URL kann Variablen enthalten. Diese werden hier ersetzt.
 * Danach wird der Link angezeigt.
 * 
 * @param $content_id ContentID des Redirects
 * @param $name Anzeigename des Links
 * @param $content_id_Submenu ID des Submenues das geoeffnet werden soll (optional)
 */
function Redirect($content_id, $name, $content_id_Submenu=null)
{
	global $sprache, $params;
	
	$content = new content();
	$content->getContent($content_id, $sprache, null, true, true);
	
	$xml = new DOMDocument();
	if($content->content!='')
	{
		$xml->loadXML($content->content);
	}
	
	if($xml->getElementsByTagName('url')->item(0))
		$url = $xml->getElementsByTagName('url')->item(0)->nodeValue;
	else
		$url='';
		
	//Variablen Ersetzen
	foreach($params as $key=>$value)
	{
		$url = str_replace('$'.$key,addslashes($value),$url);
	}
	
	if($xml->getElementsByTagName('target')->item(0))
		$target = $xml->getElementsByTagName('target')->item(0)->nodeValue;
	else
		$target='';
		
	DrawLink($url, $target, $name, $content_id_Submenu);
}

/**
 * Bei Content mit Include Templates wird 
 * das entsprechende Menu-Addon geladen und inkludiert
 * 
 * @param $content_id
 */
function IncludeMenuAddon($content_id)
{
	global $sprache, $includeparams;
	$content = new content();
	$content->getContent($content_id, $sprache, null, true, true);
	
	$xml = new DOMDocument();
	if($content->content!='')
	{
		$xml->loadXML($content->content);
	}
	if($xml->getElementsByTagName('url')->item(0))
		$url = $xml->getElementsByTagName('url')->item(0)->nodeValue;
	else
		$url='';
	if($url!='')
	{
		$includeparams['content']=$content;
		include(dirname(__FILE__).'/menu/'.$url);		
	}
}