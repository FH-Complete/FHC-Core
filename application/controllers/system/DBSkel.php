<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

class DBSkel extends CLI_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->load->library('DBSkelLib');
	}

	/**
	 * Starts the DBSkel procedure
	 */
	public function start($step = null, $selectedDirectories = null)
	{
		// If the DBSkel procedure fails then exit with an error
		// In this way it's possible to undestand from console what is the exit status of the procedure
		$this->dbskellib->start($step, $selectedDirectories) === true ? exit(0) : exit(1);
	}
}

