<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 */
class LRT extends Auth_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'getRunningLRTs' => self::PERM_LOGGED,
			)
		);

		// Loads libraries
		$this->load->library('LongRunTaskLib');
	}

	/**
	 *
	 */
	public function getRunningLRTs()
	{
		$runningLrtsResult = $this->longruntasklib->getRunningLRTsUser(getAuthUID());

		if (isError($runningLrtsResult)) $this->terminateWithJsonError($runningLrtsResult);

		$this->outputJson($runningLrtsResult);
	}
}

