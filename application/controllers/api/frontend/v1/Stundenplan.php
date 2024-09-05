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

	public function getStundenplan(){
		
		$this->load->model('ressource/Stundenplan_model', 'StundenplanModel');

		/* $result = $this->StundenplanModel->loadForUid(get_uid());

		if (isError($result))
			return $this->outputJsonError(getError($result));
 		*/
		$res = $this->StundenplanModel->stundenplanGruppierung($this->StundenplanModel->getStundenplanQuery(get_uid())); 
		
		$res = getData($res);
		
		$this->outputJsonSuccess($res);
	}

	// reservierungen is not used in the prototype for the students
    public function Reservierungen()
	{
        $this->load->model('ressource/Reservierung_model', 'ReservierungModel');
        $this->load->model('ressource/Stunde_model', 'StundeModel');
        $this->load->model('ressource/Mitarbeiter_model','MitarbeiterModel');

		//form validation
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('ort_kurzbz',"Ort","required");
		$this->form_validation->set_rules('start_date', "StartDate", "required");
		$this->form_validation->set_rules('end_date', "EndDate", "required");
		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

        // storing the get parameter in local variables
        $ort_kurzbz = $this->input->get('ort_kurzbz', TRUE);
        $start_date = $this->input->get('start_date', TRUE);
        $end_date = $this->input->get('end_date', TRUE);

        // querying the stunden
        $stunden = $this->StundeModel->load();

        $stunden = $this->getDataOrTerminateWithError($stunden);

		// querying the reservierungen
		$result = $this->ReservierungModel->getRoomReservierungen($ort_kurzbz, $start_date, $end_date);

        $result = $this->getDataOrTerminateWithError($result);
        $this->terminateWithSuccess($result);
        // imperative approach
        /* $day_events = $this->filterEventsIntoAssociativeDateArray($result, $start_date, $end_date);
        $final_reservierungen = array();
        foreach($day_events as $date => $day_eventArray){

            // loop over the stunden
            foreach( $stunden as $stunde){
                // filtering all the day reservierungen to the reservierungen that happen at the same hour of the day
                $hour_reservierungen = array_filter($day_eventArray, function($day_entry) use ($stunde){
                    return $day_entry->stunde == $stunde->stunde;
                });

                // if there are no reservierungen within that hour than we skip that iteration of the loop
                if(count($hour_reservierungen) <1){
                    continue;
                }

                $this->loglib->logInfoDB(print_r($hour_reservierungen,true),"this is the hour reservierungen");
                
                // grouping the reservierung information of reservervations of the same hour on the same day
                $grouped_uids = array();
                foreach($hour_reservierungen as $entry){

                    // grouping the reservierungs participants

                    $mitarbeiter_check = $this->MitarbeiterModel->isMitarbeiter($entry->uid);
                    
                    if(isError($mitarbeiter_check)){
                        $this->terminateWithError(getError($mitarbeiter_check), self::ERROR_TYPE_GENERAL);
                    }

                    $mitarbeiter_check = getData($mitarbeiter_check);

                    // if the uid belongs to a mitarbeiter store the mitarbeiter_kurzbz otherwise store the student uid
                    if($mitarbeiter_check){
                        $mitarbeiterKurzbz = $this->MitarbeiterModel->generateKurzbz($entry->uid);
                        
                        if(isError($mitarbeiterKurzbz)){
                            $this->terminateWithError(getError($mitarbeiterKurzbz), self::ERROR_TYPE_GENERAL);
                        }

                        $grouped_uids[] = getData($mitarbeiterKurzbz);

                    }else{
                        $grouped_uids[]= $entry->uid;
                    }

                    
                }

                
                // merging all the information into the first entry
                $final_reservierung = current($hour_reservierungen);
                
                $final_reservierung->person_kurzbz = implode(" / ",$grouped_uids);

                $final_reservierungen[] = $final_reservierung;
            }

        }
        $this->terminateWithSuccess($final_reservierungen); */
	}

    private function filterEventsIntoAssociativeDateArray($events, $start_date, $end_date){
        $php_start_date = new DateTime($start_date);
        $php_end_date = new DateTime($end_date);
        // count is used to ensure that the loop does not iterate more than 7 times (7 days per week)
        $count =0;

        $result = array();

        // loop over the days
        while($php_start_date <= $php_end_date && $count <7){
            
            $date = $php_start_date->format('Y-m-d');

            // filtering all the reservierungen with the date
            $day_events = array_filter($events, function($event) use ($date){
                // no filtering is done if the event entries do not have a datum property
                return isset($event->datum) ? $event->datum == $date : true; 
                
            });

            $result[$date] = $day_events;
            ++$count;
            $php_start_date->modify('+1 day');
        }

        return $result;
    }

}

