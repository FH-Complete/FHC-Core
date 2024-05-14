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
				'index' => 'system/developer:r'
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
	 * Everything has a beginning
	 */
	public function index()
	{
		$this->load->view('system/logs/logsViewer.php');
	}
}
