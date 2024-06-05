<?php


if (!defined('BASEPATH')) exit('No direct script access allowed');
use \DateTime as DateTime;

class BetriebsmittelP extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getAllBetriebsmittel' => ['admin:r', 'assistenz:r'],
			'addNewBetriebsmittel' => ['admin:r', 'assistenz:r'],
			'updateBetriebsmittel' => ['admin:r', 'assistenz:r'],
			'loadBetriebsmittel' => ['admin:r', 'assistenz:r'],
			'deleteBetriebsmittel' => ['admin:r', 'assistenz:r'],
			'getTypenBetriebsmittel' => ['admin:r', 'assistenz:r'],
			'loadInventarliste' => ['admin:r', 'assistenz:r']
		]);

		//Load Models
		$this->load->model('ressource/Betriebsmittel_model', 'BetriebsmittelModel');
		$this->load->model('ressource/Betriebsmittelperson_model', 'BetriebsmittelpersonModel');

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');

		// Load language phrases
		$this->loadPhrases([
			'ui',
			'wawi'
		]);
	}

	public function getAllBetriebsmittel($type_id, $id)
	{
		$result = $this->BetriebsmittelpersonModel->getBetriebsmittelData($id, $type_id);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function addNewBetriebsmittel($person_id)
	{
		$this->form_validation->set_rules('kaution', 'Kaution', 'numeric', [
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Kaution'])
		]);

		$this->form_validation->set_rules('betriebsmitteltyp', 'TYP', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Typ'])
		]);

		$this->form_validation->set_rules('ausgegebenam', 'Ausgegeben am', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Ausgegeben am'])
		]);

		if ($this->form_validation->run() == false) {
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$uid_user = getAuthUID();
		$betriebsmitteltyp = $this->input->post('betriebsmitteltyp');
		$nummer = $this->input->post('nummer');
		$nummer2 = $this->input->post('nummer2');
		$inventarData = $this->input->post('inventarData');
		$beschreibung = $this->input->post('beschreibung');
		$kaution = $this->input->post('kaution');
		$anmerkung = $this->input->post('anmerkung');
		$ausgegebenam = $this->input->post('ausgegebenam');
		$retouram = $this->input->post('retouram');
		$uid = $this->input->post('uid');

		if ($inventarData) {
			$betriebsmitteltyp = $inventarData['betriebsmitteltyp'];
			$betriebsmittel_id = $inventarData['betriebsmittel_id'];
		}

		if ($betriebsmitteltyp == 'Zutrittskarte' && !$nummer) {
			return $this->terminateWithError($this->p->t('wawi', 'error_zutrittskarteOhneNummer'), self::ERROR_TYPE_GENERAL);
		}

		if ($retouram && $retouram < $ausgegebenam) {
			return $this->terminateWithError($this->p->t('wawi', 'error_retourdatumVorAusgabe'), self::ERROR_TYPE_GENERAL);
		}

		if ($betriebsmitteltyp == "Inventar" && !($inventarData['inventarnummer'])) {
			return $this->terminateWithError($this->p->t('wawi', 'error_inventarWaehlen'), self::ERROR_TYPE_GENERAL);
		}

		// Start DB transaction
		$this->db->trans_begin();

		$betriebsmitteltyp = utf8_decode($betriebsmitteltyp);

		if (!$inventarData) {
			$result = $this->BetriebsmittelModel->insert(
				[
					'betriebsmitteltyp' => $betriebsmitteltyp,
					'nummer' => $nummer,
					'nummer2' => $nummer2,
					'beschreibung' => $beschreibung,
					'anmerkung' => $anmerkung,
					'insertvon' => $uid_user,
					'insertamum' => date('c')
				]
			);
			if ($this->db->trans_status() === false || isError($result)) {
				$this->db->trans_rollback();
				return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
			}
			$betriebsmittel_id = $result->retval;
		}

		$result = $this->BetriebsmittelpersonModel->insert(
			[
				'betriebsmittel_id' => $betriebsmittel_id,
				'person_id' => $person_id,
				'kaution' => $kaution,
				'anmerkung' => $anmerkung,
				'ausgegebenam' => $ausgegebenam,
				'retouram ' => $retouram,
				'insertvon' => $uid_user,
				'insertamum' => date('c'),
				'uid' => $uid
			]
		);

		if ($this->db->trans_status() === false || isError($result)) {
			$this->db->trans_rollback();
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		$this->db->trans_commit();
		return $this->terminateWithSuccess(true);
	}

	public function updateBetriebsmittel($betriebsmittelperson_id)
	{
		$uid_user = getAuthUID();
		$betriebsmittel_id = $this->input->post('betriebsmittel_id');
		$betriebsmitteltyp = $this->input->post('betriebsmitteltyp');
		$nummer = $this->input->post('nummer');
		$nummer2 = $this->input->post('nummer2');
		$beschreibung = $this->input->post('beschreibung');
		$kaution = $this->input->post('kaution');
		$anmerkung = $this->input->post('anmerkung');
		$ausgegebenam = $this->input->post('ausgegebenam');
		$retouram = $this->input->post('retouram');
		$person_id = $this->input->post('person_id');
		$uid = $this->input->post('uid');

		$this->form_validation->set_rules('kaution', 'Kaution', 'numeric', [
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Kaution'])
		]);

		$this->form_validation->set_rules('betriebsmitteltyp', 'TYP', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Typ'])
		]);

		$this->form_validation->set_rules('ausgegebenam', 'Ausgegeben am', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Ausgegeben am'])
		]);


		if ($this->form_validation->run() == false) {
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		if ($betriebsmitteltyp == 'Zutrittskarte' && !$nummer) {
			return $this->terminateWithError($this->p->t('wawi', 'error_zutrittskarteOhneNummer'), self::ERROR_TYPE_GENERAL);
		}

		if ($retouram && $retouram < $ausgegebenam) {
			return $this->terminateWithError($this->p->t('wawi', 'error_retourdatumVorAusgabe'), self::ERROR_TYPE_GENERAL);
		}


		// Start DB transaction
		$this->db->trans_begin();

		$result = $this->BetriebsmittelpersonModel->update(
			[
				'betriebsmittelperson_id' => $betriebsmittelperson_id,

			],
			[
				'person_id' => $person_id,
				'uid' => $uid,
				'kaution' => $kaution,
				'anmerkung' => $anmerkung,
				'ausgegebenam' => $ausgegebenam,
				'retouram ' => $retouram,
				'updatevon' => $uid_user,
				'updateamum' => date('c')
			]
		);

		if ($this->db->trans_status() === false || isError($result)) {
			$this->db->trans_rollback();
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		$result = $this->BetriebsmittelModel->update(
			[
				'betriebsmittel_id' => $betriebsmittel_id
			],
			[
				'betriebsmitteltyp' => $betriebsmitteltyp,
				'nummer' => $nummer,
				'nummer2' => $nummer2,
				'beschreibung' => $beschreibung,
				'anmerkung' => $anmerkung,
				'updatevon' => $uid_user,
				'updateamum' => date('c')
			]
		);
		if ($this->db->trans_status() === false || isError($result)) {
			$this->db->trans_rollback();
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		$this->db->trans_commit();
		return $this->terminateWithSuccess(true);
	}

	public function loadBetriebsmittel($betriebsmittelperson_id)
	{
		$result = $this->BetriebsmittelpersonModel->getBetriebsmittelData($betriebsmittelperson_id, 'betriebsmittelperson_id');

		if (isError($result)) {
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		if (!hasData($result)) {
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'Betriebsmittelperson_id']), self::ERROR_TYPE_GENERAL);
		}

		$this->terminateWithSuccess(current(getData($result)));
	}

	public function deleteBetriebsmittel($betriebsmittelperson_id)
	{
		$result = $this->BetriebsmittelpersonModel->delete(
			array('betriebsmittelperson_id' => $betriebsmittelperson_id,
			)
		);

		if (isError($result)) {
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result)) {
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'Betriebsmittelperson_id']), self::ERROR_TYPE_GENERAL);
		}
		return $this->outputJsonSuccess(current(getData($result)));
	}

	public function getTypenBetriebsmittel()
	{
		$this->load->model('ressource/Betriebsmitteltyp_model', 'BetriebsmitteltypModel');

		$this->BetriebsmitteltypModel->addOrder('beschreibung', 'ASC');
		$result = $this->BetriebsmitteltypModel->load(); // load All

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

	public function loadInventarliste($searchString)
	{
		$result = $this->BetriebsmittelModel->loadInventarliste($searchString);
		if (isError($result)) {
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess($result ?: []);
	}
}


