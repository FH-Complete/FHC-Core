<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class LVVerwaltung extends Auth_Controller
{
	public function __construct()
	{
		$permissions = [];

		$router = load_class('Router');
		$permissions[$router->method] = ['admin:r', 'assistenz:r'];
		parent::__construct($permissions);

		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
	}

	/**
	 * @return void
	 */
	public function _remap()
	{
		$this->load->view('LVVerwaltung', [
			'permissions' => [
				'lehre/lehrveranstaltung' => $this->permissionlib->isBerechtigt('lehre/lehrveranstaltung'),
				'lv-plan/gruppenentfernen' => $this->permissionlib->isBerechtigt('lv-plan/gruppenentfernen'),
				'lv-plan/lektorentfernen' => $this->permissionlib->isBerechtigt('lv-plan/lektorentfernen'),
			],
			'variables' => [
				'semester_aktuell' => $this->variablelib->getVar('semester_aktuell')
			],
			'configs' => [
				'showVertragsdetails' => defined('FAS_LV_LEKTORINNENZUTEILUNG_VERTRAGSDETAILS_ANZEIGEN') && FAS_LV_LEKTORINNENZUTEILUNG_VERTRAGSDETAILS_ANZEIGEN,
				'showGewichtung' => defined('CIS_GESAMTNOTE_GEWICHTUNG') && CIS_GESAMTNOTE_GEWICHTUNG,
				'lehreinheitAnmerkungDefault' => defined('LEHREINHEIT_ANMERKUNG_DEFAULT') ? LEHREINHEIT_ANMERKUNG_DEFAULT : '',
				'lehreinheitRaumtypDefault' => defined('DEFAULT_LEHREINHEIT_RAUMTYP') ? DEFAULT_LEHREINHEIT_RAUMTYP : '',
				'lehreinheitRaumtypAlternativeDefault' => defined('DEFAULT_LEHREINHEIT_RAUMTYP_ALTERNATIV') ? DEFAULT_LEHREINHEIT_RAUMTYP_ALTERNATIV : ''
			]
		]);

	}
}
