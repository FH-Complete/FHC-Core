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
		$this->load->library('PermissionLib');

		// Load language phrases
		$this->loadPhrases([
			'ui', 'studierendenantrag'
		]);
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

		//get Studiengang von prestudent_id
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$result = $this->PrestudentModel->load([
			'prestudent_id'=> $prestudent_id,
		]);
		if(isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$result = current(getData($result));

		$stg = $result->studiengang_kz;

		if (!$this->permissionlib->isBerechtigt('admin', 'suid', $stg) && !$this->permissionlib->isBerechtigt('assistenz', 'suid', $stg))
		{
			$result =  $this->p->t('lehre','error_keineSchreibrechte');
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}

		//TODO(Manu) neuer API Controller
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);


		//Form validation
		$this->form_validation->set_rules('priorisierung', 'Priorisierung', 'numeric', [
			'numeric' => $this->p->t('ui','error_fieldNotNumeric',['field' => 'Priorisierung'])
		]);

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$deltaData = $_POST;

		if(!$prestudent_id)
		{
			return $this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

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
			'zgvdoktor_code',
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

		$update_prestudent = array();
		foreach ($array_allowed_props_prestudent as $prop)
		{
			$val = isset($deltaData[$prop]) ? $deltaData[$prop] : null;
			if ($val !== null || $prop == 'foerderrelevant') {
				$update_prestudent[$prop] = $val;
			}
		}

		$update_prestudent['updateamum'] = date('c');
		$update_prestudent['updatevon'] = $uid;

		//utf8-decode for special chars (eg tag der offenen Tür, FH-Führer)
		function utf8_decode_if_string($value)
		{
			if (is_string($value)) {
				return utf8_decode($value);
			} else {
				return $value;
			}
		}
		$update_prestudent_encoded = array_map('utf8_decode_if_string', $update_prestudent);

		if (count($update_prestudent) && $prestudent_id === null) {
			$this->output->set_status_header(REST_Controller::HTTP_BAD_REQUEST);
			// TODO(manu): check phrase
			//return $this->outputJson("Kein/e PrestudentIn vorhanden!");
			return $this->outputJson($this->p->t('studierendenantrag','error_no_prestudent', $prestudent_id));
		}

		if (count($update_prestudent))
		{
			$result = $this->PrestudentModel->update(
				$prestudent_id,
				$update_prestudent_encoded
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

	public function getStudiensemester()
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$this->StudiensemesterModel->addOrder('start', 'DESC');
		$this->StudiensemesterModel->addLimit(20);

		$result = $this->StudiensemesterModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getStudienplaene($prestudent_id)
	{
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$result = $this->PrestudentModel->loadWhere(
			array('prestudent_id' => $prestudent_id)
		);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

		$result = current(getData($result));
		$studiengang_kz = $result->studiengang_kz;

		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');

		$this->StudienplanModel->addOrder('studienplan_id', 'DESC');

		$result = $this->StudienplanModel->getStudienplaene($studiengang_kz);

		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}
}
