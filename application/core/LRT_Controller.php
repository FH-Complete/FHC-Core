<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Long Run Task
 *
 * - This controller acts as interface of the LongRunTaskLib that contains
 * all the needed functionalities to operate with the Long Run Task system
 * that is built on top of the Jobs Queue System
 * - This is an abstract class that provide basic functionalities,
 * it has to be extended to broaden its logic
 * - Any implementation of a Long Run Task should extends this class to
 * properly operate with the LRT system
 */
abstract class LRT_Controller extends JOB_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Changes the needed configs for LogLib
		$this->LogLibJob->setConfigs(
			array(
				'dbExecuteUser' => get_class($this),
				'requestId' => 'LRT'
			)
		);

		// Loads LongRunTaskLib library
		$this->load->library('LongRunTaskLib');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	abstract public function run($jobid);

	//------------------------------------------------------------------------------------------------------------------
	// Protected methods

	/**
	 *
	 */
	protected function getLrt($jobid)
	{
		return $this->longruntasklib->getLrt($jobid);
	}

	/**
	 * 
	 */
	protected function setProgress($jobid, $progress)
	{
		return $this->longruntasklib->setProgress($jobid, $progress);
	}

	/**
	 * 
	 */
	protected function setOutput($jobid, $output)
	{
		return $this->longruntasklib->setOutuput($jobid, $output);
	}
}

