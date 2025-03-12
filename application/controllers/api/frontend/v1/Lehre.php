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
			'getStudentProjektarbeiten' => self::PERM_LOGGED, // TODO: abgabetool berechtigung?
			'getStudentProjektabgaben' => self::PERM_LOGGED
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

	public function getStudentProjektabgaben() {
		$projektarbeit_id = $this->input->get("projektarbeit_id",TRUE);

		$this->load->model('education/Abgabe_model', 'AbgabeModel');
		$ret = $this->AbgabeModel->getProjektarbeitAbgabetermine($projektarbeit_id);

		
		$this->terminateWithSuccess($ret);
	}
	
	public function getStudentProjektarbeiten($uid)
	{
		$this->load->model('education/Abgabe_model', 'AbgabeModel');
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		
		$isZugeteilterBetreuer = count($this->AbgabeModel->checkZuordnung($uid, getAuthUID())->retval) > 0;
		$ret = $this->AbgabeModel->getStudentProjektarbeitenLegacy($uid);
		$this->addMeta('legacyQueryRetval', $ret);
		$this->addMeta('isZugeteilterBetreuer', $isZugeteilterBetreuer);
		
		if ($this->MitarbeiterModel->isMitarbeiter(getAuthUID()) && ($isZugeteilterBetreuer)){
			$projektarbeiten = $this->AbgabeModel->getStudentProjektarbeitenWithBetreuer($uid);
		} else {
			$projektarbeiten = $this->AbgabeModel->getStudentProjektarbeitenWithBetreuer(getAuthUID());
		}
		

		$this->terminateWithSuccess(array($projektarbeiten, DOMAIN));	
	}
	
}

