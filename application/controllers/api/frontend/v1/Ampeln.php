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

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Ampeln extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'open' => self::PERM_LOGGED,
			'all' => self::PERM_LOGGED,
			'confirm' => self::PERM_LOGGED,
			'alleAmpeln' => self::PERM_LOGGED,
		]);

		$this->load->model('content/Ampel_model', 'AmpelModel');
		$this->load->model('system/Sprache_model', 'SpracheModel');

		$this->uid = getAuthUID();
		$this->pid = getAuthPersonID();
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * confirms ampel and inserts ampel_id in public.tbl_ampel_benutzer_bestaetigt
	 * @access public
	 *
	 */
	public function confirm($ampel_id)
	{
		$this->load->library('form_validation');
		$this->form_validation->set_data(['ampel_id'=> $ampel_id]);
		$this->form_validation->set_rules('ampel_id', 'Ampel ID', 'required|integer');
		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		// load Ampel_benutzer_bestaetigt_model to confirm the ampel
		$this->load->model('content/Ampel_Benutzer_Bestaetigt_model', 'AmpelBenutzerBestaetigtModel');
		$insert_into_result = $this->AmpelBenutzerBestaetigtModel->insert(["ampel_id"=> $ampel_id, "uid"=> $this->uid]);

		$insert_into_result = $this->getDataOrTerminateWithError($insert_into_result);

		$this->terminateWithSuccess($insert_into_result);
	}
	
	/**
	 * queries active and not confirmed ampeln by the user
	 * @access public
	 *
	 */
	public function open()
	{
		$userAmpeln = array();

		// fetch active ampeln
		$activeAmpeln = $this->AmpelModel->openActive($this->uid, false);
		
		$activeAmpeln = $this->getDataOrTerminateWithError($activeAmpeln);

		foreach ($activeAmpeln as $ampel) {
			// only include non confirmed active ampeln in the result
			if (!$ampel->bestaetigt) {
				// check if the user was assigned to the ampel
				$zugeteilt = $this->AmpelModel->isZugeteilt($this->uid, $ampel->benutzer_select);

				$zugeteilt = $this->getDataOrTerminateWithError($zugeteilt);

				if($zugeteilt) $userAmpeln[] = $ampel;
			}
		}
		
		$this->terminateWithSuccess($userAmpeln);
	}

	/**
	 * queries all ampeln of the user
	 * @access public
	 *
	 */
	public function all()
	{
		$userAmpeln = array();
		
		$ampel_result = $this->AmpelModel->active(false, $this->uid);
		
		$ampel_result = $this->getDataOrTerminateWithError($ampel_result);
		
		foreach ($ampel_result as $ampel) {
			// check if the ampel was assigned to the user
			$zugeteilt = $this->AmpelModel->isZugeteilt($this->uid, $ampel->benutzer_select);

			$zugeteilt = $this->getDataOrTerminateWithError($zugeteilt);

			if ($zugeteilt) $userAmpeln[] = $ampel;
		}

		$this->terminateWithSuccess($userAmpeln);
	}

	/**
	 * queries all ampeln that were assigned to the user until start of first work day
	 * @access public
	 *
	 */
	public function alleAmpeln()
	{

		//fetch all ampeln
		$alle_ampeln = $this->AmpelModel->alleAmpeln($this->uid);

		$alle_ampeln = $this->getDataOrTerminateWithError($alle_ampeln);

		$alle_ampeln = array_map(function ($ampel) {
			// check if ampel is confirmed by user
			$confirmedByUser = $this->AmpelModel->isConfirmed($ampel->ampel_id, $this->uid);
			$ampel->bestaetigt = $confirmedByUser;
			return $ampel;
		}, $alle_ampeln);

		$this->terminateWithSuccess($alle_ampeln);
	}
}

