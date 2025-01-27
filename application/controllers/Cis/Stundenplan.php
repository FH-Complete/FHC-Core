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
	public function index($lv_id = null)
	{

		$viewData = array(
			'lv_id' => $lv_id
		);
		
		$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'Stundenplan']);
	}
}
