<?php


if (!defined('BASEPATH')) exit('No direct script access allowed');
use \DateTime as DateTime;

class Vertraege extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getAllVertraege' => ['admin:r', 'assistenz:r'],
			'getAllContractsNotAssigned' => ['admin:r', 'assistenz:r'],
			'getAllContractsAssigned' => ['admin:r', 'assistenz:r'],
			'getAllContractTypes' =>  self::PERM_LOGGED,
			'getAllContractStati' =>  self::PERM_LOGGED,
			'getStatiOfContract' =>  self::PERM_LOGGED,
			'loadContract' => ['admin:r', 'assistenz:r'],
			'updateContract' => ['admin:r', 'assistenz:r'],
			'addNewContract' => ['admin:r', 'assistenz:r'],
			'deleteContract' => ['admin:r', 'assistenz:r'],
			'insertContractStatus' => ['admin:r', 'assistenz:r'],
			'deleteContractStatus' => ['admin:r', 'assistenz:r'],
		]);

		//Load Models
		$this->load->model('accounting/Vertrag_model', 'VertragModel');
		$this->load->model('accounting/Vertragsstatus_model', 'VertragsstatusModel');
		$this->load->model('accounting/Vertragstyp_model', 'VertragstypModel');
		$this->load->model('accounting/Vertragvertragsstatus_model', 'VertragvertragsstatusModel');
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
		//TODO(MANUU)
		$result = $this->VertragsstatusModel->load();
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess(getData($result) ?: []);
	}

	public function addNewContract($person_id)
	{
		//$this->validateNewOrUpdate();
		//TODO(Manu) check validations

		//$person_id = $this->input->post('person_id');
		$vertragsdatum = $this->input->post('vertragsdatum');
		$bezeichnung = $this->input->post('bezeichnung');
		$vertragstyp_kurzbz = $this->input->post('vertragstyp_kurzbz');
		$betrag = $this->input->post('betrag');
		$vertragsstunden = $this->input->post('vertragsstunden');
		$vertragsstunden_studiensemester_kurzbz = $this->input->post('vertragsstunden_studiensemester_kurzbz');
		$anmerkung = $this->input->post('anmerkung');

		$this->db->trans_start();

		$result = $this->VertragModel->insert( [
			'person_id' => $person_id,
			'vertragsdatum' => $vertragsdatum,
			'bezeichnung' => $bezeichnung,
			'vertragstyp_kurzbz' => $vertragstyp_kurzbz,
			'betrag' => $betrag,
			'vertragsstunden' => $vertragsstunden,
			'vertragsstunden_studiensemester_kurzbz' => $vertragsstunden_studiensemester_kurzbz,
			'anmerkung' => $anmerkung,
			'insertamum' => date('c'),
			'insertvon' => getAuthUID()
		]);

		//add entry with status neu in lehre.tbl_vertrag_vertragsstatus
		//TODO(Manu) validation, dass status nicht schon bereits vorhanden
		$status_result = $this->VertragvertragsstatusModel->insert([
			'vertrag_id' => $result->retval,
			'uid' => getAuthUID(),
			'vertragsstatus_kurzbz' => 'neu',
			'insertamum' => date('c'),
			'insertvon' => getAuthUID(),
			'datum' => date('c')
		]);

		if (!$status_result) {
			$this->db->trans_rollback();
			$this->terminateWithError('Fehler beim Hinzufügen des Vertragsstatus.');
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

	public function updateContract($vertrag_id)
	{
		//$this->validateNewOrUpdate();
		//TODO(Manu)validations


		$vertrag_id = $this->input->post('vertrag_id');
		$person_id = $this->input->post('person_id');
		$vertragsdatum = $this->input->post('vertragsdatum');
		$bezeichnung = $this->input->post('bezeichnung');
		$vertragstyp_kurzbz = $this->input->post('vertragstyp_kurzbz');
		$betrag = $this->input->post('betrag');
		$vertragsstunden = $this->input->post('vertragsstunden');
		$vertragsstunden_studiensemester_kurzbz = $this->input->post('vertragsstunden_studiensemester_kurzbz');
		$anmerkung = $this->input->post('anmerkung');

		$this->db->trans_start();

		$result = $this->VertragModel->update($vertrag_id, [
			'person_id' => $person_id,
			'vertragsdatum' => $vertragsdatum,
			'bezeichnung' => $bezeichnung,
			'vertragstyp_kurzbz' => $vertragstyp_kurzbz,
			'betrag' => $betrag,
			'vertragsstunden' => $vertragsstunden,
			'vertragsstunden_studiensemester_kurzbz' => $vertragsstunden_studiensemester_kurzbz,
			'anmerkung' => $anmerkung,
			'vertragsstunden' => $vertragsstunden,
			'updateamum' => date('c'),
			'updatevon' => getAuthUID()
		]);

		$this->getDataOrTerminateWithError($result);

		$this->db->trans_complete();

		$this->terminateWithSuccess(true);
	}

	public function deleteContract($vertrag_id)
	{
		//TODO(Manu) validations,
		$result = $this->VertragvertragsstatusModel->load([
			'vertrag_id' => $vertrag_id
		]);

		if(hasData($result)){
			$data = getData($result);
			foreach ($data as $item) {
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
		return $this->outputJsonSuccess(current(getData($result)));
	}

	public function insertContractStatus ($vertrag_id, $datum, $status){
		$result = $this->VertragvertragsstatusModel->loadWhere(
            array(
                'vertrag_id' => $vertrag_id,
                'vertragsstatus_kurzbz' => $status
            )
        );

        if (hasData($result)) {
			$this->terminateWithError("status bereits vorhanden", self::ERROR_TYPE_GENERAL);
           // $this->terminateWithError($this->p->t('ui', 'error_status_existing', ['id' => 'Vertrag_id']), self::ERROR_TYPE_GENERAL);
        }

		$status_result = $this->VertragvertragsstatusModel->insert([
			'vertrag_id' => $vertrag_id,
			'uid' => getAuthUID(),
			'vertragsstatus_kurzbz' => $status,
			'insertamum' => date('c'),
			'insertvon' => getAuthUID(),
			'datum' => $datum
		]);

		if (!$status_result) {
			$this->terminateWithError('Fehler beim Hinzufügen des Vertragsstatus.');
		}

		return $this->outputJsonSuccess(current(getData($status_result)));
	}

	public function deleteContractStatus($vertrag_id, $status){
		//return $this->terminateWithError($vertrag_id . " - " . $status, self::ERROR_TYPE_GENERAL);
		$result = $this->VertragvertragsstatusModel->delete(
			array(
				'vertrag_id' => $vertrag_id,
				'vertragsstatus_kurzbz' => $status
			)
		);

		if (isError($result)) {
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result)) {
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'vertragsstatus_kurzb']), self::ERROR_TYPE_GENERAL);
		}
		return $this->outputJsonSuccess(current(getData($result)));
	}
}