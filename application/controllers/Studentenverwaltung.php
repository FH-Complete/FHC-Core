<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Studentenverwaltung extends Auth_Controller
{
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
		$this->load->view('Studentenverwaltung', [
			'permissions' => [
				'student/bpk' => $this->permissionlib->isBerechtigt('student/bpk'),
				'student/alias' => $this->permissionlib->isBerechtigt('student/alias'),
				'basis/prestudent' => $this->permissionlib->isBerechtigt('basis/prestudent'),
				'basis/prestudentstatus' => $this->permissionlib->isBerechtigt('basis/prestudentstatus'),
				'assistenz_stgs' => $this->permissionlib->getSTG_isEntitledFor('assistenz'),
				'admin' => $this->permissionlib->isBerechtigt('admin'),
				'assistenz_schreibrechte' => $this->permissionlib->isBerechtigt('assistenz','suid'),
				'student/keine_studstatuspruefung' => $this->permissionlib->isBerechtigt('student/keine_studstatuspruefung'),
				'lehre/reihungstestAufsicht' => $this->permissionlib->isBerechtigt('lehre/reihungstestAufsicht')
			],
			'variables' => [
				'semester_aktuell' => $this->variablelib->getVar('semester_aktuell')
			]
		]);
	}
}
