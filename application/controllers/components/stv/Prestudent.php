<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Prestudent extends FHC_Controller
{
	public function __construct()
	{
		parent::__construct();

		// Load Libraries
		$this->load->library('AuthLib');
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		// Load language phrases
		/*		$this->loadPhrases([
					'ui'
				]);*/
	}

	public function get($prestudent_id)
	{
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$this->PrestudentModel->addSelect('*');
		$result = $this->PrestudentModel->loadWhere(['prestudent_id' => $prestudent_id]);

		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} elseif (!hasData($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_NOT_FOUND);
			$this->outputJson('NOT FOUND');
		} else {
			$this->outputJson(current(getData($result)));
		}
	}

	public function updatePrestudent($prestudent_id)
	{
		$this->load->library('form_validation');
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		//Todo(manu) Validierungen

/*		$result = $this->PrestudentModel->loadWhere(['prestudent_id' =>$prestudent_id]);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		} elseif (!hasData($result)) {
			return $this->outputJson(getError($result));
		} else {
			$prestudentData = current(getData($result));
		}

		var_dump($prestudentData);*/

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);
		$deltaData = $_POST[0];

		if(!$prestudent_id)
		{
			return $this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

		//Todo(manu) updateamum, updatevon ergänzen
		$uid = getAuthUID();

		$array_allowed_props_prestudent = [
			'aufmerksamdurch_kurzbz',
			'studiengang_kz',
			'gsstudientyp_kurzbz',
			'person_id',
			'berufstaetigkeit_code',
			'ausbildungcode',
			'zgv_code',
			'zgvort',
			'zgvdatum',
			'zgvnation',
			'zgvmas_code',
			'zgvmaort',
			'zgvmadatum',
			'zgvmanation',
			'facheinschlberuf',
			'bismelden',
			'anmerkung',
			'dual',
			'zgvdoktor_code', //Todo(Manu) tabelle leer? db zum testen
			'zgvdoktorort',
			'zgvdoktordatum',
			'zgvdoktornation',
			'aufnahmegruppe_kurzbz',
			'priorisierung',
			'foerderrelevant',
			'zgv_erfuellt',
			'zgvmas_erfuellt',
			'zgvdoktor_erfuellt',
			'mentor',
			'aufnahmeschluessel',
			'standort_code'
		];

/*		foreach ($array_allowed_props_prestudent as $prop)
		{


		}*/

/*		"insertamum": "2021-05-27 13:03:08",
		"insertvon": "online",
		"updateamum": "2022-10-10 15:37:31.903056",
		"updatevon": "poeckl", "ext_id": null,*/

		$update_prestudent = array();
		foreach ($array_allowed_props_prestudent as $prop)
		{
			$val = isset($deltaData[$prop]) ? $deltaData[$prop] : null;
			if ($val !== null) {
				$update_prestudent[$prop] = $val;
			}
		}
/*
		var_dump("update Array");
		var_dump($update_prestudent);*/



		if (count($update_prestudent) && $prestudent_id === null) {
			$this->output->set_status_header(REST_Controller::HTTP_BAD_REQUEST);
			// TODO(manu): phrase
			return $this->outputJson("Kein/e PrestudentIn vorhanden!");
		}

		if (count($update_prestudent))
		{
			$result = $this->PrestudentModel->update(
				$prestudent_id,
				$update_prestudent
			);
			if (isError($result)) {
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			return $this->outputJsonSuccess(true);
		}

	}

	public function getHistoryPrestudents($person_id)
	{
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$result = $this->PrestudentModel->getHistoryPrestudents($person_id);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson(getData($result) ?: []);
	}

	public function getHistoryPrestudent($prestudent_id)
	{
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$result = $this->PrestudentModel->getHistoryPrestudent($prestudent_id);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson(getData($result) ?: []);
	}

	public function getBezeichnungZgv()
	{
		$this->load->model('codex/Zgv_model', 'ZgvModel');

		$this->ZgvModel->addOrder('zgv_code');

		$result = $this->ZgvModel->load();
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getBezeichnungDZgv()
	{
		$this->load->model('codex/Zgvdoktor_model', 'ZgvdoktorModel');

		$this->ZgvdoktorModel->addOrder('zgvdoktor_code');

		$result = $this->ZgvdoktorModel->load();
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getBezeichnungMZgv()
	{
		$this->load->model('codex/Zgvmaster_model', 'ZgvmasterModel');

		$this->ZgvmasterModel->addOrder('zgvmas_code');

		$result = $this->ZgvmasterModel->load();
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getAusbildung()
	{
		$this->load->model('codex/Ausbildung_model', 'AusbildungModel');

		$this->AusbildungModel->addOrder('ausbildungcode');

		$result = $this->AusbildungModel->load();
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getAufmerksamdurch()
	{
		$this->load->model('codex/Aufmerksamdurch_model', 'AufmerksamdurchModel');

		$this->AufmerksamdurchModel->addOrder('aufmerksamdurch_kurzbz');

		$result = $this->AufmerksamdurchModel->load();
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getBerufstaetigkeit()
	{
		$this->load->model('codex/Berufstaetigkeit_model', 'BerufstaetigkeitModel');

		$this->BerufstaetigkeitModel->addOrder('berufstaetigkeit_code');

		$result = $this->BerufstaetigkeitModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getTypenStg()
	{
		$this->load->model('education/Gsstudientyp_model', 'GsstudientypModel');

		$this->GsstudientypModel->addOrder('gsstudientyp_kurzbz');

		$result = $this->GsstudientypModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}


}