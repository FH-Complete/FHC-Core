<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Benotungstool extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct([
			'index' => self::PERM_LOGGED
		]);

		$this->_ci =& get_instance();
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