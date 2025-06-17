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

use CI3_Events as Events;

class LvPlan extends FHCAPI_Controller
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
			'LvPlanEvents' => self::PERM_LOGGED,
			'getLehreinheitStudiensemester' => self::PERM_LOGGED,
			'studiensemesterDateInterval' => self::PERM_LOGGED,
			'getLvPlanForStudiensemester' => self::PERM_LOGGED,
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

		//load models
		$this->load->model('ressource/Stundenplan_model', 'StundenplanModel');
		$this->load->model('ressource/Reservierung_model', 'ReservierungModel');


	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	 /**
     * fetches LvPlan and Moodle events together
     * @access public
     *
     */
	public function LvPlanEvents(){
		$this->load->library('StundenplanLib');

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
		$lv_id = $this->input->get('lv_id', TRUE);

		$res_lvplan_events = $this->stundenplanlib->getStundenplan($start_date,$end_date,$lv_id);
		$lvplan_events = $this->getDataOrTerminateWithError($res_lvplan_events);
		if( is_null($lvplan_events) || isEmptyArray($lvplan_events) )
		{
			$lvplan_events = array();
		}

		// fetching moodle events
		$moodle_events = [];
		Events::trigger(
			'moodleCalendarEvents',
			function & () use (&$moodle_events)
			{
				return $moodle_events;
			},
			[
				'start_date' => $start_date,
				'end_date' => $end_date, 
				'username' => getAuthUID()
			]
		);
		
		$result = array_merge($lvplan_events,$moodle_events);
		// sort array with moodle events first
		usort($result, function($a, $b){
			if ($a->type === 'moodle' && $b->type !== 'moodle') {
				return -1;
			} elseif ($a->type !== 'moodle' && $b->type === 'moodle') {
				return 1;
			} else {
				return 0;
			}
		});
		$this->terminateWithSuccess($result);
	}

	//TODO: delete this function if we don't use the old calendar export endpoints anymore
	public function studiensemesterDateInterval($date){
		$this->load->model('organisation/Studiensemester_model','StudiensemesterModel');
		$studiensemester =$this->StudiensemesterModel->getByDate(date_format(date_create($date),'Y-m-d'));
		$studiensemester =current($this->getDataOrTerminateWithError($studiensemester));
		$this->terminateWithSuccess($studiensemester);
	}

	public function getLvPlanForStudiensemester($studiensemester,$lvid){
		$this->load->library('StundenplanLib');
		$this->load->model('organisation/Studiensemester_model','StudiensemesterModel');
		
		$studiensemester_result = $this->StudiensemesterModel->loadWhere(["studiensemester_kurzbz"=>$studiensemester]);
		$studiensemester_result = current($this->getDataOrTerminateWithError($studiensemester_result));
		$timespan_start = new DateTime($studiensemester_result->start);
		$timespan_ende = new DateTime($studiensemester_result->ende);
		$lvplan = $this->stundenplanlib->getStundenplan(date_format($timespan_start, 'Y-m-d'),date_format($timespan_ende, 'Y-m-d'), $lvid);
		$this->terminateWithSuccess($lvplan);
		
	}
		

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
		$this->load->library('StundenplanLib');
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

		$roomplan_data = $this->LvPlan->lvPlanGruppierung($this->LvPlan->getRoomQuery($ort_kurzbz, $start_date, $end_date));

        $roomplan_data = $this->getDataOrTerminateWithError($roomplan_data);
		$this->stundenplanlib->expand_object_information($roomplan_data);

		$this->terminateWithSuccess($roomplan_data);

	}
	
	// gets the reservierungen of a room if the ort_kurzbz parameter is supplied otherwise gets the reservierungen of the lvplan of a student
    public function Reservierungen($ort_kurzbz = null)
	{
		$this->load->library('StundenplanLib');
		//form validation
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('start_date', "StartDate", "required");
		$this->form_validation->set_rules('end_date', "EndDate", "required");
		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$this->load->model('ressource/Mitarbeiter_model','MitarbeiterModel');

		// storing the get parameter in local variables
        $start_date = $this->input->get('start_date', TRUE);
        $end_date = $this->input->get('end_date', TRUE);

		$result = $this->stundenplanlib->getReservierungen($start_date,$end_date,$ort_kurzbz);
		$result = $this->getDataOrTerminateWithError($result);	
		$this->terminateWithSuccess($result);
	}

	public function getLehreinheitStudiensemester($lehreinheit_id){
		$this->load->model('education/Lehreinheit_model', 'LehreinheitModel');
		$this->LehreinheitModel->addSelect(["studiensemester_kurzbz"]);
		$result = $this->LehreinheitModel->load($lehreinheit_id);
		$result = current($this->getDataOrTerminateWithError($result))->studiensemester_kurzbz;
		$this->terminateWithSuccess($result);
	}

	

}
