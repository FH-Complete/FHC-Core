<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use CI3_Events as Events;

class Config extends FHC_Controller
{
	public function __construct()
	{
		// TODO(chris): access!
		parent::__construct();
	}

	public function student()
	{
		$result = [];
		$result['details'] = [
			'title' => 'Details',
			'component' => './Stv/Studentenverwaltung/Details/Details.js'
		];
		$result['kontakt'] = [
			'title' => 'Kontakt',
			'component' => './Stv/Studentenverwaltung/Details/Kontakt.js'
		];
		$result['notizen'] = [
			'title' => 'Notizen',
			'component' => './Stv/Studentenverwaltung/Details/Notizen.js'
		];

		Events::trigger('stv_conf_student', $result);

		$this->outputJsonSuccess($result);
	}

	public function students()
	{
		$this->outputJsonSuccess([]);
	}
}
