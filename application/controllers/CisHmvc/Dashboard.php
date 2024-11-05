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
		$this->load->view('CisHmvc/Dashboard.php');
	}
}
