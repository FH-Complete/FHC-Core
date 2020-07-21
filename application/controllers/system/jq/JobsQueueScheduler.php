<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller is a job that exposes the start method
 * If it is called it will look into the configuration file to get all the extensions
 * that need to place jobs into the jobs queue
 */
class JobsQueueScheduler extends JQW_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * 
	 */
	public function schedule()
	{
		$this->logInfo('Scheduler started');

		$schedulerResult = $this->jobsqueuelib->schedule();

		// If error occurred then log it
		if (isError($schedulerResult)) $this->logError(getError($schedulerResult));

		// If non blocking errors occurred log them
		if (hasData($schedulerResult) && !isEmptyArray(getData($schedulerResult)))
		{
			foreach (getData($schedulerResult) as $nonBlockingError)
			{
				$this->logWarning($nonBlockingError);
			}
		}

		$this->logInfo('Scheduler ended');
	}
}

