<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class CoodleExternal extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct([
			'index' => [self::PERM_ANONYMOUS],
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index()
	{
		$this->load->view('CisRouterView/CisRouterView.php', ['route' => 'CoodleExternal', 'isExternal' => true]);
	}
}
