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

	public function getBezeichnungZgv(){
		$this->load->model('codex/Zgv_model', 'ZgvModel');

		$this->ZgvModel->addOrder('zgv_code');

		$result = $this->ZgvModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getAusbildung(){
		$this->load->model('codex/Ausbildung_model', 'AusbildungModel');

		$this->AusbildungModel->addOrder('ausbildungcode');

		$result = $this->AusbildungModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getAufmerksamdurch(){
		$this->load->model('codex/Aufmerksamdurch_model', 'AufmerksamdurchModel');

		$this->AufmerksamdurchModel->addOrder('aufmerksamdurch_kurzbz');

		$result = $this->AufmerksamdurchModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getBerufstaetigkeit(){
		$this->load->model('codex/Berufstaetigkeit_model', 'BerufstaetigkeitModel');

		$this->BerufstaetigkeitModel->addOrder('berufstaetigkeit_code');

		$result = $this->BerufstaetigkeitModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getTypenStg(){
		$this->load->model('organisation/Studiengangstyp_model', 'StudiengangstypModel');

		$this->StudiengangstypModel->addOrder('typ');

		$result = $this->StudiengangstypModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$data = $result->retval;

		//filter NullValues
		$filteredData = array_filter($data, function ($entry) {
			return $entry->bezeichnung !== null;
		});
		$result->retval = $filteredData;

		$this->outputJson($result);
	}


}