<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Vertragsverwaltung extends Auth_Controller
{
	//TODO(Manu) Permissions
	public function __construct()
	{
		$permissions = [];
		$router = load_class('Router');
		$permissions[$router->method] = ['admin:r', 'assistenz:r'];
		parent::__construct($permissions);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
	}

	/**
	 * @return void
	 */
	public function _remap()
	{
		$this->load->view('Vertragsverwaltung', [
			'permissions' => [
				'assistenz_stgs' => $this->permissionlib->getSTG_isEntitledFor('assistenz'),
				'admin' => $this->permissionlib->isBerechtigt('admin'),
				'assistenz_schreibrechte' => $this->permissionlib->isBerechtigt('assistenz','suid'),
			]
		]);
	}
}
