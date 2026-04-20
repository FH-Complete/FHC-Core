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

class Profil extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
            'fotoSperre' => self::PERM_LOGGED,
			'getGemeinden' => self::PERM_LOGGED,
			'getAllNationen' => self::PERM_LOGGED,
			'isMitarbeiter' => self::PERM_LOGGED,
			'profilViewData' => self::PERM_LOGGED,
		]);
		
		$this->load->library('PermissionLib');

		$this->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('person/Person_model', 'PersonModel');


		//? put the uid and pid inside the controller for reusability
		$this->uid = getAuthUID();
		$this->pid = getAuthPersonID();

	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods
	public function profilViewData($uid=null){
		$this->load->library('ProfilLib');
		$editable = false;
		if(isset($uid) && $uid != null){
			$profil_data = $this->profillib->getView($uid);
			if($uid == getAuthUID()){
				$editable = true;
			}
		}else{
			$editable = true;
			$profil_data = $this->profillib->getView(getAuthUID());
		}
		
		$profil_data = hasData($profil_data) ? getData($profil_data) : null;
		$viewData = array(
			'editable'=>$editable,
			'profil_data' => $profil_data,
		);
		$this->terminateWithSuccess($viewData);
	}

    /**
	 * update column foto_sperre in public.tbl_person
	 * @access public
	 * @param  boolean $value  new value for the column
	 * @return boolean the new value added to the column in public.tbl_person
	 */
	public function fotoSperre($value)
	{
        if(!isset($value)){
            $this->terminateWithError("Missing parameter", self::ERROR_TYPE_GENERAL);
        }

		$res = $this->PersonModel->update($this->pid, ["foto_sperre" => $value]);
		if (isError($res)) {
			$this->terminateWithError("error while trying to update table public.tbl_person");
		}
		$this->PersonModel->addSelect("foto_sperre");
		$res = $this->PersonModel->load($this->pid);
		
        $res = $this->getDataOrTerminateWithError($res);
		
        $this->terminateWithSuccess(current($res));
	}

	/**
	 * gets all nations in the table bis.tbl_nation
	 *
	 * @access public
	 * @return array all the nations in table bis.tbl_nation
	 */
	public function getAllNationen()
	{
		// load the nationen from the database
		$this->load->model('codex/Nation_model', "NationModel");
		$this->NationModel->addSelect(["nation_code as code", "langtext"]);
		$nation_res = $this->NationModel->load();

		if (isError($nation_res)) {
			$this->terminateWithError("error while trying to query table codex.tbl_nation", self::ERROR_TYPE_GENERAL);
		}
		
		$nation_res = $this->getDataOrTerminateWithError($nation_res);

		$this->terminateWithSuccess($nation_res);
	}

	public function getGemeinden($nation, $zip)
	{
		if(!isset($nation) || !isset($zip)){
			echo json_encode(error("Missing parameters"));
			return;
		}
		
		$this->load->model('codex/Gemeinde_model', "GemeindeModel");
		

		$gemeinde_res = $this->GemeindeModel->getGemeindeByPlz($zip);
		
		if (isError($gemeinde_res)) {
			$this->terminateWithError(getError($gemeinde_res),self::ERROR_TYPE_GENERAL);
		}
		$gemeinde_res = $this->getDataOrTerminateWithError($gemeinde_res);
		
		/* $gemeinde_res = array_map(function ($obj) {
			return $obj->ortschaftsname;
		}, $gemeinde_res); */

		$this->terminateWithSuccess($gemeinde_res);	
		
	}

    
	/**
	 * checks whether a specific userID is a mitarbeiter or not (foreword declaration of the function isMitarbeiter in Mitarbeiter_model.php)
	 * @access public
	 * @param  $uid the userID used to check if it is a mitarbeiter
	 * @return boolean 
	 */
	public function isMitarbeiter($uid)
	{

		if(!$uid) $this->terminateWithError("No uid provided", self::ERROR_TYPE_GENERAL);
		
		
		$result = $this->MitarbeiterModel->isMitarbeiter($uid);
		
		if (isError($result)) {
			$this->terminateWithError("error when calling Mitarbeiter_model function isMitarbeiter with uid " . $uid, self::ERROR_TYPE_GENERAL);
		}

		$result = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($result);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	
}

