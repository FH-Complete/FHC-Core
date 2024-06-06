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

        $this->load->library('LogLib');
        $this->loglib->setConfigs(array(
			'classIndex' => 5,
			'functionIndex' => 5,
			'lineIndex' => 4,
			'dbLogType' => 'API', // required
			'dbExecuteUser' => 'RESTful API'
		));
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

    public function Stunden()
	{
		$this->load->model('ressource/Stunde_model', 'StundeModel');

		$result = $this->StundeModel->load();

		if (isError($result))
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);

		$this->terminateWithSuccess(getData($result));
	}
    
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
		$this->load->model('ressource/Stunde_model', 'StundeModel');

		$stunden = $this->StundeModel->load();
        if(isError($stunden)){
            $this->terminateWithError(getError($stunden), self::ERROR_TYPE_GENERAL);
        }
        $stunden = getData($stunden);

        $this->loglib->logInfoDB(print_r($stunden,true),"stunden");


		$result = $this->StundenplanModel->getRoomDataOnDay($ort_kurzbz,$start_date,$end_date);
		if(isError($result)){
            $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
        }

        $result = hasData($result) ? getData($result) : [];
        $this->loglib->logInfoDB(print_r($result,true),"result");
        // set up the log library and configure the library to log to the db
        

        /* foreach($result as $event){
            $this->loglib->logInfoDB($event->datum,"NEW DATE");
        } */

        $testStartDate = new DateTime($start_date);
        $testEndDate = new DateTime($end_date);
        $count =0;
        $final_events = array();
        while($testStartDate <= $testEndDate && $count <7){
            $date = $testStartDate->format('Y-m-d');
            //TODO: array filtering for every day and hour could be too time consuming causing slow response
            $day_events = array_filter($result,function($entry) use ($date){
                return $entry->datum == $date;
            });
            //$this->loglib->logInfoDB(print_r($day_events,true),"day_events");
            foreach($stunden as $stunde){
                $stunden_events = array_filter($day_events, function($entry) use ($stunde){
                    return $entry->stunde == $stunde->stunde;
                });

                // aenderung aller events die am gleichen tag und zur gleichen Stunde gehalten werden
                foreach($stunden_events as $event_key => $stunden_event){
                    $this->loglib->logInfoDB(print_r($stunden_event,true),"this is the stunden evnet");
                    
                    // lektor bestimmen
                    if($stunden_event->mitarbeiter_kurzbz == null){
                        $simml_lektor = $stunden_event->lektor;
                    }else{
                        $simml_lektor = $stunden_event->mitarbeiter_kurzbz;
                    }

                    

                    // lehrverband bestimmen
                    if(strlen($stunden_event->gruppe_kurzbz)>0){
                        $lehrverband = $stunden_event->gruppe_kurzbz;
                    }else{
                        $lehrverband=$stunden_event->stg.'-'.$stunden_event->sem;
                        // checks whether the verband is not null, '' or '0'
                        if($stunden_event->verband !=null && $stunden_event->verband != '0' && $stunden_event->verband != ''){ 
                            $lehrverband.=$stunden_event->verband;
                            // if gruppe is not set it will concatenate nothing but it is only appended if the verband is set
                            $lehrverband.=$stunden_event->gruppe;
                        }
                    }

                    // lehrfach bestimmen
                    $lehrfach = $stunden_event->lehrfach;
                    if(isset($stunden_event->lehrform)){
                        $lehrfach .= '-'.$stunden_event->lehrform;
                    }
                    
                    // GRUPIEREN DER GLEICHEN EVENTS
                    // vergleiche das aktuelle Event mit allen anderen Events die am gleichen Tag und zur gleichen Stunde gehalten werden
                    foreach($stunden_events as $compare_key => $stunden_event_compare){
                        if($compare_key != $event_key){

                           if ( 
                                // the unr's have to be equal to be grouped
                                $stunden_event->unr==$stunden_event_compare->unr && 
                                // and either the lektor or the ort_kurzbz have to be equal
                                ($stunden_event->ort_kurzbz==$stunden_event_compare->ort_kurzbz 
                                || $stunden_event->lektor==$stunden_event_compare->lektor)
                           )
                                {

                                }
                        }
                    }
                    



                }
                if (count($stunden_events) == 1){
                    $final_events[] = current($stunden_events);
                }else if(count($stunden_events) > 1){
                    $gruppe = '';
                    foreach($stunden_events as $stunden_event){
                        $gruppe .= $stunden_event->gruppe . ',';
                    }
                    current($stunden_events)->gruppe = $gruppe;
                    $final_events[] = current($stunden_events);
                }
                //$this->loglib->logInfoDB(print_r($stunden_events,true),"date: " . $date . " - stunde:" .$stunde->stunde);
            }
            /* $this->loglib->logInfoDB(print_r($testStartDate,true),"startdate");
            $this->loglib->logInfoDB($count,"count");
            $this->loglib->logInfoDB(print_r($testEndDate,true),"enddate");
            */
            $testStartDate->modify('+1 day');
            $count++;
        }     
        
        $this->loglib->logInfoDB(print_r($final_events,true),"final_events");
      
        $this->groupTheCalendar($result);
        //php start date
        $phpStartDate = new DateTime($start_date);

        //$phpStartDate->modify('+1 day');
        $this->addMeta('result',$phpStartDate);
       
            error_log("test".print_r($result,true));
        
		//echo($this->db->last_query());
		$this->terminateWithSuccess($final_events);
		
	}

    private function groupTheCalendar($data){

    }

}

