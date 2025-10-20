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

class Kalender extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{

		parent::__construct([
			'getRoomplan' => self::PERM_LOGGED,
            'Stunden' => self::PERM_LOGGED,
            'Reservierungen' => self::PERM_LOGGED,
			'getStundenplan' => self::PERM_LOGGED,
			'getLehreinheitStudiensemester' => self::PERM_LOGGED,
			'updateKalenderEvent' => 'lehre/lvplan:rw',
			'addKalenderEvent' => 'lehre/lvplan:rw'
		]);

        $this->load->library('LogLib');
        $this->loglib->setConfigs(array(
			'classIndex' => 5,
			'functionIndex' => 5,
			'lineIndex' => 4,
			'dbLogType' => 'API', // required
			'dbExecuteUser' => 'RESTful API'
		));

		$this->uid = getAuthUID();

        $this->load->library('form_validation');

		//load models
		//$this->load->model('ressource/Stundenplan_model', 'StundenplanModel');
		//$this->load->model('ressource/Reservierung_model', 'ReservierungModel');

		$this->load->library('KalenderLib');


	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

    /**
     * fetches Stunden layout from database
     * @access public
     *
     */
    public function Stunden()
	{
		$this->load->model('ressource/Stunde_model', 'StundeModel');

		$stunden = $this->StundeModel->load();

        $stunden = $this->getDataOrTerminateWithError($stunden);

		$this->terminateWithSuccess($stunden);
	}

	/**
	 * fetches stundenplan events from a Room and start/end date
	 * @access public
	 *
	 */
	public function getRoomplan()
	{

		// form validation
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('start_date',"start_date","required");
		$this->form_validation->set_rules('end_date',"end_date","required");
		$this->form_validation->set_rules('ort_kurzbz',"ort_kurzbz","required");
		if($this->form_validation->run() === FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		// storing the get parameter in local variables
		$start_date = $this->input->get('start_date', TRUE);
		$end_date = $this->input->get('end_date', TRUE);
		$ort_kurzbz = $this->input->get('ort_kurzbz', TRUE);

		$stundenplan_data =$this->kalenderlib->getRoomData($ort_kurzbz, $start_date, $end_date);

		$this->terminateWithSuccess($stundenplan_data);
	}

	public function updateKalenderEvent()
	{
		// form validation
		$this->load->library('form_validation');
		$this->form_validation->set_data($_POST);
		$this->form_validation->set_rules('kalender_id',"kalender_id","required");
		$this->form_validation->set_rules('ort_kurzbz',"ort_kurzbz","required");
		$this->form_validation->set_rules('start_date',"start_date","required");
		$this->form_validation->set_rules('end_date',"end_date","optional");

		if($this->form_validation->run() === FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		// storing the get parameter in local variables
		$kalender_id = $this->input->post('kalender_id', TRUE);
		$ort_kurzbz = $this->input->post('ort_kurzbz', TRUE);
		$start_date = $this->input->post('start_date', TRUE);
		$end_date = $this->input->post('end_date', TRUE);


		// Was passiert hier?
		// Raumänderung, Tagesänderung, Start / Ende Zeit korrektur
		// Ist das alles ein Endpunkt?
		$stundenplan_data =$this->kalenderlib->updateKalenderEvent($this->uid,$kalender_id, $ort_kurzbz, $start_date, $end_date);

		$this->terminateWithSuccess($stundenplan_data);
	}

	public function addKalenderEvent()
	{
		// form validation
		$this->load->library('form_validation');
		$this->form_validation->set_data($_POST);
		$this->form_validation->set_rules('lehreinheit_id',"kalender_id","required");
		$this->form_validation->set_rules('ort_kurzbz',"ort_kurzbz","required");
		$this->form_validation->set_rules('start_date',"start_date","required");
		$this->form_validation->set_rules('end_date',"end_date","required");

		if($this->form_validation->run() === FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		// storing the get parameter in local variables
		$lehreinheit_id = $this->input->post('lehreinheit_id', TRUE);
		$ort_kurzbz = $this->input->post('ort_kurzbz', TRUE);
		$start_date = $this->input->post('start_date', TRUE);
		$end_date = $this->input->post('end_date', TRUE);

		$this->kalenderlib->addKalenderEvent($this->uid, $ort_kurzbz, $start_date, $end_date, $lehreinheit_id);

		$this->terminateWithSuccess();
	}

	// gets the reservierungen of a room if the ort_kurzbz parameter is supplied otherwise gets the reservierungen of the stundenplan of a student
    public function Reservierungen($ort_kurzbz = null)
	{
		$this->terminateWithSuccess();
	}

	public function getLehreinheitStudiensemester($lehreinheit_id)
	{
		$this->load->model('education/Lehreinheit_model', 'LehreinheitModel');
		$this->LehreinheitModel->addSelect(["studiensemester_kurzbz"]);
		$result = $this->LehreinheitModel->load($lehreinheit_id);
		$result = current($this->getDataOrTerminateWithError($result))->studiensemester_kurzbz;
		$this->terminateWithSuccess($result);
	}
}
