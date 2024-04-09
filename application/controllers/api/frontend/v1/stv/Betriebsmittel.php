<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Betriebsmittel extends FHCAPI_Controller
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
			'ui'
		]);
	}

	public function getAllBetriebsmittel($uid, $person_id)
	{
		//uid
		//$result = $this->BetriebsmittelpersonModel->getBetriebsmittelData($uid, 'uid');

		//person_id
		$result = $this->BetriebsmittelpersonModel->getBetriebsmittelData($person_id, 'person');

		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function addNewBetriebsmittel()
	{
		//TODO(Manu) Berechtigungen
/*		if(!$this->permissionlib->isBerechtigt('admin', 'suid', $stg) && !$this->permissionlib->isBerechtigt('assistenz', 'suid', $stg))
		{
			$result =  $this->p->t('lehre','error_keineSchreibrechte');

			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}*/


		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$this->form_validation->set_rules('kaution', 'Kaution', 'numeric', [
			'numeric' => $this->p->t('ui','error_fieldNotNumeric',['field' => 'Kaution'])
		]);

		$this->form_validation->set_rules('betriebsmitteltyp', 'TYP', 'required', [
			'required' => $this->p->t('ui','error_fieldRequired',['field' => 'Typ'])
		]);

		$this->form_validation->set_rules('ausgegebenam', 'Ausgegeben am', 'required', [
			'required' => $this->p->t('ui','error_fieldRequired',['field' => 'Ausgegeben am'])
		]);


		if ($this->form_validation->run() == false)
		{
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
		$person_id = $this->input->post('person_id');
		$uid = $this->input->post('uid');

		if($inventarData)
		{
			$betriebsmitteltyp = $inventarData['betriebsmitteltyp'];
			$betriebsmittel_id = $inventarData['betriebsmittel_id'];
		}

		if($betriebsmitteltyp == 'Zutrittskarte' && !$nummer)
		{
			return $this->terminateWithError("Eine Zutrittskarte muss eine Nummer haben. Um die Zuordnung zu dieser Karte zu loeschen entfernen Sie bitte den ganzen Datensatz", self::ERROR_TYPE_GENERAL);
		}

		if($retouram && $retouram < $ausgegebenam)
			return $this->terminateWithError("Retourdatum darf nicht vor Datum der Ausgabe liegen", self::ERROR_TYPE_GENERAL);

		if($betriebsmitteltyp == "Inventar" && !($inventarData['inventarnummer']))
			return $this->terminateWithError("Bitte wählen Sie das entsprechende Inventar aus dem Drop Down Menü aus", self::ERROR_TYPE_GENERAL);

		// Start DB transaction
		$this->db->trans_begin();

		$betriebsmitteltyp = utf8_decode($betriebsmitteltyp);

		if(!$inventarData)
		{
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
			if($this->db->trans_status() === false || isError($result))
			{
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

		if($this->db->trans_status() === false || isError($result))
		{
			$this->db->trans_rollback();
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		$this->db->trans_commit();
		return $this->terminateWithSuccess(true);

	}

	public function updateBetriebsmittel()
	{
		//TODO(Manu) Berechtigungen
		/*		if(!$this->permissionlib->isBerechtigt('admin', 'suid', $stg) && !$this->permissionlib->isBerechtigt('assistenz', 'suid', $stg))
				{
					$result =  $this->p->t('lehre','error_keineSchreibrechte');

					return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
				}*/


		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$uid_user = getAuthUID();

		$betriebsmittel_id = $this->input->post('betriebsmittel_id');
		$betriebsmittelperson_id = $this->input->post('betriebsmittelperson_id');
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
			'numeric' => $this->p->t('ui','error_fieldNotNumeric',['field' => 'Kaution'])
		]);

		$this->form_validation->set_rules('betriebsmitteltyp', 'TYP', 'required', [
			'required' => $this->p->t('ui','error_fieldRequired',['field' => 'Typ'])
		]);

		$this->form_validation->set_rules('ausgegebenam', 'Ausgegeben am', 'required', [
			'required' => $this->p->t('ui','error_fieldRequired',['field' => 'Ausgegeben am'])
		]);


		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		if($betriebsmitteltyp == 'Zutrittskarte' && !$nummer)
		{
			return $this->terminateWithError("Eine Zutrittskarte muss eine Nummer haben. Um die Zuordnung zu dieser Karte zu loeschen entfernen Sie bitte den ganzen Datensatz", self::ERROR_TYPE_GENERAL);
		}

		if($retouram && $retouram < $ausgegebenam)
			return $this->terminateWithError("Retourdatum darf nicht vor Datum der Ausgabe liegen", self::ERROR_TYPE_GENERAL);


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

		if($this->db->trans_status() === false || isError($result))
			{
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
		if($this->db->trans_status() === false || isError($result))
		{
			$this->db->trans_rollback();
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		$this->db->trans_commit();
		return $this->terminateWithSuccess(true);

	}

	public function loadBetriebsmittel()
	{
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$betriebsmittelperson_id = $this->input->post('betriebsmittelperson_id');

		$result = $this->BetriebsmittelpersonModel->getBetriebsmittelData($betriebsmittelperson_id, 'betriebsmittelperson_id');

		if (isError($result))
		{
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		if (!hasData($result))
		{
			$this->terminateWithError("no Betriebsmittelperson with ID found: " . $betriebsmittelperson_id, self::ERROR_TYPE_GENERAL);
		}

		$this->terminateWithSuccess(current(getData($result)));

	}

	public function deleteBetriebsmittel()
	{

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$betriebsmittelperson_id = $this->input->post('betriebsmittelperson_id');

		$result = $this->BetriebsmittelpersonModel->delete(
			array('betriebsmittelperson_id' => $betriebsmittelperson_id,
				)
		);

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result))
		{
			return $this->terminateWithError($this->p->t('ui','error_missingId',['id'=> 'Betriebsmittelperson_id']), self::ERROR_TYPE_GENERAL);
		}
		return $this->outputJsonSuccess(current(getData($result)));

	}

	public function getTypenBetriebsmittel()
	{
		$this->load->model('ressource/Betriebsmitteltyp_model', 'BetriebsmitteltypModel');

		$this->BetriebsmitteltypModel->addOrder('beschreibung', 'ASC');
		$result = $this->BetriebsmitteltypModel->load(); // load All

		if (isError($result))
		{
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


