<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');


class Lehrverband extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'hasOrgforms' => ['admin:r', 'assistenz:r'],
			'getTree' => ['admin:r', 'assistenz:r'],
			'getSpecialgroups' => ['admin:r', 'assistenz:r']
		]);
	}

	public function hasOrgforms($studiengang_kz)
	{
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		
		$result = $this->StudiengangModel->load($studiengang_kz);
		
		$data = $this->getDataOrTerminateWithError($result);
		if ($data) {
			$data = current($data)->mischform;
		}
		
		$this->terminateWithSuccess($data);
	}

	public function getTree($studiengang_kz)
	{
		$this->load->model('organisation/Lehrverband_model', 'LehrverbandModel');

		$result = $this->LehrverbandModel->loadWhere([
			'studiengang_kz' => $studiengang_kz,
			'aktiv' => true
		]);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getSpecialgroups($studiengang_kz)
	{
		$this->load->model('organisation/Gruppe_model', 'GruppeModel');

		$where = [
			'studiengang_kz' => $studiengang_kz,
			'lehre' => true,
			'sichtbar' => true,
			'aktiv' => true,
			'direktinskription' => false
		];

		$result = $this->GruppeModel->loadWhere($where);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}
}
