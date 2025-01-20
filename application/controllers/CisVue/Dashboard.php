<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Dashboard extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'index' => 'dashboard/benutzer:r'
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
		$begruesung = $this->PersonModel->getFirstName(getAuthUID());
		if(isError($begruesung))
		{
			show_error("name couldn't be loaded for username ".getAuthUID());
		}
		$begruesung = getData($begruesung);
		$viewData = array(
			'name' => $begruesung
		);
		
		$this->load->view('CisVue/Dashboard.php', ['viewData' => $viewData]);

	}
}