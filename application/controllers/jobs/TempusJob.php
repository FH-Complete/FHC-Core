<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

class TempusJob extends JOB_Controller
{
	private $_ci;

	public function __construct()
	{
		parent::__construct();

		$this->_ci =& get_instance();

		$this->_ci->load->helper('hlp_sancho_helper');
		$this->_ci->load->library('KalenderLib');
	}


	public function sync()
	{
		$this->_ci->logInfo('Start job FHC-Core->Tempus->sync');
		$this->_ci->kalenderlib->sync();
		$this->_ci->logInfo('End job FHC-Core->Tempus->sync');
	}

}
