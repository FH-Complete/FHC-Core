<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Cms extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct(['index' => 'basis/cms:r']);
		$this->loadPhrases(['global', 'ui', 'cms']);
	}

	public function index()
	{
		$this->load->view('Cms.php');
	}
}
