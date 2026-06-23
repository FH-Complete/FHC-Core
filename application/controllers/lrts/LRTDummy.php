<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Testing LRT to check if the LRT system is working properly
 * This will be called by the LongRunTaskExecJob
 */
class LRTDummy extends LRT_Controller
{
	/**
	 * This method must be implemented!
	 */
	public function run($jobid)
	{
		$this->logInfo('Long run tasks '.get_class($this).' started');

		$this->_doIt($jobid);

		$this->logInfo('Long run tasks '.get_class($this).' ended');
	}

	/**
	 * Loops on the number of seconds provided by the LRT input
	 * Sleeps every time 1 sec
	 * Writes the progress
	 * Writes the output
	 */
	private function _doIt($jobid)
	{
		// Get the LRT record related to the provided jobid
		$lrtResult = $this->getLrt($jobid);

		// If an error occurred or the record has not been found
		if (isError($lrtResult))
		{
			$this->logError($lrtResult);
			return;
		}
		if (!hasData($lrtResult))
		{
			$this->logError('LRT not found in database');
			return;
		}
		
		// Get the record
		$lrt = getData($lrtResult)[0];

		// Get and check the input
		$input = json_decode($lrt->{LongRunTaskLib::PROPERTY_INPUT});
		if ($input == null)
		{
			$this->logError('LRT input is not a valid json');
			return;
		}

		// Operation
		for ($i = 0; $i < (int)$input->sleep; $i++)
		{
			sleep(1);
			// Set the progress
			$setProgressResult = $this->setProgress($jobid, (($i + 1) / (int)$input->sleep) * 100);
			if (isError($setProgressResult))
			{
				$this->logError($setProgressResult);
				return;
			}
		}

		$sleepMsg = 'The user '.$lrt->{LongRunTaskLib::PROPERTY_UID}.' slept for '.$input->sleep.' seconds';

		$this->logInfo($sleepMsg);

		// Set the output
		$setOutputResult = $this->setOutput($jobid, $sleepMsg);
		if (isError($setOutputResult))
		{
			$this->logError($setOutputResult);
		}
	}
}

