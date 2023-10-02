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
		$this->load->library('AuthLib');
		$this->load->library('PermissionLib');

		$this->load->view('Studentenverwaltung', [
			'permissions' => [
				'student/bpk' => $this->permissionlib->isBerechtigt('student/bpk'),
				'student/alias' => $this->permissionlib->isBerechtigt('student/alias')
			]
		]);
	}
}
