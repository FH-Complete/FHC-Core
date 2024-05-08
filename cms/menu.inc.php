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

$user = null;
//Parameter fuer Include Addons
$includeparams = array();
$contentobjects=array();
$chldsobject = array();
/**
 * Zeichnet einen Menueeintrag aus dem CMS System
 *
 * @param $content_id
 */
function drawSubmenu($content_id)
{
	global $sprache;
	global $contentarr;
	global $childsobject;
	global $contentobjects;
	$content = new content();
	$sprache = getSprache();

	// Daten Laden

	// Alle Kindelemente des Contents holen
	$ids = $content->getAllChilds($content_id);

	// Alle vorkommenden Contenteintraege laden
	$content->loadArray($ids, $sprache, true);
	$contentobjects = $content->result;

	// Baumstruktur laden
	$childsobject = $content->getChildArray($content_id);

	// Menue rausschreiben
	drawSubmenu1($content_id);

}

function drawSubmenu1($content_id)
{
	global $childsobject;
	global $contentobjects;

	if(isset($childsobject[$content_id]) && count($childsobject[$content_id])>0)
	{
		// jeden Untermenuepunkt durchlaufen
		foreach($childsobject[$content_id] as $entry)
		{
			$contentobj=null;
			//Content Objekt suchen
			foreach($contentobjects as $row)
			{
				if($row->content_id==$entry)
				{
					$contentobj = $row;
					break;
				}
			}
			if(!is_null($contentobj))
			{
				//Eintrag zeichnen
				drawEntry($contentobj);
			}
		}


	}
}
/**
 * Zeichnet den Menueeintrag samt Untermenues
 * @param $item Menue Array
 */
function drawEntry($item)
{
	global $childsobject, $user;

	//pruefen ob der Content eine Berechtigung erfordert
	if($item->locked)
	{
		if(is_null($user))
			$user = get_uid();
		$content = new content();
		//wenn der User nicht berechtigt ist, dann wird der Eintrag nicht angezeigt
		if(!$content->berechtigt($item->content_id, $user))
			return;
	}

	if(isset($childsobject[$item->content_id]) && count($childsobject[$item->content_id])>0)
	{
		echo "\n<li>";
		// Eintrag hat Untermenue -> Aufklappbar machen
		if($item->template_kurzbz=='include')
			IncludeMenuAddon($item);
		elseif($item->template_kurzbz=='redirect')
			Redirect($item, $item->content_id);
		else
			DrawLink(APP_ROOT.'cms/content.php?content_id='.$item->content_id,'content',$item->titel, $item->content_id, $item->menu_open);

		echo "\n<ul class=\"menu\">";
		drawSubmenu1($item->content_id);
		echo "\n</ul>";
		echo "\n</li>";
	}
	else
	{
		// Normaler Eintrag ohne Untermenue
		echo "\n<li>";
		if($item->template_kurzbz=='include')
			IncludeMenuAddon($item);
		elseif($item->template_kurzbz=='redirect')
			Redirect($item);
		else
			DrawLink(APP_ROOT.'cms/content.php?content_id='.$item->content_id,'content',$item->titel);

		echo "</li>";
	}
}

/**
 * Zeichnet einen normalen Menue Link
 * @param $link URL
 * @param $target Target
 * @param $name Anzeigename
 * @param $content_id Wenn die Content_id uebergeben wird, oeffnet der Klick das Submenu
 */
function DrawLink($link, $target, $name, $content_id=null, $open=null)
{
	if($target=='')
		$target='content';

	if($open)
		$class='class="selected"';
	else
		$class='';
	echo '<a '.$class.' href="'.$link.'" target="'.$target.'" title="'.htmlspecialchars($name).'">'.htmlspecialchars($name).'</a>';
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
function Redirect($content, $content_id_Submenu=null)
{
	global $sprache, $params;

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

	DrawLink($url, $target, $content->titel, $content_id_Submenu, $content->menu_open);
}

/**
 * Bei Content mit Include Templates wird
 * das entsprechende Menu-Addon geladen und inkludiert
 *
 * @param $content_id
 */
function IncludeMenuAddon($content)
{
	global $sprache, $includeparams;

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
