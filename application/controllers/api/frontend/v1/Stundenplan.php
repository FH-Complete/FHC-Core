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

class Stundenplan extends FHCAPI_Controller
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
		]);

        $this->load->library('LogLib');
        $this->loglib->setConfigs(array(
			'classIndex' => 5,
			'functionIndex' => 5,
			'lineIndex' => 4,
			'dbLogType' => 'API', // required
			'dbExecuteUser' => 'RESTful API'
		));

        $this->load->library('form_validation');
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
     * fetches room events from a certain date
     * @access public
     * 
     */
	public function getRoomplan()
	{
       
        $this->load->model('ressource/Stundenplan_model', 'StundenplanModel');
		$this->load->model('ressource/Stunde_model', 'StundeModel');
		
        // form validation
        $this->load->library('form_validation');
        $this->form_validation->set_data($_GET);
        $this->form_validation->set_rules('ort_kurzbz',"Ort","required");
        $this->form_validation->set_rules('start_date',"start_date","required");
        $this->form_validation->set_rules('end_date',"end_date","required");
        if($this->form_validation->run() === FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array()); 
        
        // storing the get parameter in local variables
        $ort_kurzbz = $this->input->get('ort_kurzbz', TRUE);
        $start_date = $this->input->get('start_date', TRUE);
        $end_date = $this->input->get('end_date', TRUE);

		$result = $this->StundenplanModel->stundenplanGruppierung($this->StundenplanModel->getRoomQuery($ort_kurzbz, $start_date, $end_date));  
		
        $result = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($result);
		
	}

	/**
	 * fetches stundenplan events from a UID and start/end date
	 * @access public
	 * 
	 */
	public function getStundenplan(){

		// form validation
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('start_date', "start_date", "required");
		$this->form_validation->set_rules('end_date', "end_date", "required");
		if ($this->form_validation->run() === FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		// storing the get parameter in local variables
		$start_date = $this->input->get('start_date', TRUE);
		$end_date = $this->input->get('end_date', TRUE);
		
		$this->load->model('ressource/Stundenplan_model', 'StundenplanModel');

		// the stundenplan query needs the uid and a start and end date
		$stundenplan_data = $this->StundenplanModel->stundenplanGruppierung($this->StundenplanModel->getStundenplanQuery(get_uid(),$start_date,$end_date)); 
		$stundenplan_data = $this->getDataOrTerminateWithError($stundenplan_data);
		
		$this->terminateWithSuccess($stundenplan_data);
	}

	// gets the reservierungen of a room if the ort_kurzbz parameter is supplied otherwise gets the reservierungen of the stundenplan of a student
    public function Reservierungen($ort_kurzbz = null)
	{
        $this->load->model('ressource/Reservierung_model', 'ReservierungModel');
        $this->load->model('ressource/Stunde_model', 'StundeModel');
        $this->load->model('ressource/Mitarbeiter_model','MitarbeiterModel');

		//form validation
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('start_date', "StartDate", "required");
		$this->form_validation->set_rules('end_date', "EndDate", "required");
		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

        // storing the get parameter in local variables
        $start_date = $this->input->get('start_date', TRUE);
        $end_date = $this->input->get('end_date', TRUE);

        // querying the stunden
        $stunden = $this->StundeModel->load();

        $stunden = $this->getDataOrTerminateWithError($stunden);

		// querying the reservierungen
		
		$result = $this->ReservierungModel->getReservierungen($start_date, $end_date, $ort_kurzbz);
		

        $result = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($result);
        
	}

    

}

