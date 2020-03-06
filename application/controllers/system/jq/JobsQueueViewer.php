<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Jobs Queue Viewer
 *
 * This controller renders a FilterWidget to monitor the current status of the Jobs Queue System
 */
class JobsQueueViewer extends Auth_Controller
{
	const PARAM_START_DATE = 'startDate';

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

		// Loads JobsQueueLib
		$this->load->library('JobsQueueLib');

		// Loads phrases system
		$this->loadPhrases(
			array(
				'global',
				'ui',
				'filter'
			)
		);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Everything has a beginning
	 */
	public function index()
	{
		$this->load->view('system/jq/jobsQueueViewer.php');
	}
}
