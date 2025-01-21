<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Vertraege extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getAllVertraege' => ['vertrag/mitarbeiter:r'],
			'getAllContractsNotAssigned' => ['vertrag/mitarbeiter:r'],
			'getAllContractsAssigned' => ['vertrag/mitarbeiter:r'],
			'getAllContractTypes' =>  ['vertrag/mitarbeiter:r'],
			'getAllContractStati' =>  ['vertrag/mitarbeiter:r'],
			'getStatiOfContract' =>  ['vertrag/mitarbeiter:r'],
			'loadContract' => ['vertrag/mitarbeiter:r'],
			'loadContractStatus' => ['vertrag/mitarbeiter:r'],
			'updateContract' =>['vertrag/mitarbeiter:w'],
			'addNewContract' =>['vertrag/mitarbeiter:w'],
			'deleteContract' =>['vertrag/mitarbeiter:w'],
			'insertContractStatus' =>['vertrag/mitarbeiter:w'],
			'deleteContractStatus' =>['vertrag/mitarbeiter:w'],
			'updateContractStatus' =>['vertrag/mitarbeiter:w'],
			'deleteLehrauftrag' =>['vertrag/mitarbeiter:w'],
			'deleteBetreuung' =>['vertrag/mitarbeiter:w'],
			'getMitarbeiter' => ['vertrag/mitarbeiter:r'],
			'getHeader' => ['vertrag/mitarbeiter:r'],
			'getPersonAbteilung' => ['vertrag/mitarbeiter:r'],
			'getLeitungOrg' => ['vertrag/mitarbeiter:r'],
			'getMitarbeiter_uid' => ['vertrag/mitarbeiter:r'],
			]);

		//Load Models and Libraries
		$this->load->model('accounting/Vertrag_model', 'VertragModel');
		$this->load->model('accounting/Vertragsstatus_model', 'VertragsstatusModel');
		$this->load->model('accounting/Vertragstyp_model', 'VertragstypModel');
		$this->load->model('accounting/Vertragvertragsstatus_model', 'VertragvertragsstatusModel');

		// Load language phrases
		$this->loadPhrases([
			'ui',
			'vertrag'
		]);
	}

	public function getAllVertraege($person_id)
	{
		$result = $this->VertragModel->loadContractsOfPerson($person_id);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function getAllContractsNotAssigned($person_id)
	{
		$result = $this->VertragModel->loadContractsOfPersonNotAssigned($person_id);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function getAllContractsAssigned($person_id, $vertrag_id)
	{
		$result = $this->VertragModel->loadContractsOfPersonAssigned($person_id, $vertrag_id);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function getStatiOfContract($vertrag_id)
	{
		$result = $this->VertragModel->getStatiOfContract($vertrag_id);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function getAllContractTypes()
	{
		$result = $this->VertragstypModel->load();
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getAllContractStati()
	{
		$result = $this->VertragsstatusModel->load();
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess(getData($result) ?: []);
	}

	public function addNewContract()
	{
		$this->load->library('form_validation');

		$person_id = $this->input->post('person_id');

		if(!$person_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Person_id']), self::ERROR_TYPE_GENERAL);
		}

		$formData = $this->input->post('formData');

		//Manually rewrite to postparameter for form validation
		$_POST['vertragstyp_kurzbz'] = $formData['vertragstyp_kurzbz'];
		$_POST['vertragsdatum']= $formData['vertragsdatum'];
		$_POST['bezeichnung']= $formData['bezeichnung'];
		$_POST['betrag'] = $formData['betrag'];
		$_POST['vertragsstunden'] =
			isset($formData['vertragsstunden'])
			? $formData['vertragsstunden']
			: null;
		$_POST['vertragsstunden_studiensemester_kurzbz'] =
			isset($formData['vertragsstunden_studiensemester_kurzbz'])
				? $formData['vertragsstunden_studiensemester_kurzbz']
				: null;
		$_POST['anmerkung'] = isset($formData['anmerkung']) ? $formData['anmerkung'] : null;

		$this->form_validation->set_rules('bezeichnung', 'Bezeichnung', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Bezeichnung'])
		]);

		$this->form_validation->set_rules('vertragstyp_kurzbz', 'Vertragstyp', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Vertragstyp'])
		]);
		$this->form_validation->set_rules('vertragsdatum', 'Vertragsdatum', 'required|is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'Vertragsdatum']),
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Vertragsdatum'])
		]);
		$this->form_validation->set_rules('betrag', 'Betrag', 'required|numeric', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Betrag']),
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Betrag'])
		]);
		$this->form_validation->set_rules('vertragsstunden', 'Stunden(Vertrags-Urfassung)', 'numeric', [
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Stunden(Vertrags-Urfassung)'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$lehrauftraege = $this->input->post('clickedRows');

		$this->db->trans_start();

		$result = $this->VertragModel->insert([
			'person_id' => $person_id,
			'vertragsdatum' => $this->input->post('vertragsdatum'),
			'bezeichnung' => $this->input->post('bezeichnung'),
			'vertragstyp_kurzbz' => $this->input->post('vertragstyp_kurzbz'),
			'betrag' => $this->input->post('betrag'),
			'vertragsstunden' => $this->input->post('vertragsstunden'),
			'vertragsstunden_studiensemester_kurzbz' => $this->input->post('vertragsstunden_studiensemester_kurzbz'),
			'anmerkung' => $this->input->post('anmerkung'),
			'insertamum' => date('c'),
			'insertvon' => getAuthUID()
		]);

		$this->getDataOrTerminateWithError($result);
		$vertrag_id = $result->retval;

		$status_result = $this->VertragvertragsstatusModel->insert([
			'vertrag_id' => $vertrag_id,
			'uid' => getAuthUID(),
			'vertragsstatus_kurzbz' => 'neu',
			'insertamum' => date('c'),
			'insertvon' => getAuthUID(),
			'datum' => date('c')
		]);

		if (!$status_result) {
			$this->db->trans_rollback();
			$this->terminateWithError($this->p->t('vertrag', 'error_insertOrUpdateStatusVertrag'), self::ERROR_TYPE_GENERAL);
		}

		//Hinzufügen der Lehraufträge
		foreach ($lehrauftraege as $row)
		{
			if ($row['type'] == 'Lehrauftrag')
			{
				$this->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');

				$result_lehrauftrag = $this->LehreinheitmitarbeiterModel->update(
					[
						'lehreinheit_id' => $row['lehreinheit_id'],
						'mitarbeiter_uid' => $row['mitarbeiter_uid']
					],
					[
						'vertrag_id' => $vertrag_id
					]
				);

				if (!$result_lehrauftrag) {
					$this->db->trans_rollback();
					$this->terminateWithError($this->p->t('vertrag', 'error_addOrUpdateLehrauftraege'), self::ERROR_TYPE_GENERAL);
				}
			}

			if ($row['type'] == 'Betreuung')
			{
				$this->load->model('education/Projektbetreuer_model', 'Projektbetreuermodel');

				$result_projektbetreuer = $this->Projektbetreuermodel->update(
					[
						'person_id' => $person_id,
						'projektarbeit_id' => $row['projektarbeit_id'],
						'betreuerart_kurzbz' => $row['betreuerart_kurzbz']
					],
					[
						'vertrag_id' => $vertrag_id
					]
				);

				if (!$result_projektbetreuer)
				{
					$this->db->trans_rollback();
					$this->terminateWithError($this->p->t('vertrag', 'error_addOrUpdateLehrauftraege'), self::ERROR_TYPE_GENERAL);
				}
			}
		}

		$this->db->trans_complete();
		$this->terminateWithSuccess(true);
	}

	public function updateContract()
	{
		$this->load->library('form_validation');

		$vertrag_id = $this->input->post('vertrag_id');
		$person_id = $this->input->post('person_id');
		$formData = $this->input->post('formData');
		$lehrauftraege = $this->input->post('clickedRows');

		//Manually rewrite to postparameter for form validation
		$_POST['vertragstyp_kurzbz'] = $formData['vertragstyp_kurzbz'];
		$_POST['vertragsdatum']= $formData['vertragsdatum'];
		$_POST['bezeichnung']= $formData['bezeichnung'];
		$_POST['betrag'] = $formData['betrag'];
		$_POST['vertragsstunden'] =
			isset($formData['vertragsstunden'])
				? $formData['vertragsstunden']
				: null;
		$_POST['vertragsstunden_studiensemester_kurzbz']=
			isset($formData['vertragsstunden_studiensemester_kurzbz'])
				? $formData['vertragsstunden_studiensemester_kurzbz']
				: null;
		$_POST['anmerkung']= isset($formData['anmerkung']) ? $formData['anmerkung'] : null;

		$this->form_validation->set_rules('bezeichnung', 'Bezeichnung', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Bezeichnung'])
		]);

		$this->form_validation->set_rules('vertragstyp_kurzbz', 'Vertragstyp', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Vertragstyp'])
		]);
		$this->form_validation->set_rules('vertragsdatum', 'Vertragsdatum', 'required|is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'Vertragsdatum']),
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Vertragsdatum'])
		]);
		$this->form_validation->set_rules('betrag', 'Betrag', 'required|numeric', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Betrag']),
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Betrag'])
		]);
		$this->form_validation->set_rules('vertragsstunden', 'Stunden(Vertrags-Urfassung)', 'numeric', [
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Stunden(Vertrags-Urfassung)'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$this->db->trans_start();

		$result = $this->VertragModel->update(
			$vertrag_id,
			[
				'person_id' => $person_id,
				'vertragsdatum' => $this->input->post('vertragsdatum'),
				'bezeichnung' => $this->input->post('bezeichnung'),
				'vertragstyp_kurzbz' => $this->input->post('vertragstyp_kurzbz'),
				'betrag' => $this->input->post('betrag'),
				'vertragsstunden' => $this->input->post('vertragsstunden'),
				'vertragsstunden_studiensemester_kurzbz' => $this->input->post('vertragsstunden_studiensemester_kurzbz'),
				'anmerkung' => $this->input->post('anmerkung'),
				'updateamum' => date('c'),
				'updatevon' => getAuthUID()
			]
		);

		$this->getDataOrTerminateWithError($result);

		//Adding of Lehraufträge
		foreach ($lehrauftraege as $row)
		{
			if ($row['type'] == 'Lehrauftrag')
			{
				$this->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');

				$result_lehrauftrag = $this->LehreinheitmitarbeiterModel->update(
					[
						'lehreinheit_id' => $row['lehreinheit_id'],
						'mitarbeiter_uid' => $row['mitarbeiter_uid']
					],
					[
						'vertrag_id' => $vertrag_id
					]
				);

				if (!$result_lehrauftrag) {
					$this->db->trans_rollback();
					$this->terminateWithError($this->p->t('vertrag', 'error_addOrUpdateLehrauftraege'), self::ERROR_TYPE_GENERAL);
				}
			}

			if ($row['type'] == 'Betreuung')
			{
				$this->load->model('education/Projektbetreuer_model', 'Projektbetreuermodel');

				$result_projektbetreuer = $this->Projektbetreuermodel->update(
					[
						'person_id' => $person_id,
						'projektarbeit_id' => $row['projektarbeit_id'],
						'betreuerart_kurzbz' => $row['betreuerart_kurzbz']
					],
					[
						'vertrag_id' => $vertrag_id
					]
				);

				if (!$result_projektbetreuer)
				{
					$this->db->trans_rollback();
					$this->terminateWithError($this->p->t('vertrag', 'error_addOrUpdateLehrauftraege'), self::ERROR_TYPE_GENERAL);
				}
			}
		}
		$this->db->trans_complete();

		$this->terminateWithSuccess(true);
	}

	public function loadContract($vertrag_id)
	{
		$result = $this->VertragModel->load($vertrag_id);

		if (isError($result)) {
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		if (!hasData($result)) {
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'Vertrag_id']), self::ERROR_TYPE_GENERAL);
		}

		$this->terminateWithSuccess(current(getData($result)));
	}

	public function deleteContract($vertrag_id)
	{
		$this->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');

		//check if attached Lehrauftrag
		$resultLehrauftrag = $this->LehreinheitmitarbeiterModel->load([
			'vertrag_id' => $vertrag_id
		]);

		if(hasData($resultLehrauftrag))
		{
			$resultLehrauftrag = getData($resultLehrauftrag);
			foreach($resultLehrauftrag as $lehrauftrag)
			{
				$result = $this->LehreinheitmitarbeiterModel->update(
					[
						'lehreinheit_id' => $lehrauftrag->lehreinheit_id,
						'mitarbeiter_uid' => $lehrauftrag->mitarbeiter_uid,
						'vertrag_id' => $vertrag_id
					],
					[
						'vertrag_id' => null
					]
				);

				$this->getDataOrTerminateWithError($result);
			}
		}

		//if attached Betreuung
		$this->load->model('education/Projektbetreuer_model', 'Projektbetreuermodel');

		//if attached Betreuung
		$resultBetreuung = $this->Projektbetreuermodel->load([
			'vertrag_id' => $vertrag_id
		]);

		if(hasData($resultBetreuung))
		{
			$resultBetreuung = getData($resultBetreuung);
			foreach($resultBetreuung as $betreuung)
			{
				$result = $this->Projektbetreuermodel->update(
					[
						'person_id' => $betreuung->person_id,
						'projektarbeit_id' => $betreuung->projektarbeit_id,
						'betreuerart_kurzbz' => $betreuung->betreuerart_kurzbz,
						'vertrag_id' => $vertrag_id
					],
					[
						'vertrag_id' => null
					]
				);

				$this->getDataOrTerminateWithError($result);
			}
		}

		$result = $this->VertragvertragsstatusModel->load([
			'vertrag_id' => $vertrag_id
		]);

		if(hasData($result))
		{
			$data = getData($result);
			foreach ($data as $item)
			{
				//delete all entries in lehre.tbl_vertrag_vertragsstatus
				$result = $this->VertragvertragsstatusModel->delete([
					'vertrag_id' => $vertrag_id,
					'vertragsstatus_kurzbz' => $item->vertragsstatus_kurzbz,
					'uid' => $item->uid
				]);
				if(isError($result))
					$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
			}
		}

		//delete Contract
		$result = $this->VertragModel->delete(
			array('vertrag_id' => $vertrag_id,
			)
		);

		if (isError($result)) {
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result)) {
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'Vertrag_id']), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(current(getData($result)));
	}

	public function insertContractStatus($vertrag_id)
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('vertragsstatus_kurzbz', 'vertragsstatus_kurzbz', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'vertragsstatus_kurzbz'])
		]);
		$this->form_validation->set_rules('datum', 'Datum', 'required|is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'Datum']),
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Datum'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->VertragvertragsstatusModel->loadWhere(
            array(
                'vertrag_id' => $vertrag_id,
                'vertragsstatus_kurzbz' => $this->input->post('vertragsstatus_kurzbz')
            )
        );

        if (hasData($result))
		{
			$this->terminateWithError($this->p->t('vertrag', 'error_statusVorhanden'), self::ERROR_TYPE_GENERAL);
        }

		$status_result = $this->VertragvertragsstatusModel->insert([
			'vertrag_id' => $vertrag_id,
			'uid' => getAuthUID(),
			'vertragsstatus_kurzbz' => $this->input->post('vertragsstatus_kurzbz'),
			'insertamum' => date('c'),
			'insertvon' => getAuthUID(),
			'datum' => $this->input->post('datum')
		]);

		if (!$status_result) {
			$this->terminateWithError('Fehler beim Hinzufügen des Vertragsstatus.');
		}

		return $this->terminateWithSuccess(current(getData($status_result)));
	}

	public function deleteContractStatus($vertrag_id)
	{
		$status = $this->input->post('vertragsstatus_kurzbz');

		$result = $this->VertragvertragsstatusModel->delete(
			array(
				'vertrag_id' => $vertrag_id,
				'vertragsstatus_kurzbz' => $status
			)
		);

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result))
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'vertragsstatus_kurzb']), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(current(getData($result)));
	}

	public function loadContractStatus($vertrag_id)
	{
		$status = $this->input->post('vertragsstatus_kurzbz');

		$result = $this->VertragvertragsstatusModel->loadWhere(
			array(
				'vertrag_id' => $vertrag_id,
				'vertragsstatus_kurzbz' => $status
			)
		);
		if (!$result) {
			$this->terminateWithError('Status not existing');
		}
		return $this->terminateWithSuccess(current(getData($result)));
	}

	public function updateContractStatus($vertrag_id)
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('vertragsstatus_kurzbz', 'vertragsstatus_kurzbz', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'vertragsstatus_kurzbz'])
		]);
		$this->form_validation->set_rules('datum', 'Datum', 'required|is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'Datum']),
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Datum'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$status_result = $this->VertragvertragsstatusModel->update(
			[
			'vertrag_id' => $vertrag_id,
			'vertragsstatus_kurzbz' => $this->input->post('vertragsstatus_kurzbz')],
			[
			'uid' => getAuthUID(),
			'updateamum' => date('c'),
			'updatevon' => getAuthUID(),
			'datum' => $this->input->post('datum')
			]
		);

		if (!$status_result) {
			$this->terminateWithError('Fehler beim Updaten des Vertragsstatus.');
		}

		return $this->terminateWithSuccess(current(getData($status_result)));
	}

	public function deleteLehrauftrag($vertrag_id)
	{
		$lehreinheit_id = $this->input->post('lehreinheit_id');
		$mitarbeiter_uid = $this->input->post('mitarbeiter_uid');

		$this->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');

		//kein delete: ein update, bei dem die vertrag_id auf null gesetzt wird
		$result = $this->LehreinheitmitarbeiterModel->update(
			[
				'lehreinheit_id' => $lehreinheit_id,
				'mitarbeiter_uid' => $mitarbeiter_uid,
				'vertrag_id' => $vertrag_id
			],
			[
				'vertrag_id' => null
			]
		);

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result))
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'Id_Lehrauftrag']), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(current(getData($result)));
	}

	public function deleteBetreuung($vertrag_id)
	{
		$person_id= $this->input->post('person_id');
		$projektarbeit_id = $this->input->post('projektarbeit_id');
		$betreuerart_kurzbz = $this->input->post('betreuerart_kurzbz');

		$this->load->model('education/Projektbetreuer_model', 'Projektbetreuermodel');

		$result = $this->Projektbetreuermodel->update(
			[
				'person_id' => $person_id,
				'projektarbeit_id' => $projektarbeit_id,
				'betreuerart_kurzbz' => $betreuerart_kurzbz,
				'vertrag_id' => $vertrag_id
			],
			[
				'vertrag_id' => null
			]
		);

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result))
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'Id_Projektbetreuung']), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(current(getData($result)));
	}

	public function getMitarbeiter()
	{
		$this->load->model('ressource/Mitarbeiter_model', 'Mitarbeitermodel');

		$result = $this->Mitarbeitermodel->getPersonenWithContractDetails();

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result))
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'Id_Lehrauftrag']), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result));
	}

	public function getPersonAbteilung($person_id)
	{
		$this->load->model('ressource/Mitarbeiter_model', 'Mitarbeitermodel');

		$result = $this->Mitarbeitermodel->getPersonAbteilung($person_id);

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result))
		{
			//TODO(Manu) rewrite better
			return $this->terminateWithSuccess("no benutzerdata", self::ERROR_TYPE_GENERAL);

		}
		return $this->terminateWithSuccess(getData($result));
	}

	public function getLeitungOrg($oekurzbz)
	{
		$this->load->model('ressource/Mitarbeiter_model', 'Mitarbeitermodel');

		$result = $this->Mitarbeitermodel->getLeitungOrg($oekurzbz);

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result))
		{
			//TODO(Manu) rewrite better
			return $this->terminateWithSuccess("no benutzerdata", self::ERROR_TYPE_GENERAL);

		//	return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'personID']), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result));
	}

	public function getHeader($person_id)
	{
		$this->load->model('ressource/Mitarbeiter_model', 'Mitarbeitermodel');

		$result = $this->Mitarbeitermodel->getHeader($person_id);

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result))
		{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'personID']), self::ERROR_TYPE_GENERAL);
		}

		return $this->terminateWithSuccess(getData($result));
	}


	public function getMitarbeiter_uid($person_id)
	{
		$this->load->model('ressource/Mitarbeiter_model', 'Mitarbeitermodel');

		$result = $this->Mitarbeitermodel->getMitarbeiter_uid($person_id);

		if (isError($result))
		{
			//TODO(Manu) check ErrorLogic
			return $this->terminateWithSuccess($result);
			//return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result))
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'personID']), self::ERROR_TYPE_GENERAL);
		}

		return $this->terminateWithSuccess($result);
	}


}
