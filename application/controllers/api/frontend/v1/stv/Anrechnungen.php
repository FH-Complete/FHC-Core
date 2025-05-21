<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Anrechnungen extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getAnrechnungen' => ['admin:r', 'assistenz:r'],
			'deleteAnrechnung' => ['admin:rw', 'assistenz:rw'],
			'getLehrveranstaltungen' => ['admin:r', 'assistenz:r'],
			'getBegruendungen' => ['admin:r', 'assistenz:r'],
			'getLektoren' => ['admin:r', 'assistenz:r'],
			'getLvsKompatibel' => ['admin:r', 'assistenz:r'],
			'insertAnrechnung' => ['admin:rw', 'assistenz:rw'],
			'loadAnrechnung' => ['admin:rw', 'assistenz:rw'],
			'updateAnrechnung' => ['admin:rw', 'assistenz:rw'],
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		// Load language phrases
		$this->loadPhrases([
			'ui', 'lehre'
		]);

		// Load models
		$this->load->model('education/Anrechnung_model', 'AnrechnungsModel');
	}

	public function getAnrechnungen($prestudent_id)
	{
		$result = $this->AnrechnungsModel->getAnrechnungsData($prestudent_id);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getBegruendungen()
	{
		$this->load->model('education/Anrechnungbegruendung_model', 'AnrechnungbegrueundungsModel');

		$result = $this->AnrechnungbegrueundungsModel->load();
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getLehrveranstaltungen($prestudent_id)
	{
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
		$result = $this->PrestudentstatusModel->getLastStatus($prestudent_id);

		$data = $this->getDataOrTerminateWithError($result);
		$studienplan_id = current($data)->studienplan_id;

		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$result = $this->LehrveranstaltungModel->getLvsByStudienplanId($studienplan_id);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getLvsKompatibel($lehrveranstaltung_id)
	{
		$this->AnrechnungsModel->addJoin('lehre.tbl_lehrveranstaltung lv', 'ON (lv.lehrveranstaltung_id = lehre.tbl_anrechnung.lehrveranstaltung_id)');
		$result = $this->AnrechnungsModel->loadWhere(
			['lehrveranstaltung_id_kompatibel' => $lehrveranstaltung_id]
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getLektoren($studiengang_kz)
	{
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');

		$result = $this->MitarbeiterModel->getLektoren($studiengang_kz);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function insertAnrechnung()
	{
		$this->load->library('form_validation');

		$prestudent_id = $this->input->post('prestudent_id');

		if(!$prestudent_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Student UID']), self::ERROR_TYPE_GENERAL);
		}

		$formData = $this->input->post('formData');
		$_POST['lehrveranstaltung_id'] =
			(isset($formData['lehrveranstaltung_id']) && !empty($formData['lehrveranstaltung_id']))
			? $formData['lehrveranstaltung_id']
			: null;
		$_POST['lehrveranstaltung_id_kompatibel'] =
			(isset($formData['lehrveranstaltung_id_kompatibel']) && !empty($formData['lehrveranstaltung_id_kompatibel']))
				? $formData['lehrveranstaltung_id_kompatibel']
				: null;
		$_POST['begruendung'] =
			(isset($formData['begruendung_id']) && !empty($formData['begruendung_id']))
				? $formData['begruendung_id']
				: null;
		$_POST['genehmigtVon'] = (isset($formData['genehmigt_von']) && !empty($formData['genehmigt_von']))
			? $formData['genehmigt_von']
			: null;

		$this->form_validation->set_rules('lehrveranstaltung_id', 'Lehrveranstaltung_id', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Lehrveranstaltung'])
		]);

		$this->form_validation->set_rules('begruendung', 'Begruendung', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Begruendung'])
		]);

		if($_POST['begruendung'] == 2)
		{
			$this->form_validation->set_rules('lehrveranstaltung_id_kompatibel', 'Lehrveranstaltung_id', 'required', [
				'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Lehrveranstaltung Kompatibel'])
			]);
		}

		$this->form_validation->set_rules('genehmigtVon', 'GenehmigtVon', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'GenehmigtVon'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->AnrechnungsModel->insert(
			[
				'prestudent_id' => $prestudent_id,
				'lehrveranstaltung_id' => $_POST['lehrveranstaltung_id'],
				'lehrveranstaltung_id_kompatibel' => $_POST['lehrveranstaltung_id_kompatibel'],
				'begruendung_id' => $_POST['begruendung'],
				'genehmigt_von' => $_POST['genehmigtVon']
			]
		);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function loadAnrechnung($anrechnung_id)
	{
		$result = $this->AnrechnungsModel->loadWhere(
			array('anrechnung_id' => $anrechnung_id)
		);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}

	public function updateAnrechnung()
	{
		$this->load->library('form_validation');

		$anrechnung_id = $this->input->post('anrechnung_id');

		if(!$anrechnung_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Anrechnung UID']), self::ERROR_TYPE_GENERAL);
		}

		$formData = $this->input->post('formData');
		$_POST['lehrveranstaltung_id'] =
			(isset($formData['lehrveranstaltung_id']) && !empty($formData['lehrveranstaltung_id']))
				? $formData['lehrveranstaltung_id']
				: null;
		$_POST['lehrveranstaltung_id_kompatibel'] =
			(isset($formData['lehrveranstaltung_id_kompatibel']) && !empty($formData['lehrveranstaltung_id_kompatibel']))
				? $formData['lehrveranstaltung_id_kompatibel']
				: null;
		$_POST['begruendung'] = (isset($formData['begruendung_id']) && !empty($formData['begruendung_id'])) ? $formData['begruendung_id'] : null;
		$_POST['genehmigtVon'] = (isset($formData['genehmigt_von']) && !empty($formData['genehmigt_von'])) ? $formData['genehmigt_von'] : null;

		$this->form_validation->set_rules('lehrveranstaltung_id', 'Lehrveranstaltung_id', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Lehrveranstaltung'])
		]);

		$this->form_validation->set_rules('begruendung', 'Begruendung', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Begruendung'])
		]);

		if($_POST['begruendung'] == 2)
		{
			$this->form_validation->set_rules('lehrveranstaltung_id_kompatibel', 'Lehrveranstaltung_id', 'required', [
				'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Lehrveranstaltung Kompatibel'])
			]);
		}

		$this->form_validation->set_rules('genehmigtVon', 'GenehmigtVon', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'GenehmigtVon'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->AnrechnungsModel->update(
			[
				'anrechnung_id' => $anrechnung_id,
			],
			[

				'lehrveranstaltung_id' => $_POST['lehrveranstaltung_id'],
				'lehrveranstaltung_id_kompatibel' => $_POST['lehrveranstaltung_id_kompatibel'],
				'begruendung_id' => $_POST['begruendung'],
				'genehmigt_von' => $_POST['genehmigtVon']
			]
		);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function deleteAnrechnung($anrechnung_id)
	{
		$result = $this->AnrechnungsModel->delete(
			array('anrechnung_id' => $anrechnung_id)
		);

		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}
}
