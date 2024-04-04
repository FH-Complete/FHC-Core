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
			'getTypenBetriebsmittel' => ['admin:r', 'assistenz:r']
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
		//$result = $this->BetriebsmittelpersonModel->getBetriebsmittelByUid($uid);

		//person_id
		$result = $this->BetriebsmittelpersonModel->getBetriebsmittel($person_id);

		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		//all
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

		$uid_user = getAuthUID();
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

		// Start DB transaction
		$this->db->trans_begin();

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
				'insertvon' => $uid_user,
				'insertamum' => date('c')
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

	//TODO(Manu) defaultmässig ersten Eintrag in Edit Modus
	public function getFirstBetriebsmittel($uid, $person_id)
	{
		//uid
		//$result = $this->BetriebsmittelpersonModel->getFirstBetriebsmittelByUid($uid);

		//person_id
		$result = $this->BetriebsmittelpersonModel->getFirstBetriebsmittelByUid($person_id);

		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function loadBetriebsmittel()
	{
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$betriebsmittelperson_id = $this->input->post('betriebsmittelperson_id');

		//$this->terminateWithError("id in function api: " . $betriebsmittelperson_id, self::ERROR_TYPE_GENERAL);

		$this->BetriebsmittelpersonModel->addJoin('wawi.tbl_betriebsmittel', 'betriebsmittel_id');

		$result = $this->BetriebsmittelpersonModel->loadWhere(
		array('betriebsmittelperson_id' => $betriebsmittelperson_id));

		if (isError($result))
		{
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		if (!hasData($result))
		{
			$this->terminateWithError("no Betriebsmittelperson with ID found: " . $betriebsmittelperson_id, self::ERROR_TYPE_GENERAL);
		}

	//	var_dump($result);

		$this->terminateWithSuccess(current(getData($result)));

	}

	public function deleteBetriebsmittel()
	{
		//var_dump($betriebsmittelperson_id);

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$betriebsmittelperson_id = $this->input->post('betriebsmittelperson_id');

		//return $this->terminateWithError("Betriebsmittelperson " . $betriebsmittelperson_id . " wird gelöscht",self::ERROR_TYPE_GENERAL);


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

		$result = $this->BetriebsmitteltypModel->load();
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

}


