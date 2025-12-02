<?php
/**
 * Copyright (C) 2025 fhcomplete.org
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

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Setup extends FHCAPI_Controller
{
	private $_ci;
	private $_uid;

	public function __construct()
	{
		parent::__construct([
			'getLETabs' => ['admin:r', 'assistenz:r'],
			'getLVTabs' => ['admin:r', 'assistenz:r'],
			'getStudiensemester' => ['admin:r', 'assistenz:r'],
			'getSprache' => ['admin:r', 'assistenz:r'],
			'getRaumtyp' => ['admin:r', 'assistenz:r'],
			'getLehrform' => ['admin:r', 'assistenz:r'],
		]);

		$this->_ci = &get_instance();
		$this->_setAuthUID();

		$this->_ci->load->model('education/Lehreinheit_model', 'LehreinheitModel');
		$this->_ci->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		$this->_ci->load->library('VariableLib', ['uid' => $this->_uid]);
	}

	public function getLETabs()
	{
		$tabs['details'] = array (
			'title' =>  'Details',
			'component' => APP_ROOT . 'public/js/components/LVVerwaltung/Tabs/Details.js',
			'config' => []
		);
		$tabs['gruppen'] = array (
			'title' =>  'Gruppen',
			'component' => APP_ROOT . 'public/js/components/LVVerwaltung/Tabs/Gruppen.js',
			'config' => []
		);
		$tabs['lektor'] = array (
			'title' =>  'LektorInnenzuteilung',
			'component' => APP_ROOT . 'public/js/components/LVVerwaltung/Tabs/Lektor.js',
			'config' => []
		);
		$tabs['termine'] = array (
			'title' =>  'Termine',
			'component' => APP_ROOT . 'public/js/components/LVVerwaltung/Tabs/Termine.js',
			'config' => []
		);
		$tabs['notiz'] = array (
			'title' =>  'Notizen',
			'component' => APP_ROOT . 'public/js/components/LVVerwaltung/Tabs/Notiz.js',
			'config' => []
		);
		$this->terminateWithSuccess($tabs);
	}

	public function getLVTabs()
	{
		$tabs['termine'] = array (
			'title' =>  'Termine',
			'component' => APP_ROOT . 'public/js/components/LVVerwaltung/Tabs/LVTermine.js',
			'config' => []
		);
		$this->terminateWithSuccess($tabs);
	}

	public function getStudiensemester()
	{
		$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->_ci->StudiensemesterModel->addOrder('start', 'DESC');
		$this->terminateWithSuccess(getData($this->_ci->StudiensemesterModel->load()));
	}
	public function getSprache()
	{
		$this->_ci->load->model('system/Sprache_model', 'SpracheModel');
		$this->terminateWithSuccess(getData($this->_ci->SpracheModel->load()));
	}

	public function getRaumtyp()
	{
		$this->_ci->load->model('ressource/Raumtyp_model', 'RaumtypModel');
		$this->_ci->RaumtypModel->addOrder('raumtyp_kurzbz');
		$this->terminateWithSuccess(getData($this->_ci->RaumtypModel->loadWhere(array('aktiv' => true))));
	}

	public function getLehrform()
	{
		$language = $this->_getLanguageIndex();

		$this->_ci->load->model('codex/lehrform_model', 'LehrformModel');

		$this->_ci->LehrformModel->addSelect(
			'*,
			bezeichnung_kurz[('.$language.')] as bez_kurz,
			bezeichnung_lang[('.$language.')] as bez
			'
		);
		$this->terminateWithSuccess(getData($this->_ci->LehrformModel->load()));
	}

	private function _getLanguageIndex()
	{
		$this->_ci->load->model('system/Sprache_model', 'SpracheModel');
		$this->_ci->SpracheModel->addSelect('index');
		$result = $this->_ci->SpracheModel->loadWhere(array('sprache' => getUserLanguage()));

		return hasData($result) ? getData($result)[0]->index : 1;
	}

	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();

		if (!$this->_uid)
			show_error('User authentification failed');
	}
}
