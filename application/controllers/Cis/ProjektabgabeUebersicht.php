<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class ProjektabgabeUebersicht extends Auth_Controller
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
		// TODO create permission
		$viewData = array(
			'uid' => getAuthUID(),
			'showEdit' => true
		);

		$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'ProjektabgabeUebersicht']);
	}
}
