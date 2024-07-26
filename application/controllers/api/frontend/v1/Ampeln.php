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

class Ampeln extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getNonConfirmedActiveAmpeln' => self::PERM_LOGGED,
            'getAllActiveAmpeln' => self::PERM_LOGGED,
            'getConfirmedActiveAmpeln' => self::PERM_LOGGED,
            'confirmAmpel' => self::PERM_LOGGED,
            'alleAmpeln' => self::PERM_LOGGED,
            
		]);


		$this->load->model('content/Ampel_model', 'AmpelModel');
        $this->load->model('system/Sprache_model','SpracheModel');


		//? put the uid and pid inside the controller for reusability
		$this->uid = getAuthUID();
		$this->pid = getAuthPersonID();

	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

    /**
	 * confirms ampel and inserts ampelID in public.tbl_ampel_benutzer_bestaetigt
     * @access public
	 * 
	 */
	public function confirmAmpel($ampel_id)
	{
        if(!isset($ampel_id)){
            $this->terminateWithError("missing parameter");
        }

        $insert_into_result = $this->AmpelModel->confirmAmpel($ampel_id,$this->uid);

        if(isError($insert_into_result)){
            $this->terminateWithError(getError($insert_into_result));
        }

        $insert_into_result = $this->getDataOrTerminateWithError($insert_into_result);

        $this->terminateWithSuccess($insert_into_result);
    }
	
    /**
	 * queries active and not confirmed ampeln by the user 
     * @access public
	 * 
	 */
	public function getNonConfirmedActiveAmpeln()
	{

        $userAmpeln = array();

        // fetch active ampeln
        $activeAmpeln = $this->AmpelModel->active();

        $activeAmpeln = $this->getDataOrTerminateWithError($activeAmpeln);

        foreach($activeAmpeln as $ampel){
            
            // check if ampel is confirmed by user
            $confirmedByUser = $this->AmpelModel->isConfirmed($ampel->ampel_id,$this->uid);
            $ampel->bestaetigt = $confirmedByUser;

            // only include non confirmed active ampeln in the result
            if(!$confirmedByUser){
                $userUID_array = $this->AmpelModel->execBenutzerSelect($ampel->benutzer_select);
                $userUID_array = $this->getDataOrTerminateWithError($userUID_array);

                // check if user is assigned to the ampel
                foreach($userUID_array as $user_obj){
                    // property is called uid for students and mitarbeiter_uid for employees
                    $user_uid = property_exists($user_obj,"uid") ? $user_obj->uid : $user_obj->mitarbeiter_uid;
                    
                    if($user_uid === $this->uid){
                        $userAmpeln[] = $this->translateAmpel($ampel);
                    }
                } 
            }
            
        }

        $this->terminateWithSuccess($userAmpeln);
	}

    /**
	 * queries active ampeln by the user 
     * @access public
	 * 
	 */
    public function getAllActiveAmpeln()
	{
        $userAmpeln = array();
        $ampel_result = $this->AmpelModel->active();

        $ampel_result = $this->getDataOrTerminateWithError($ampel_result);

        foreach($ampel_result as $ampel){
            
            $confirmedByUser = $this->AmpelModel->isConfirmed($ampel->ampel_id,$this->uid);
            $ampel->bestaetigt = $confirmedByUser;
            $userUID_array = $this->AmpelModel->execBenutzerSelect($ampel->benutzer_select);
            $userUID_array = $this->getDataOrTerminateWithError($userUID_array);
            foreach($userUID_array as $user_obj){
                
                $user_uid = property_exists($user_obj,"uid") ? $user_obj->uid : $user_obj->mitarbeiter_uid;
                if($user_uid === $this->uid){
                    $userAmpeln[] = $this->translateAmpel($ampel);
                }
            }
        }

        $this->terminateWithSuccess($userAmpeln);
	}

    /**
	 * queries all ampeln that were assigned to the user until start of first work day
     * @access public
	 * 
	 */
    public function alleAmpeln(){

        //fetch all ampeln
        $alle_ampeln = $this->AmpelModel->alleAmpeln($this->uid);

        if(isError($alle_ampeln)) $this->terminateWithError(getError($alle_ampeln));

        $alle_ampeln = $this->getDataOrTerminateWithError($alle_ampeln);

        // translate ampeln
        array_map(function($ampel){ return $this->translateAmpel($ampel);}, $alle_ampeln);

        $this->terminateWithSuccess($alle_ampeln);

    }

    //------------------------------------------------------------------------------------------------------------------
	// Private methods
    
    /**
	 * translate ampel description and button text
     * @access private
	 * 
	 */
    public function translateAmpel($ampel){

        // fetch user language 
        $userLanguage = getUserLanguage();
        
        $userLanguage = $this->SpracheModel->loadWhere(["sprache" => $userLanguage]);
        
        if(isError($userLanguage)) $this->terminateWithError(getError($userLanguage));
        $userLanguage = $this->getDataOrTerminateWithError($userLanguage)[0]->index - 1; // why does the index start at 1?

        // translate the ampel description and button text
        $ampel->beschreibung = $ampel->beschreibung[$userLanguage];
        $ampel->buttontext = $ampel->buttontext[$userLanguage];

        return $ampel;

    }

    
}

