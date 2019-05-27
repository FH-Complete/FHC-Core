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
	 * Starts the migration procedure
	 */
	public function start()
	{
		$this->dbskellib->start();
	}
}
