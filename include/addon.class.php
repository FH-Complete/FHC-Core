<?php
/* Copyright (C) 2013 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class addon extends basis_db
{
	public $new;
	public $result=array();

	public $addon_name;
	public $addon_version;
	public $addon_description;
	public $fhcomplete_target_version;

	public $aktive_addons=array();

	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->aktive_addons = array_filter(array_map('trim', explode(";", ACTIVE_ADDONS)));

	}

	/**
	 * Liefert alle aktivierten Addons
	 */
	public function loadAddons()
	{

		foreach($this->aktive_addons as $addon)
		{
			$addon_name='';
			$addon_version='';
			$addon_description='';
			$fhcomplete_target_version='';

			include(dirname(__FILE__).'/../addons/'.$addon.'/version.php');

			$obj = new stdClass();
			$obj->kurzbz = $addon;
			$obj->addon_name = $addon_name;
			$obj->addon_version = $addon_version;
			$obj->addon_description = $addon_description;
			$obj->fhcomplete_target_version = $fhcomplete_target_version;


			$this->result[] = $obj;
		}
		return true;
	}

	/**
	 * Laedt Information zu dem Addon
	 */
	public function getInformation($addon)
	{
		$addon_name='';
		$addon_version='';
		$addon_description='';
		$fhcomplete_target_version='';

		include(dirname(__FILE__).'/../addons/'.$addon.'/version.php');

		$this->addon_name = $addon_name;
		$this->addon_version = $addon_version;
		$this->addon_description = $addon_description;
		$this->fhcomplete_target_version = $fhcomplete_target_version;

	}

	/**
	 * Prüfen, ob ein bestimmtes Addon aktivierten ist
	 * @param $addon_kurzbz (fhtw,casetime, wawi..)
	 * @return true wenn addon aktiv, sonst false
	 */
	public function checkActiveAddon($addon_kurzbz)
	{
		$addonIsActive = false;
		foreach($this->aktive_addons as $addon)
		{
			if ($addon == $addon_kurzbz)
				$addonIsActive = true;
		}
		return $addonIsActive;
	}
}
?>
