<?php
/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller operates between (interface) the JS (GUI) and the PhrasesLib (back-end)
 * Provides data to the ajax get calls about the Phrasen plugin
 * This controller works with JSON calls on the HTTP GET and the output is always JSON
 */
class Phrasen extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'loadModule' => self::PERM_ANONYMOUS,
			'setLanguage' => self::PERM_ANONYMOUS,
			'getLanguage' => self::PERM_ANONYMOUS,
			'getAllLanguages' => self::PERM_ANONYMOUS,
		]);

		$this->load->helper('hlp_language');
	}
	
	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @param string $module
	 */
	public function loadModule($module)
	{
		$this->load->library('PhrasesLib', [$module], 'pj');
		$this->terminateWithSuccess(json_decode($this->pj->getJSON()));
	}

	public function setLanguage()
	{
		$postParams = $this->getPostJSON();
		$language = $postParams->language;
		$categories = $postParams->categories;

		setUserLanguage($language);

		$this->load->library('PhrasesLib', array($categories, $language), 'p');

		$phrases = $this->p->setPhrases($categories, $language);
		$this->terminateWithSuccess($phrases);
	}

	// gets the langauge of the currently logged in user session and otherwhise the system language
	public function getLanguage()
	{
		$lang = getUserLanguage();
		$this->terminateWithSuccess($lang);
	}

	// gets all languages that are set as active in the database
	public function getAllLanguages()
	{
		$this->load->model('system/Sprache_model', 'SprachenModel');

		// Add order clause by index and select the sprache,bezeichnung and index column
		$this->SprachenModel->addOrder('index');
		$this->SprachenModel->addSelect('sprache, bezeichnung, index');

		// Retrieves from public.tbl_sprache
		$langs = $this->SprachenModel->loadWhere(array('content' => true));
		$langs = $this->getDataOrTerminateWithError($langs);
		$langs = array_map(function($lang){
			$data = new stdClass();
			$data->sprache = $lang->sprache;
			$data->bezeichnung = $lang->bezeichnung[($lang->index-1)]; 
			return $data;
		}, $langs);

		$this->terminateWithSuccess($langs);
	}

}