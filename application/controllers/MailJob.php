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

class MailJob extends FHC_Controller
{
	/**
	 * API constructor
	 */
	public function __construct()
	{
		// An empty array as parameter will ensure that this controller is ONLY callable from command line
		parent::__construct(array());

		// Loads MessageLib
		$this->load->library("MessageLib");
	}

	public function sendMessages($numberToSent = null, $numberPerTimeRange = null, $email_time_range = null, $email_from_system = null)
	{
		$this->messagelib->sendAll($numberToSent, $numberPerTimeRange, $email_time_range, $email_from_system);
	}
}
