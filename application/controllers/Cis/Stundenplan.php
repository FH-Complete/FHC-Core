<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Stundenplan extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct([
			'index' => ['basis/cis:r']
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index()
	{
		
		$viewData = array(
			'uid'=>getAuthUID(),
		);
		
		$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'Stundenplan']);
	}
}
