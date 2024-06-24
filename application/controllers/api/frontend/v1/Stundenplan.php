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
            'Stunden' => self::PERM_LOGGED,
            'Reservierungen' => self::PERM_LOGGED
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
       
        $this->load->model('ressource/Stundenplan_model', 'StundenplanModel');
		$this->load->model('ressource/Stunde_model', 'StundeModel');

        // storing the get parameter in local variables
        $ort_kurzbz = $this->input->get('ort_kurzbz', TRUE);
        $start_date = $this->input->get('start_date', TRUE);
        $end_date = $this->input->get('end_date', TRUE);
        
        //return early if the get parameter are not present
        if(!$ort_kurzbz || !$start_date || !$end_date){
            $this->terminateWithError("Missing parameters", self::ERROR_TYPE_GENERAL);
        }
        
		// querying the stunden
        $stunden = $this->StundeModel->load();

        if(isError($stunden))
            $this->terminateWithError(getError($stunden), self::ERROR_TYPE_GENERAL);
        
        $stunden = getData($stunden);

		$result = $this->StundenplanModel->groupedCalendarEvents($ort_kurzbz,$start_date,$end_date);  
        //$this->loglib->logErrorDB(print_r($result,TRUE),"this is the result of the grouped query");
		
        if(isError($result)){
            $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
        }

        $result = hasData($result) ? getData($result) : [];
        

        foreach($result as $entry){
            if(COUNT($entry->lektor)>1){
                // gruppierung hat stattgefunden und das array muss in einem String konvertiert werden
            }
        }

        // this is the old way the events were grouped, kept in case that the query doesnt work out as expected
        /* 
        $final_events = array();
        $grouped = array();
        $associative_day_events = $this->filterEventsIntoAssociativeDateArray($result, $start_date, $end_date);
        foreach($associative_day_events as $date => $day_events ){
            
            foreach($stunden as $stunde){

                // filtering all the events at the same hour of the day
                $stunden_events = array_filter($day_events, function($entry) use ($stunde){
                    return $entry->stunde == $stunde->stunde;
                });

                $lehrverband_array = array();

                // for loop that is just used to fill the lehrverband_array
                foreach($stunden_events as $key=>$stunden_event){

                    // lehrverband bestimmen
                    if(strlen($stunden_event->gruppe_kurzbz)>0){
                        $lehrverband = $stunden_event->gruppe_kurzbz;
                    }else{
                        $lehrverband=$stunden_event->stg_typ . $stunden_event->stg_kurzbz .'-'.$stunden_event->semester;
                        // checks whether the verband is not null, '' or '0'
                        if($stunden_event->verband !=null && $stunden_event->verband != '0' && $stunden_event->verband != ''){ 
                            $lehrverband.=$stunden_event->verband;
                            // if gruppe is not set it will concatenate nothing but it is only appended if the verband is set
                            $lehrverband.=$stunden_event->gruppe;
                        }
                    }
                    $lehrverband_array[$key] = $lehrverband;
                }
                
                // compare nested loop start
                foreach($stunden_events as $event_key => $stunden_event){
                    
                    // skip the loop iteration if the event was already grouped
                    if(isset($grouped[$event_key])){
                        continue;
                    }
                     
                    // lektor bestimmen
                    if($stunden_event->mitarbeiter_kurzbz != null){
                        $stunden_events[$event_key]->lektor = $stunden_event->mitarbeiter_kurzbz;
                    }

                    // lehrfach bestimmen
                    if(isset($stunden_event->lehrform)){
                        $stunden_events[$event_key]->lehrfach .= '-'.$stunden_event->lehrform;
                    }
                    
                    // GRUPIEREN DER GLEICHEN EVENTS
                    // vergleiche das aktuelle Event mit allen anderen Events die am gleichen Tag und zur gleichen Stunde gehalten werden
                    foreach($stunden_events as $compare_key => $stunden_event_compare){
                        
                        if($compare_key != $event_key){
                            
                            // will be used to skip the loop iteration with this index because it was already grouped
                            $grouped[$compare_key] = TRUE;

                            // can the events be grouped?
                           if ( 
                                // the unr's have to be equal to be grouped
                                $stunden_event->unr==$stunden_event_compare->unr && 
                                // and either the lektor or the ort_kurzbz have to be equal
                                ($stunden_event->ort_kurzbz==$stunden_event_compare->ort_kurzbz 
                                || $stunden_event->lektor==$stunden_event_compare->lektor)
                           )
                                {
                                    // Bezeichnung des Events zusammenfuehren
                                    // group the events properties if they are groupable

                                    //Lektoren
                                    if(!mb_strstr($stunden_event->lektor,$stunden_event_compare->lektor)){
                                        $stunden_events[$event_key]->lektor = $stunden_event->lektor . ' \ ' . $stunden_event_compare->lektor;
                                        $stunden_events[$event_key]->mitarbeiter_kurzbz = $stunden_event->mitarbeiter_kurzbz . ' \ ' . $stunden_event_compare->mitarbeiter_kurzbz;
                                    }

                                    //Ort
                                    if(!mb_strstr($stunden_event->ort_kurzbz,$stunden_event_compare->ort_kurzbz)){
                                        $stunden_events[$event_key]->ort_kurzbz = $stunden_event->ort_kurzbz . ' \ ' . $stunden_event_compare->ort_kurzbz;
                                    }

                                    //Lehrverband
                                    if(!mb_strstr($lehrverband_array[$event_key],$lehrverband_array[$compare_key])){
                                         $lehrverband_array[$event_key] .= ' / ' . $lehrverband_array[$compare_key];
                                    }

                                }
                        }
                    }

                    // add the grouped lehrverband entry to the event
                    $stunden_events[$event_key]->stg = $lehrverband_array[$event_key];
                    $final_events[] = $stunden_events[$event_key];
                }
            }
        } */     
        
		$this->terminateWithSuccess($result);
		
	}

    public function Reservierungen()
	{
        $this->load->model('ressource/Reservierung_model', 'ReservierungModel');
        $this->load->model('ressource/Stunde_model', 'StundeModel');
        $this->load->model('ressource/Mitarbeiter_model','MitarbeiterModel');

        // storing the get parameter in local variables
        $ort_kurzbz = $this->input->get('ort_kurzbz', TRUE);
        $start_date = $this->input->get('start_date', TRUE);
        $end_date = $this->input->get('end_date', TRUE);

        // return early if the get parameter are not present
        if(!$ort_kurzbz || !$start_date || !$end_date){
            $this->terminateWithError("Missing parameters", self::ERROR_TYPE_GENERAL);
        }

        // querying the stunden
        $stunden = $this->StundeModel->load();

        if(isError($stunden))
            $this->terminateWithError(getError($stunden), self::ERROR_TYPE_GENERAL);
        
        $stunden = getData($stunden);

		// querying the reservierungen
		$result = $this->ReservierungModel->getRoomReservierungen($ort_kurzbz, $start_date, $end_date);

		if (isError($result))
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
        
        $result = hasData($result) ? getData($result) : [];
        
        // loop over the days
        $day_events = $this->filterEventsIntoAssociativeDateArray($result, $start_date, $end_date);
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

                    // grouping the 
                    
                }

                
                // merging all the information into the first entry
                $final_reservierung = current($hour_reservierungen);
                
                $final_reservierung->person_kurzbz = implode(" / ",$grouped_uids);

                $final_reservierungen[] = $final_reservierung;
            }

        }
        $this->terminateWithSuccess($final_reservierungen);
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

