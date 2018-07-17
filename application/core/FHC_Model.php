<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class FHC_Model extends CI_Model
{
	/**
	 * Standard constructor for all the models
	 */
	public function __construct()
	{
		parent::__construct();

		// Load languages files
		$this->lang->load('fhc_model');
		$this->lang->load('fhcomplete');
	}
}
