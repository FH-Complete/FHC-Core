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
	protected $_jobid; // record id for this LRT

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

		$this->_jobid = null; // default value
	}

	/**
	 * Destructor, once the LRT execution is over...
	 */
	public function __destruct()
	{
		// Sends email to the user

		// Set the status and the endtime of this LRT as done
		$lrtExecOverResult = $this->longruntasklib->lrtExecOver($this->_jobid);
		// If an error occurred then log it
		if (isError($lrtExecOverResult)) $this->logError($lrtExecOverResult);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	abstract public function run($jobid);

	//------------------------------------------------------------------------------------------------------------------
	// Protected methods

	/**
	 *
	 */
	protected function getLrt()
	{
		return $this->longruntasklib->getLrt($this->_jobid);
	}

	/**
	 * 
	 */
	protected function setProgress($progress)
	{
		return $this->longruntasklib->setProgress($this->_jobid, $progress);
	}

	/**
	 * 
	 */
	protected function setOutput($output)
	{
		return $this->longruntasklib->setOutuput($this->_jobid, $output);
	}
}

