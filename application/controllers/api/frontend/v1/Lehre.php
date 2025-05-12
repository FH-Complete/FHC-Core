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


class Lehre extends FHCAPI_Controller
{
	
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'lvStudentenMail' => self::PERM_LOGGED,
			'LV' => self::PERM_LOGGED,
			'Pruefungen' => self::PERM_LOGGED,
			'getLvViewData' => self::PERM_LOGGED,
			'getZugewieseneLv' => self::PERM_LOGGED
		]);

		// Loads phrases system
		$this->loadPhrases([
			'global'
		]);
		
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

    /**
	 * constructs the emails of the groups from a lehrveranstaltung
	 */
    public function lvStudentenMail()
	{
        $lehreinheit_id = $this->input->get("lehreinheit_id",TRUE);
        
        // return early if the required parameter is missing
        if(!isset($lehreinheit_id))
        {
            $this->terminateWithError('Missing required parameter', self::ERROR_TYPE_GENERAL);
        }

        $this->load->model('education/Lehreinheit_model', 'LehreinheitModel');
        
        $studentenMails = $this->LehreinheitModel->getStudentenMail($lehreinheit_id);

        $studentenMails = $this->getDataOrTerminateWithError($studentenMails);

		//convert array of objects into array of strings
		$studentenMails = array_map(function($element){
			return $element->mail;
		}, $studentenMails);

        $this->terminateWithSuccess($studentenMails);
	}

	public function LV($studiensemester_kurzbz, $lehrveranstaltung_id)
	{
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		$result = $this->LehrveranstaltungModel->getLvsByStudentWithGrades(getAuthUID(), $studiensemester_kurzbz, getUserLanguage(), $lehrveranstaltung_id);

		$result = current($this->getDataOrTerminateWithError($result));
		
		$this->terminateWithSuccess($result);
	}

	/**
	 * fetches all Pruefungen of a student for a specific lehrveranstaltung
	 * if the student passed the Pruefung on the first attempt, no information about the Pruefungen is stored in the database 
	 * @param mixed $lehrveranstaltung_id
	 * @return void
	 */
	public function Pruefungen($lehrveranstaltung_id)
	{
		$this->load->model('education/Pruefung_model', 'PruefungModel');

		$result = $this->PruefungModel->getByStudentAndLv(getAuthUID(), $lehrveranstaltung_id, getUserLanguage());

		$result = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($result);
	}

	/**
	 * fetches all assigned lehrveranstaltungen of a mitarbeiter for a given semester
	 * @param mixed $uid
	 * @param mixed $sem_kurzbz
	 * @return void
	 */
	public function getZugewieseneLv() {
		$uid = $this->input->get("uid",TRUE);
		$sem_kurzbz = $this->input->get("sem_kurzbz",TRUE);

		if(!isset($sem_kurzbz) || isEmptyString($sem_kurzbz))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');

		if (!isset($uid) || isEmptyString($uid))
			$uid = getAuthUID();

		// querying other ma_uids data requires admin permission
		if($uid !== getAuthUID()) {
			$this->load->library('PermissionLib');
			$isAdmin = $this->permissionlib->isBerechtigt('admin');
			if(!$isAdmin) $this->terminateWithError($this->p->t('ui', 'keineBerechtigung'), 'general');
		}

		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$result = $this->LehrveranstaltungModel->getLvForLektorInSemester($sem_kurzbz, $uid);
		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}

	

	

	
}

