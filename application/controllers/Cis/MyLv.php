<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class MyLv extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct([
			'index' => ['basis/cis:r'],
			'Info' => [self::PERM_LOGGED]
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index()
	{
		
		$viewData = array(

		);

		$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'MyLv']);
	}

	public function Info($studien_semester,$lvid)
	{
		$this->load->view('Cis/LvInfo',['lvid'=> $lvid, 'studien_semester' => $studien_semester]);
	}
}
