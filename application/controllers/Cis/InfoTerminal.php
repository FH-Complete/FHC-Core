<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class InfoTerminal extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct([
			'index' => ['basis/cis:r'],
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index()
	{
		$this->load->view('Cis/InfoTerminal.php', []);
	}
}
