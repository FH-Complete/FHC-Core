<?php


if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;
use \DateTimeZone as DateTimeZone;
use \DateInterval as DateInterval;
use \DatePeriod as DatePeriod;

class StundenplanLib
{

	private $_ci; // Code igniter instance
	

	/**
	 * fetches Stundenplan events for the loggedin user between start and end
	 * or for a lv
	 *
	 * @param string		$start
	 * @param string		$end
	 * @param string|null	$lehrveranstaltung_id
	 * @return stdClass
	 * @access public
	 */
	public function getStundenplan($start, $end, $lehrveranstaltung_id = null)
	{
		if (!$lehrveranstaltung_id && $lehrveranstaltung_id !== 0)
			return $this->getEventsUser($start, $end);

		return $this->getEventsLv($lehrveranstaltung_id, $start, $end);
	}

	/**
	 * fetches Stundenplan events for the loggedin user between start and end
	 *
	 * @param string		$start
	 * @param string		$end
	 * @return stdClass
	 * @access public
	 */
	public function getEventsUser($start, $end)
	{
		$this->_ci =& get_instance();

		$this->_ci->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');

		$uid = getAuthUID();
		if (is_null($uid))
			return error("No UID");
		
		$is_mitarbeiter = getData($this->_ci->MitarbeiterModel->isMitarbeiter($uid));

		if ($is_mitarbeiter)
			return $this->getEventsEmployee($uid, $start, $end);

		return $this->getEventsStudent($uid, $start, $end);
	}

	/**
	 * fetches Stundenplan events for a student between start and end
	 *
	 * @param string		$student_uid
	 * @param string		$start
	 * @param string		$end
	 * @return stdClass
	 * @access public
	 */
	public function getEventsStudent($student_uid, $start, $end)
	{
		$this->_ci =& get_instance();

		$this->_ci->load->model('ressource/Stundenplan_model', 'StundenplanModel');

		$semester_range = $this->studienSemesterErmitteln($start, $end);
		if (isError($semester_range))
			return $semester_range;
		$semester_range = getData($semester_range);

		$this->sortStudienSemester($semester_range);

		$function_error = $this->applyLoadUeberSemesterHaelfte($semester_range);
		if ($function_error)
			return $function_error;
		
		// getting the gruppen_kurzbz of the student in the different studiensemester
		$benutzer_gruppen = $this->fetchBenutzerGruppenFromStudiensemester($student_uid, $semester_range);
		if (isError($benutzer_gruppen))
			return $benutzer_gruppen;
		$benutzer_gruppen = getData($benutzer_gruppen);

		// getting the student_lehrverbaende of the student in the different studiensemester
		$student_lehrverband = $this->fetchStudentlehrverbandFromStudiensemester($student_uid, $semester_range);
		if (isError($student_lehrverband))
			return $student_lehrverband;
		$student_lehrverband = getData($student_lehrverband);
		
		$stundenplan_query = $this->_ci->StundenplanModel->getStundenplanQuery(
			$start,
			$end,
			$semester_range,
			$benutzer_gruppen,
			$student_lehrverband
		);
		if (!$stundenplan_query)
			return success([]);
		
		$stundenplan_data = $this->_ci->StundenplanModel->stundenplanGruppierung($stundenplan_query);
		if (isError($stundenplan_data))
			return $stundenplan_data;
		$stundenplan_data = getData($stundenplan_data) ?? [];

		$function_error = $this->expandObjectInformation($stundenplan_data);
		if ($function_error)
			return $function_error;
		
		return success($stundenplan_data);
	}

	/**
	 * fetches Stundenplan events for an employee between start and end
	 *
	 * @param string		$uid
	 * @param string		$start
	 * @param string		$end
	 * @return stdClass
	 * @access public
	 */
	public function getEventsEmployee($uid, $start, $end)
	{
		$this->_ci =& get_instance();

		$this->_ci->load->model('ressource/Stundenplan_model', 'StundenplanModel');

		$stundenplan_data = $this->_ci->StundenplanModel->getStundenplanMitarbeiter($start, $end, $uid);
		if (isError($stundenplan_data))
			return $stundenplan_data;
		$stundenplan_data = getData($stundenplan_data) ?? [];

		$function_error = $this->expandObjectInformation($stundenplan_data);
		if ($function_error)
			return $function_error;
		
		return success($stundenplan_data);
	}

	/**
	 * fetches Stundenplan events for a LV between start and end
	 *
	 * @param integer		$lehrveranstaltung_id
	 * @param string		$start
	 * @param string		$end
	 * @return stdClass
	 * @access public
	 */
	public function getEventsLv($lehrveranstaltung_id, $start, $end)
	{
		$this->_ci =& get_instance();

		$this->_ci->load->model('ressource/Stundenplan_model', 'StundenplanModel');

		$stundenplan_data = $this->_ci->StundenplanModel->getStundenplanLVA($start, $end, $lehrveranstaltung_id);
		if (isError($stundenplan_data))
			return $stundenplan_data;
		$stundenplan_data = getData($stundenplan_data) ?? [];

		$function_error = $this->expandObjectInformation($stundenplan_data);
		if ($function_error)
			return $function_error;

		// query lv itself in case its Stundenplan is being queried and it has no entries
		$this->_ci->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		$lv_result = $this->_ci->LehrveranstaltungModel->load($lehrveranstaltung_id);
		if (isError($lv_result))
			return $lv_result;
		if (!hasData($lv_result))
			return error('LV not found');
		
		return success($stundenplan_data);
	}

	public function getEventsByLE($lehreinheit_id, $start, $end, $stundenplan)
	{
		$this->_ci =& get_instance();

		$this->_ci->load->model('ressource/Stundenplan_model', 'StundenplanModel');
		return $this->_ci->StundenplanModel->getStundenplanLE($lehreinheit_id, $start, $end, $stundenplan);
	}

	public function getEventsByLV($lehrveranstaltung_id, $start, $end, $stundenplan)
	{
		$this->_ci =& get_instance();

		$this->_ci->load->model('ressource/Stundenplan_model', 'StundenplanModel');
		return $this->_ci->StundenplanModel->getStundenplanLV($lehrveranstaltung_id, $start, $end, $stundenplan);
	}
	/**
	 * Get stundenplan for a room
	 *
	 * @param string	$ort_kurzbz
	 * @param string	$start_date
	 * @param string	$end_date
	 * @return stdClass
	 */
	public function getRoomplan($ort_kurzbz, $start_date, $end_date)
	{
		$this->_ci =& get_instance();

		// Load Config
		$this->_ci->load->config('calendar');
		// Load Models
		$this->_ci->load->model('ressource/Stundenplan_model', 'StundenplanModel');

		$query = $this->_ci->StundenplanModel->getRoomQuery($ort_kurzbz, $start_date, $end_date);
		$roomplan_data = $this->_ci->StundenplanModel->stundenplanGruppierung($query);

		if (isError($roomplan_data))
			return $roomplan_data;

		$this->expandObjectInformation($roomplan_data->retval);

		return $roomplan_data;
	}

	/**
	 * Get reservations (for a room or all)
	 *
	 * @param string	$start_date
	 * @param string	$end_date
	 * @param string	$ort_kurzbz
	 * @return stdClass
	 */
	public function getReservierungen($start_date, $end_date, $ort_kurzbz = '')
	{
		$this->_ci =& get_instance();

		// Load Config
		$this->_ci->load->config('calendar');
		// Load Models
		$this->_ci->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		$this->_ci->load->model('ressource/Reservierung_model', 'ReservierungModel');
		$this->_ci->load->model('ressource/Stundenplan_model', 'StundenplanModel');

		$is_mitarbeiter = getData($this->_ci->MitarbeiterModel->isMitarbeiter(getAuthUID()));

		if ($is_mitarbeiter && empty($ort_kurzbz)) {
			// request for personal lvplan show only reservations of logged in user
			$reservierungen = $this->_ci->ReservierungModel->getReservierungenMitarbeiter($start_date, $end_date);
		} else {
			// querying the reservierungen
			$reservierungen = $this->_ci->ReservierungModel->getReservierungen($start_date, $end_date, $ort_kurzbz);
		}
		
		if (isError($reservierungen))
			return $reservierungen;
		
		$function_error = $this->expandObjectInformation($reservierungen->retval);
		
		if (!is_null($function_error))
			return $function_error;
		
		return $reservierungen;
	}

	public function getLektorenFromLehrveranstaltung($lehrveranstaltung_id, $semester, $studiengang_kz, $studiensemester_kurzbz){
		$this->_ci =& get_instance();
		$this->_ci->load->model('ressource/Stundenplan_model', 'StundenplanModel');
		$this->_ci->load->model('organisation/Studiensemester_model','StudiensemesterModel');
		
		$studiensemester = $this->_ci->StudiensemesterModel->loadWhere(["studiensemester_kurzbz"=>$studiensemester_kurzbz]);
		if(isError($studiensemester))
		{
			return error(getData($studiensemester));
		}
		$studiensemester = current(getData($studiensemester));
		$lektoren = $this->_ci->StundenplanModel->execReadOnlyQuery("
		SELECT DISTINCT uid 
		FROM campus.vw_stundenplan
		WHERE lehrveranstaltung_id = ? AND
		studiengang_kz = ? AND
		semester = ? AND
		(datum BETWEEN ? AND ?)
		",[$lehrveranstaltung_id, $studiengang_kz, $semester, $studiensemester->start, $studiensemester->ende]);
		
		if(isError($lektoren))
		{
			return error(getData($lektoren));
		}
		$lektoren = getData($lektoren);
		if(isset($lektoren)){
			$lektoren = array_map(function($lektor){
				return $lektor->uid;
			},$lektoren);

		}
		return success($lektoren);
	}

	public function expandObjectInformation($data)
	{
		$this->_ci =& get_instance();
		
		// Load Config
		$this->_ci->load->config('calendar');
		// Load Model
		$this->_ci->load->model('ressource/Stundenplan_model', 'StundenplanModel');

		foreach ($data as $item)
		{
			$tz = new DateTimeZone($this->_ci->config->item('timezone'));
			$isostart = new DateTime($item->datum . ' ' . $item->beginn, $tz);
			$item->isostart = $isostart->format(DateTime::ATOM);

			$isoend = new DateTime($item->datum . ' ' . $item->ende, $tz);
			$item->isoend = $isoend->format(DateTime::ATOM);

			$lektor_obj_array = array();
			$gruppe_obj_array = array();

			// load lektor object
			foreach ($item->lektor as $lv_lektor)
			{
				$this->_ci->StundenplanModel->addLimit(1);
				$lektor_object = $this->_ci->StundenplanModel->execReadOnlyQuery("
				SELECT mitarbeiter_uid, vorname, nachname, kurzbz
				FROM public.tbl_mitarbeiter
				JOIN public.tbl_benutzer benutzer ON benutzer.uid = mitarbeiter_uid
				JOIN public.tbl_person person ON person.person_id = benutzer.person_id
				WHERE kurzbz = ?", [$lv_lektor]);
				if (isError($lektor_object)) {
					$this->_ci->show_error(getError($lektor_object));
				}
				if(isError($lektor_object))
				{
					return error(getData($lektor_object));
				}
				$lektor_object = getData($lektor_object);
				if(count($lektor_object) == 0)
				{
					return error("No lektor object");
				}
				$lektor_object = current($lektor_object);
				// only provide needed information of the mitarbeiter object
				$lektor_obj_array[] = $lektor_object;
			}

			// load gruppe object
			foreach ($item->gruppe as $lv_gruppe)
			{
				$lv_gruppe = strtr($lv_gruppe, ['(' => '', ')' => '', '"' => '']);
				$lv_gruppe_array = explode(",", $lv_gruppe);
				list($gruppe, $verband, $semester, $studiengang_kz, $gruppen_kuerzel) = $lv_gruppe_array;

				$lv_gruppe_object = new stdClass();
				$lv_gruppe_object->gruppe = $gruppe;
				$lv_gruppe_object->verband = $verband;
				$lv_gruppe_object->semester = $semester;
				$lv_gruppe_object->studiengang_kz = $studiengang_kz;
				$lv_gruppe_object->kuerzel = $gruppen_kuerzel;

				$gruppe_obj_array[] = $lv_gruppe_object;
			}
			
			if($item->ort_kurzbz) {

				$ort_content_object = $this->_ci->StundenplanModel->execReadOnlyQuery("
				SELECT content_id
				FROM public.tbl_ort
				WHERE ort_kurzbz = ?", [$item->ort_kurzbz]);
				if (isError($ort_content_object)) {
					return error(getData($ort_content_object));
				}
				$ort_content_object = getData($ort_content_object)[0];
				if($ort_content_object) {
					$item->ort_content_id = $ort_content_object->content_id;
				}
				
				
			}

			$item->gruppe = $gruppe_obj_array;
			$item->lektor = $lektor_obj_array;

		}
	}

	public function fetchFerienTageEvents($start_date, $end_date, $studiengang_kz)
	{
		$this->_ci =& get_instance();

		// Load Config
		$this->_ci->load->config('calendar');

		$this->_ci->load->model('organisation/Ferien_model', 'FerienModel');

		$tz = new DateTimeZone($this->_ci->config->item('timezone'));

		$ferienEvents = $this->_ci->FerienModel->execReadOnlyQuery("
		SELECT * 
		FROM lehre.tbl_ferien
		WHERE (bisdatum >= ? AND vondatum < ?) AND (studiengang_kz = 0 OR studiengang_kz = ?)
		", [$start_date, $end_date, $studiengang_kz]);

		if (isError($ferienEvents))
			return $ferienEvents;
		
		$ferienEvents = getData($ferienEvents);

		if (!$ferienEvents)
			return success([]);
		
		$ferienEvents = array_map(function ($event) {
			$event_start = new DateTime($event->vondatum);
			$event_end = new DateTime($event->bisdatum);
			$event_end->modify('+1 day');

			$interval = new DateInterval('P1D');
			$period = new DatePeriod($event_start, $interval, $event_end);
			$event->dates = iterator_to_array($period);
			return $event;
		}, $ferienEvents);

		$start_date = new DateTime($start_date);
		$start_date->setTime(0, 0, 0);
		$end_date = new DateTime($end_date);
		$end_date->setTime(23, 59, 59);

		$ferienEventsFlattened = [];
		foreach ($ferienEvents as $ferien_event) {
			foreach ($ferien_event->dates as $date) {
				if ($date < $start_date || $date > $end_date)
					continue;
				$event = new stdClass();
				$event->bezeichnung = $ferien_event->bezeichnung;
				$event->datum = $date->format('Y-m-d');
				$event->type = 'ferien';
				$ferienEventsFlattened[] = $event;
			}
		};

		$today = new DateTime();
		$ferienEventsFlattened = array_map(function ($event) use ($today, $tz) {
			$ferien_event = (object)array(
				'type' => 'ferien',
				'beginn' => $today->format('H:i:s'),
				'ende' => $today->format('H:i:s'),
				'isostart' => (new DateTime($event->datum . ' 00:00:00', $tz))->format('c'),
				'isoend' => (new DateTime($event->datum . ' 23:59:59', $tz))->format('c'),
				'allDayEvent' => true,
				'datum' => $event->datum,
				'topic' => $event->bezeichnung,
				'titel' => $event->bezeichnung,
				'farbe' => '00689E'
			);
			return $ferien_event;
		}, $ferienEventsFlattened);
		
		return success($ferienEventsFlattened);
	}

	// start of the private functions ########################################################################################################

	// function used to sort an array of studiensemester strings
	private function sortStudienSemester(&$semester_range){
		usort(
			$semester_range,
			function($first,$second)
			{
				$sem_first = null;
				$year_first = null;
				$match_first = null;

				$sem_second = null;
				$year_second = null;
				$match_second = null;

				preg_match('/([WS]+)([0-9]+)/',$first,$match_first);
				preg_match('/([WS]+)([0-9]+)/',$second,$match_second);
				
				$sem_first = $match_first[1];
				$year_first = intval($match_first[2]);

				$sem_second = $match_second[1];
				$year_second = intval($match_second[2]);

				if($year_first < $year_second)
				{
					return -1;
				}
				else if($year_first > $year_second)
				{
					return 1;
				}
				else if($year_first == $year_second && $sem_first > $sem_second)
				{
					return 1;
				}
				else if($year_first == $year_second && $sem_first < $sem_second)
				{
					return -1;
				}
				return 0;
			} 
		);
	}

	

	private function fetchBenutzerGruppenFromStudiensemester($student_uid, $semester_range)
	{
		$this->_ci->load->model('person/Benutzergruppe_model', 'BenutzergruppeModel');

		$benutzer_gruppen = [];
		// for each studiensemester fetch the benutzer gruppen and add them to an associate $bentuzer_gruppen array
		/*
		[
			['WS2023'] => [['gruppe1_SS2023','gruppe2_SS2023'],['gruppe1_WS2023','gruppe2_WS2023']],
			['SS2024'] => [['gruppe1_WS2023','gruppe2_WS2023'],['gruppe1_SS2024','gruppe2_SS2024']],
			['WS2024'] => [['gruppe1_SS2024','gruppe2_SS2024'],['gruppe1_WS2024','gruppe2_WS2024']],
		]
		*/ 
		foreach($semester_range as $semester_key => $semester_array)
		{
			$benutzer_gruppen[$semester_key] = [];
			// each semester could have ajoint semesters that need to be checked
			foreach($semester_array as $semester=>$semester_date_range)
			{
				// for each active semester query the benutzer_gruppen associated to the semester
				$benutzer_query = $this->_ci->BenutzergruppeModel->execReadOnlyQuery("
				SELECT * FROM tbl_benutzergruppe where uid = ? AND studiensemester_kurzbz = ?",[$student_uid, $semester]);
				if(isError($benutzer_query)){
					return error(getData($benutzer_query));
				}
				$benutzer_query_result = getData($benutzer_query)??[];
				
				array_push(
					$benutzer_gruppen[$semester_key],
					 array_map(
						function($item)
						{
							return "'".$item->gruppe_kurzbz. "'";
						},
						$benutzer_query_result
					)
				);
			}
		}
		
		// merge the gruppen of each studiensemester together for the original studiensemester
		/*
		[
			['WS2023'] => ['gruppe1_SS2023','gruppe2_SS2023','gruppe1_WS2023','gruppe2_WS2023'],
			['SS2024'] => ['gruppe1_WS2023','gruppe2_WS2023','gruppe1_SS2024','gruppe2_SS2024'],
			['WS2024'] => ['gruppe1_SS2024','gruppe2_SS2024','gruppe1_WS2024','gruppe2_WS2024'],
		]
		*/ 
		$benutzer_gruppen = array_map(
			function($gruppe)
			{
				$merged_gruppe = [];
				foreach($gruppe as $gruppen_array)
				{
					$merged_gruppe = array_merge($merged_gruppe, $gruppen_array);
				}
				return $merged_gruppe;
			},
			$benutzer_gruppen
		);

		return success($benutzer_gruppen);
	}

	private function fetchStudentlehrverbandFromStudiensemester($student_uid, $semester_range)
	{
		$this->_ci->load->model('person/Benutzergruppe_model', 'BenutzergruppeModel');

		$student_lehrverband = [];
		// for each studiensemester fetch the studentlehrverbaende and add them to an associate $student_lehrverband array
		/*
		[
			['WS2023'] => [ [ ['stg_kz'=>298,'semester'=>1,'verband'=>"A",'gruppe'=>""] ] ],
			['SS2024'] => [ [ ['stg_kz'=>298,'semester'=>1,'verband'=>"A",'gruppe'=>""] ], [ ['stg_kz'=>298,'semester'=>2,'verband'=>"A",'gruppe'=>""] ] ],
			['WS2024'] => [ [ ['stg_kz'=>298,'semester'=>2,'verband'=>"A",'gruppe'=>""] ], [ ['stg_kz'=>298,'semester'=>3,'verband'=>"A",'gruppe'=>""] ] ],
		]
		*/ 
		foreach($semester_range as $semester_key => $semester_array)
		{
			$student_lehrverband[$semester_key] = [];
			foreach($semester_array as $semester=>$semester_date_range)
			{
				// for each active semester query the student_lehrverband associated to the semester
				$lehrverband_query = $this->_ci->BenutzergruppeModel->execReadOnlyQuery("
				SELECT * FROM tbl_studentlehrverband where student_uid = ? AND studiensemester_kurzbz = ?", [$student_uid, $semester]);
				if(isError($lehrverband_query)){
					return error(getData($lehrverband_query));
				}	
				$lehrverband_query_result = getData($lehrverband_query)??[];
				
				$converted_studentLehrverband= array_map(
					function ($item)
					{
						$result = new stdClass();
						$result->studiengang_kz = $item->studiengang_kz;
						$result->semester = $item->semester;
						$result->verband = $item->verband;
						$result->gruppe = $item->gruppe;
						return $result;
					},
					$lehrverband_query_result);
				
				array_push($student_lehrverband[$semester_key], $converted_studentLehrverband); 
				
			}
			
		}

		// merge the studentlehrverband of each studiensemester together for the original studiensemester
		/*
		[
			['WS2023'] => [ ['stg_kz'=>298,'semester'=>1,'verband'=>"A",'gruppe'=>""] ],
			['SS2024'] => [ ['stg_kz'=>298,'semester'=>1,'verband'=>"A",'gruppe'=>""], ['stg_kz'=>298,'semester'=>2,'verband'=>"A",'gruppe'=>""] ],
			['WS2024'] => [ ['stg_kz'=>298,'semester'=>2,'verband'=>"A",'gruppe'=>""], ['stg_kz'=>298,'semester'=>3,'verband'=>"A",'gruppe'=>""] ],
		]
		*/
		
		$student_lehrverband = array_map(
			function($studentlehrverband)
			{	
				$merged_studentlehrverband = [];
				foreach($studentlehrverband as $studentlehrverband_array)
				{
					$merged_studentlehrverband = array_merge($merged_studentlehrverband, $studentlehrverband_array);
				}
				return $merged_studentlehrverband;
			},
			$student_lehrverband
		);

		return success($student_lehrverband);
	}

	private function applyLoadUeberSemesterHaelfte(&$semester_range)
	{
		$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		/*
		@var($semester_collection)
		convert the array of studiensemester into an associative array with the studiensemester as the key 
		and the values of each key are the studiensemester needed for the query associated to that studiensemester
		example:

		#INPUT:
		['WS2023','SS2024','WS2024']
		#OUTPUT:
		[
			'WS2023' => ['SS2023','WS2023']
			'SS2024' => ['WS2023','SS2024']
			'WS2024' => ['SS2024','WS2024']
		]
		*/
		$semester_collection = [];
		foreach($semester_range as $studiensemester)
		{
			$previous_studiensemester = $this->_ci->StudiensemesterModel->getPreviousFrom($studiensemester);
			if(isError($previous_studiensemester))
			{
				return error(getData($previous_studiensemester));
			}
			$previous_studiensemester = getData($previous_studiensemester);
			if (count($previous_studiensemester) == 0) {
				return error('no previous semester');
			}
			$previous_studiensemester = current($previous_studiensemester)->studiensemester_kurzbz;
			$semester_collection[$studiensemester] = [$previous_studiensemester, $studiensemester];
		}

		/*
		@var($studienSemesterDateRanges)
		fetches for each studiensemester the start and end date, (SS) summer studiensemester are extended by 1 month to cover the summerbreak
		based on the LVPLAN_LOAD_UEBER_SEMESTERHAELFTE constant it will load both the semester and the previous semester with the full date range 
		or the semester with the full date range and the previous semester with the half date range:

		#INPUT:
		[
			'WS2023' => ['SS2023','WS2023']
			'SS2024' => ['WS2023','SS2024']
			'WS2024' => ['SS2024','WS2024']
		]
		#OUTPUT: depends whether LVPLAN_LOAD_UEBER_SEMESTERHAELFTE is true or false
		~ if LVPLAN_LOAD_UEBER_SEMESTERHAELFTE == true
		[
			"SS2024": [
				"WS2023": [
					"start"=> "2024-02-03",
					"ende"=> "2024-08-31"
				],
				"SS2024": [
					"start"=> "2024-02-03",
					"ende"=> "2024-08-31"
				]
			]
		]
		~ if LVPLAN_LOAD_UEBER_SEMESTERHAELFTE == false
		[
			"SS2024": [
				"WS2023": [
					"start"=> "2024-02-03",
					"ende"=> "2024-05-17"
				],
				"SS2024": [
					"start"=> "2024-02-03",
					"ende"=> "2024-08-31"
				]
			]
		]
		*/
		$studienSemesterDateRanges=[]; 
		foreach($semester_collection as $semester_original => $semester_adjoint)
		{
			$semester_start_ende = $this->_ci->StudiensemesterModel->getStartEndeFromStudiensemester($semester_original);
			if(isError($semester_start_ende))
			{
				return error(getData($semester_start_ende));
			}
			$semester_start_ende = current(getData($semester_start_ende)); 
				
			// initialize empty arrays to add key value pairs
			$studienSemesterDateRanges[$semester_original] = [];

			// check if the studiensemester is a summer semester and add 1 month to bridge the school summer break
			$match = null;
			preg_match("/^(SS)([0-9]+)/",$semester_original,$match);
			if(count($match) >0)
			{
				$one_month = new DateInterval('P1M');
				$one_day = DateInterval::createFromDateString('1 days');
				$summer_studiensemester_end_date = DateTime::createFromFormat('Y-m-d',$semester_start_ende->ende);
				$summer_studiensemester_end_date->add($one_month);
				$summer_studiensemester_end_date->sub($one_day);
				$semester_start_ende->ende = date_format($summer_studiensemester_end_date,'Y-m-d');
			}
			if (defined('LVPLAN_LOAD_UEBER_SEMESTERHAELFTE') && LVPLAN_LOAD_UEBER_SEMESTERHAELFTE === true)
			{
				foreach($semester_adjoint as $adjoint)
				{
					$studienSemesterDateRanges[$semester_original][$adjoint]=$semester_start_ende;
				}
			}
			else
			{
				//TODO: half of a DateInterval might not be correctly calculated
				// calculate the half of the studiensemester
				$studiensemester_start_date = DateTime::createFromFormat('Y-m-d',$semester_start_ende->start);
				$studiensemester_end_date = DateTime::createFromFormat('Y-m-d',$semester_start_ende->ende);
				$studiensemester_time_difference = $studiensemester_start_date->diff($studiensemester_end_date);
				$half_dateNumber = ceil($studiensemester_time_difference->d/2)+ceil(($studiensemester_time_difference->m*30)/2);
				$half_dateInterval = new DateInterval('P'.strval($half_dateNumber) .'D');
				$studiensemester_half = date_format($studiensemester_start_date->add($half_dateInterval),'Y-m-d');
				
				$first_half = new stdClass();
				$first_half->start = $semester_start_ende->start;
				$first_half->ende = $studiensemester_half;

				$studienSemesterDateRanges[$semester_original][$semester_adjoint[0]] = $first_half;
				$studienSemesterDateRanges[$semester_original][$semester_adjoint[1]] = $semester_start_ende;
			}
			$semester_range = $studienSemesterDateRanges;
		}
	}

	private function studienSemesterErmitteln($start_date, $end_date)
	{
		$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		// gets all studiensemester from the student from start_date to end_date
		$semester_range = $this->_ci->StudiensemesterModel->getByDateRange($start_date, $end_date);
		if (isError($semester_range))
			return $semester_range;
		
		$semester_range = array_map(
			function ($sem) {
				return $sem->studiensemester_kurzbz;
			},
			getData($semester_range) ?: []
		);

		// if no studiensemester is found for the given timespan, get the nearest studiensemester
		if (count($semester_range) == 0)
		{
			$aktuelle_studiensemester = $this->_ci->StudiensemesterModel->getNearest();
			if (isError($aktuelle_studiensemester))
				return $aktuelle_studiensemester;
			
			$aktuelle_studiensemester = getData($aktuelle_studiensemester);
			if (count($aktuelle_studiensemester) == 0) {
				return error("No aktuelles semester");
			}
			$aktuelle_studiensemester = current($aktuelle_studiensemester)->studiensemester_kurzbz;
			// push aktuelles semester in active semester array
			array_push($semester_range, $aktuelle_studiensemester);
		}

		return success($semester_range);
	}
}
