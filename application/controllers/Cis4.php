<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

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

		// Load Config
		$this->load->config('calendar');
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index()
	{
		$this->load->view('CisRouterView/CisRouterView.php', ['route' => 'FhcDashboard']);
	}
}
