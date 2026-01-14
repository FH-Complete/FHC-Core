<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Zeitsperren extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct([
			'index' => ['basis/cis:r'],
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
	}

	/**
	 * index loads the view Zeitsperren
	 * @access public
	 * @return void
	 */
	public function index()
	{
		$viewData = array(
			'uid'=>getAuthUID(),
		);

		$this->load->view('CisRouterView/CisRouterView.php',['viewData' => $viewData, 'route' => 'zeitsperren']);
	}
}
