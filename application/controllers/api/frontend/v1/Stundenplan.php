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
			'StundenplanEvents' => self::PERM_LOGGED,
			'getLehreinheitStudiensemester' => self::PERM_LOGGED,
			'studiensemesterDateInterval' => self::PERM_LOGGED,
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
     * fetches Stundenplan and Moodle events together
     * @access public
     *
     */
	public function StundenplanEvents(){
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

		$stundenplan_events = $this->stundenplanlib->getStundenplan($start_date,$end_date,$lv_id);
		$stundenplan_events = $this->getDataOrTerminateWithError($stundenplan_events);	

		// fetching moodle events
		$moodle_start_date = new DateTime($start_date);
		$moodle_start_date = $moodle_start_date->getTimestamp();
		$moodle_end_date = new DateTime($end_date);
		$moodle_end_date = $moodle_end_date->getTimestamp();
		$moodle_events = [];
		Events::trigger('moodleCalendarEvents',
						function & () use (&$moodle_events) {
							return $moodle_events;
						},
						['timestart'=>$moodle_start_date,'timeend'=>$moodle_end_date, 'username'=>getAuthUID()]
		);
		
		$moodle_events = array_map(function($event){
			$moodle_event_timestart = new DateTime($event->timestart);
			$moodle_event_timeend = new DateTime($event->timeend);
			$convertedEvent = new stdClass();
			$convertedEvent->type = 'moodle';
			$convertedEvent->beginn = $moodle_event_timestart->format('H:i:s');
			$convertedEvent->ende = $moodle_event_timeend->format('H:i:s');
			$convertedEvent->allDayEvent = true;
			$convertedEvent->datum = $moodle_event_timestart->format('Y-n-j');
			$convertedEvent->purpose = $event->purpose;
			$convertedEvent->assignment = $event->activityname;
			$convertedEvent->topic = $event->activitystr;
			$convertedEvent->lektor = [];
			$convertedEvent->gruppe = [];
			$convertedEvent->ort_kurzbz = $event->location;
			$convertedEvent->lehreinheit_id = $event->lehreinheitsNummber ?? null;
			$convertedEvent->titel = $event->course->fullname;
			$convertedEvent->lehrfach = '';
			$convertedEvent->lehrform = '';
			$convertedEvent->lehrfach_bez = '';
			$convertedEvent->organisationseinheit = '';
			$convertedEvent->farbe = '00689E';
			$convertedEvent->lehrveranstaltung_id = 0;
			$convertedEvent->ort_content_id = 0;
			$convertedEvent->url = $event->url;
			return $convertedEvent;
		},$moodle_events);
		
		$result = array_merge($stundenplan_events,$moodle_events);
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

		$roomplan_data = $this->StundenplanModel->stundenplanGruppierung($this->StundenplanModel->getRoomQuery($ort_kurzbz, $start_date, $end_date));

        $roomplan_data = $this->getDataOrTerminateWithError($roomplan_data);

		$this->expand_object_information($roomplan_data);

		$this->terminateWithSuccess($roomplan_data);

	}
	
	// gets the reservierungen of a room if the ort_kurzbz parameter is supplied otherwise gets the reservierungen of the stundenplan of a student
    public function Reservierungen($ort_kurzbz = null)
	{
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

		$this->load->library('StundenplanLib');
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
