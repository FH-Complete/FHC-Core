<?php


if (!defined('BASEPATH')) exit('No direct script access allowed');
use \DateTime as DateTime;

class BetriebsmittelP extends FHCAPI_Controller
{
	private $person_id = null;

	public function __construct()
	{
		parent::__construct([
			'getAllBetriebsmittel' => ['admin:r', 'assistenz:r'],
			'addNewBetriebsmittel' => self::PERM_LOGGED,
			'updateBetriebsmittel' => self::PERM_LOGGED,
			'loadBetriebsmittel' => ['admin:r', 'assistenz:r'],
			'deleteBetriebsmittel' => self::PERM_LOGGED,
			'getTypenBetriebsmittel' => ['admin:r', 'assistenz:r'],
			'loadInventarliste' => ['admin:r', 'assistenz:r']
		]);

		//Load Models
		$this->load->model('ressource/Betriebsmittel_model', 'BetriebsmittelModel');
		$this->load->model('ressource/Betriebsmittelperson_model', 'BetriebsmittelpersonModel');

		// Additional Permission Checks
		if ($this->router->method == 'addNewBetriebsmittel') {
			$this->person_id = current(array_slice($this->uri->rsegments, 2));
			
			$this->checkPermissionsForPerson(
				$this->person_id,
				['admin:rw', 'mitarbeiter:rw', 'basis/betriebsmittel:rw'],
				['admin:rw', 'assistenz:rw', 'basis/betriebsmittel:rw']
			);
		} elseif ($this->router->method == 'updateBetriebsmittel' || $this->router->method == 'deleteBetriebsmittel') {
			$betriebsmittelperson_id = current(array_slice($this->uri->rsegments, 2));
			$result = $this->BetriebsmittelpersonModel->load($betriebsmittelperson_id);
			if (!hasData($result))
				show_404();
			$this->person_id = current(getData($result))->person_id;
			
			$this->checkPermissionsForPerson(
				$this->person_id,
				['admin:rw', 'mitarbeiter:rw', 'basis/betriebsmittel:rw'],
				['admin:rw', 'assistenz:rw', 'basis/betriebsmittel:rw']
			);
		}

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

	protected function validateNewOrUpdate()
	{
		$this->form_validation->set_rules('betriebsmitteltyp', 'Typ', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired')
		]);

		$this->form_validation->set_rules('kaution', 'Kaution', 'numeric|less_than_equal_to[9999.99]', [
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric')
		]);

		$this->form_validation->set_rules('ausgegebenam', 'Ausgegeben am', 'required|is_valid_date', [
			'required' => $this->p->t('ui', 'error_fieldRequired')
		]);

		if ($this->input->post('ausgegebenam') && $this->input->post('retouram')) {
			$this->form_validation->set_rules('retouram', 'Retour am', [
				'is_valid_date',
				['is_not_before_ausgegebenam', function ($value) {
					return (new DateTime($value) >= new DateTime($this->input->post('ausgegebenam')));
				}]
			], [
				'is_not_before_ausgegebenam' => $this->p->t('wawi', 'error_retourdatumVorAusgabe')
			]);
		} else {
			$this->form_validation->set_rules('retouram', 'Retour am', 'is_valid_date');
		}

		$this->form_validation->set_rules('anmerkung', 'Anmerkung', 'max_length[256]');

		if ($this->input->post('betriebsmitteltyp') == 'Inventar') {
			// Inventar
			$this->form_validation->set_rules('betriebsmittel_id', 'Inventarnummer', 'required');
		} elseif ($this->input->post('betriebsmitteltyp') == 'Zutrittskarte') {
			// Zutrittskarte
			if ($this->input->post('nummer') === null && $this->input->post('nummer') === null) {
				$this->form_validation->set_rules('nummer', 'Nummer', 'required', [
					'required' => $this->p->t('wawi', 'error_zutrittskarteOhneNummer')
				]);
				$this->form_validation->set_rules('nummer2', 'Nummer2', 'required', [
					'required' => $this->p->t('wawi', 'error_zutrittskarteOhneNummer')
				]);
			} else {
				if ($this->input->post('nummer') === null) {
					$result = $this->BetriebsmittelpersonModel->loadViewWhere([
						'betriebsmitteltyp' => $this->input->post('betriebsmitteltyp'),
						'nummer2' => $this->input->post('nummer2'),
						'person_id !=' => $this->person_id,
						'retouram IS NULL' => null
					]);
					if (hasData($result))
						$this->form_validation->set_rules('nummer2', 'Nummer2', 'is_array', [
							'is_array' => $this->p->t('wawi', 'error_bmZutrittskarteOccupied', (array)current(getData($result)))
						]);
				} else {
					$result = $this->BetriebsmittelpersonModel->loadViewWhere([
						'betriebsmitteltyp' => $this->input->post('betriebsmitteltyp'),
						'nummer' => $this->input->post('nummer'),
						'person_id !=' => $this->person_id,
						'retouram IS NULL' => null
					]);
					if (hasData($result))
						$this->form_validation->set_rules('nummer', 'Nummer', 'is_array', [
							'is_array' => $this->p->t('wawi', 'error_bmZutrittskarteOccupied', (array)current(getData($result)))
						]);
				}
			}
		}

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());
	}

	public function addNewBetriebsmittel($person_id)
	{
		$this->form_validation->set_rules('uid', 'UID', [
			['uid_in_person', function ($value) use ($person_id) {
				if ($value === null)
					return true;
				$this->load->model('person/Benutzer_model', 'BenutzerModel');
				$result = $this->BenutzerModel->loadWhere([
					'uid' => $value,
					'person_id' => $person_id
				]);

				return hasData($result);
			}]
		], [
			'uid_in_person' => $this->p->t('person', 'error_uidNotInPerson')
		]);
		$this->validateNewOrUpdate();

		$betriebsmitteltyp = $this->input->post('betriebsmitteltyp');
		$nummer = $this->input->post('nummer');
		$nummer2 = $this->input->post('nummer2');
		$beschreibung = $this->input->post('beschreibung');
		$betriebsmittel_id = $this->input->post('betriebsmittel_id');
		$anmerkung = $this->input->post('anmerkung');
		$kaution = $this->input->post('kaution');
		$ausgegebenam = $this->input->post('ausgegebenam');
		$retouram = $this->input->post('retouram');
		$uid = $this->input->post('uid');

		// NOTE(chris): transform_kartennummer
		if ($betriebsmitteltyp == 'Zutrittskarte' && $nummer)
			$nummer = is_numeric($nummer) ? ltrim($nummer, "0") : hexdec(implode("", array_reverse(str_split(trim($nummer)))));

		$this->db->trans_start();

		if ($betriebsmitteltyp != 'Inventar') {
			$this->BetriebsmittelModel->addOrder('updateamum', 'DESC');
			if ($betriebsmitteltyp == 'Zutrittskarte' && $nummer === null) {
				$result = $this->BetriebsmittelModel->loadWhere([
					'betriebsmitteltyp' => $betriebsmitteltyp,
					'nummer2' => $nummer2
				]);
			} else {
				$result = $this->BetriebsmittelModel->loadWhere([
					'betriebsmitteltyp' => $betriebsmitteltyp,
					'nummer' => $nummer
				]);
			}
			$data = $this->getDataOrTerminateWithError($result);

			if ($data) {
				$data = current($data);
				if ($data->nummer !== $nummer || $data->nummer2 !== $nummer2 || $data->beschreibung !== $beschreibung) {
					$result = $this->BetriebsmittelModel->update($data->betriebsmittel_id, [
						'nummer' => $nummer,
						'nummer2' => $nummer2,
						'beschreibung' => $beschreibung,
						'updateamum' => date('c'),
						'updatevon' => getAuthUID()
					]);
					$this->getDataOrTerminateWithError($result);
				}
				$betriebsmittel_id = $data->betriebsmittel_id;
			} else {
				$result = $this->BetriebsmittelModel->insert([
					'betriebsmitteltyp' => $betriebsmitteltyp,
					'nummer' => $nummer,
					'nummer2' => $nummer2,
					'beschreibung' => $beschreibung,
					'reservieren' => false,
					'ort_kurzbz' => null,
					'insertamum' => date('c'),
					'insertvon' => getAuthUID(),
				]);
				$betriebsmittel_id = $this->getDataOrTerminateWithError($result);
			}
		}

		$result = $this->BetriebsmittelpersonModel->insert([
			'person_id' => $person_id,
			'betriebsmittel_id' => $betriebsmittel_id,
			'anmerkung' => $anmerkung,
			'kaution' => $kaution,
			'ausgegebenam' => $ausgegebenam,
			'retouram' => $retouram,
			'uid' => $uid,
			'insertamum' => date('c'),
			'insertvon' => getAuthUID()
		]);

		$data = $this->getDataOrTerminateWithError($result);

		$this->db->trans_complete();

		$this->terminateWithSuccess(true);
	}

	public function updateBetriebsmittel($betriebsmittelperson_id)
	{
		$this->validateNewOrUpdate();

		$betriebsmitteltyp = $this->input->post('betriebsmitteltyp');
		$nummer = $this->input->post('nummer');
		$nummer2 = $this->input->post('nummer2');
		$beschreibung = $this->input->post('beschreibung');
		$betriebsmittel_id = $this->input->post('betriebsmittel_id');
		$anmerkung = $this->input->post('anmerkung');
		$kaution = $this->input->post('kaution');
		$ausgegebenam = $this->input->post('ausgegebenam');
		$retouram = $this->input->post('retouram');

		// NOTE(chris): transform_kartennummer
		if ($betriebsmitteltyp == 'Zutrittskarte' && $nummer)
			$nummer = is_numeric($nummer) ? ltrim($nummer, "0") : hexdec(implode("", array_reverse(str_split(trim($nummer)))));

		$this->db->trans_start();

		if ($betriebsmitteltyp != 'Inventar') {
			$found = false;
			if ($nummer !== null && $betriebsmittel_id !== null) {
				$result = $this->BetriebsmittelModel->load($betriebsmittel_id);
				$data = $this->getDataOrTerminateWithError($result);
				if ($data && current($data)->nummer == $nummer) {
					$found = true;
				}
			}

			if (!$found) {
				$this->BetriebsmittelModel->addOrder('updateamum', 'DESC');
				if ($betriebsmitteltyp == 'Zutrittskarte' && $nummer === null) {
					$result = $this->BetriebsmittelModel->loadWhere([
						'betriebsmitteltyp' => $betriebsmitteltyp,
						'nummer2' => $nummer2
					]);
				} else {
					$result = $this->BetriebsmittelModel->loadWhere([
						'betriebsmitteltyp' => $betriebsmitteltyp,
						'nummer' => $nummer
					]);
				}
				$data = $this->getDataOrTerminateWithError($result);
			}

			if ($data) {
				$data = current($data);
				if ($data->nummer !== $nummer || $data->nummer2 !== $nummer2 || $data->beschreibung !== $beschreibung) {
					$result = $this->BetriebsmittelModel->update($data->betriebsmittel_id, [
						'nummer' => $nummer,
						'nummer2' => $nummer2,
						'beschreibung' => $beschreibung,
						'updateamum' => date('c'),
						'updatevon' => getAuthUID()
					]);
					$this->getDataOrTerminateWithError($result);
				}
				$betriebsmittel_id = $data->betriebsmittel_id;
			} else {
				$result = $this->BetriebsmittelModel->insert([
					'betriebsmitteltyp' => $betriebsmitteltyp,
					'nummer' => $nummer,
					'nummer2' => $nummer2,
					'beschreibung' => $beschreibung,
					'reservieren' => false,
					'ort_kurzbz' => null,
					'insertamum' => date('c'),
					'insertvon' => getAuthUID(),
				]);
				$betriebsmittel_id = $this->getDataOrTerminateWithError($result);
			}
		}

		$result = $this->BetriebsmittelpersonModel->update($betriebsmittelperson_id, [
			'betriebsmittel_id' => $betriebsmittel_id,
			'anmerkung' => $anmerkung,
			'kaution' => $kaution,
			'ausgegebenam' => $ausgegebenam,
			'retouram' => $retouram,
			'updateamum' => date('c'),
			'updatevon' => getAuthUID()
		]);

		$data = $this->getDataOrTerminateWithError($result);

		$this->db->trans_complete();

		$this->terminateWithSuccess(true);
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

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}
}


