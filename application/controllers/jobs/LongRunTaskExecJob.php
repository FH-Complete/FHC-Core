<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * - This controller acts as interface of the LongRunTaskLib that contains
 * all the needed functionalities to operate with the Long Run Task system
 * that is built on top of the Jobs Queue System
 * - This is a Job Queue Worker that gets scheduled LRTs from the queue and executes them
 * - Once all the LRTs have been started checks if there are LRTs that are running for too long and kills them
 */
class LongRunTaskExecJob extends JOB_Controller
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

	/**
	 * Executes all the new LRTs
	 */
	public function execEmAll()
	{
		$this->logInfo('Execute long run tasks started');

		// Get all the LRTs that is possible to execute now
		$lrtsResult = $this->longruntasklib->getNewLRTs();
		// If an error occurred then return it
		if (isError($lrtsResult)) return $lrtsResult;

		if (hasData($lrtsResult))
		{
			// For each LRT
			foreach (getData($lrtsResult) as $lrt)
			{
				// Execute the task
				$executeLrtResult = $this->longruntasklib->executeLrt($lrt);
				if (isError($executeLrtResult)) $this->logError($executeLrtResult);
			}
		}

		$this->logInfo('Execute long run tasks ended');
	}

	/**
	 * Kills all the hanging LRTs
	 */
	public function killHangingLRTs()
	{
		$this->logInfo('Kill hanging LRTs started');

		// Get all the LRTs that is possible to execute now
		$lrtsResult = $this->longruntasklib->getHangingLRTs();
		// If an error occurred then return it
		if (isError($lrtsResult)) return $lrtsResult;

		if (hasData($lrtsResult))
		{
			// For each LRT
			foreach (getData($lrtsResult) as $lrt)
			{
				// Kill the task
				$killLrtResult = $this->longruntasklib->killLrt($lrt);
				if (isError($killLrtResult)) $this->logError($killLrtResult);
			}
		}

		$this->logInfo('Kill hanging LRTs ended');
	}

	/**
	 * 
	 */
	public function checkExecution()
	{
		$this->logInfo('Check execution long run tasks started');

		// Get the related LRT data from the queue
		$lrtsResult = $this->longruntasklib->getRunningLRTs();
		// If an error occurred then return it
		if (isError($lrtsResult)) return $lrtsResult;

		// If there are running LRTs
		if (hasData($lrtsResult))
		{
			// For each LRT
			foreach (getData($lrtsResult) as $lrt)
			{
				// Check the LRT execution
				$checkExecutionResult = $this->longruntasklib->checkExecution($lrt);
				if (isError($checkExecutionResult)) $this->logError($checkExecutionResult);
			}
		}

		$this->logInfo('Check execution long run tasks ended');
	}
}

