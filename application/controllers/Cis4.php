<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Cis4 extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
		    array(
			'index' => 'basis/cis:r'
		    )
		);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index()
	{
		$this->load->model('person/Person_model','PersonModel');
		$personData = getData($this->PersonModel->getByUid(getAuthUID()))[0];
		
		$viewData = array(
			'uid' => getAuthUID(),
			'name' => $personData->vorname,
			'person_id' => $personData->person_id
		);

		$this->load->view('CisRouterView/CisRouterView.php',['viewData' => $viewData, 'route' => 'FhcDashboard']);
	}
}