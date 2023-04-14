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
		$this->load->view('CisHtml/Dashboard.php');
	}

	

}
