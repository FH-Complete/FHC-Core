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


		//? put the uid and pid inside the controller for reusability
		$this->uid = getAuthUID();
		$this->pid = getAuthPersonID();

	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

    /**
	 * function that queries all the ampeln that are addressed to the user uid
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
	 * function that queries all the ampeln that are addressed to the user uid
     * @access public
	 * 
	 */
	public function getNonConfirmedActiveAmpeln()
	{
        $userAmpeln = array();
        $ampel_result = $this->AmpelModel->active();

        $ampel_result = $this->getDataOrTerminateWithError($ampel_result);

        foreach($ampel_result as $ampel){
            
            $confirmedByUser = $this->AmpelModel->isConfirmed($ampel->ampel_id,$this->uid);
            $ampel->bestaetigt = $confirmedByUser;
            if(!$confirmedByUser){
                $userUID_array = $this->AmpelModel->execBenutzerSelect($ampel->benutzer_select);
                $userUID_array = $this->getDataOrTerminateWithError($userUID_array);
                foreach($userUID_array as $user_obj){
                    
                    $user_uid = property_exists($user_obj,"uid") ? $user_obj->uid : $user_obj->mitarbeiter_uid;
                    if($user_uid === $this->uid){
                        $userAmpeln[] = $ampel;
                    }
                }
                    
            }
            
        }

        $this->terminateWithSuccess($userAmpeln);
	}

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
                    $userAmpeln[] = $ampel;
                }
            }
            
        }

        $this->terminateWithSuccess($userAmpeln);
	}

    public function alleAmpeln(){

        $alle_ampeln = $this->AmpelModel->alleAmpeln($this->uid);

        if(isError($alle_ampeln)) $this->terminateWithError(getError($alle_ampeln));

        $alle_ampeln = $this->getDataOrTerminateWithError($alle_ampeln);

        $this->terminateWithSuccess($alle_ampeln);

    }
    

    
}

