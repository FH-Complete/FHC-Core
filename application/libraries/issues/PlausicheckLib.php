<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Lib
{
	private $_ci; // Code igniter instance

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->_ci =& get_instance();

		$this->_ci->load->model('', '');
	}

	public function getStudents()
	{
		$qry = '
			
		';
	}
}
