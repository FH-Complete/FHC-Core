<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Overview on cronjob logs
 */
class Ferienverwaltung extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'index' => ['basis/ferien:r']
			)
		);

		// Loads WidgetLib
		//$this->load->library('WidgetLib');

		// Loads phrases system
		$this->loadPhrases(
			array(
				'global',
				'ui'
				//'ferien'
			)
		);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Everything has a beginning
	 */
	public function index()
	{
		$this->load->view('lehre/ferienverwaltung.php');
	}
}
