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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
/**
 * Phrasen fuer Mehrsprachigkeit
 * 
 * Diese Klasse liefert Phrasen fuer dynamische Seiten in der ausgewaehlten Sprache.
 * Die Phrasen werden im Filesystem nach Modulen getrennt abgelegt und bei Bedarf geladen.
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/functions.inc.php');
require_once(dirname(__FILE__).'/sprache.class.php');
require_once(dirname(__FILE__).'/addon.class.php');

class phrasen extends basis_db
{
	public $sprache='';
	protected $phrasen=array();
	protected $loadedModules=array();

	/**
	 * Konstruktor
	 * 
	 * @param $sprache Wenn keine Sprache uebergeben wird, wird die Default Sprache aus der Config verwendet
	 */
	public function __construct($sprache=null)
	{
		parent::__construct();
		if($sprache=='')
			$sprache=DEFAULT_LANGUAGE;
		$this->sprache = $sprache;
	}

	/**
	 * Laedt ein Sprachmodul
	 * Zuerst wird die Default Sprache des Moduls geladen. Danach die zugehoerige Sprache
	 * Dadurch werden die Phrasen die nicht vorhanden sind automatisch in der Default Sprache angezeigt
	 * 
	 * @param $module Name des Moduls
	 */
	public function loadModule($module)
	{
		$sprache = new sprache();
		$sprache->load(DEFAULT_LANGUAGE);
		
		//Default Sprache laden
		$filename = dirname(__FILE__).'/../locale/'.$sprache->locale.'/'.$module.'.php';
		if(file_exists($filename))
			include($filename);
		
		$addons = new addon();
		
		foreach($addons->aktive_addons as $addon)
		{
			$addon_locale_filename = dirname(__FILE__).'/../addons/'.$addon.'/locale/'.$sprache->locale.'/'.$module.'.php';

			if(file_exists($addon_locale_filename))
			{
				include($addon_locale_filename);
			}
		}

		$sprache = new sprache();
		$sprache->load($this->sprache);
		//Anzeigesprache laden
		$filename = dirname(__FILE__).'/../locale/'.$sprache->locale.'/'.$module.'.php';
		if(file_exists($filename))
			include($filename);
		

		
		foreach($addons->aktive_addons as $addon)
		{
			$addon_locale_filename = dirname(__FILE__).'/../addons/'.$addon.'/locale/'.$sprache->locale.'/'.$module.'.php';

			if(file_exists($addon_locale_filename))
			{
				include($addon_locale_filename);
			}
		}
		$this->loadedModules[]=$module;
	}
	
	/**
	 * Prueft ob das Modul des Keys bereits geladen wurde und laedt dieses wenn noetig
	 * 
	 * @param $key der Phrase
	 */
	public function checkModule($key)
	{
		$module = mb_substr($key, 0, mb_strpos($key,'/'));
		if(!in_array($module, $this->loadedModules))
			$this->loadModule($module);
	}
	
	/**
	 * Liefert eine Phrase in der eingestellten Sprache
	 * 
	 * Die Phrasen koennen Platzhalter fuer Variablen enthalten. Diese Variablen koennen als Array
	 * mit den 2. Parameter uebergeben werden.
	 * 
	 * Um in der Phrase die Reihenfolge der Variablen zu tauschen kann folgendes verwendet werden: "%x$s" 
	 * Wobei x durch den Index+1 im Array zu ersetzten ist. zB "%2$s" um die Variable $value[1] auszugeben
	 * naehere Infos siehe sprintf  
	 * 
	 * @param $key Key der Phrase
	 * @param $value Array mit Parametern fuer die Phrase
	 */
	public function t($key, $value=array())
	{
		$this->checkModule($key);
		$string = isset($this->phrasen[$key])?$this->phrasen[$key]:'[[PHRASE:'.$key.']]';
		return vsprintf($string, $value);
	}
}
?>
