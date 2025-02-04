<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Gruppen extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getGruppen' => ['admin:r', 'assistenz:r'],
			'deleteGruppe' => ['admin:rw', 'assistenz:rw'],
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		// Load language phrases
		$this->loadPhrases([
			'ui', 'gruppenmanagement'
		]);

		// Load models
		$this->load->model('person/Benutzergruppe_model', 'BenutzergruppeModel');
		$this->load->model('organisation/Gruppe_model', 'GruppeModel');
	}

	public function getGruppen($student_uid)
	{
		$this->BenutzergruppeModel ->addSelect('gruppe_kurzbz');
		$this->BenutzergruppeModel ->addSelect('bezeichnung');
		$this->BenutzergruppeModel ->addSelect('generiert');
		$this->BenutzergruppeModel ->addSelect('uid');
		$this->BenutzergruppeModel ->addSelect('studiensemester_kurzbz');
		$this->BenutzergruppeModel ->addSelect('public.tbl_benutzergruppe.insertvon');
		$this->BenutzergruppeModel ->addJoin('public.tbl_gruppe', 'gruppe_kurzbz');
		$this->BenutzergruppeModel-> addOrder('bezeichnung', 'ASC');

		$result = $this->BenutzergruppeModel->loadWhere(
			array(
				'uid' => $student_uid
			)
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function deleteGruppe()
	{
		$student_uid = $this->input->post('id');
		$gruppe_kurzbz = $this->input->post('gruppe_kurzbz');

		//Validate if automatic group generation
		$result = $this->GruppeModel-> loadWhere(
			array(
				'gruppe_kurzbz' => $gruppe_kurzbz
			)
		);
		$data = $this->getDataOrTerminateWithError($result);
		$generation = current($data);

		if($generation->generiert)
		{
			$this->terminateWithError($this->p->t('gruppenmanagement', 'error_deleteGeneratedGroups'), self::ERROR_TYPE_GENERAL);
		}

		$result = $this->BenutzergruppeModel->delete(
			array(
				'gruppe_kurzbz' => $gruppe_kurzbz,
				'uid' => $student_uid
				)
		);

		$data = $this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess($data);
	}
}
