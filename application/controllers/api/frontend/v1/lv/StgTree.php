<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class StgTree extends FHCAPI_Controller
{
	public function __construct()
	{
		$permissions = [];
		$router = load_class('Router');
		$permissions[$router->method] = ['admin:r', 'assistenz:r'];
		parent::__construct($permissions);

		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
	}

	public function _remap($method, $params = [])
	{
		if ($method == '' || $method == 'index')
			return $this->getBase();

		if (!$this->permissionlib->isBerechtigt('assistenz', 's', $method)
			&& !$this->permissionlib->isBerechtigt('admin', 's', $method)
		) {
			return $this->_outputAuthError([$method => ['admin:r', 'assistenz:r']]);
		}

		return $this->getStudiengang($method);
		show_404();
	}

	protected function getBase()
	{
		$this->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');

		$this->StudiengangModel->addDistinct();
		$this->StudiengangModel->addSelect("v.studiengang_kz AS link");
		$this->StudiengangModel->addSelect(
			"CONCAT(kurzbzlang, ' (', UPPER(CONCAT(typ, kurzbz)), ') - ', tbl_studiengang.bezeichnung) AS name",
			false
		);
		$this->StudiengangModel->addSelect('erhalter_kz');
		$this->StudiengangModel->addSelect('typ');
		$this->StudiengangModel->addSelect('kurzbz');
		$this->StudiengangModel->addSelect('studiengang_kz');
		$this->StudiengangModel->addSelect('studiengang_kz AS stg_kz');

		$this->StudiengangModel->addOrder('erhalter_kz');
		$this->StudiengangModel->addOrder('typ');
		$this->StudiengangModel->addOrder('kurzbz');

		$stgs = $this->permissionlib->getSTG_isEntitledFor('admin') ?: [];
		$stgs = array_merge($stgs, $this->permissionlib->getSTG_isEntitledFor('assistenz') ?: []);

		if (!$stgs)
			$this->terminateWithSuccess([]);

		$this->StudiengangModel->db->where_in('studiengang_kz', $stgs);

		$result = $this->StudiengangModel->loadWhere(['v.aktiv' => true]);

		$list = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($list);
	}

	protected function getStudiengang($studiengang_kz)
	{
		$link = $studiengang_kz . '/';

		$this->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');

		$this->StudiengangModel->addDistinct();
		$this->StudiengangModel->addSelect("CONCAT(" . $this->StudiengangModel->escape($link) . ", semester) AS link", false);
		$this->StudiengangModel->addSelect("CONCAT(UPPER(CONCAT(typ, kurzbz)), '-', semester, (SELECT CASE WHEN bezeichnung IS NULL OR bezeichnung='' THEN ''::TEXT ELSE CONCAT(' (', bezeichnung, ')') END FROM public.tbl_lehrverband WHERE studiengang_kz=v.studiengang_kz AND semester=v.semester ORDER BY verband, gruppe LIMIT 1)) AS name", false);
		$this->StudiengangModel->addSelect("TRUE AS leaf", false);

		$this->StudiengangModel->addSelect('semester');
		$this->StudiengangModel->addSelect($this->StudiengangModel->escape($studiengang_kz) . '::integer AS stg_kz', false);

		$this->StudiengangModel->addOrder('semester');

		$result = $this->StudiengangModel->loadWhere([
			'v.studiengang_kz' => $studiengang_kz,
			'v.aktiv' => true
		]);
		$list = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($list);
	}
}
