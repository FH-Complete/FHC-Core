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
				'installFromCore' => 'admin:rw'
			)
		);

		// Loads PhrasesLib
		$this->load->library('PhrasesLib');
	}

	/**
	 * 
	 */
	public function installFromCore()
	{
		$this->phraseslib->installFromCore();
	}
}

