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
		$granted_Ass = $this->permissionlib->getSTG_isEntitledFor('assistenz') ? $this->permissionlib->getSTG_isEntitledFor('assistenz') : [];
		$granted_Adm = $this->permissionlib->getSTG_isEntitledFor('admin') ? $this->permissionlib->getSTG_isEntitledFor('admin') : [];
		$granted = array_merge($granted_Ass, $granted_Adm);

		if(!in_array($stg, $granted)){
			$result = "Sie haben keine Schreibrechte fuer diesen Studiengang!";
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

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
		$bestaetigtvon = $uid;

		//GET lastStatus
		$result = $this->PrestudentstatusModel->getLastStatus($prestudent_id);

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		elseif(!hasData($result))
		{
			$lastStatusData = [];
		}
		else
			$lastStatusData = current(getData($result));

		//Different handling depending on newStatus
		if($status_kurzbz == 'Absolvent' || $status_kurzbz == 'Diplomand')
		{
			//$studiensemester = $semester_aktuell; //TODO(Manu) oder ist hier defaultsemester gemeint?
			$ausbildungssemester = $lastStatusData->ausbildungssemester;
		}

		//Todo(manu) check if this check makes sense?
/*		if($status_kurzbz != 'Student')
		{
			$ausbildungssemester = $lastStatusData->ausbildungssemester;
		}*/

		//check if Rolle already exists
		$result = $this->PrestudentstatusModel->checkIfExistingPrestudentRolle($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		if($result->retval == '1')
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result->code);
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

			$result = $this->PrestudentstatusModel->checkIfExistingBewerberstatus($prestudent_id, $name);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			if($result->retval == "0")
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($result->code);
			}
		}

		//check if studentrolle already exists
		if($status_kurzbz == 'Student')
		{
			$this->load->model('crm/Student_model', 'StudentModel');
			$result = $this->StudentModel->checkIfExistingStudentRolle($prestudent_id);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			if($result->retval == "0")
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($result->code);
			}
		}

		//TODO(Manu) check permission...
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
			}
		}

		// Start DB transaction
		$this->db->trans_start(false);

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

		// Transaction complete!
		$this->db->trans_complete();

		if ($this->db->trans_status() === false || isError($result))
		{
			$this->db->trans_rollback();
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		else
		{
			$this->db->trans_commit();
			return $this->outputJsonSuccess(true);
		}
	}

	public function loadStatus()
	{
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$prestudent_id = $this->input->post('prestudent_id');
		$status_kurzbz = $this->input->post('status_kurzbz');
		$ausbildungssemester = $this->input->post('ausbildungssemester');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');

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
			$this->outputJsonSuccess(current(getData($result)));
		}
	}

	public function deleteStatus()
	{
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$prestudent_id = $this->input->post('prestudent_id');
		$status_kurzbz = $this->input->post('status_kurzbz');
		$ausbildungssemester = $this->input->post('ausbildungssemester');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		$deletePrestudent = false;

		//TODO(Manu) check permissions: warum sind beim Löschen andere Berechtigungen?
		//ich darf keine Stati anlegen, aber löschen, wenn mehr als einer übrig???
/*		$granted_Ass = $this->permissionlib->getSTG_isEntitledFor('assistenz');
		$granted_Adm = $this->permissionlib->getSTG_isEntitledFor('admin');
		$granted = array_merge($granted_Ass, $granted_Adm);

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

		if(!in_array($stg, $granted))
		{
			$result = "Sie haben keine Schreibrechte fuer diesen Studiengang!";
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}*/

		$isBerechtigtAdmin = $this->permissionlib->isBerechtigt('admin', null, 'suid');
		$isBerechtigtNoStudstatusCheck = $this->permissionlib->isBerechtigt('student/keine_studstatuspruefung', null, 'suid');


		if($status_kurzbz=="Student" && !$isBerechtigtAdmin && !$isBerechtigtNoStudstatusCheck)
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson("Studentenrolle kann nur durch den Administrator geloescht werden");
		}


		//check if last status
		$result = $this->PrestudentstatusModel->checkIfLastStatusEntry($prestudent_id);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}
		if($result->retval == "1")
		{
			//Berechtigungen nach Check prüfen!
			if(!$isBerechtigtAdmin && !$isBerechtigtNoStudstatusCheck)
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($result->code);
			}
			else
			{
				$deletePrestudent = true;
			}
		}

		// Start DB transaction
		$this->db->trans_start(false);

		//TODO(manu) $this->db->trans_start(false); and rollback?
		//Delete Status
		$result = $this->PrestudentstatusModel->delete(
			array(
				'prestudent_id' => $prestudent_id,
				'status_kurzbz' => $status_kurzbz,
				'ausbildungssemester' => $ausbildungssemester,
				'studiensemester_kurzbz' => $studiensemester_kurzbz
			)
		);

		// Transaction complete!
		$this->db->trans_complete();

		if ( $this->db->trans_status() === false || isError($result))
		{
			$this->db->trans_rollback();
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}
		if(!hasData($result)) {
			$this->outputJson($result);
		}

		$this->db->trans_commit();

		//Delete Studentlehrverband if no Status left
		$result = $this->PrestudentstatusModel->getLastStatus($prestudent_id, $studiensemester_kurzbz);

		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		if (!hasData($result))
		{
			//get student_uid
			$this->load->model('crm/Student_model', 'StudentModel');
			$result = $this->StudentModel->checkIfUid($prestudent_id);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			if (!hasData($result))
			{
				$this->outputJson($result);
			}
			$student_uid = $result->retval;

			$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
			$result = $this->StudentlehrverbandModel->delete(
				array(
					'student_uid' => $student_uid,
					'studiensemester_kurzbz' => $studiensemester_kurzbz
				)
			);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				$this->outputJson("Fehler beim Löschen der Lehrverbandszuordnung");
			}
			if (!hasData($result))
			{
				$this->outputJson($result);
				var_dump("no data2");
			}
			$this->outputJsonSuccess(true);

		}

		//Delete Prestudent if no data is left
		if($deletePrestudent)
		{
			//TODO(manu) check all connected tables and delete them
			//es wird noch auf prestudent_dokumentprestudent verwiesen
			//löschen zuerst von Doks
			$this->load->model('crm/Prestudent_model', 'PrestudentModel');
			$result = $this->PrestudentModel->delete(
				array(
					'prestudent_id' => $prestudent_id
				)
			);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				$this->outputJson($result);
			}
			if(!hasData($result))
			{
				$this->outputJson($result);
			}
			$this->outputJson($result);


		}

		return $this->outputJsonSuccess(true);
	}

	public function updateStatus($key_prestudent_id, $key_status_kurzbz, $key_studiensemester_kurzbz, $key_ausbildungssemester)
	{
		//TODO(Manu) check: Annahme, dass hier immer suid bei Berechtigung STG vergeben wird!
		$granted_Ass = $this->permissionlib->getSTG_isEntitledFor('assistenz') ? $this->permissionlib->getSTG_isEntitledFor('assistenz') : [];
		$granted_Adm = $this->permissionlib->getSTG_isEntitledFor('admin') ? $this->permissionlib->getSTG_isEntitledFor('admin') : [];
		$granted = array_merge($granted_Ass, $granted_Adm);

		//get Studiengang von prestudent_id
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$result = $this->PrestudentModel->load([
			'prestudent_id'=> $key_prestudent_id,
		]);
		if(isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$result = current(getData($result));

		$stg = $result->studiengang_kz;

		if(!in_array($stg, $granted)){
			$result = "Sie haben keine Schreibrechte fuer diesen Studiengang!";
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}
		//var_dump($key_prestudent_id, $key_status_kurzbz, $key_studiensemester_kurzbz, $key_ausbildungssemester);

		/*		$this->load->library('form_validation');


				if ($this->form_validation->run() == false)
				{
					return $this->outputJsonError($this->form_validation->error_array());
				}*/

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$uid = getAuthUID();
		$prestudent_id = $this->input->post('prestudent_id');
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
		$bestaetigtvon = $uid;

		// Start DB transaction
		//$this->db->trans_start(false);

		//check if Rolle already exists
		if(($key_studiensemester_kurzbz != $studiensemester_kurzbz)
			|| ($key_ausbildungssemester != $ausbildungssemester))
			{
				$result = $this->PrestudentstatusModel->checkIfExistingPrestudentRolle($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester);
				if (isError($result))
				{
					$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
					return $this->outputJson(getError($result));
				}
				if($result->retval == '1')
				{
					$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
					return $this->outputJson($result->code);
				}
			}

		//TODO(Manu) check permissions
		$hasPermissionToSkipStatusCheck =  $this->permissionlib->isBerechtigt('student/keine_studdatuspruefung');
		/*		var_dump($hasPermissionToSkipStatusCheck);

				$basis =  $this->permissionlib->isBerechtigt('basis/prestudent');
				var_dump($basis);*/
		if(!$hasPermissionToSkipStatusCheck)
		{
			//Block STATUSCHECKS
			//bei update wohl nicht?
/*			$new_status_datum = isset($datum) ? $datum  : date('Y-m-d');
			$result = $this->PrestudentstatusModel->checkDatumNewStatus($new_status_datum);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}*/

			$new_status_datum = isset($datum) ? $datum  : date('Y-m-d');

			$result = $this->PrestudentstatusModel->checkIfValidStatusHistory($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $new_status_datum, $ausbildungssemester);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
				//return $this->outputJson("DEBUG: in Funktion checkIfValidStatusHistory");
			}
			$statusArr = $result; //wenn return result ok

		}

		$result = $this->PrestudentstatusModel->update(
			[
				'prestudent_id' => $key_prestudent_id,
				'status_kurzbz' => $key_status_kurzbz,
				'studiensemester_kurzbz' => $key_studiensemester_kurzbz,
				'ausbildungssemester' => $key_ausbildungssemester,
			],
			[
				'prestudent_id' => $prestudent_id,
				'status_kurzbz' => $status_kurzbz,
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'ausbildungssemester' => $ausbildungssemester,
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
		if (!hasData($result)) {
			return error('No Statusdata vorhanden');
		}
		return $this->outputJsonSuccess(true);
	}

	public function advanceStatus($key_prestudent_id, $key_status_kurzbz, $key_studiensemester_kurzbz, $key_ausbildungssemester)
	{
		//TODO(Manu) check: Annahme, dass hier immer suid bei Berechtigung STG vergeben wird!
		$granted_Ass = $this->permissionlib->getSTG_isEntitledFor('assistenz') ? $this->permissionlib->getSTG_isEntitledFor('assistenz') : [];
		$granted_Adm = $this->permissionlib->getSTG_isEntitledFor('admin') ? $this->permissionlib->getSTG_isEntitledFor('admin') : [];
		$granted = array_merge($granted_Ass, $granted_Adm);

		//get Studiengang von prestudent_id
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$result = $this->PrestudentModel->load([
			'prestudent_id'=> $key_prestudent_id,
		]);
		if(isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$result = current(getData($result));

		$stg = $result->studiengang_kz;

		if(!in_array($stg, $granted)){
			$result = "Sie haben keine Schreibrechte fuer diesen Studiengang!";
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}

		//Data Vorrücken
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$result = $this->PrestudentstatusModel->loadWhere(
			array(
				'prestudent_id' => $key_prestudent_id,
				'status_kurzbz' => $key_status_kurzbz,
				'ausbildungssemester' => $key_ausbildungssemester,
				'studiensemester_kurzbz' => $key_studiensemester_kurzbz
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
			$statusData = current(getData($result));
		}

		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$result = $this->StudiensemesterModel->getNextFrom($key_studiensemester_kurzbz);

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$studiensem_next = current(getData($result));
		$studiensem_next = $studiensem_next->studiensemester_kurzbz;

		$ausbildungssem_next = $key_ausbildungssemester+1;

		//check if Rolle already exists
		$result = $this->PrestudentstatusModel->checkIfExistingPrestudentRolle($key_prestudent_id, $key_status_kurzbz, $studiensem_next, $ausbildungssem_next);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		if($result->retval == '1')
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result->code);
		}

		//check if studentrolle already exists
		if($key_status_kurzbz == 'Student')
		{
			$this->load->model('crm/Student_model', 'StudentModel');
			$result = $this->StudentModel->checkIfExistingStudentRolle($key_prestudent_id);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			if ($result->retval == "0")
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($result->code);
			}
		}

		// Start DB transaction
		$this->db->trans_start(false);

		//insert prestudentstatus
		$uid = getAuthUID();
		$result = $this->PrestudentstatusModel->insert(
			[
				'prestudent_id' => $key_prestudent_id,
				'status_kurzbz' => $key_status_kurzbz,
				'studiensemester_kurzbz' => $studiensem_next,
				'ausbildungssemester' => $ausbildungssem_next,
				'insertamum' => date('c'),
				'insertvon' => $uid,
				'bestaetigtam' => date('c'),
				'bestaetigtvon' => $uid,
				'studienplan_id' => $statusData->studienplan_id,
				'datum' => date('c'),
				'anmerkung' => $statusData->anmerkung
			]
		);

		// Transaction complete!
		$this->db->trans_complete();

		if ($this->db->trans_status() === false || isError($result))
		{
			$this->db->trans_rollback();
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}

		//Studentlehrverband anlegen
		$this->load->model('crm/Student_model', 'StudentModel');
		$result = $this->StudentModel->checkIfUid($key_prestudent_id);
		if(isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$student_uid = $result->retval;

		//check if Lehrverband exists
		$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
		$result = $this->StudentlehrverbandModel->checkIfLehrverbandExists($student_uid, $studiensem_next);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		if ($result->retval == "0")
		{
			//load Data Lehrverband
			$result = $this->StudentlehrverbandModel->load(
				[
					'student_uid' => $student_uid,
					'studiensemester_kurzbz' => $key_studiensemester_kurzbz
				]);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

				return $this->outputJson("Error in insert Studentlehrverband");
			}
			$lvbData = current(getData($result));

			$result = $this->StudentlehrverbandModel->insert(
				[
					'student_uid' => $student_uid,
					'studiensemester_kurzbz' => $studiensem_next,
					'semester' => $ausbildungssem_next,
					'verband' => $lvbData->verband,
					'gruppe' => $lvbData->gruppe,
					'insertamum' => date('c'),
					'insertvon' => $uid,
					'studiengang_kz' => $lvbData->studiengang_kz
				]);
			if (isError($result))
			{
				$this->db->trans_rollback();
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

				return $this->outputJson("Error in insert Studentlehrverband");
			}
		}
		$this->db->trans_commit();
		return $this->outputJsonSuccess(true);
	}

	public function confirmStatus($key_prestudent_id, $key_status_kurzbz, $key_studiensemester_kurzbz, $key_ausbildungssemester)
	{
		$uid = getAuthUID();
		//TODO(Manu) check: Annahme, dass hier immer suid bei Berechtigung STG vergeben wird!
		$granted_Ass = $this->permissionlib->getSTG_isEntitledFor('assistenz') ? $this->permissionlib->getSTG_isEntitledFor('assistenz') : [];
		$granted_Adm = $this->permissionlib->getSTG_isEntitledFor('admin') ? $this->permissionlib->getSTG_isEntitledFor('admin') : [];
		$granted = array_merge($granted_Ass, $granted_Adm);

		//get Studiengang von prestudent_id
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$result = $this->PrestudentModel->load([
			'prestudent_id'=> $key_prestudent_id,
		]);
		if(isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$result = current(getData($result));

		$stg = $result->studiengang_kz;

		if(!in_array($stg, $granted)){
			$result = "Sie haben keine Schreibrechte fuer diesen Studiengang!";
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}

		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$result = $this->PrestudentstatusModel->loadWhere(
			array(
				'prestudent_id' => $key_prestudent_id,
				'status_kurzbz' => $key_status_kurzbz,
				'ausbildungssemester' => $key_ausbildungssemester,
				'studiensemester_kurzbz' => $key_studiensemester_kurzbz
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
			$statusData = current(getData($result));
		}

		//check if Status is unconfirmed.. TODO(manu) check further conditions? Status < Student
		if($statusData->bestaetigtam != NULL)
		{
			$result = "Der Status ist bereits bestätigt!";
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}

		//check if Bewerbung abgeschickt
		if($statusData->bewerbung_abgeschicktamum == NULL)
		{
			$result = "Die Bewerbung wurde noch nicht abgeschickt und kann deshalb nicht bestaetigt werden!";
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}

		$result = $this->PrestudentstatusModel->update(
			[
				'prestudent_id' => $key_prestudent_id,
				'status_kurzbz' => $key_status_kurzbz,
				'studiensemester_kurzbz' => $key_studiensemester_kurzbz,
				'ausbildungssemester' => $key_ausbildungssemester,
			],
			[
				'bestaetigtam' => date('c'),
				'bestaetigtvon' => $uid
			]
		);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		if (!hasData($result)) {
			return error('No Statusdata vorhanden');
		}
		return $this->outputJsonSuccess(true);

	}
}
