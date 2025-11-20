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
use \DateTime as DateTime;
use \DateTimeZone as DateTimeZone;

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
            'getReservierungen' => self::PERM_LOGGED,
			'LvPlanEvents' => self::PERM_LOGGED,
			'eventsPersonal' => self::PERM_LOGGED,
			'eventsLv' => self::PERM_LOGGED,
			'getLehreinheitStudiensemester' => self::PERM_LOGGED,
			'studiensemesterDateInterval' => self::PERM_LOGGED,
			'getLvPlanForStudiensemester' => self::PERM_LOGGED,
			'getLv' => self::PERM_LOGGED,
			'eventsStgOrg' => self::PERM_LOGGED,
			'fetchFerienEvents' => self::PERM_LOGGED,
			'getStudiengaenge' => self::PERM_LOGGED,
			'getLehrverband' => self::PERM_LOGGED,

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
     * fetches LvPlan and Moodle events together
     * @access public
     *
     */
	public function LvPlanEvents()
	{
		$hasLv = $this->input->post('lv_id');

		return $hasLv ? $this->eventsLv() : $this->eventsPersonal();
	}

	/**
	 * fetches LvPlan, Moodle and Ferien events together for the logged in user
	 *
	 * @access public
	 */
	public function eventsPersonal()
	{
		$this->load->library('StundenplanLib');

		// form validation
		$this->form_validation->set_rules('start_date', "start_date", "required");
		$this->form_validation->set_rules('end_date', "end_date", "required");

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		// storing the post parameter in local variables
		$start_date = $this->input->post('start_date', true);
		$end_date = $this->input->post('end_date', true);

		// fetching lvplan events
		$result = $this->stundenplanlib->getEventsUser($start_date, $end_date);
		$lvplanEvents = $this->getDataOrTerminateWithError($result);

		// fetching moodle events
		$moodleEvents = $this->fetchMoodleEvents($start_date, $end_date);

		// fetching ferien events
		$ferienEvents = $this->fetchFerienEvents($start_date, $end_date);


		$this->terminateWithSuccess(array_merge(
			$lvplanEvents,
			$moodleEvents,
			$ferienEvents
		));
	}

	/**
	 * fetches LvPlan for studiengang / semester / verband / gruppe
	 *
	 * @access public
	 */
	public function eventsStgOrg()
	{
		$this->load->library('StundenplanLib');

		// form validation
		$this->form_validation->set_rules('start_date', "start_date", "required");
		$this->form_validation->set_rules('end_date', "end_date", "required");
		//$this->form_validation->set_rules('stg_kz', "stg_kz", "required"); //no validation show empty calendar

		if (!$this->form_validation->run())
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
			$stgOrgEvents = [];
			$ferienEvents = [];
		}

		else
		{
			$start_date = $this->input->post('start_date', true);
			$end_date = $this->input->post('end_date', true);
			$stg_kz = $this->input->post('stg_kz', true);
			$sem = $this->input->post('sem', true);
			$verband = $this->input->post('verband', true);
			$gruppe = $this->input->post('gruppe', true);

			$result = $this->stundenplanlib->getEventsStgOrg($start_date, $end_date, $stg_kz, $sem, $verband, $gruppe);
			$stgOrgEvents = $this->getDataOrTerminateWithError($result);

			$result = $this->stundenplanlib->fetchFerienTageEvents($start_date, $end_date, $stg_kz);
			$ferienEvents = $this->getDataOrTerminateWithError($result);
		}

		$this->terminateWithSuccess(array_merge(
			$stgOrgEvents,
			$ferienEvents
		));
	}

	/**
	 * fetches LvPlan and Ferien events together for the lv
	 *
	 * @access public
	 */
	public function eventsLv()
	{
		$this->load->library('StundenplanLib');

		// form validation
		$this->form_validation->set_rules('start_date', "start_date", "required");
		$this->form_validation->set_rules('end_date', "end_date", "required");
		$this->form_validation->set_rules('lv_id', "lv_id", "required|integer");
		
		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		// storing the post parameter in local variables
		$start_date = $this->input->post('start_date', true);
		$end_date = $this->input->post('end_date', true);
		$lv_id = $this->input->post('lv_id', true);

		// fetching lvplan events
		$result = $this->stundenplanlib->getEventsLv($lv_id, $start_date, $end_date);
		$lvplanEvents = $this->getDataOrTerminateWithError($result);

		// fetching ferien events
		$ferienEvents = $this->fetchFerienEvents($start_date, $end_date);

		$this->terminateWithSuccess(array_merge(
			$lvplanEvents,
			$ferienEvents
		));
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

		$this->StundeModel->addOrder('stunde', 'ASC');
		$stunden = $this->StundeModel->load();

        $stunden = $this->getDataOrTerminateWithError($stunden);

		$this->terminateWithSuccess($stunden);
	}

	/**
	 * fetches room events from a certain date
	 * @access public
	 *
	 * @return void
	 */
	public function getRoomplan()
	{
		$this->form_validation->set_rules('ort_kurzbz', "Ort", "required");
		$this->form_validation->set_rules('start_date', "start_date", "required");
		$this->form_validation->set_rules('end_date', "end_date", "required");

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		// storing the post parameter in local variables
		$ort_kurzbz = $this->input->post('ort_kurzbz', true);
		$start_date = $this->input->post('start_date', true);
		$end_date = $this->input->post('end_date', true);

		// get data
		$this->load->library('StundenplanLib');

		$roomplan_data = $this->stundenplanlib->getRoomplan($ort_kurzbz, $start_date, $end_date);

		$roomplan_data = $this->getDataOrTerminateWithError($roomplan_data);
		
		$this->terminateWithSuccess($roomplan_data);
	}
	
	/**
	 * gets the reservierungen of a room if the ort_kurzbz parameter is
	 * supplied otherwise gets the reservierungen of the lvplan of a student
	 * @access public
	 *
	 * @param string	$ort_kurzbz
	 * @return void
	 */
	public function getReservierungen($ort_kurzbz = null)
	{
		$this->form_validation->set_rules('start_date', "StartDate", "required");
		$this->form_validation->set_rules('end_date', "EndDate", "required");
		
		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		// storing the post parameter in local variables
		$start_date = $this->input->post('start_date', true);
		$end_date = $this->input->post('end_date', true);

		// get data
		$this->load->library('StundenplanLib');

		$result = $this->stundenplanlib->getReservierungen($start_date, $end_date, $ort_kurzbz);

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

	/**
	 * get details for a lv
	 * @access public
	 *
	 * @param integer		$lehrveranstaltung_id
	 * @return void
	 */
	public function getLv($lehrveranstaltung_id)
	{
		if (!$lehrveranstaltung_id && $lehrveranstaltung_id !== 0 && $lehrveranstaltung_id !== '0')
			return show_404();

		// Load Phrases
		$this->loadPhrases(['lehre']);

		// Validation
		$this->form_validation->set_data([
			'lehrveranstaltung_id' => $lehrveranstaltung_id
		]);

		$this->form_validation->set_rules('lehrveranstaltung_id', $this->p->t('lehre', 'lehrveranstaltung_id'), 'integer');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		// Get Data
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		$result = $this->LehrveranstaltungModel->load($lehrveranstaltung_id);

		$result = $this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess(current($result));
	}

	public function getStudiengaenge()
	{
		$this->load->model('organisation/Studiengang_model','StudiengangModel');

		$this->StudiengangModel->addOrder('typ');
		$this->StudiengangModel->addOrder('kurzbz');
		$result = $this->StudiengangModel->loadWhere([
			'aktiv' => true
		]);

		$data = $this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess($data);
	}

	public function getLehrverband($studiengang_kz, $semester=null, $verband=null)
	{
		$this->load->model('organisation/Lehrverband_model','LehrverbandModel');

		$where = [
			'aktiv' => true,
			'studiengang_kz' => $studiengang_kz,
		];

		if ($semester !== null  && $semester !== 'null') {
			$where['semester'] = $semester;
		}
		if ($verband !== null  && $verband !== 'null') {
			$where['verband'] = $verband;
		}

		$this->LehrverbandModel->addOrder('studiengang_kz');
		$this->LehrverbandModel->addOrder('semester');
		$this->LehrverbandModel->addOrder('verband');
		$this->LehrverbandModel->addOrder('gruppe');
		$result = $this->LehrverbandModel->loadWhere($where);

		$data = $this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess($data);
	}

	/**
	 * fetch moodle events
	 *
	 * @param string		$start_date
	 * @param string		$end_date
	 * @return array
	 */
	private function fetchMoodleEvents($start_date, $end_date)
	{
		$this->load->config('calendar');

		$tz = new DateTimeZone($this->config->item('timezone'));
		
		$start = new DateTime($start_date);
		$start->setTimezone($tz);
		
		$end = new DateTime($end_date);
		$end->setTimezone($tz);
		$end->modify('+1 day -1 second');
		
		$moodle_events = [];
		
		Events::trigger(
			'moodleCalendarEvents',
			function & () use (&$moodle_events) {
				return $moodle_events;
			},
			[
				'start_date' => $start->format('c'),
				'end_date' => $end->format('c'),
				'username' => getAuthUID()
			]
		);

		return $moodle_events;
	}

	/**
	 * fetch ferien events
	 *
	 * @param string		$start_date
	 * @param string		$end_date
	 * @return array
	 */
	private function fetchFerienEvents($start_date, $end_date)
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->load->model('education/Studentlehrverband_model', 'StudentLehrverbandModel');

		$currentStudiensemester = $this->StudiensemesterModel->getByDate($start_date);
		$currentStudiensemester = $this->getDataOrTerminateWithError($currentStudiensemester);

		if ($currentStudiensemester) {
			$studentsemester_kurzbz = current($currentStudiensemester)->studiensemester_kurzbz;

			$studiengang = $this->StudentLehrverbandModel->loadWhere([
				"student_uid" => getAuthUID(),
				"studiensemester_kurzbz" => $studentsemester_kurzbz
			]);
			$studiengang = $this->getDataOrTerminateWithError($studiengang);

			if ($studiengang)
				$studiengang_kz = current($studiengang)->studiengang_kz;
			else
				$studiengang_kz = 0;
		} else {
			$studiengang_kz = 0;
		}

		$ferienEvents = $this->stundenplanlib->fetchFerienTageEvents($start_date, $end_date, $studiengang_kz);

		return $this->getDataOrTerminateWithError($ferienEvents);
	}
}
