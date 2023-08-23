<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Studentenverwaltung extends FHC_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return void
	 */
	public function index()
	{
		$this->load->view('Studentenverwaltung');
	}
}
