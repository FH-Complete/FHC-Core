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
	public function _remap()
	{
		$this->load->library('AuthLib');
		$this->load->library('PermissionLib');
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		$this->load->view('Studentenverwaltung', [
			'permissions' => [
				'student/bpk' => $this->permissionlib->isBerechtigt('student/bpk'),
				'student/alias' => $this->permissionlib->isBerechtigt('student/alias')
			],
			'variables' => [
				'semester_aktuell' => $this->variablelib->getVar('semester_aktuell')
			]
		]);
	}
}
