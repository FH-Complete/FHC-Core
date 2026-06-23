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
		$lrtsResult = $this->longruntasklib->getLRTs();
		if (isError($lrtsResult)) return $lrtsResult;

		if (hasData($lrtsResult))
		{
			// For each LRT
			foreach (getData($lrtsResult) as $lrt)
			{
				// Execute the task
				$this->longruntasklib->executeLrt($lrt);
			}
		}

		$this->logInfo('Execute long run tasks ended');
	}

	/**
	 *
	 */
	public function killHangingLRTs()
	{
		$this->logInfo('Kill hanging LRTs started');

		// Get all the LRTs that is possible to execute now
		$lrtsResult = $this->longruntasklib->getHangingLRTs();
		if (isError($lrtsResult)) return $lrtsResult;

		if (hasData($lrtsResult))
		{
			// For each LRT
			foreach (getData($lrtsResult) as $lrt)
			{
				// Kill the process with a SIGKILL
				exec('kill -9 '.$lrt->pid.' > /dev/null 2>&1');

				// Set the LRT as failed
				$lrtExecFailedResult = $this->longruntasklib->lrtExecFailed($lrt->jobid);
				if (isError($lrtExecFailedResult)) $this->logError(getError(lrtExecFailedResult));
			}
		}

		$this->logInfo('Kill hanging LRTs ended');
	}
}

