<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Cis4 extends FHC_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads Libraries
		$this->load->library('AuthLib');
		$this->load->library('PermissionLib');
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
