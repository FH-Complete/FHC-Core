<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Testing LRT to check if the LRT system is working properly
 * This will be called by the LongRunTaskExecJob
 */
class LRTDummy extends LRT_Controller
{
	/**
	 *
	 */
	public function run($jobid)
	{
		// Get the LRT record related to the provided jobid
		$lrtResult = $this->getLrt($jobid);

		// If an error occurred or a record has not been found
		if (isError($lrtResult) || !hasData($lrtResult))
		{
			$this->logError(getError($lrtResult));
		}
		else
		{
			$lrt = getData($lrtResult)[0];
			$input = json_decode($lrt->input);
			sleep((int)$input->sleep);
			$this->setProgress($jobid, 100);
			$this->setOutput($jobid, 'I slept for '.$input->sleep.' seconds');
		}
	}
}

