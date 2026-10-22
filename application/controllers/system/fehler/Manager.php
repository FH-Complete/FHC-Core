<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

class Manager extends Auth_Controller
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'installAll' => 'admin:rw',
				'installFromCore' => 'admin:rw'
			)
		);

		// Load libraries
		$this->load->library('FehlerUpdateLib');
	}

	/**
	 *
	 */
	public function installAll()
	{
		$this->fehlerupdatelib->installAll();
	}

	/**
	 *
	 */
	public function installFromCore()
	{
		$this->fehlerupdatelib->installFromCore();
	}

	/**
	 *
	 */
	public function installFrom($fehlerConfigDirectory)
	{
		$this->fehlerupdatelib->installFrom($fehlerConfigDirectory);
	}
}