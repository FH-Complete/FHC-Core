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
			'index' => ['student/anrechnung_beantragen:r','user:r'] // TODO(chris): permissions?
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index()
	{
		$this->load->view('Cis/Stundenplan');
	}
}
