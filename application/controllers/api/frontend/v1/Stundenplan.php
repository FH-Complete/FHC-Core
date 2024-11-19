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
			'getLehreinheitStudiensemester' => self::PERM_LOGGED,
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

	/**
	 * fetches stundenplan events from a UID and start/end date
	 * @access public
	 *
	 */
	//TODO: getStundenplan fuer Mitarbeiter anpassen
	public function getStundenplan(){

		$this->load->model('ressource/Mitarbeiter_model','MitarbeiterModel');
		$this->load->model('organisation/Studiensemester_model','StudiensemesterModel');
		$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
		$this->load->model('person/Benutzergruppe_model','BenutzergruppeModel');

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

 		$student_uid = getAuthUID();

		// check if authUID is mitarbeiter
		$is_mitarbeiter = getData($this->MitarbeiterModel->isMitarbeiter($student_uid));
		if($is_mitarbeiter)
		{
			$this->terminateWithError("Not possible to look at the Student Calendar as a Mitarbeiter");
		}
		if(is_null($student_uid))
		{
			$this->terminateWithError("No UID");
		}

		// gets all studiensemester from the student from start_date to end_date
		$semester_range = $this->StudiensemesterModel->getByDate($start_date,$end_date);
		$semester_range = array_map( 
			function($sem)
			{
				return $sem->studiensemester_kurzbz;
			},
			$this->getDataOrTerminateWithError($semester_range)
		);

		// if no studiensemester is found for the given timespan, get the nearest studiensemester
		if(count($semester_range) == 0)
		{
			$aktuelle_studiensemester = $this->StudiensemesterModel->getNearest();
			$aktuelle_studiensemester = $this->getDataOrTerminateWithError($aktuelle_studiensemester);
			if (count($aktuelle_studiensemester) == 0) {
				$this->terminateWithError("No aktuelles semester");
			}
			$aktuelle_studiensemester = current($aktuelle_studiensemester)->studiensemester_kurzbz;
			// push aktuelles semester in active semester array
			array_push($semester_range, $aktuelle_studiensemester);

		}

		
		$this->sortStudienSemester($semester_range);

		//semester des Studenten ermitteln
		if (defined('LVPLAN_LOAD_UEBER_SEMESTERHAELFTE') && LVPLAN_LOAD_UEBER_SEMESTERHAELFTE === true)
		{
			// get next studiensemester
			$next_studiensemester = $this->StudiensemesterModel->getNextFrom($semester_range[(count($semester_range)-1)]);
			$next_studiensemester = $this->getDataOrTerminateWithError($next_studiensemester);
			if(count($next_studiensemester) == 0)
			{
				$this->terminateWithError("No next semester");
			}
			$next_studiensemester = current($next_studiensemester)->studiensemester_kurzbz;
			//push next semester in active semester array
			array_push($semester_range, $next_studiensemester);
			$this->sortStudienSemester($semester_range);
			
			// get previous studiensemester
			$previous_studiensemester = $this->StudiensemesterModel->getPreviousFrom($semester_range[0]);
			$previous_studiensemester = $this->getDataOrTerminateWithError($previous_studiensemester);
			if (count($previous_studiensemester) == 0) {
				$this->terminateWithError("No previous semester");
			}
			$previous_studiensemester = current($previous_studiensemester)->studiensemester_kurzbz;
			// push previous semester in active semester array
			array_push($semester_range, $previous_studiensemester);
			$this->sortStudienSemester($semester_range);
		}	
		else
		{
			$nearest_studiensemester = $this->StudiensemesterModel->getNearestFrom($semester_range[(count($semester_range)-1)]);
			$nearest_studiensemester = $this->getDataOrTerminateWithError($nearest_studiensemester);
			if (count($nearest_studiensemester) == 0) {
				$this->terminateWithError("No nearest semester");
			}
			$nearest_studiensemester = current($nearest_studiensemester)->studiensemester_kurzbz;
			// push nearest semester in active semester array
			array_push($semester_range, $nearest_studiensemester);
			$this->sortStudienSemester($semester_range);

		}


		// getting the gruppen_kurzbz of the student in the different studiensemester
		$benutzer_gruppen = [];
		
		foreach($semester_range as $semester)
		{
			// for each active semester query the benutzer_gruppen associated to the semester
			$benutzer_query = $this->BenutzergruppeModel->execReadOnlyQuery("
			SELECT * FROM tbl_benutzergruppe where uid = ? AND studiensemester_kurzbz = ?",[$student_uid, $semester]);
			$benutzer_query_result = $this->getDataOrTerminateWithError($benutzer_query);
			$benutzer_gruppen[$semester] = array_map(function($item){ return "'".$item->gruppe_kurzbz. "'";}, $benutzer_query_result);
		}

		$student_lehrverband = [];
		
		foreach($semester_range as $semester)
		{
			// for each active semester query the student_lehrverband associated to the semester
			$lehrverband_query = $this->BenutzergruppeModel->execReadOnlyQuery("
			SELECT * FROM tbl_studentlehrverband where student_uid = ? AND studiensemester_kurzbz = ?", [$student_uid, $semester]);
			$lehrverband_query_result = $this->getDataOrTerminateWithError($lehrverband_query);
			$student_lehrverband[$semester] = array_map(
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

		}

		$studienSemesterDateRanges=[]; 
		foreach($semester_range as $semester){
			$semester_start_ende = $this->StudiensemesterModel->getStartEndeFromStudiensemester($semester);
			$semester_start_ende = current($this->getDataOrTerminateWithError($semester_start_ende)); 
			$studienSemesterDateRanges[$semester] = $semester_start_ende; 
		}
		$semester_range = $studienSemesterDateRanges;

		$stundenplan_data = $this->StundenplanModel->stundenplanGruppierung($this->StundenplanModel->getStundenplanQuery($start_date, $end_date, $semester_range, $benutzer_gruppen, $student_lehrverband));
		$stundenplan_data = $this->getDataOrTerminateWithError($stundenplan_data) ?? [];

		$this->expand_object_information($stundenplan_data);

		$this->terminateWithSuccess($stundenplan_data);
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

        // storing the get parameter in local variables
        $start_date = $this->input->get('start_date', TRUE);
        $end_date = $this->input->get('end_date', TRUE);

		// querying the reservierungen
		$reservierungen = $this->ReservierungModel->getReservierungen($start_date, $end_date, $ort_kurzbz);

        $reservierungen = $this->getDataOrTerminateWithError($reservierungen) ?? [];

		$this->expand_object_information($reservierungen);

		$this->terminateWithSuccess($reservierungen);

	}

	public function getLehreinheitStudiensemester($lehreinheit_id){
		$this->load->model('education/Lehreinheit_model', 'LehreinheitModel');
		$this->LehreinheitModel->addSelect(["studiensemester_kurzbz"]);
		$result = $this->LehreinheitModel->load($lehreinheit_id);
		$result = current($this->getDataOrTerminateWithError($result))->studiensemester_kurzbz;
		$this->terminateWithSuccess($result);
	}

	// ################# Private Functions

	private function expand_object_information($data){

		foreach ($data as $item)
		{

			$lektor_obj_array = array();
			$gruppe_obj_array = array();

			// load lektor object
			foreach ($item->lektor as $lv_lektor)
			{
				$this->StundenplanModel->addLimit(1);
				$lektor_object = $this->StundenplanModel->execReadOnlyQuery("
				SELECT mitarbeiter_uid, vorname, nachname, kurzbz
				FROM public.tbl_mitarbeiter
				JOIN public.tbl_benutzer benutzer ON benutzer.uid = mitarbeiter_uid
				JOIN public.tbl_person person ON person.person_id = benutzer.person_id
				WHERE kurzbz = ?", [$lv_lektor]);
				if (isError($lektor_object)) {
					$this->show_error(getError($lektor_object));
				}
				$lektor_object = $this->getDataOrTerminateWithError($lektor_object);
				if(count($lektor_object) == 0)
				{
					$this->terminateWithError("No lektor object");
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

			$item->gruppe = $gruppe_obj_array;
			$item->lektor = $lektor_obj_array;

		}
	}

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

}
