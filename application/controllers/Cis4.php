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
		$begruesung = $this->PersonModel->getFirstName(getAuthUID());
		if(isError($begruesung))
		{
			show_error("name couldn't be loaded for username ".getAuthUID());
		}
		$begruesung = getData($begruesung);
		$viewData = array(
			'name' => $begruesung
		);

		$this->load->view('CisVue/Dashboard.php',['viewData' => $viewData]);
	}
}