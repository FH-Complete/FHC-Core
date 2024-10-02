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
	public function getStundenplan(){

		// Query fuer Studenten prototyp ok
		//TODO: getStundenplan fuer Mitarbeiter anpassen
		//TODO: viele edge cases die es nicht moeglich machen besseren tbl_stundenplan index zu verwenden als idx_stundenplan_datum

		// include Student model to fetch Studiengang_kz and Semester
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');

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
		
		// get Student_uid / Studiengang_kz / Semester in order to use index idx_stundenplan_datum_stgsem
		$student_uid = get_uid();
		$student_data = $this->StudentModel->load([$student_uid]);
		$student_data = getData($student_data)[0];
		$this->addMeta('studg',$student_data->studiengang_kz);
		$this->addMeta('sem', $student_data->semester);	

		$stundenplan_data = $this->StundenplanModel->stundenplanGruppierung($this->StundenplanModel->getStundenplanQuery(get_uid(),$start_date,$end_date,$student_data->studiengang_kz,$student_data->semester)); 
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

	private function expand_object_information($data){
		foreach ($data as $item) {

			$lektor_obj_array = array();
			$gruppe_obj_array = array();

			// load lektor object
			foreach ($item->lektor as $lv_lektor) {
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
				$lektor_object = current(getData($lektor_object));
				// only provide needed information of the mitarbeiter object 
				$lektor_obj_array[] = $lektor_object;
			}

			// load gruppe object
			foreach ($item->gruppe as $lv_gruppe) {
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

    

}

