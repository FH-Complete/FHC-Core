<?php
/**
 * Copyright (C) 2026 fhcomplete.org
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

class StudstatusAntraege extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getTodos' => self::PERM_LOGGED
		]);

		//load models
		$this->load->model('education/Studierendenantrag_model', 'StudierendenantragModel');
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');

	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * gets all todos of antraegen of the user with stg leitungsfunction
	 * or list of open antraege for user with assistance function
	 * @access public
	 * @return array || []
	 */
	public function getTodos()
	{
		$uid = getAuthUID();

		//at first get studiengang with leitungsfunktion
		$result = $this->BenutzerfunktionModel->getSTGLByUID($uid);

		if(hasData($result))
		{
			$funktionen = getData($result);

			$studiengaenge = [];

			foreach ($funktionen as $funktion) {
				$studiengaenge[] = $funktion->studiengang_kz;
			}

			$dataAntrage = [];

			$result = $this->StudierendenantragModel->getOpenAntraegeForStgl($studiengaenge);

			if (isError($result)) {
				$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
			$dataAntrage[] = getData($result) ?: [];
			$statusRole = 1;

			$this->terminateWithSuccess([count($dataAntrage) > 0 ? $dataAntrage[0] : $dataAntrage, $statusRole]);

		}

		//TODO delete after check if needed or not
		//get studiengaenge of assistance (regarding benutzerfunktion)
		//$result = $this->BenutzerfunktionModel->getSTGAssByUID($uid);
		/*		if(hasData($result)) {
			$statusRole = 2;
			$funktionen = getData($result);

			$studiengaenge = [];

			foreach ($funktionen as $funktion) {
				$studiengaenge[] = $funktion->studiengang_kz;
			}

			$ci->addMeta('stgAss', $studiengaenge);
			$dataAntrage = [];

			foreach ($studiengaenge as $studiengang)
			{
				$result = $this->StudierendenantragModel->getOpenAntraegeForAss($studiengang);

				if (isError($result)) {
					$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
				}
				if(hasData($result)) {
					$dataAntrage[] = getData($result);
					$statusRole = 2;
				}
			}
		}
		else
		{
			$statusRole = 0;
			$dataAntrage = [];
			//$this->terminateWithSuccess([[], $statusRole]);
		}*/

		//get studiengaenge of assistance (regarding rights)
		$stgAss = $this->permissionlib->getSTG_isEntitledFor('student/studierendenantrag');
		if(!is_array($stgAss))
		{
			$statusRole = 0;
			$this->terminateWithSuccess([[], $statusRole]);
		}
		else {
			$statusRole = 2;
			$studiengaenge = [];

			foreach ($stgAss as $stg) {
				$studiengaenge[] = (int) $stg;
			}

			$dataAntrage = [];
			$result = $this->StudierendenantragModel->getOpenAntraegeForAss($studiengaenge);

			if (isError($result)) {
				$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
			if(hasData($result)) {
				$dataAntrage[] = getData($result);
				$statusRole = 2;
			}

			$this->terminateWithSuccess([count($dataAntrage) > 0 ? $dataAntrage[0] : $dataAntrage, $statusRole]);

		}
	}
}