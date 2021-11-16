<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

class CLI_Manager extends CLI_Controller
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads PhrasesLib
		$this->load->library('PhrasesLib');
	}

	/**
	 * 
	 */
	public function installFrom($phrasesDirectoryPath)
	{
		$this->phraseslib->installFrom(urldecode($phrasesDirectoryPath));
	}
}

