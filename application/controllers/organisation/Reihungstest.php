<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Overview of Placementtests
 */
class Reihungstest extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'index' => 'infocenter:r'
			)
		);

		$this->load->library('WidgetLib');
		$this->loadPhrases(
			array(
				'global',
				'ui',
				'filter'
			)
		);

		$this->setControllerId(); // sets the controller id
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Main page of the InfoCenter tool
	 */
	public function index()
	{
		$this->load->view('organisation/reihungstest/reihungstest.php');
	}
}
