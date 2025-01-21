<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Vertragsverwaltung extends Auth_Controller
{

	//TODO(Manu) Permissions
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
		$this->load->view('Vertragsverwaltung', [
			'permissions' => [
				'vertragsverwaltung_schreibrechte' => $this->permissionlib->isBerechtigt('vertrag/mitarbeiter','suid')
			]
		]);
	}
}
