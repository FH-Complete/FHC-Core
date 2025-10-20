<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Tempus extends Auth_Controller
{
	public function __construct()
	{
		$permissions = [];
		$router = load_class('Router');
		$permissions[$router->method] = ['admin:r', 'assistenz:r'];
		parent::__construct($permissions);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		
		// Load Config
		$this->load->config('calendar');
	}

	/**
	 * @return void
	 */
	public function _remap()
	{

		$this->load->view('Tempus', [
			'permissions' => [
				'admin' => $this->permissionlib->isBerechtigt('admin')
			],
			'variables' => [
				'semester_aktuell' => $this->variablelib->getVar('semester_aktuell'),
				'timezone' => $this->config->item('timezone')
			]
		]);
	}
}
