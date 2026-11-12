<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

use CI3_Events as Events;

class InfoTerminalAPI extends FHCAPI_Controller
{
	const EXCLUDED_CONTENT_TYPES = ["redirect"];

	public function __construct()
	{
		parent::__construct(array(
			'getUserData' => self::PERM_ANONYMOUS,
			'getPersonalEvents' => self::PERM_ANONYMOUS,
			'getStudySemestersStartingFrom' => self::PERM_ANONYMOUS,
			'getContainingOrNearestStudySemesterByDateRange' => self::PERM_ANONYMOUS,
			'getHours' => self::PERM_ANONYMOUS,
			'compactibleEventTypes' => self::PERM_ANONYMOUS,
			'getInfoTerminalRenderers' => self::PERM_ANONYMOUS,
			'getContentPage' => self::PERM_ANONYMOUS,
		));

		$this->load->config('info_terminal');
		$this->load->config('lv_plan');
		
		$this->load->helper('hlp_common_helper');

		if (!defined('CI_ENVIRONMENT') || CI_ENVIRONMENT !== 'development') 
			$this->_checkIpWhitelist();
		
		
		
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$this->load->model('organisation/studiensemester_model', 'StudiensemesterModel');

		$this->load->library('form_validation');
		$this->load->library('StundenplanLib');
 	}

	public function getUserData()
	{
		$cardNumber = $this->input->get("key_input");
		$userUID = $this->_getUIDFromCardNumber($cardNumber);
		if (!$userUID) return $this->terminateWithError("User not found!");

		$this->BenutzerModel->addSelect("uid, person_id, insertamum");
		$userResult = $this->BenutzerModel->load(["uid" => $userUID]);
		$userData = $this->getDataOrTerminateWithError($userResult);

		$this->terminateWithSuccess($userData);
	}

	public function getPersonalEvents()
	{
		$cardNumber = $this->input->post("key_input");
		$userUID = $this->_getUIDFromCardNumber($cardNumber);
		if (!$userUID) return $this->terminateWithError("User not found!");

		$this->load->library('LvPlanCalendarLib', array('uid' => $userUID));

		// form validation
		$this->form_validation->set_rules('start_date', "start_date", "required");
		$this->form_validation->set_rules('end_date', "end_date", "required");

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		// storing the post parameter in local variables
		$start_date = $this->input->post('start_date', true);
		$end_date = $this->input->post('end_date', true);

		// fetching lvplan events
		if ($this->config->item('is_new_calendar_structure_enabled')) {
			$lvplanEvents = $this->lvplancalendarlib->getEventsForUser($start_date, $end_date, $userUID);
			$lvplanEvents = array_map(function($item) {
				$item["deletable"] = false;

				return $item;
			}, $lvplanEvents);
			$ferienEvents = $this->lvplancalendarlib->fetchFerienEventsByUser($start_date, $end_date, $userUID);
		} else {
			$result = $this->stundenplanlib->getEventsUser($start_date, $end_date, $userUID);
			$lvplanEvents = $this->getDataOrTerminateWithError($result);
			$ferienEvents = $this->fetchFerienEvents($start_date, $end_date, $userUID);
		}
		
		// fetching moodle events
		$moodleEvents = $this->fetchMoodleEvents($start_date, $end_date, $userUID);

		$this->terminateWithSuccess(array_merge(
			$lvplanEvents,
			$moodleEvents,
			[]
		));
	}

	public function getStudySemestersStartingFrom()
	{
		$start = $this->input->get('start');

		$this->StudiensemesterModel->addOrder('ende', 'DESC');


		if ($start)
		{
			$result = $this->StudiensemesterModel->loadWhere([
				'start >= ' => $start
			]);
		} else {
			$result = $this->StudiensemesterModel->load();
		}

		if (isError($result)) {
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_DB);
		}

		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function getContainingOrNearestStudySemesterByDateRange()
	{
		$from = $this->input->get("from");
		$to = $this->input->get("to");

		$result = $this->StudiensemesterModel->getContainingOrNearestByDateRange($from, $to);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getHours()
	{
		$this->load->model('ressource/Stunde_model', 'StundenModel');

		$this->StundenModel->addOrder('stunde', 'ASC');
		$stunden = $this->StundenModel->load();

		$stunden = $this->getDataOrTerminateWithError($stunden);

		$this->terminateWithSuccess($stunden);
	}

	public function compactibleEventTypes()
	{
		$this->terminateWithSuccess(["lehreinheit", "reservierung", "ferien", "moodle"]);
	}

	public function getInfoTerminalRenderers(){
		$renderer_paths = [];
		Events::trigger(
			'loadInfoTerminalRenderers',
			function & () use (&$renderer_paths)
			{
				return $renderer_paths;
			}
		);
		$this->terminateWithSuccess($renderer_paths);
	}

	public function getContentPage()
	{
		$cardNumber = $this->input->get("key_input");
		$userUID = $this->_getUIDFromCardNumber($cardNumber);
		if (!$userUID) return $this->terminateWithError("User not found!");

		$this->load->library('CmsLib', ["uid" => $userUID]);

        $content_id = $this->input->get("content_id",TRUE);
		if (!$content_id) $this->terminateWithError("Content id not provided!");

        $version = $this->input->get("version",TRUE);
        $sprache = $this->input->get("sprache",TRUE);
        $sichtbar = $this->input->get("sichtbar",TRUE);

		$contentResult = $this->cmslib->getContent($content_id, $version, $sprache, $sichtbar);
		if (isError($contentResult))  $this->terminateWithError("Content with provided id not found!");

		$content = getData($contentResult);

		if (in_array($content["type"], self::EXCLUDED_CONTENT_TYPES)) $this->terminateWithError("Content with provided id not found!");
		
		$this->terminateWithSuccess($content);
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	private function fetchFerienEvents($start_date, $end_date, $uid = null)
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->load->model('education/Studentlehrverband_model', 'StudentLehrverbandModel');

		$currentStudiensemester = $this->StudiensemesterModel->getByDate($start_date);
		$currentStudiensemester = $this->getDataOrTerminateWithError($currentStudiensemester);

		if ($currentStudiensemester) {
			$studentsemester_kurzbz = current($currentStudiensemester)->studiensemester_kurzbz;

			$studiengang = $this->StudentLehrverbandModel->loadWhere([
				"student_uid" => $uid ?? getAuthUID(),
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

	private function fetchMoodleEvents($start_date, $end_date, $uid = null)
	{
		if ($uid && $uid !== getAuthUID()) {
			return [];
		}

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
			function &() use (&$moodle_events) {
				return $moodle_events;
			},
			[
				'start_date' => $start->format('c'),
				'end_date' => $end->format('c'),
				'username' => $uid ?? getAuthUID()
			]
		);

		return $moodle_events;
	}

	/**
	 * Ends the request with a 403 if the client IP is not in the whitelist
	 */
	private function _checkIpWhitelist()
	{
		$clientIp = $this->input->ip_address();
		$allowed = $this->config->item('info_terminal_allowed_ips') ?: [];

		foreach ($allowed as $rule)
		{
			if (is_ip_allowed_by_rule($clientIp, $rule))
				return;
		}

		log_message('error', 'InfoTerminalAPI: access denied for IP ' . $clientIp);

		$this->output
			->set_status_header(403)
			->set_content_type('application/json')
			->set_output(json_encode(['error' => 'Access denied']))
			->_display();
		exit;
	}

	private function _getUIDFromCardNumber($cardNumber) {
		if (empty($cardNumber)) {
			return show_error('Auth data not provided');
		}
		
		$this->load->model('person/Person_model', 'PersonModel');
		$return = $this->PersonModel->getUIDByCardNumber($cardNumber);
		if (isError($return))
			return show_error('Issue encountered! Try again later.');

		if (!hasData($return))
		show_error('Card number does not match any user');

		$uid = getData($return)[0]->uid;
		if (isEmptyString($uid))
			show_error('Card number does not match any user');

		return $uid;
	}
}