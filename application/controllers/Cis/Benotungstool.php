<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

// the constructor needs the permissions before CodeIgniter can load a helper
require_once APPPATH . 'helpers/hlp_benotungstool_helper.php';

/**
 * The page of the Benotungstool. The same permissions open it as the Noten API.
 */
class Benotungstool extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct([
			'index' => benotungstoolPermissions()
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

		$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'Benotungstool']);
	}
	
}