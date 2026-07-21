<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');


class KalenderSync extends FHCAPI_Controller
{
	private $_ci;

	const ALLOWED_STATUS = ['preview', 'live'];
	public function __construct()
	{
		parent::__construct([
			'getSyncs' => ['admin:r', 'assistenz:r'],
			'loadSync' => ['admin:r', 'assistenz:r'],
			'add' => ['admin:r', 'assistenz:r'],
			'start' => ['admin:r', 'assistenz:r'],
			'delete' => ['admin:r', 'assistenz:r'],
			'updateSync' => ['admin:r', 'assistenz:r'],
			'getStudienplan' => ['admin:r', 'assistenz:r'],
			'getSyncStatus' => ['admin:r', 'assistenz:r'],
		]);

		$this->loadPhrases([
			'lehre',
		]);

		$this->_ci = &get_instance();
		$this->_ci->load->library('form_validation');
		$this->_ci->load->model('organisation/Studienplan_model', 'StudienplanModel');
		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->_ci->load->model('ressource/Kalendersyncstatus_model', 'KalendersyncstatusModel');
		$this->_ci->load->model('ressource/Kalenderstatus_model', 'KalenderStatusModel');
		$this->_ci->load->library('KalenderSyncLib');
	}

	public function getSyncs()
	{
		$this->_ci->form_validation->set_data($_GET);
		$this->_ci->form_validation->set_rules('studiensemester_kurzbz',"studiensemester_kurzbz","required");

		if($this->_ci->form_validation->run() === FALSE)
			$this->terminateWithValidationErrors($this->_ci->form_validation->error_array());
		$studiensemester_kurzbz = $this->_ci->input->get('studiensemester_kurzbz', TRUE);

		$language = getUserLanguage() == 'German' ? 0 : 1;

		$this->_ci->KalenderStatusModel->addSelect('tbl_kalender_syncstatus.*, array_to_json(bezeichnung_mehrsprachig::varchar[])->>' . $language .' AS sync_status_kurzbz, tbl_organisationseinheit.bezeichnung as oebezeichnung, tbl_studienplan.bezeichnung as studienplanbezeichnung');
		$this->_ci->KalenderStatusModel->addJoin('lehre.tbl_kalender_status', 'tbl_kalender_syncstatus.sync_status_kurzbz = tbl_kalender_status.status_kurzbz');
		$this->_ci->KalenderStatusModel->addJoin('public.tbl_organisationseinheit', 'tbl_kalender_syncstatus.oe_kurzbz = tbl_organisationseinheit.oe_kurzbz');
		$this->_ci->KalenderStatusModel->addJoin('lehre.tbl_studienplan', 'tbl_kalender_syncstatus.studienplan_id = tbl_studienplan.studienplan_id', 'LEFT');

		$data = $this->_ci->KalendersyncstatusModel->loadWhere(array('studiensemester_kurzbz' => $studiensemester_kurzbz));

		if (isError($data))
			$this->terminateWithError(getError($data));

		$this->terminateWithSuccess(getData($data));
	}

	public function delete()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('kalender_syncstatus_id', 'kalender_syncstatus_id', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$kalender_syncstatus_id = $this->input->post('kalender_syncstatus_id');

		$result = $this->_ci->KalendersyncstatusModel->load($kalender_syncstatus_id);

		if (isError($result))
			$this->terminateWithError(getError($result));

		if (!hasData($result))
			$this->terminateWithError($this->p->t('lehre', 'errorDeleting'));

		$del_result = $this->_ci->KalendersyncstatusModel->delete(getData($result)[0]->kalender_syncstatus_id);

		if (isError($del_result))
			$this->terminateWithError(getError($del_result));
		$this->terminateWithSuccess($del_result);
	}

	public function loadSync()
	{
		$this->_ci->form_validation->set_data($_GET);
		$this->_ci->form_validation->set_rules('kalender_syncstatus_id',"kalender_syncstatus_id","required");

		if($this->_ci->form_validation->run() === FALSE)
			$this->terminateWithValidationErrors($this->_ci->form_validation->error_array());
		$kalender_syncstatus_id = $this->_ci->input->get('kalender_syncstatus_id', TRUE);
		$data = $this->_ci->KalendersyncstatusModel->loadWhere(array('kalender_syncstatus_id' => $kalender_syncstatus_id));
		if (isError($data))
			$this->terminateWithError(getError($data));

		$this->terminateWithSuccess(getData($data)[0]);
	}

	public function getSyncStatus()
	{
		$language = getUserLanguage() == 'German' ? 0 : 1;

		$this->_ci->KalenderStatusModel->addSelect('*, array_to_json(bezeichnung_mehrsprachig::varchar[])->>' . $language .' AS bezeichnung, status_kurzbz');
		$this->_ci->KalenderStatusModel->db->where_in('status_kurzbz', self::ALLOWED_STATUS);
		$data = $this->_ci->KalenderStatusModel->load();

		if (isError($data))
			$this->terminateWithError(getError($data));

		$this->terminateWithSuccess(getData($data));
	}


	public function add()
	{
		$formData = $this->input->post('formData');

		$this->_ci->form_validation->set_data($formData);

		$this->_ci->form_validation->set_rules('oe_kurzbz',"oe_kurzbz","required");
		$this->_ci->form_validation->set_rules('studiensemester_kurzbz',"studiensemester_kurzbz","required");
		$this->_ci->form_validation->set_rules('sync_status_kurzbz',"sync_status_kurzbz","required");

		if($this->_ci->form_validation->run() === FALSE)
			$this->terminateWithValidationErrors($this->_ci->form_validation->error_array());

		$ausbildungssemester = isEmptyString($formData['ausbildungssemester']) ? null : $formData['ausbildungssemester'];
		$studienplan_id = isEmptyString($formData['studienplan_id']) ? null : $formData['studienplan_id'];

		$existing = $this->_checkIfExist(
			$formData['oe_kurzbz'],
			$formData['studiensemester_kurzbz'],
			$ausbildungssemester,
			$studienplan_id
		);

		if (isError($existing))
			$this->terminateWithError(getError($existing));

		if (hasData($existing))
		{
			$this->terminateWithError('Es existiert bereits ein Eintrag.');
		}

		$result = $this->_ci->KalendersyncstatusModel->insert(
			array(
				'oe_kurzbz' => $formData['oe_kurzbz'],
				'studiensemester_kurzbz' => $formData['studiensemester_kurzbz'],
				'datum_bis' => $formData['datum_bis'],
				'studienplan_id' => $studienplan_id,
				'ausbildungssemester' => $ausbildungssemester,
				'sync_status_kurzbz' => $formData['sync_status_kurzbz'],
				'mail' => $formData['mail'],
				'insertvon' => getAuthUID()
			)
		);

		$this->terminateWithSuccess($result);
	}


	public function start()
	{
		$formData = $this->input->post('formData');

		$this->_ci->form_validation->set_data($formData);

		$this->_ci->form_validation->set_rules('oe_kurzbz',"oe_kurzbz","required");
		$this->_ci->form_validation->set_rules('studiensemester_kurzbz',"studiensemester_kurzbz","required");
		$this->_ci->form_validation->set_rules('sync_status_kurzbz',"sync_status_kurzbz","required");

		if($this->_ci->form_validation->run() === FALSE)
			$this->terminateWithValidationErrors($this->_ci->form_validation->error_array());

		$result = $this->_ci->kalendersynclib->runManual($formData['oe_kurzbz'], $formData['studiensemester_kurzbz'], $formData['ausbildungssemester'], $formData['studienplan_id'], $formData['mail'], $formData['datum_bis'], $formData['sync_status_kurzbz']);

		if (isError($result))
			$this->terminateWithError(getError($result));

		$this->terminateWithSuccess($result);
	}
	public function updateSync()
	{
		$formData = $this->input->post('formData');

		$this->_ci->form_validation->set_data($formData);

		$this->_ci->form_validation->set_rules('kalender_syncstatus_id',"kalender_syncstatus_id","required");
		$this->_ci->form_validation->set_rules('oe_kurzbz',"oe_kurzbz","required");
		$this->_ci->form_validation->set_rules('studiensemester_kurzbz',"studiensemester_kurzbz","required");
		$this->_ci->form_validation->set_rules('sync_status_kurzbz',"sync_status_kurzbz","required");

		if($this->_ci->form_validation->run() === FALSE)
			$this->terminateWithValidationErrors($this->_ci->form_validation->error_array());

		$ausbildungssemester = isEmptyString($formData['ausbildungssemester']) ? null : $formData['ausbildungssemester'];
		$studienplan_id = isEmptyString($formData['studienplan_id']) ? null : $formData['studienplan_id'];

		$existing = $this->_checkIfExist(
			$formData['oe_kurzbz'],
			$formData['studiensemester_kurzbz'],
			$ausbildungssemester,
			$studienplan_id,
			$formData['kalender_syncstatus_id']
		);

		if (isError($existing))
			$this->terminateWithError(getError($existing));

		if (hasData($existing))
		{
			$this->terminateWithError('Es existiert bereits ein Eintrag.');
		}

		$this->_ci->KalendersyncstatusModel->update(
			array('kalender_syncstatus_id' => $formData['kalender_syncstatus_id']),
			array(
				'oe_kurzbz' => $formData['oe_kurzbz'],
				'studiensemester_kurzbz' => $formData['studiensemester_kurzbz'],
				'datum_bis' => $formData['datum_bis'],
				'studienplan_id' => $studienplan_id,
				'ausbildungssemester' => $ausbildungssemester,
				'sync_status_kurzbz' => $formData['sync_status_kurzbz'],
				'mail' => $formData['mail'],
				'updatevon' => getAuthUID(),
				'updateamum' => date('c'),
			)
		);
	}
	public function getStudienplan()
	{
		$this->_ci->form_validation->set_data($_GET);
		$this->_ci->form_validation->set_rules('oe_kurzbz',"oe_kurzbz","required");

		if($this->_ci->form_validation->run() === FALSE)
			$this->terminateWithValidationErrors($this->_ci->form_validation->error_array());

		$oe_kurzbz = $this->_ci->input->get('oe_kurzbz', TRUE);
		$studiensemester_kurzbz = $this->_ci->input->get('studiensemester_kurzbz', TRUE);
		$ausbildungssemester = $this->_ci->input->get('ausbildungssemester', TRUE);

		$this->_ci->StudiengangModel->addJoin('tbl_organisationseinheit', 'oe_kurzbz');
		$is_studiengang = $this->_ci->StudiengangModel->loadWhere(array('oe_kurzbz' => $oe_kurzbz));

		if (hasData($is_studiengang) && !isEmptyString($studiensemester_kurzbz))
		{
			$studiengang = getData($is_studiengang)[0];

			$studienplaene = $this->_ci->StudienplanModel->getStudienplaeneBySemester(
				$studiengang->studiengang_kz,
				$studiensemester_kurzbz,
				isEmptyString($ausbildungssemester) ? null : $ausbildungssemester
			);


			$data = $this->getDataOrTerminateWithError($studienplaene);

			$unique = array();
			foreach ($data as $row)
			{
				$unique[$row->studienplan_id] = (object) array(
					'studienplan_id' => $row->studienplan_id,
					'studienordnung_id' => $row->studienordnung_id,
					'bezeichnung' => $row->bezeichnung,
					'orgform_kurzbz' => $row->orgform_kurzbz
				);
			}

			$this->terminateWithSuccess(array_values($unique));
		}
		$this->terminateWithSuccess();
	}

	private function _checkIfExist($oe_kurzbz, $studiensemester_kurzbz, $ausbildungssemester, $studienplan_id, $kalender_syncstatus_id = null)
	{
		$this->_ci->KalendersyncstatusModel->db->where('oe_kurzbz', $oe_kurzbz);
		$this->_ci->KalendersyncstatusModel->db->where('studiensemester_kurzbz', $studiensemester_kurzbz);

		if (is_null($ausbildungssemester))
			$this->_ci->KalendersyncstatusModel->db->where('ausbildungssemester IS NULL', null, false);
		else
			$this->_ci->KalendersyncstatusModel->db->where('ausbildungssemester', $ausbildungssemester);

		if (is_null($studienplan_id))
			$this->_ci->KalendersyncstatusModel->db->where('studienplan_id IS NULL', null, false);
		else
			$this->_ci->KalendersyncstatusModel->db->where('studienplan_id', $studienplan_id);

		if (!is_null($kalender_syncstatus_id))
			$this->_ci->KalendersyncstatusModel->db->where('kalender_syncstatus_id !=', $kalender_syncstatus_id);
		return $this->_ci->KalendersyncstatusModel->load();
	}

}
