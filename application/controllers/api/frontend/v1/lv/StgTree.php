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

		$count = count($params);
		if (!$count)
			return $this->getStudiengang($method);

		if ($count == 1) {
			if (is_numeric($params[0]))
				return $this->getSemester($method, $params[0]);
			else
				return $this->getStudiengang($method, $params[0]);
		}
		if ($count == 2) {
			if (is_numeric($params[0]))
				return $this->getVerband($method, $params[0], $params[1]);
			else
				return $this->getSemester($method, $params[1], $params[0]);
		}



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

	protected function getStudiengang($studiengang_kz, $org_form = null)
	{
		$link = $studiengang_kz . '/';
		if ($org_form !== null)
			$link .= $org_form . '/';

		$this->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');

		$this->StudiengangModel->addDistinct();
		$this->StudiengangModel->addSelect("CONCAT(" . $this->StudiengangModel->escape($link) . ", semester) AS link", false);
		$this->StudiengangModel->addSelect("CONCAT(
			UPPER(CONCAT(typ, kurzbz)), 
			'-', 
			semester, 
			(
				SELECT CASE WHEN bezeichnung IS NULL OR bezeichnung='' THEN ''::TEXT ELSE CONCAT(' (', bezeichnung, ')') END 
				FROM public.tbl_lehrverband 
				WHERE studiengang_kz=v.studiengang_kz AND semester=v.semester 
				ORDER BY verband, gruppe LIMIT 1
			)
		) AS name", false);

		$this->StudiengangModel->addSelect('semester');
		$this->StudiengangModel->addSelect($this->StudiengangModel->escape($studiengang_kz) . '::integer AS stg_kz', false);

		$this->StudiengangModel->addOrder('semester');

		$result = $this->StudiengangModel->loadWhere([
			'v.studiengang_kz' => $studiengang_kz,
			'v.aktiv' => true
		]);
		$list = $this->getDataOrTerminateWithError($result);

		$result = $this->StudiengangModel->load($studiengang_kz);
		$result = $this->getDataOrTerminateWithError($result);
		if ($result)
		{
			if (current($result)->mischform)
			{
				$this->load->model('organisation/Studienordnung_model', 'StudienordnungModel');

				$this->StudienordnungModel->addDistinct();
				$this->StudienordnungModel->addSelect("CONCAT(studiengang_kz, '/', p.orgform_kurzbz) AS link");
				$this->StudienordnungModel->addSelect("p.orgform_kurzbz AS name");
				$this->StudienordnungModel->addSelect("TRUE as leaf", false);

				$this->StudienordnungModel->addJoin('lehre.tbl_studienplan p', 'studienordnung_id');

				$result = $this->StudienordnungModel->loadWhere([
					'aktiv' => true,
					'studiengang_kz' => $studiengang_kz,
					'p.orgform_kurzbz !=' => 'DDP'
				]);
				$result = $this->getDataOrTerminateWithError($result);

				$list = array_merge($list, $result);
			}
		}
		$this->terminateWithSuccess($list);
	}

	protected function getSemester($studiengang_kz, $semester, $org_form = null)
	{
		$link = $studiengang_kz . '/';
		if ($org_form !== null)
			$link .= $org_form . '/';
		$link .= $semester . '/';


		$this->load->model('organisation/Gruppe_model', 'GruppeModel');

		$this->GruppeModel->addDistinct();
		$this->GruppeModel->addSelect("CONCAT(" . $this->GruppeModel->escape($link . 'grp/') . ", gruppe_kurzbz) AS link", false);
		$this->GruppeModel->addSelect("CONCAT(gruppe_kurzbz, ' (', bezeichnung, ')') AS name", false);
		$this->GruppeModel->addSelect("TRUE AS leaf", false);

		$this->GruppeModel->addSelect('sort');
		$this->GruppeModel->addSelect('gruppe_kurzbz');
		$this->GruppeModel->addSelect($this->GruppeModel->escape($studiengang_kz) . '::integer AS stg_kz', false);

		$this->GruppeModel->addOrder('sort');
		$this->GruppeModel->addOrder('gruppe_kurzbz');

		$where = [
			'studiengang_kz' => $studiengang_kz,
			'semester' => $semester,
			'lehre' => true,
			'sichtbar' => true,
			'aktiv' => true,
			'direktinskription' => false
		];

		if ($org_form !== null)
			$where['orgform_kurzbz'] = $org_form;

		$result = $this->GruppeModel->loadWhere($where);

		$list = $this->getDataOrTerminateWithError($result);

		$this->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');

		$this->StudiengangModel->addSelect("CONCAT(" . $this->StudiengangModel->escape($link) . ", verband) AS link", false);
		$this->StudiengangModel->addSelect("CONCAT(UPPER(CONCAT(typ, kurzbz)), '-', semester, verband, (SELECT CASE WHEN bezeichnung IS NULL OR bezeichnung='' THEN ''::TEXT ELSE CONCAT(' (', bezeichnung, ')') END FROM public.tbl_lehrverband WHERE studiengang_kz=v.studiengang_kz AND semester=v.semester AND verband=v.verband ORDER BY gruppe LIMIT 1)) AS name", false);
		$this->StudiengangModel->addSelect("CASE WHEN MAX(gruppe)='' OR MAX(gruppe)=' ' THEN TRUE ELSE FALSE END AS leaf");

		$this->StudiengangModel->addSelect($this->StudiengangModel->escape($semester) . ' AS semester');
		$this->StudiengangModel->addSelect('verband');
		$this->StudiengangModel->addSelect($this->StudiengangModel->escape($studiengang_kz) . '::integer AS stg_kz', false);

		$this->StudiengangModel->addOrder('verband');

		$this->StudiengangModel->addGroupBy('link, name, verband');

		$where = [
			'v.studiengang_kz' => $studiengang_kz,
			'v.semester' => $semester,
			'v.verband !=' => '',
			'v.aktiv' => true
		];

		if ($org_form !== null && $semester) // NOTE(chris): on semester 0 show all?
			$where['v.orgform_kurzbz'] = $org_form;

		$result = $this->StudiengangModel->loadWhere($where);
		$result = $this->getDataOrTerminateWithError($result);

		$list = array_merge($list, $result);

		$this->terminateWithSuccess($list);
	}

	protected function getVerband($studiengang_kz, $semester, $verband, $org_form = null)
	{
		$link = $studiengang_kz . '/';
		if ($org_form !== null)
			$link .= $org_form . '/';
		$link .= $semester . '/'. $verband . '/';


		$this->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');

		$this->StudiengangModel->addDistinct();
		$this->StudiengangModel->addSelect("CONCAT(" . $this->StudiengangModel->escape($link) . ", gruppe) AS link", false);
		$this->StudiengangModel->addSelect("CONCAT(UPPER(CONCAT(typ, kurzbz)), '-', semester, verband, gruppe, (SELECT CASE WHEN bezeichnung IS NULL OR bezeichnung='' THEN ''::TEXT ELSE CONCAT(' (', bezeichnung, ')') END FROM public.tbl_lehrverband WHERE studiengang_kz=v.studiengang_kz AND semester=v.semester AND verband=v.verband AND gruppe=v.gruppe ORDER BY gruppe LIMIT 1)) AS name", false);
		$this->StudiengangModel->addSelect("TRUE AS leaf", false);

		$this->StudiengangModel->addSelect('v.semester');
		$this->StudiengangModel->addSelect('v.verband');
		$this->StudiengangModel->addSelect('gruppe');
		$this->StudiengangModel->addSelect($this->StudiengangModel->escape($studiengang_kz) . '::integer AS stg_kz', false);

		$this->StudiengangModel->addOrder('gruppe');

		$where = [
			'v.studiengang_kz' => $studiengang_kz,
			'v.semester' => $semester,
			'v.verband' => $verband,
			'v.gruppe !=' => '',
			'v.aktiv' => true
		];

		if ($org_form !== null && $semester)
			$where['v.orgform_kurzbz'] = $org_form;

		$result = $this->StudiengangModel->loadWhere($where);

		$list = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($list);
	}
}
