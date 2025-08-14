<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class MyLvPlan extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct([
			'index' => ['basis/cis:r']
		]);

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
		
		$viewData = array(
			'uid'=>getAuthUID(),
			'timezone' => $this->config->item('timezone')
		);
		
		$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'MyLvPlan']);
	}
}
