<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Compat extends Auth_Controller
{

	/**
	 * Constructor
	 */

	public function __construct()
	{
		parent::__construct([
			'ci' => ['basis/cis:r'],
			'legacy' => ['basis/cis:r'],
			
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods


	/**
	 * @access public
	 * @return void 
	 */
	public function ci()
	{
		$this->load->view('CisRouterView/CisRouterView.php',['route' => 'Compat']);
	}

	/**
	 * @access public
	 * @return void
	 */
	public function legacy()
	{
		$this->load->view('CisRouterView/CisRouterView.php',['route' => 'Compat']);
	}

}
