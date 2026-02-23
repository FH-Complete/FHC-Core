<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Studium extends Auth_Controller
{

	/**
	 * Constructor
	 */

	public function __construct()
	{
		parent::__construct([
			'index' => ['basis/cis:r'],
			
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods


	/**
	 * index loads the Studium view
	 * @access public
	 * @return void 
	 */
	public function index()
	{
		$viewData = array(

		);
		$this->load->view('CisRouterView/CisRouterView.php',['viewData' => $viewData, 'route' => 'studium']);
	}



}
