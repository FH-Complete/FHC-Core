<?php

/**
 * class defining ressources and method to use for plausicheck issue producer
 */
abstract class PlausiChecker
{
	protected $_ci; // code igniter instance

	public function __construct()
	{
		$this->_ci =& get_instance(); // get code igniter instance

		// load libraries
		$this->_ci->load->library('issues/PlausicheckLib'); // load plausicheck library
	}

	/**
	 * Executes a plausi check.
	 * @param $paramsForChecking array parameters needed for executing the check
	 * @return array with objects which failed the plausi check
	 */
	abstract public function executePlausiCheck($paramsForChecking);
}
