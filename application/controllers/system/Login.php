<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends VileSci_Controller 
{

	public function __construct()
    {
        parent::__construct();
    }

	public function index()
	{
		$this->load->view('test.php');
	}
}
