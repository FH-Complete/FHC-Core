<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Testing LRT to check if the LRT system is working properly
 * This will be called by the LongRunTaskExecJob
 */
class LRTDummy extends LRT_Controller
{
	/**
	 * Loops on the number of seconds provided by the LRT input
	 * Sleeps every time 1 sec
	 * Writes the progress
	 * Writes the output
	 */
	public function run($jobid)
	{
		$this->logInfo('Long run tasks '.get_class($this).' started');

		$this->_jobid = $jobid;

		// Get the LRT record related to the provided jobid
		$lrtResult = $this->getLrt($jobid);

		// If an error occurred or the record has not been found
		if (isError($lrtResult) || !hasData($lrtResult))
		{
			$this->logError($lrtResult);
		}
		else
		{
			// Get the record
			$lrt = getData($lrtResult)[0];

			// Get and check the input
			$input = json_decode($lrt->input);
			if ($input == null)
			{
				$this->logError('LRT input is not a valid json');
			}
			else
			{
				$error = false; // be optimistic

				// Operation
				for ($i = 0; $i < (int)$input->sleep; $i++)
				{
					sleep(1);
					// Set the progress
					$setProgressResult = $this->setProgress($jobid, (($i + 1) / (int)$input->sleep) * 100);
					if (isError($setProgressResult))
					{
						$this->logError($setProgressResult);
						$error = true;
					}
				}

				// If no errors
				if (!$error)
				{
					$this->logInfo('The user '.$lrt->uid.' slept for '.$input->sleep.' seconds');

					// Set the output
					$setOutputResult = $this->setOutput($jobid, 'The user '.$lrt->uid.' slept for '.$input->sleep.' seconds');
					if (isError($setOutputResult))
					{
						$this->logError($setOutputResult);
					}
				}
			}
		}

		$this->logInfo('Long run tasks '.get_class($this).' ended');
	}
}

