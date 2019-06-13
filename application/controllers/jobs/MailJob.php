<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (!defined("BASEPATH")) exit("No direct script access allowed");

class MailJob extends CLI_Controller
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
	 * Send all not sent messages
	 * Parameters are used to overrride messages and mail configuration
	 */
	public function sendMessages($numberToSent = null, $numberPerTimeRange = null, $emailTimeRange = null, $emailFromSystem = null)
	{
		$this->messagelib->sendAllNotices($numberToSent, $numberPerTimeRange, $emailTimeRange, $emailFromSystem);
	}
}
