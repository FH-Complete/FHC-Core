<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Status extends FHC_Controller
{
	public function __construct()
	{
		parent::__construct();

		// Load Libraries
		$this->load->library('AuthLib');
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('PermissionLib');

		// Load language phrases
		/*		$this->loadPhrases([
					'ui'
				]);*/
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

	public function getStatusgruende()
	{
		$this->load->model('crm/Statusgrund_model', 'StatusgrundModel');

		$result = $this->StatusgrundModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function addNewStatus($prestudent_id)
	{
/*		$this->load->library('form_validation');


		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}*/

		//check rights
		//get Studiengang von prestudent_id
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->PrestudentModel->addJoin('public.tbl_person p', 'ON (p.person_id = public.tbl_prestudent.person_id)');
		$result = $this->PrestudentModel->load([
			'prestudent_id'=> $prestudent_id,
		]);
		if(isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$result = current(getData($result));

		//Variablen für Statuscheck
		$stg = $result->studiengang_kz;
		$reihungstest_angetreten = $result->reihungstestangetreten;
		$name = trim($result->vorname . " ". $result->nachname);
		$zgv_code = $result->zgv_code;

		//TODO(Manu) check: Annahme, dass hier immer suid bei Berechtigung STG vergeben wird!
		$granted_Ass = $this->permissionlib->getSTG_isEntitledFor('assistenz');
		$granted_Adm = $this->permissionlib->getSTG_isEntitledFor('admin');
		$granted = array_merge($granted_Ass, $granted_Adm);

		if(!in_array($stg, $granted)){
			$result = "Sie haben keine Schreibrechte fuer diesen Studiengang!";
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		//var_dump($_POST);
		$uid = getAuthUID();
		$status_kurzbz = $this->input->post('status_kurzbz');
		$ausbildungssemester = $this->input->post('ausbildungssemester');
		$datum = $this->input->post('datum');
		$bestaetigtam = $this->input->post('bestaetigtam');
		$bewerbung_abgeschicktamum = $this->input->post('bewerbung_abgeschicktamum');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		$studienplan_id = $this->input->post('studienplan_id');
		$anmerkung  = $this->input->post('anmerkung');
		$statusgrund_id = $this->input->post('statusgrund_id');
		$rt_stufe = $this->input->post('rt_stufe');
		$bestaetigtvon = $this->input->post('bestaetigtvon');

		// Start DB transaction
		//$this->db->trans_start(false);

		//GET lastStatus
		$result = $this->PrestudentstatusModel->getLastStatus($prestudent_id);

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$lastStatusData = current(getData($result));
		//var_dump($lastStatusData);

		//Different handling depending on newStatus
		if($status_kurzbz == 'Absolvent' || $status_kurzbz == 'Diplomand')
		{
			//$studiensemester = $semester_aktuell; //TODO(Manu) oder ist hier defaultsemester gemeint?
			$ausbildungssemester = $lastStatusData->ausbildungssemester;
		}

		if($status_kurzbz != 'Student')
		{
			$ausbildungssemester = $lastStatusData->ausbildungssemester;
		}

		//check if Rolle already exists
		$result = $this->PrestudentstatusModel->checkIfExistingPrestudentRolle($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
			//return $this->outputJson("DEBUG: in Funktion checkIfExistingPrestudentRolle");
		}

		//Check ob Reihungstest berücksichtigt werden soll
		if(REIHUNGSTEST_CHECK)
		{
			if($status_kurzbz=='Bewerber' && !$reihungstest_angetreten)
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($name . ": Um einen Interessenten zum Bewerber zu machen, muss die Person das Reihungstestverfahren abgeschlossen haben");
			}
		}

		//Check ZGV
		if(!defined("ZGV_CHECK") || ZGV_CHECK)
		{
			if($status_kurzbz=='Bewerber' && $zgv_code=='')
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($name . ": Um einen Interessenten zum Bewerber zu machen, muss die Zugangsvoraussetzung eingetragen sein.");
			}
		}

		//Check ZGV-Master
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$result = $this->StudiengangModel->load([
			'studiengang_kz'=> $stg
		]);
		if(isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$result = current(getData($result));
		$typ = $result->typ;

		if(!defined("ZGV_CHECK") || ZGV_CHECK)
		{
			if($status_kurzbz=='Bewerber' && $zgv_code=='' && $typ=='m')
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($name . ": Um einen Interessenten zum Bewerber zu machen, muss die Zugangsvoraussetzung Master eingetragen sein.");
			}
		}

		//check if bewerberstatus exists
		if($status_kurzbz == 'Aufgenommener' || $status_kurzbz == 'Wartender')
		{
			//TODO(manu) Wartender NICHT in Liste!? nur in diesem Code
			//FAS: Aufnahme ist möglich: Beispiel prestudent_id = 129629

			$result = $this->PrestudentstatusModel->checkIfExistingBewerberstatus($prestudent_id);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
		}

		//TODO(Manu) permission not working here...
		$hasPermissionToSkipStatusCheck =  $this->permissionlib->isBerechtigt('student/keine_studdatuspruefung');
		/*		var_dump($hasPermissionToSkipStatusCheck);

				$basis =  $this->permissionlib->isBerechtigt('basis/prestudent');
				var_dump($basis);*/
		if(!$hasPermissionToSkipStatusCheck)
		{
			//Block STATUSCHECKS
			$new_status_datum = isset($datum) ? $datum  : date('Y-m-d');
			$result = $this->PrestudentstatusModel->checkDatumNewStatus($new_status_datum);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}

			$result = $this->PrestudentstatusModel->checkIfValidStatusHistory($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $new_status_datum, $ausbildungssemester);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
				//return $this->outputJson("DEBUG: in Funktion checkIfValidStatusHistory");
			}
			$statusArr = $result; //wenn return result ok

		}


		$result = $this->PrestudentstatusModel->insert(
			[
				'prestudent_id' => $prestudent_id,
				'status_kurzbz' => $status_kurzbz,
				'bewerbung_abgeschicktamum' => $bewerbung_abgeschicktamum,
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'studienplan_id' => $studienplan_id,
				'ausbildungssemester' => $ausbildungssemester,
				'anmerkung' => $anmerkung,
				'statusgrund_id' => $statusgrund_id,
				'insertvon' => $uid,
				'insertamum' => date('c'),
				'bestaetigtam' => $bestaetigtam,
				'bestaetigtvon' => $bestaetigtvon,
				'datum' => $datum,
				'rt_stufe' => $rt_stufe
			]
		);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			//return $this->outputJson(getError($result));
			return $this->outputJson("DEBUG: in insert Funktion");
		}

		return $this->outputJsonSuccess(true);
	}

	public function loadStatus()
	{
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$prestudent_id = $this->input->post('prestudent_id');
		$status_kurzbz = $this->input->post('status_kurzbz');
		$ausbildungssemester = $this->input->post('ausbildungssemester');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		//echo $prestudent_id . $status_kurzbz . $ausbildungssemester . $studiensemester_kurzbz;


		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$result = $this->PrestudentstatusModel->loadWhere(
			array(
				'prestudent_id' => $prestudent_id,
				'status_kurzbz' => $status_kurzbz,
				'ausbildungssemester' => $ausbildungssemester,
				'studiensemester_kurzbz' => $studiensemester_kurzbz
			)
		);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}

		elseif (!hasData($result)) {
			$this->outputJson($result);
		}
		else
		{
			//var_dump($result);
			$this->outputJsonSuccess(current(getData($result)));
		}
	}

	public function deleteStatus()
	{
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$prestudent_id = $this->input->post('prestudent_id');
		$status_kurzbz = $this->input->post('status_kurzbz');
		$ausbildungssemester = $this->input->post('ausbildungssemester');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
/*		var_dump($prestudent_id);
		var_dump($status_kurzbz);*/

		//var_dump($_POST);

		//Todo(manu)
		//Löschen auch aus anderen Tabellen

		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$result = $this->PrestudentstatusModel->delete(
			array(
				'prestudent_id' => $prestudent_id,
				'status_kurzbz' => $status_kurzbz,
				'ausbildungssemester' => $ausbildungssemester,
				'studiensemester_kurzbz' => $studiensemester_kurzbz
			)
		);

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}
		elseif (!hasData($result)) {
			$this->outputJson($result);
		}
		return $this->outputJsonSuccess(current(getData($result)));
	}

	public function updateStatus($prestudent_id)
	{
		/*		$this->load->library('form_validation');


				if ($this->form_validation->run() == false)
				{
					return $this->outputJsonError($this->form_validation->error_array());
				}*/

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		var_dump($_POST);

		$uid = getAuthUID();
		//$prestudent_id = $this->input->post('prestudent_id');
		$status_kurzbz = $this->input->post('status_kurzbz');
		$ausbildungssemester = $this->input->post('ausbildungssemester');
		$datum = $this->input->post('datum');
		$bestaetigtam = $this->input->post('bestaetigtam');
		$bewerbung_abgeschicktamum = $this->input->post('bewerbung_abgeschicktamum');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		$studienplan_id = $this->input->post('studienplan_id');
		$anmerkung  = $this->input->post('anmerkung ');
		$statusgrund_id = $this->input->post('statusgrund_id');
		$rt_stufe = $this->input->post('rt_stufe');
		$bestaetigtvon = $this->input->post('bestaetigtvon');

		// Start DB transaction
		//$this->db->trans_start(false);

		$result = $this->PrestudentstatusModel->update(
			[
				'prestudent_id' => $prestudent_id,
				'status_kurzbz' => $status_kurzbz,
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'ausbildungssemester' => $ausbildungssemester,
			],
			[

				'bewerbung_abgeschicktamum' => $bewerbung_abgeschicktamum,
				'studienplan_id' => $studienplan_id,
				'anmerkung' => $anmerkung,
				'statusgrund_id' => $statusgrund_id,
				'updatevon' => $uid,
				'updateamum' => date('c'),
				'bestaetigtam' => $bestaetigtam,
				'bestaetigtvon' => $bestaetigtvon,
				'datum' => $datum,
				'rt_stufe' => $rt_stufe
			]
		);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		return $this->outputJsonSuccess(true);
	}

}
