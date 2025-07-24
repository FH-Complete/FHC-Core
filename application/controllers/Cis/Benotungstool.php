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

		
		// TODO: check if related CIS config is also loaded when being routed in Cis4 by vuerouter
		// TODO: check if new benotungstool should be configurable the exact same way?
		$viewData = array(
			'uid'=>getAuthUID(),
			'CIS_GESAMTNOTE_UEBERSCHREIBEN' => CIS_GESAMTNOTE_UEBERSCHREIBEN
		);

		$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'Benotungstool']);
	}
	
}