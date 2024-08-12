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
 * This controller operates between (interface) the JS (GUI) and the back-end
 * Provides data to the ajax get calls about generally used lists
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Lists extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getStudiensemester' => self::PERM_LOGGED,
			'getStgs' => self::PERM_LOGGED,
			'getSprachen' => self::PERM_LOGGED,
			'getGeschlechter' => self::PERM_LOGGED,
			'getAusbildungen' => self::PERM_LOGGED,
			'getOrgforms' => self::PERM_LOGGED,
			'getStati' => self::PERM_LOGGED
		]);
	}

	public function getStudiensemester()
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$this->StudiensemesterModel->addOrder('ende');

		$result = $this->StudiensemesterModel->load();

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getStgs()
	{
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$this->StudiengangModel->addSelect('*');
		$this->StudiengangModel->addSelect('UPPER(typ || kurzbz) AS kuerzel');

		$this->StudiengangModel->addOrder('typ');
		$this->StudiengangModel->addOrder('kurzbz');

		$result = $this->StudiengangModel->load();

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getSprachen()
	{
		$this->load->model('system/Sprache_model', 'SpracheModel');

		$this->SpracheModel->addOrder('sprache');

		$result = $this->SpracheModel->load();
		$data = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($data);
	}

	public function getGeschlechter()
	{
		$this->load->model('person/Geschlecht_model', 'GeschlechtModel');

		$this->GeschlechtModel->addOrder('sort');
		$this->GeschlechtModel->addOrder('geschlecht');

		$this->GeschlechtModel->addSelect('*');
		#$this->GeschlechtModel->addTranslatedSelect("bezeichnung_mehrsprachig", "bezeichnung");
		$this->GeschlechtModel->addSelect("bezeichnung_mehrsprachig[(SELECT index FROM public.tbl_sprache WHERE sprache=" . $this->GeschlechtModel->escape(DEFAULT_LANGUAGE) . " LIMIT 1)] AS bezeichnung");

		$result = $this->GeschlechtModel->load();
		$data = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($data);
	}

	public function getAusbildungen()
	{
		$this->load->model('codex/Ausbildung_model', 'AusbildungModel');

		$this->AusbildungModel->addOrder('ausbildungcode');

		$result = $this->AusbildungModel->load();
		$data = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($data);
	}

	public function getOrgforms()
	{
		$this->load->model('codex/Orgform_model', 'OrgformModel');

		$this->OrgformModel->addOrder('bezeichnung');

		$result = $this->OrgformModel->loadWhere(['rolle' => true]);
		$data = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($data);
	}

	public function getStati()
	{
		$lang = getUserLanguage();
		$this->load->model('crm/Status_model', 'StatusModel');

		$this->StatusModel->addSelect('*');
		#$this->StatusModel->addTranslatedSelect('bezeichnung_mehrsprachig', 'bezeichnung');
		$this->StatusModel->addSelect(
			'bezeichnung_mehrsprachig[(
				SELECT index
				FROM public.tbl_sprache
				WHERE sprache=' . $this->StatusModel->escape($lang) . '
				LIMIT 1
			)] AS bezeichnung',
			false
		);
		#$this->StatusModel->addOrder('ext_id');

		$result = $this->StatusModel->load();
		$data = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($data);
	}
}
