<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Gruppen extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'add' => ['admin:rw', 'assistenz:rw'],
			'search' => ['admin:r', 'assistenz:r'],
			'getGruppen' => ['admin:r', 'assistenz:r'],
			'deleteGruppe' => ['admin:rw', 'assistenz:rw'],
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		// Load language phrases
		$this->loadPhrases([
			'ui',
			'gruppenmanagement',
			'lehre'
		]);

		// Load models
		$this->load->model('person/Benutzergruppe_model', 'BenutzergruppeModel');
		$this->load->model('organisation/Gruppe_model', 'GruppeModel');
	}

	public function add()
	{
		$this->load->library("form_validation");

		$this->form_validation->set_rules(
			'gruppe_kurzbz',
			$this->p->t('gruppenmanagement', 'gruppe'),
			'required|is_in_db[organisation/Gruppe_model]',
			[
				'required' => $this->p->t('ui', 'error_fieldRequired'),
				'is_in_db' => $this->p->t('ui', 'error_fieldNotFound')
			]
		);
		$this->form_validation->set_rules(
			'uid',
			$this->p->t('ui', 'student_uid'),
			'required|is_in_db[crm/Student_model:student_uid]',
			[
				'required' => $this->p->t('ui', 'error_fieldRequired'),
				'is_in_db' => $this->p->t('ui', 'error_fieldNotFound')
			]
		);
		$this->form_validation->set_rules(
			'studiensemester_kurzbz',
			$this->p->t('lehre', 'studiensemester'),
			'required|is_in_db[organisation/Studiensemester_model]',
			[
				'required' => $this->p->t('ui', 'error_fieldRequired'),
				'is_in_db' => $this->p->t('ui', 'error_fieldNotFound')
			]
		);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$gruppe_kurzbz = $this->input->post('gruppe_kurzbz');
		$uid = $this->input->post('uid');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');

		$result = $this->BenutzergruppeModel->load([
			$gruppe_kurzbz,
			$uid
		]);
		$benutzergruppe = $this->getDataOrTerminateWithError($result);

		if ($benutzergruppe) {
			$this->terminateWithError(
				$this->p->t('gruppenmanagement', 'error_alreadyInGroup', [
					'uid' => $uid,
					'studiensemester_kurzbz' => current($benutzergruppe)->studiensemester_kurzbz
				]),
				self::ERROR_TYPE_GENERAL
			);
		}

		$result = $this->BenutzergruppeModel->insert([
			'uid' => $uid,
			'gruppe_kurzbz' => $gruppe_kurzbz,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'insertamum' => date('c'),
			'insertvon' => getAuthUID()
		]);

		$this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess();
	}

	public function search()
	{
		$query = $this->input->post('query');
		if (!$query)
			$this->terminateWithSuccess([]);

		// add query to where clause
		$query = strtoupper($query);
		$query = $this->GruppeModel->db->escape_like_str($query);
		$query = '%' . str_replace(' ', '%', $query) . '%';

		$this->GruppeModel->db->group_start();
		$this->GruppeModel->db->or_like('UPPER(gruppe_kurzbz)', $query, 'none', false);
		$this->GruppeModel->db->or_like('UPPER(bezeichnung)', $query, 'none', false);
		$this->GruppeModel->db->or_like('UPPER(beschreibung)', $query, 'none', false);
		$this->GruppeModel->db->group_end();
		
		// add stg sorting 1
		$studiengang_kz = $this->input->post('studiengang_kz');
		$sort_stg = $studiengang_kz ? "WHEN studiengang_kz = " . $this->GruppeModel->escape($studiengang_kz) . " THEN 0" : "";

		// add stg sorting 2
		$studiengang_kzs = [];
		$result = $this->permissionlib->getSTG_isEntitledFor('admin');
		if ($result)
			$studiengang_kzs = array_merge($studiengang_kzs, $result);
		$result = $this->permissionlib->getSTG_isEntitledFor('assistenz');
		if ($result)
			$studiengang_kzs = array_merge($studiengang_kzs, $result);

		// selects
		$this->GruppeModel->addSelect("*");
		$this->GruppeModel->addSelect("CASE
			" . $sort_stg . "
			WHEN studiengang_kz IN (" . implode(",", $this->GruppeModel->db->escape($studiengang_kzs)) . ")
			THEN 1
			ELSE 2
		END AS sort_stg");

		// ordering
		$this->GruppeModel->addOrder("sort_stg");
		$this->GruppeModel->addOrder("sort");
		$this->GruppeModel->addOrder("gruppe_kurzbz");

		// default where clause & execute
		$result = $this->GruppeModel->loadWhere([
			'lehre' => true,
			'sichtbar' => true,
			'aktiv' => true,
			'direktinskription' => false
		]);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getGruppen($student_uid)
	{
		$this->BenutzergruppeModel->addSelect('gruppe_kurzbz');
		$this->BenutzergruppeModel->addSelect('bezeichnung');
		$this->BenutzergruppeModel->addSelect('generiert');
		$this->BenutzergruppeModel->addSelect('uid');
		$this->BenutzergruppeModel->addSelect('studiensemester_kurzbz');
		$this->BenutzergruppeModel->addJoin('public.tbl_gruppe', 'gruppe_kurzbz');
		$this->BenutzergruppeModel->addOrder('bezeichnung', 'ASC');

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
		$this->load->library("form_validation");

		$this->form_validation->set_rules(
			'uid',
			$this->p->t('person', 'UID'),
			'required',
			[
				'required' => $this->p->t('ui', 'error_fieldRequired')
			]
		);

		$this->form_validation->set_rules(
			'gruppe_kurzbz',
			$this->p->t('gruppenmanagement', 'gruppe'),
			'required',
			[
				'required' => $this->p->t('ui', 'error_fieldRequired')
			]
		);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$uid = $this->input->post('uid');
		$gruppe_kurzbz = $this->input->post('gruppe_kurzbz');

		// Validate if automatic group generation
		$result = $this->GruppeModel->loadWhere([
			'gruppe_kurzbz' => $gruppe_kurzbz
		]);
		$data = $this->getDataOrTerminateWithError($result);
		$generation = current($data);

		if ($generation->generiert)
		{
			$this->terminateWithError($this->p->t('gruppenmanagement', 'error_deleteGeneratedGroups'), self::ERROR_TYPE_GENERAL);
		}

		$result = $this->BenutzergruppeModel->delete([
			'gruppe_kurzbz' => $gruppe_kurzbz,
			'uid' => $uid
		]);

		$data = $this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess($data);
	}
}
