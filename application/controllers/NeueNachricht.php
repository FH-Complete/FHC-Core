<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class NeueNachricht extends Auth_Controller
{
	public function __construct()
	{
		$permissions = [];
		$router = load_class('Router');
		$permissions[$router->method] = ['vertrag/mitarbeiter:r'];
		parent::__construct($permissions);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
	}

	/**
	 * @return void
	 */
	public function _remap()
	{
		//now working
		$this->load->view('Nachrichten', [
			'permissions' => [
				'assistenz_schreibrechte' => $this->permissionlib->isBerechtigt('assistenz','suid'),
			]
		]);
	}
}
