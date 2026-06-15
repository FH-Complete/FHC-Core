<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Coodle extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct([
			'index' => [self::PERM_LOGGED],
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index()
	{
		$this->load->view('CisRouterView/CisRouterView.php', ["viewData" => [], 'route' => 'Coodle']);
	}
}
