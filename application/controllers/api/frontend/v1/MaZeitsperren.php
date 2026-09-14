<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class MaZeitsperren extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getAllActiveZeitsperren' => self::PERM_LOGGED,
			'getAllZeitsperrenFixeMa' => self::PERM_LOGGED,
			'getAllZeitsperrenLector' => self::PERM_LOGGED,
			'getAllZeitsperrenOes' => self::PERM_LOGGED,
			'getZeitsperrenAss' => self::PERM_LOGGED,
			'getStgLectors' => self::PERM_LOGGED,
			'loadZeitsperrenLectorStg' => self::PERM_LOGGED,
			'loadZeitsperrenMa' => self::PERM_LOGGED,
			'getDetailsMa' => self::PERM_LOGGED,
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');

		// Load language phrases
		$this->loadPhrases([
			'ui',
			'person',
			'zeitsperren'
		]);

		// Load models
		$this->load->model('ressource/Zeitsperre_model', 'ZeitsperreModel');
	}

	//only user with zeitsperren
	public function getAllActiveZeitsperren($days)
	{
		$result = $this->ZeitsperreModel->getZeitsperrenForNextDays($days);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	//TODO: check query: more results after join with hr-table
	//all fixe Ma
	public function getAllZeitsperrenFixeMa($days)
	{
		$von = date('Y-m-d');
		$bis = date('Y-m-d', strtotime("+{$days} days"));
		$result = $this->ZeitsperreModel->getMitarbeiterWithZeitsperren($von, $bis, true, false, null, false);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getAllZeitsperrenLector($days)
	{
		$von = date('Y-m-d');
		$bis = date('Y-m-d', strtotime("+{$days} days"));
		$result = $this->ZeitsperreModel->getMitarbeiterWithZeitsperren($von, $bis, false, true, null, false);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getZeitsperrenAss($days)
	{
		$von = date('Y-m-d');
		$bis = date('Y-m-d', strtotime("+{$days} days"));
		$result = $this->ZeitsperreModel->getMitarbeiterWithZeitsperren($von, $bis, false, false, null, true);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getAllZeitsperrenOes($days, $oe)
	{
		if(!$oe)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'OE Kurzbezeichnung']), self::ERROR_TYPE_GENERAL);
		}
		$von = date('Y-m-d');
		$bis = date('Y-m-d', strtotime("+{$days} days"));
		$result = $this->ZeitsperreModel->getMitarbeiterWithZeitsperren($von, $bis, false, false, $oe, false);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function loadZeitsperrenLectorStg($days, $stg)
	{
		if(!$stg)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Studiengangskennzahl']), self::ERROR_TYPE_GENERAL);
		}
		if(!is_numeric($stg))
		{
			return $this->terminateWithError($this->p->t('ui', 'error_paramNoNumber', ['param'=> 'Studiengangskennzahl']), self::ERROR_TYPE_GENERAL);
		}

		$von = date('Y-m-d');
		$bis = date('Y-m-d', strtotime("+{$days} days"));
		$result = $this->ZeitsperreModel->getMitarbeiterWithZeitsperren($von, $bis, false, true, false, false, $stg);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getStgLectors()
	{
		$sql = "
			select
				studiengang_kz,
				kurzbz,
				kurzbzlang,
				typ,
				bezeichnung,
				kurzbzlang || ' (' || bezeichnung || ')' AS label,
				aktiv
			from
				public.tbl_studiengang
			where 
				typ in ('b','m')
			order by typ asc, kurzbz Asc
			";

		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$result = $this->StudiengangModel->execReadOnlyQuery($sql);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function loadZeitsperrenMa($days, $uid)
	{
		if ($uid === null || $uid === '')
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Mitarbeiter UID']), self::ERROR_TYPE_GENERAL);
		}
		$result = $this->ZeitsperreModel->getZeitsperrenForNextDays($days, $uid);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getDetailsMa($uid)
	{
		if ($uid === null || $uid === '')
		{
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Mitarbeiter UID']), self::ERROR_TYPE_GENERAL);
		}
		$this->load->model('person/Person_model', 'PersonModel');
		$result = $this->PersonModel->getFullName($uid);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

}

