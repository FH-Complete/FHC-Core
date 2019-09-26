<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Overview on cronjob logs
 */
class LogsViewer extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'index' => 'admin:r'
			)
		);

		// Loads WidgetLib
		$this->load->library('WidgetLib');

		// Loads phrases system
		$this->loadPhrases(
			array(
				'global',
				'ui',
				'filter'
			)
		);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Main page of the InfoCenter tool
	 */
	public function index()
	{
		$this->load->view('system/logs/logsViewer.php');
	}
}
