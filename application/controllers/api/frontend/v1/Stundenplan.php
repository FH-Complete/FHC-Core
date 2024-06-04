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

/**
 * This controller operates between (interface) the JS (GUI) and the SearchBarLib (back-end)
 * Provides data to the ajax get calls about the searchbar component
 * This controller works with JSON calls on the HTTP GET and the output is always JSON
 */
class Stundenplan extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
	
		parent::__construct([
			'roomInformation' => self::PERM_LOGGED,
            'Stunden' => self::PERM_LOGGED
		]);

		// Load the library ...
		// $this->load->library('');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Gets a JSON body via HTTP POST and provides the parameters
	 */
	public function roomInformation()
	{

        
        // storing the get parameter in local variables
        $ort_kurzbz = $this->input->get('ort_kurzbz', TRUE);
        $start_date = $this->input->get('start_date', TRUE);
        $end_date = $this->input->get('end_date', TRUE);
        
        $this->addMeta("test_start_date",$start_date);

        $this->addMeta("ort",$ort_kurzbz);
        $this->addMeta("start date",$start_date);
        $this->addMeta("end date",$end_date);
        $this->addMeta("testKey","testValue");
        
        $this->load->model('ressource/Stundenplan_model', 'StundenplanModel');
		
		$result = $this->StundenplanModel->getRoomDataOnDay($ort_kurzbz,$start_date,$end_date);
		if(isError($result)){
            $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
        }

        $result = hasData($result) ? getData($result) : [];
        
        // set up the log library and configure the library to log to the db
        $this->load->library('LogLib');
        $this->loglib->setConfigs(array(
			'classIndex' => 5,
			'functionIndex' => 5,
			'lineIndex' => 4,
			'dbLogType' => 'API', // required
			'dbExecuteUser' => 'RESTful API'
		));

        /* foreach($result as $event){
            $this->loglib->logInfoDB($event->datum,"NEW DATE");
        } */

        $testStartDate = new DateTime($start_date);
        $this->loglib->logInfoDB($testStartDate->format('Y-m-d'),"php start date");
        $testStartDate->modify('+1 day');
        $this->loglib->logInfoDB($testStartDate->format('Y-m-d'),"php start date plus one day");
        $testStartDate->modify('+1 day');
        $this->loglib->logInfoDB($testStartDate->format('Y-m-d'),"php start date plus one day");
        $testStartDate->modify('+1 day');
        $this->loglib->logInfoDB($testStartDate->format('Y-m-d'),"php start date plus one day");
        $testStartDate->modify('+1 day');
        $this->loglib->logInfoDB($testStartDate->format('Y-m-d'),"php start date plus one day");
        
        $this->groupTheCalendar($result);
        //php start date
        $phpStartDate = new DateTime($start_date);

        //$phpStartDate->modify('+1 day');
        $this->addMeta('result',$phpStartDate);
       
            error_log("test".print_r($result,true));
        
		//echo($this->db->last_query());
		$this->terminateWithSuccess($result);
		
	}

    private function groupTheCalendar($data){

    }

    public function Stunden()
	{
		$this->load->model('ressource/Stunde_model', 'StundeModel');

		$result = $this->StundeModel->load();

		if (isError($result)){
            $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
        }
            
        $result = hasData($result)? getData($result) : [];

        $this->terminateWithSuccess($result);
	}
}

