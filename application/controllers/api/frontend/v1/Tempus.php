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

class Tempus extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{

		parent::__construct([
			'getCourses' => self::PERM_LOGGED,
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
		//$this->load->model('ressource/Stundenplan_model', 'StundenplanModel');
		//$this->load->model('ressource/Reservierung_model', 'ReservierungModel');


	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods


	/**
	* fetches courses
	 * @access public
	 *
	 */
	public function getCourses()
	{
		// form validation
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('searchfilter',"searchfilter","required");
		if($this->form_validation->run() === FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		// storing the get parameter in local variables
		$searchfilter = $this->input->get('searchfiler', TRUE);

		// TODO implement Loading Data
		$course_data = array(
			array(
			'lehreinheit_id'=>'1',
			'bezeichnung' => 'Englisch 1',
			'studiengang_kurzbz' => 'BMR',
			'semester' => '1',
			'kurzbz' => 'ENG',
			'lektoren' => array('OesterAn','KindlMa')
			),
			array(
			'lehreinheit_id'=>'2',
			'bezeichnung' => 'Mahtematik 1',
			'studiengang_kurzbz' => 'BMR',
			'semester' => '1',
			'kurzbz' => 'MAT',
			'lektoren' => array('BamberHa')
			)
		);

		$this->terminateWithSuccess($course_data);
	}
}
