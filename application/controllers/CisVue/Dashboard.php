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
				'index' => 'user:r'
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
		$this->load->view('CisVue/Dashboard.php');
	}
}
