<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

class MailJob extends JOB_Controller
{
	/**
	 * API constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads MessageLib
		$this->load->library('MessageLib');
	}

	/**
	 * Send all the NOT sent notice emails for messaging system
	 * The parameters are all not mandatory, they could be used to overrides the configs for testing, debug or one shot purposes
	 */
	public function sendAllMessageEmailNotices($since = '1970-01-01', $numberToSent = null, $numberPerTimeRange = null, $emailTimeRange = null, $emailFromSystem = null)
	{
		$this->logInfo('Send all message email notices started');

		// Send them all!
		$sendAllEmailNotices = $this->messagelib->sendAllEmailNotices($since, $numberToSent, $numberPerTimeRange, $emailTimeRange, $emailFromSystem);

		if (isError($sendAllEmailNotices))
		{
			$optionalParameters = new stdClass();
			$optionalParameters->$since = $since;
			$optionalParameters->$numberToSent = $numberToSent;
			$optionalParameters->$numberPerTimeRange = $numberPerTimeRange;
			$optionalParameters->$emailTimeRange = $emailTimeRange;
			$optionalParameters->$emailFromSystem = $emailFromSystem;

			$this->logError($sendAllEmailNotices->retval, $optionalParameters);
		}
		elseif (!hasData($sendAllEmailNotices))
		{
			$this->logInfo('There were no unsent messages');
		}

		$this->logInfo('Send all message email notices ended');
	}
}
