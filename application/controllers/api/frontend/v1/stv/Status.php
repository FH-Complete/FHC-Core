<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use CI3_Events as Events;
use \DateTime as DateTime;

class Status extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getHistoryPrestudent' => ['admin:r', 'assistenz:r'],
			'addNewStatus' => ['admin:r', 'assistenz:r', 'student/keine_studstatuspruefung'],
			//'turnIntoStudent' => ['admin:r', 'assistenz:r', 'student/keine_studstatuspruefung'],
			'addStudent' => ['admin:r', 'assistenz:r', 'student/keine_studstatuspruefung'],
			'getStatusgruende' => self::PERM_LOGGED,
			'getLastBismeldestichtag' => self::PERM_LOGGED,
			'isLastStatus' => self::PERM_LOGGED,
			'isErsterStudent' => self::PERM_LOGGED,
			'hasStatusBewerber' => self::PERM_LOGGED,
			'deleteStatus' => ['admin:r','assistenz:r'],
			'loadStatus' => ['admin:r', 'assistenz:r'],
			'updateStatus' => ['admin:r', 'assistenz:r'],
			'advanceStatus' => ['admin:r', 'assistenz:r'],
			'confirmStatus' => ['admin:r', 'assistenz:r'],

		]);

		//Load Models
		$this->load->model('crm/Prestudentstatus_model','PrestudentstatusModel');
		$this->load->model('person/Person_model','PersonModel');

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('PrestudentstatusCheckLib');

		// Load language phrases
		$this->loadPhrases([
			'ui', 'bismeldestichtag','lehre','studierendenantrag'
		]);
	}

	public function getHistoryPrestudent($prestudent_id)
	{
		$result = $this->PrestudentstatusModel->getHistoryPrestudent($prestudent_id);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getStatusgruende()
	{
		$this->load->model('crm/Statusgrund_model', 'StatusgrundModel');

		$result = $this->StatusgrundModel->getAktiveGruende();

		$data = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($data);
	}

	public function getLastBismeldestichtag()
	{
		$this->load->model('codex/Bismeldestichtag_model', 'BismeldestichtagModel');

		$result = $this->BismeldestichtagModel->getLastReachedMeldestichtag();
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function isLastStatus($prestudent_id)
	{
		$result = $this->PrestudentstatusModel->checkIfLastStatusEntry($prestudent_id);


		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if($result->retval == "1")
		{
			$this->terminateWithError($this->p->t('lehre','error_lastRole'), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess($result);
	}

	public function isErsterStudent($prestudent_id)
	{
		//check if studentrolle already exists
		$this->load->model('crm/Student_model','StudentModel');
		$result = $this->StudentModel->checkIfExistingStudentRolle($prestudent_id);
		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		if($result->retval == "0")
		{
			return $this->terminateWithSuccess($result);
		}
		return $this->terminateWithSuccess($result);

	}

	public function hasStatusBewerber($prestudent_id)
	{
		$result = $this->prestudentstatuschecklib->checkIfExistingBewerberstatus($prestudent_id);
		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess($result);
	}

	public function addNewStatus($prestudent_id)
	{
		//get Studiengang von prestudent_id
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->PrestudentModel->addJoin('public.tbl_person p', 'ON (p.person_id = public.tbl_prestudent.person_id)');
		$result = $this->PrestudentModel->load([
			'prestudent_id'=> $prestudent_id,
		]);
		if(isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		$result = current(getData($result));

		$stg = $result->studiengang_kz;
		$isStudent = false;

		if(!$this->permissionlib->isBerechtigt('admin', 'suid', $stg) && !$this->permissionlib->isBerechtigt('assistenz', 'suid', $stg))
		{
			$result =  $this->p->t('lehre','error_keineSchreibrechte');

			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$uid = getAuthUID();
		$status_kurzbz = $this->input->post('status_kurzbz');
		$ausbildungssemester = $this->input->post('ausbildungssemester');
		$datum = $this->input->post('datum');
		$bestaetigtam = $this->input->post('bestaetigtam');
		$bewerbung_abgeschicktamum = $this->input->post('bewerbung_abgeschicktamum');
		$studienplan_id = $this->input->post('studienplan_id');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		$anmerkung = $this->input->post('anmerkung');
		$statusgrund_id = $this->input->post('statusgrund_id');
		$rt_stufe = $this->input->post('rt_stufe');
		$bestaetigtvon = $uid;

		//Form Validation
		$this->load->library('form_validation');

		$this->form_validation->set_rules('ausbildungssemester', 'Ausbildungssemester', 'integer', [
			'integer' => $this->p->t('ui', 'error_fieldNotInteger', ['field' => 'Ausbildungssemester'])
		]);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		//GET lastStatus
		$result = $this->PrestudentstatusModel->getLastStatus($prestudent_id);

		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		elseif(!hasData($result))
		{
			$lastStatusData = [];
		}
		else
			$lastStatusData = current(getData($result));

		//Different handling depending on newStatus
		if($status_kurzbz == Prestudentstatus_model::STATUS_ABSOLVENT || $status_kurzbz == Prestudentstatus_model::STATUS_DIPLOMAND)
		{
			$ausbildungssemester = $lastStatusData->ausbildungssemester;
		}

		//check if Rolle already exists
		$result = $this->prestudentstatuschecklib->checkIfExistingPrestudentRolle(
			$prestudent_id,
			$status_kurzbz,
			$studiensemester_kurzbz,
			$ausbildungssemester
		);

		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		//Check Reihungstest
		if(REIHUNGSTEST_CHECK)
		{
			if($status_kurzbz==Prestudentstatus_model::STATUS_BEWERBER)
			{
				$result = $this->prestudentstatuschecklib->checkIfAngetreten($prestudent_id);
				if (isError($result))
				{
					return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
				}
			}
		}

		//Check ZGV
		if(!defined("ZGV_CHECK") || ZGV_CHECK)
		{
			if($status_kurzbz==Prestudentstatus_model::STATUS_BEWERBER)
			{
				$result = $this->prestudentstatuschecklib->checkIfZGVEingetragen($prestudent_id);
				if (isError($result))
				{
					return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
				}
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
		//$stgkzl = $result->kurzbz;

		if(!defined("ZGV_CHECK") || ZGV_CHECK)
		{
			if($status_kurzbz==Prestudentstatus_model::STATUS_BEWERBER && $typ=='m')
			{
				$result = $this->prestudentstatuschecklib->checkIfZGVEingetragen($prestudent_id, $typ);
				if (isError($result))
				{
					return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
				}
			}
		}

		//check if bewerberstatus exists
		if($status_kurzbz == Prestudentstatus_model::STATUS_AUFGENOMMENER || $status_kurzbz == Prestudentstatus_model::STATUS_WARTENDER)
		{

			$result = $result = $this->prestudentstatuschecklib->checkIfExistingBewerberstatus($prestudent_id);
			if (isError($result))
			{
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}

		}

		//check if studentrolle already exists
		if($status_kurzbz == Prestudentstatus_model::STATUS_STUDENT || $status_kurzbz == Prestudentstatus_model::STATUS_DIPLOMAND || $lastStatusData->status_kurzbz == Prestudentstatus_model::STATUS_STUDENT)
		{
			$result = $this->prestudentstatuschecklib->checkIfExistingStudentRolle($prestudent_id);
			if (isError($result))
			{
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
			$isStudent = true;
		}

		$isBerechtigtNoStudstatusCheck =  $this->permissionlib->isBerechtigt('student/keine_studstatuspruefung');

		if(!$isBerechtigtNoStudstatusCheck)
		{
			//Block STATUSCHECKS
			$new_status_datum = isset($datum) ? $datum : date('Y-m-d');

			$result = $this->prestudentstatuschecklib->checkStatusAdd(
				$prestudent_id,
				$status_kurzbz,
				$studiensemester_kurzbz,
				$new_status_datum,
				$ausbildungssemester,
				$studienplan_id
			);

			if (isError($result))
			{
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}

			//TODO(manu) check Berechtigung
			//check if Bismeldestichtag erreicht ADDNEW status
			$result = $this->prestudentstatuschecklib->checkIfMeldestichtagErreicht($new_status_datum, $studiensemester_kurzbz);

			//just testing
			//return $this->terminateWithError("here", self::ERROR_TYPE_GENERAL);
			if (isError($result))
			{
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
		}

		//TODO(Manu) DELETE
		// $new_status_datum = isset($datum) ? $datum : date('Y-m-d');

		// $result = $this->prestudentstatuschecklib->checkStatusAdd(
		// 	$prestudent_id,
		// 	$status_kurzbz,
		// 	$studiensemester_kurzbz,
		// 	$new_status_datum,
		// 	$ausbildungssemester,
		// 	$studienplan_id
		// );

		// if (isError($result))
		// {
		// 	return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		// }

		// $this->load->model('codex/Bismeldestichtag_model', 'BismeldestichtagModel');
		// $result = $this->BismeldestichtagModel->checkIfMeldestichtagErreicht($datum, $studiensemester_kurzbz);
		// return $this->terminateWithError("retval: " . $result->retval, self::ERROR_TYPE_GENERAL);

		// if (isError($result))
		// {
		// 	return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		// }
		// if ($result->retval == "1")
		// {
		// 	return $this->terminateWithError($this->p->t('lehre','error_dataVorMeldestichtag'), self::ERROR_TYPE_GENERAL);
		// }

		switch($status_kurzbz){
			case Prestudentstatus_model::STATUS_ABBRECHER:
				{
					$studiensemester_kurzbz = $lastStatusData->studiensemester_kurzbz;

					$this->load->model('crm/Statusgrund_model', 'StatusgrundModel');
					$result = $this->StatusgrundModel->load($statusgrund_id);
					if (isError($result))
					{
						return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
					}
					$result = current(getData($result));
					$statusgrund_kurzbz = $result->statusgrund_kurzbz;
		
					$this->load->library('PrestudentLib');
					$result = $this->prestudentlib->setAbbrecher($prestudent_id, $studiensemester_kurzbz, null, $statusgrund_kurzbz, $datum, $bestaetigtam, $bestaetigtvon);
					if (isError($result))
					{
						return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
					}
					else
						$this->terminateWithSuccess($prestudent_id);
				}
				break;
			case Prestudentstatus_model::STATUS_UNTERBRECHER:
				{
					$ausbildungssemester = $lastStatusData->ausbildungssemester;
					$studiensemester_kurzbz = $lastStatusData->studiensemester_kurzbz;

					$this->load->library('PrestudentLib');
					$result = $this->prestudentlib->setUnterbrecher($prestudent_id, $studiensemester_kurzbz, null, null, $ausbildungssemester);
					if (isError($result))
					{
						return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
					}
					else
						$this->terminateWithSuccess($prestudent_id);
				}
				break;
			case Prestudentstatus_model::STATUS_STUDENT:
				{
					$this->load->library('PrestudentLib');
					$result = $this->prestudentlib->setStudent($prestudent_id, $studiensemester_kurzbz, $ausbildungssemester, $statusgrund_id, $bestaetigtam, $bestaetigtvon);

					if (isError($result))
					{
						return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
					}
					else
						$this->terminateWithSuccess($prestudent_id);

				}
				break;
			case Prestudentstatus_model::STATUS_DIPLOMAND:
				{
					$this->load->library('PrestudentLib');
					$result = $this->prestudentlib->setDiplomand($prestudent_id, $studiensemester_kurzbz, $ausbildungssemester);

					if (isError($result))
					{
						return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
					}
					else
						$this->terminateWithSuccess($prestudent_id);
				}
				break;
			case Prestudentstatus_model::STATUS_ABSOLVENT:
				{
					$this->load->library('PrestudentLib');
					$result = $this->prestudentlib->setAbsolvent($prestudent_id, $studiensemester_kurzbz, $ausbildungssemester);

					if (isError($result))
					{
						return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
					}
					else
						$this->terminateWithSuccess($prestudent_id);

				}
				break;
			default:
			{
/*				if ($isStudent) {
					return $this->terminateWithError("LehrverbandData needs no handling", self::ERROR_TYPE_GENERAL);
				}*/
				$result = $this->PrestudentstatusModel->insert(
					[
						'prestudent_id' => $prestudent_id,
						'status_kurzbz' => $status_kurzbz,
						'bewerbung_abgeschicktamum' => $bewerbung_abgeschicktamum,
						'studiensemester_kurzbz' => $studiensemester_kurzbz,
						'studienplan_id' => $lastStatusData->studienplan_id,
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
					return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
				}
				else
					$this->terminateWithSuccess($prestudent_id);
			}
		}
	}

	//TODO(Manu) DEPRECATED
	// public function turnIntoStudent($prestudent_id)
	// {

	// 	//get Studiengang von prestudent_id
	// 	$this->load->model('crm/Prestudent_model', 'PrestudentModel');
	// 	$this->PrestudentModel->addJoin('public.tbl_person p', 'ON (p.person_id = public.tbl_prestudent.person_id)');
	// 	$this->PrestudentModel->addJoin('public.tbl_studiengang sg', 'ON (sg.studiengang_kz = public.tbl_prestudent.studiengang_kz)');
	// 	$result = $this->PrestudentModel->load([
	// 		'prestudent_id' => $prestudent_id,
	// 	]);
	// 	if (isError($result)) {
	// 		return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
	// 	}
	// 	$result = current(getData($result));

	// 	$stg = $result->studiengang_kz;
	// 	$stgkzl = $result->kurzbz;
	// 	$person_id = $result->person_id;

	// 	if (!$this->permissionlib->isBerechtigt('admin', 'suid', $stg) && !$this->permissionlib->isBerechtigt('assistenz', 'suid', $stg)) {
	// 		$result = $this->p->t('lehre', 'error_keineSchreibrechte');

	// 		return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
	// 	}

	// 	$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

	// 	$uid = getAuthUID();
	// 	$status_kurzbz = $this->input->post('status_kurzbz');
	// 	$ausbildungssemester = $this->input->post('ausbildungssemester');
	// 	$bestaetigtam = $this->input->post('bestaetigtam');
	// 	$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
	// 	$bestaetigtvon = $uid;

	// 	//Form Validation
	// 	$this->load->library('form_validation');

	// 	$this->form_validation->set_rules('ausbildungssemester', 'Ausbildungssemester', 'integer', [
	// 		'integer' => $this->p->t('ui', 'error_fieldNotInteger', ['field' => 'Ausbildungssemester'])
	// 	]);

	// 	if (!$this->form_validation->run())
	// 		$this->terminateWithValidationErrors($this->form_validation->error_array());

	// 	//GET lastStatus
	// 	$result = $this->PrestudentstatusModel->getLastStatus($prestudent_id);

	// 	if (isError($result)) {
	// 		return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
	// 	} elseif (!hasData($result)) {
	// 		$lastStatusData = [];
	// 	} else
	// 		$lastStatusData = current(getData($result));

	// 	//check if studentrolle already exists
	// 	$this->load->model('crm/Student_model', 'StudentModel');
	// 	$result = $this->StudentModel->checkIfExistingStudentRolle($prestudent_id);
	// 	if (isError($result)) {
	// 		return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
	// 	}
	// 	if ($result->retval == "0") {
	// 		$isStudent = false;
	// 	} else
	// 		$isStudent = true;

	// 	return $this->terminateWithError("here: before check: isStudent: " . $isStudent, self::ERROR_TYPE_GENERAL);

	// 	if (!$isStudent) {

	// 		//Check ZGV
	// 		if ($status_kurzbz == Prestudentstatus_model::STATUS_BEWERBER) {
	// 			$result = $this->prestudentstatuschecklib->checkIfZGVEingetragen($prestudent_id);
	// 			if (isError($result)) {
	// 				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
	// 			}
	// 		}

	// 		//Check ZGV-Master
	// 		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
	// 		$result = $this->StudiengangModel->load([
	// 			'studiengang_kz' => $stg
	// 		]);
	// 		if (isError($result)) {
	// 			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
	// 			return $this->outputJson(getError($result));
	// 		}
	// 		$result = current(getData($result));
	// 		$typ = $result->typ;

	// 		if (!defined("ZGV_CHECK") || ZGV_CHECK) {
	// 			if ($status_kurzbz == Prestudentstatus_model::STATUS_BEWERBER && $typ == 'm') {
	// 				$result = $this->prestudentstatuschecklib->checkIfZGVEingetragen($prestudent_id, $typ);
	// 				if (isError($result)) {
	// 					return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
	// 				}
	// 			}
	// 		}

	// 		//check if bewerberstatus exists
	// 		$result = $this->prestudentstatuschecklib->checkIfExistingBewerberstatus($prestudent_id);
	// 		if (isError($result)) {
	// 			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
	// 		}

	// 		//checkIf Kaution bezahlt
	// 		//TODO(Manu)

	// 		//check if Aufgenommenener Status
	// 		$result = $this->prestudentstatuschecklib->checkIfExistingAufgenommenerstatus($prestudent_id);
	// 		if (isError($result)) {
	// 			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
	// 		}

	// 		return $this->terminateWithError("here: after check: new Student", self::ERROR_TYPE_GENERAL);


	// 		//generate Personenkennzeichen(matrikelnr)
	// 		//getStudiensemester vom Status aufgenommen
	// 		$resultMat = $this->StudentModel->generateMatrikelnummer2($stg, $studiensemester_kurzbz, $typ);
	// 		if (isError($resultMat)) {
	// 			return $this->terminateWithError($resultMat, self::ERROR_TYPE_GENERAL);
	// 		}
	// 		$matrikelnr = getData($resultMat);
	// 		$jahr = mb_substr($matrikelnr, 0, 2);

	// 		//generate UID
	// 		$resultUid = $this->StudentModel->generateUID($stgkzl, $jahr, $typ, $matrikelnr);
	// 		if (isError($resultMat)) {
	// 			return $this->terminateWithError("in generateUid" . $resultUid, self::ERROR_TYPE_GENERAL);
	// 		}
	// 		$uidStudent = getData($resultUid);

	// 		//Check for additional logic (config entries, addons)
	// 		//TODO(Manu) check include/tw/generatematrikelnr.inc.php
	// 		//(1) addons durchsuchen, ob eigene Logik für Matrikelnr-erstellung existiert
	// 		//Default: keine Matrikelnummer wird generiert

	// 		//(2) personenkz = uid
	// 		if (defined('SET_UID_AS_PERSONENKENNZEICHEN') && SET_UID_AS_PERSONENKENNZEICHEN) {
	// 			$matrikelnr = $uidStudent;
	// 		}

	// 		//(3) Matrikelnummer = uid
	// 		if (defined('SET_UID_AS_MATRIKELNUMMER') && SET_UID_AS_MATRIKELNUMMER) {
	// 			//update person
	// 			$result = $this->PersonModel->update(
	// 				[
	// 					'person_id' => $person_id,
	// 				],
	// 				[
	// 					'matr_nr' => $uidStudent,
	// 				]
	// 			);
	// 			if (isError($result)) {
	// 				return $this->terminateWithError("uidAsMatrikelnummer" . getError($result), self::ERROR_TYPE_GENERAL);
	// 			}
	// 		}

	// 		//TODO(Manu) check if benutzer already exists to avoid conflicts..
	// 		//person kann mehrere Benutzer haben... student_uid?
	// 		//add benutzerdatensatz mit Aktierungscode
	// 		$this->load->model('person/Benutzer_model', 'BenutzerModel');
	// 		$result = $this->BenutzerModel->checkIfExistingBenutzer($person_id);
	// 		if (isError($result)) {
	// 			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
	// 		}
	// 		if ($result->retval == "0") {

	// 			//activation key
	// 			$aktivierungscode = null;
	// 			$result = $this->BenutzerModel->generateActivationKey();
	// 			if (isError($result)) {
	// 				return $this->terminateWithError("in Error " . $result, self::ERROR_TYPE_GENERAL);
	// 			}
	// 			$aktivierungscode = getData($result);


	// 			//Alias generieren
	// 			$result = $this->PersonModel->generateAliasByPersonId($person_id);
	// 			if (isError($result)) {
	// 				return $this->terminateWithError("in generate alias " . $result, self::ERROR_TYPE_GENERAL);
	// 			}
	// 			$alias = getData($result) ?: null;
	// 			$alias = $alias->retval[0];

	// 			$sanitizedVorname = $this->_sanitizeAliasName($alias->vorname);
	// 			$sanitizedNachname = $this->_sanitizeAliasName($alias->nachname);

	// 			// Erstelle den Alias
	// 			$alias = $sanitizedVorname . '.' . $sanitizedNachname;

	// 			if (isError($result)) {
	// 				return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
	// 			}

	// 			$result = $this->BenutzerModel->insert(
	// 				[
	// 					'person_id' => $person_id,
	// 					'uid' => $uidStudent,
	// 					'aktiv' => true,
	// 					'aktivierungscode' => $aktivierungscode,
	// 					'alias' => $alias,
	// 					'insertvon' => $uid,
	// 					'insertamum' => date('c'),
	// 				]
	// 			);

	// 			if (isError($result)) {
	// 				return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
	// 			}

	// 		} else
	// 			return $this->terminateWithError("Benutzer already existing" . $person_id, self::ERROR_TYPE_GENERAL);

	// 		//Student anlegen
	// 		$result = $this->StudentModel->insert(
	// 			[
	// 				'student_uid' => $uidStudent,
	// 				'prestudent_id' => $prestudent_id,
	// 				'matrikelnr' => $matrikelnr,
	// 				'studiengang_kz' => $stg,
	// 				'semester' => $ausbildungssemester,
	// 				'verband' => '',
	// 				'gruppe' => '',
	// 				'insertvon' => $uid,
	// 				'insertamum' => date('c')
	// 			]
	// 		);
	// 		if (isError($result))
	// 		{
	// 			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
	// 		}

	// 		$this->load->library('PrestudentLib');
	// 		$result = $this->prestudentlib->setFirstStudent($prestudent_id, $studiensemester_kurzbz, $ausbildungssemester, null, $bestaetigtam, $bestaetigtvon, $stg, $uidStudent);

	// 		if (isError($result)) {
	// 			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
	// 		} else
	// 			$this->terminateWithSuccess($prestudent_id);

	// 	}
	// 	else
	// 		return $this->terminateWithError("addNewStatus(student)", self::ERROR_TYPE_GENERAL);
	// }

	public function addStudent($prestudent_id)
	{
		//get Studiengang von prestudent_id
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->PrestudentModel->addJoin('public.tbl_person p', 'ON (p.person_id = public.tbl_prestudent.person_id)');
		$this->PrestudentModel->addJoin('public.tbl_studiengang sg', 'ON (sg.studiengang_kz = public.tbl_prestudent.studiengang_kz)');
		$result = $this->PrestudentModel->load([
			'prestudent_id' => $prestudent_id,
		]);
		if (isError($result)) {
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		$result = current(getData($result));

		$stg = $result->studiengang_kz;
		$stgkzl = $result->kurzbz;
		$person_id = $result->person_id;

		if (!$this->permissionlib->isBerechtigt('admin', 'suid', $stg) && !$this->permissionlib->isBerechtigt('assistenz', 'suid', $stg)) {
			$result = $this->p->t('lehre', 'error_keineSchreibrechte');

			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$uid = getAuthUID();
		$status_kurzbz = $this->input->post('status_kurzbz');
		$ausbildungssemester = $this->input->post('ausbildungssemester');
		$bestaetigtam = $this->input->post('bestaetigtam');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		$bestaetigtvon = $uid;
		$studienplan_id = $this->input->post('studienplan_id');
		$statusgrund_id = $this->input->post('statusgrund_id');

		//Form Validation
		$this->load->library('form_validation');

		$this->form_validation->set_rules('ausbildungssemester', 'Ausbildungssemester', 'integer', [
			'integer' => $this->p->t('ui', 'error_fieldNotInteger', ['field' => 'Ausbildungssemester'])
		]);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		//GET lastStatus
		$result = $this->PrestudentstatusModel->getLastStatus($prestudent_id);

		if (isError($result)) {
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		} elseif (!hasData($result)) {
			$lastStatusData = [];
		} else
			$lastStatusData = current(getData($result));

		//check if studentrolle already exists
		$this->load->model('crm/Student_model', 'StudentModel');
		$result = $this->StudentModel->checkIfExistingStudentRolle($prestudent_id);
		if (isError($result)) {
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		if ($result->retval == "0") {
			$isStudent = false;
		} else
			$isStudent = true;


		//TODO(manu) check if for ersterStudent and Student
		if(REIHUNGSTEST_CHECK)
		{
			if($status_kurzbz==Prestudentstatus_model::STATUS_BEWERBER)
			{
				$result = $this->prestudentstatuschecklib->checkIfAngetreten($prestudent_id);
				if (isError($result))
				{
					return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
				}
			}
		}

		//Check ZGV
		if(!defined("ZGV_CHECK") || ZGV_CHECK)
		{
			if($status_kurzbz==Prestudentstatus_model::STATUS_BEWERBER)
			{
				$result = $this->prestudentstatuschecklib->checkIfZGVEingetragen($prestudent_id);
				if (isError($result))
				{
					return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
				}
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
		//$stgkzl = $result->kurzbz;

		if(!defined("ZGV_CHECK") || ZGV_CHECK)
		{
			if($status_kurzbz==Prestudentstatus_model::STATUS_BEWERBER && $typ=='m')
			{
				$result = $this->prestudentstatuschecklib->checkIfZGVEingetragen($prestudent_id, $typ);
				if (isError($result))
				{
					return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
				}
			}
		}

		//check if bewerberstatus exists
		if($status_kurzbz == Prestudentstatus_model::STATUS_AUFGENOMMENER || $status_kurzbz == Prestudentstatus_model::STATUS_WARTENDER ||$status_kurzbz == Prestudentstatus_model::STATUS_STUDENT)
		{

			$result = $this->prestudentstatuschecklib->checkIfExistingBewerberstatus($prestudent_id);
			if (isError($result))
			{
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
		}

		$isBerechtigtNoStudstatusCheck =  $this->permissionlib->isBerechtigt('student/keine_studstatuspruefung');

		if(!$isBerechtigtNoStudstatusCheck) {
			//Block STATUSCHECKS
			$new_status_datum = isset($datum) ? $datum : date('Y-m-d');

			$result = $this->prestudentstatuschecklib->checkStatusAdd(
				$prestudent_id,
				$status_kurzbz,
				$studiensemester_kurzbz,
				$new_status_datum,
				$ausbildungssemester,
				$studienplan_id
			);

			if (isError($result)) {
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}

			//TODO(manu) check Berechtigung
			//check if Bismeldestichtag erreicht ADDNEW status
			$result = $this->prestudentstatuschecklib->checkIfMeldestichtagErreicht($new_status_datum, $studiensemester_kurzbz);

			if (isError($result)) {
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
		}

		if(!$isStudent)
		{
			//checkIf Kaution bezahlt
			//TODO(Manu)

			//check if Aufgenommenener Status
			$result = $this->prestudentstatuschecklib->checkIfExistingAufgenommenerstatus($prestudent_id);
			if (isError($result)) {
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}

			//getSemester of Status Aufgenommen
			$result = $this->PrestudentstatusModel->loadWhere([
				"prestudent_id" => $prestudent_id,
				"status_kurzbz" => Prestudentstatus_model::STATUS_AUFGENOMMENER
				]);
				//$this->PrestudentstatusModel->addOrder('ausbildungssemester', 'DESC');
				$this->PrestudentstatusModel->addLimit(1);
			if (isError($result)) 
			{
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
			$semesterAufgenommen = current(getData($result));

			//generate Personenkennzeichen(matrikelnr)
			$matrikelnr = false;
			Events::trigger('generate_personenkennzeichen', $stg, $semesterAufgenommen, $typ, function ($value) use ($matrikelnr) {
				$matrikelnr = $value;
			});
			if ($matrikelnr === false) {
				$resultMat = $this->StudentModel->generateMatrikelnummer2($stg, $semesterAufgenommen->studiensemester_kurzbz, $typ);
				if (isError($resultMat)) {
					return $this->terminateWithError($resultMat, self::ERROR_TYPE_GENERAL);
				}
				$matrikelnr = getData($resultMat);
			}
			$jahr = mb_substr($semesterAufgenommen, 4, 2);

			//generate UID
			$resultUid = $this->StudentModel->generateUID($stgkzl, $jahr, $typ, $matrikelnr);
			if (isError($resultMat)) {
				return $this->terminateWithError(getData($resultUid), self::ERROR_TYPE_GENERAL);
			}
			$uidStudent = getData($resultUid);

			//Check for additional logic (config entries, addons)
			//TODO(Manu) check include/tw/generatematrikelnr.inc.php
			//(1) addons durchsuchen, ob eigene Logik für Matrikelnr-erstellung existiert
			//Default: keine Matrikelnummer wird generiert

			//(2) personenkz = uid
			if (defined('SET_UID_AS_PERSONENKENNZEICHEN') && SET_UID_AS_PERSONENKENNZEICHEN) {
				$matrikelnr = $uidStudent;
			}

			//(3) Matrikelnummer = uid
			if (defined('SET_UID_AS_MATRIKELNUMMER') && SET_UID_AS_MATRIKELNUMMER) {
				//update person
				$result = $this->PersonModel->update(
					[
						'person_id' => $person_id,
					],
					[
						'matr_nr' => $uidStudent,
					]
				);
				if (isError($result)) {
					return $this->terminateWithError("uidAsMatrikelnummer" . getError($result), self::ERROR_TYPE_GENERAL);
				}
			}

			//add benutzerdatensatz mit Aktierungscode
			$this->load->model('person/Benutzer_model', 'BenutzerModel');
			$result = $this->BenutzerModel->checkIfExistingBenutzer($uidStudent);
			if (isError($result)) {
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
			if ($result->retval != "0")
			{
				return $this->terminateWithError($this->p->t('lehre','error_benutzerBereitsVorhanden', self::ERROR_TYPE_GENERAL));
			}

			$aktivierungscode = null;
			$result = $this->BenutzerModel->generateActivationKey();
			if (isError($result)) {
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
			$aktivierungscode = getData($result);

			//generate Alias
			$result = $this->PersonModel->generateAliasByPersonId($person_id);

			if (isError($result))
			{
				return $this->terminateWithError(getData($result), self::ERROR_TYPE_GENERAL);
			}

			$alias = getData($result) ?: null;
			$alias = $alias->retval[0];

			$sanitizedVorname = $this->_sanitizeAliasName($alias->vorname);
			$sanitizedNachname = $this->_sanitizeAliasName($alias->nachname);
			$alias = $sanitizedVorname . '.' . $sanitizedNachname;

			if (isError($result))
			{
				return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
			}
			$result = $this->BenutzerModel->insert(
				[
					'person_id' => $person_id,
					'uid' => $uidStudent,
					'aktiv' => true,
					'aktivierungscode' => $aktivierungscode,
					'alias' => $alias,
					'insertvon' => $uid,
					'insertamum' => date('c'),
				]
			);

			if (isError($result)) {
				return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
			}

			//Student anlegen
			$result = $this->StudentModel->insert(
				[
					'student_uid' => $uidStudent,
					'prestudent_id' => $prestudent_id,
					'matrikelnr' => $matrikelnr,
					'studiengang_kz' => $stg,
					'semester' => $ausbildungssemester,
					'verband' => '',
					'gruppe' => '',
					'insertvon' => $uid,
					'insertamum' => date('c')
				]
			);
			if (isError($result))
			{
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}

			$this->load->library('PrestudentLib');
			$result = $this->prestudentlib->setFirstStudent($prestudent_id, $studiensemester_kurzbz, $ausbildungssemester, null, $bestaetigtam, $bestaetigtvon, $stg, $uidStudent);

			if (isError($result)) {
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			} else
				$this->terminateWithSuccess($prestudent_id);

		}
		//if not ersterStudent
		else
		{
			//check if studentrolle already exists
			if($status_kurzbz == Prestudentstatus_model::STATUS_STUDENT || $status_kurzbz == Prestudentstatus_model::STATUS_DIPLOMAND || $lastStatusData->status_kurzbz == Prestudentstatus_model::STATUS_STUDENT)
			{
				$result = $this->prestudentstatuschecklib->checkIfExistingStudentRolle($prestudent_id);
				if (isError($result))
				{
					return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
				}
			}

			$this->load->library('PrestudentLib');
			$result = $this->prestudentlib->setStudent($prestudent_id, $studiensemester_kurzbz, $ausbildungssemester, $statusgrund_id, $bestaetigtam, $bestaetigtvon);

			if (isError($result))
			{
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
			else
				$this->terminateWithSuccess($prestudent_id);
		}
	}

	public function loadStatus()
	{
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$prestudent_id = $this->input->post('prestudent_id');
		$status_kurzbz = $this->input->post('status_kurzbz');
		$ausbildungssemester = $this->input->post('ausbildungssemester');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');

		$result = $this->PrestudentstatusModel->loadWhere(
			array(
				'prestudent_id' => $prestudent_id,
				'status_kurzbz' => $status_kurzbz,
				'ausbildungssemester' => $ausbildungssemester,
				'studiensemester_kurzbz' => $studiensemester_kurzbz
			)
		);
		if (isError($result))
		{
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		elseif (!hasData($result))
		{
			$this->terminateWithError($this->p->t('lehre','error_noStatusFound'), self::ERROR_TYPE_GENERAL);
		}
		else
		{
			$this->terminateWithSuccess(current(getData($result)));
		}
	}

	public function deleteStatus()
	{
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$prestudent_id = $this->input->post('prestudent_id');
		$status_kurzbz = $this->input->post('status_kurzbz');
		$ausbildungssemester = $this->input->post('ausbildungssemester');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		$deletePrestudent = false;

		//get Studiengang von prestudent_id
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$result = $this->PrestudentModel->load([
			'prestudent_id'=> $prestudent_id,
		]);
		if(isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$result = current(getData($result));

		$stg = $result->studiengang_kz;

		$isBerechtigtAdmin = $this->permissionlib->isBerechtigt('admin', null, 'suid');
		$isBerechtigtNoStudstatusCheck = $this->permissionlib->isBerechtigt('student/keine_studstatuspruefung', null, 'suid');
		$isBerechtigtToDeleteAbgewiesen = $this->permissionlib->isBerechtigt('lehre/reihungstestAufsicht') && $status_kurzbz == Prestudentstatus_model::STATUS_ABGEWIESENER;

		if(!$this->permissionlib->isBerechtigt('admin', 'suid', $stg) && !$this->permissionlib->isBerechtigt('assistenz', 'suid', $stg) && !$isBerechtigtToDeleteAbgewiesen)
		{
			return $this->terminateWithError($this->p->t('lehre','error_keineSchreibrechte'), self::ERROR_TYPE_GENERAL);
		}

		if($status_kurzbz==Prestudentstatus_model::STATUS_STUDENT && !$isBerechtigtAdmin && !$isBerechtigtNoStudstatusCheck)
		{
			return $this->terminateWithError($this->p->t('lehre','error_onlyAdminDeleteRolleStudent'), self::ERROR_TYPE_GENERAL);
		}

		//check if last status
		$result = $this->PrestudentstatusModel->checkIfLastStatusEntry($prestudent_id);

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if($result->retval == "1")
		{
			//Berechtigungen nach Check prüfen!
			if(!$isBerechtigtAdmin && !$isBerechtigtNoStudstatusCheck)
			{
				return $this->terminateWithError($this->p->t('lehre','error_onlyAdminDeleteLastStatus'), self::ERROR_TYPE_GENERAL);
			}
			else
			{
				$deletePrestudent = true;
			}
		}

		// Start DB transaction
		$this->db->trans_begin();

		//load rolle für LOG sqlundo
		$result = $this->PrestudentstatusModel->loadWhere(
			array(
				'prestudent_id' => $prestudent_id,
				'status_kurzbz' => $status_kurzbz,
				'ausbildungssemester' => $ausbildungssemester,
				'studiensemester_kurzbz' => $studiensemester_kurzbz
			)
		);

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result)) {
			return $this->terminateWithError($this->p->t('lehre','error_noStatusFound'), self::ERROR_TYPE_GENERAL);

		}

		//Data of current Rolle
		$statusData = current(getData($result));

		$datum = $statusData->datum == '' ? 'null' : $statusData->datum;
		$insertamum = $statusData->insertamum == '' ? 'null' : $statusData->insertamum;
		$insertvon = $statusData->insertvon == '' ? 'null' : $statusData->insertvon;
		$updateamum = $statusData->updateamum == '' ? 'null' : $statusData->updateamum;
		$updatevon = $statusData->updatevon == '' ? 'null' : $statusData->updatevon;
		$ext_id = $statusData->ext_id == '' ? 'null' : $statusData->ext_id;
		$orgform_kurzbz = $statusData->orgform_kurzbz == '' ? 'null' : $statusData->orgform_kurzbz;
		$bestaetigtam = $statusData->bestaetigtam == '' ? 'null' : $statusData->bestaetigtam;
		$bestaetigtvon = $statusData->bestaetigtvon == '' ? 'null' : $statusData->bestaetigtvon;
		$anmerkung = $statusData->anmerkung == '' ? 'null' : $statusData->anmerkung;
		$bewerbung_abgeschicktamum = $statusData->bewerbung_abgeschicktamum == '' ? 'null' : $statusData->bewerbung_abgeschicktamum;
		$studienplan_id = $statusData->studienplan_id == '' ? 'null' : $statusData->studienplan_id;
		$rt_stufe = $statusData->rt_stufe == '' ? 'null' : $statusData->rt_stufe;
		$statusgrund_id = $statusData->statusgrund_id == '' ? 'null' : $statusData->statusgrund_id;

		$quotes_datum = $datum == "null" ? " " : "'";
		$quotes_insertamum = $insertamum == "null" ? " " : "'";
		$quotes_insertvon = $insertvon == "null" ? " " : "'";
		$quotes_updateamum = $updateamum == "null" ? " " : "'";
		$quotes_updatevon = $updatevon == "null" ? " " : "'";
		$quotes_ext_id = $ext_id == "null" ? " " : "'";
		$quotes_orgform_kurzbz = $orgform_kurzbz == "null" ? " " : "'";
		$quotes_bestaetigtam = $bestaetigtam == "null" ? " " : "'";
		$quotes_bestaetigtvon = $bestaetigtvon == "null" ? " " : "'";
		$quotes_anmerkung = $anmerkung == "null" ? " " : "'";
		$quotes_bewerbung_abgeschicktamum = $bewerbung_abgeschicktamum == "null" ? " " : "'";
		$quotes_studienplan_id = $studienplan_id == "null" ? " " : "'";
		$quotes_rt_stufe = $rt_stufe == "null" ? " " : "'";
		$quotes_statusgrund_id = $statusgrund_id == "null" ? " " : "'";

		$sqlundo =
			"
			INSERT INTO public.tbl_prestudentstatus(prestudent_id, status_kurzbz, studiensemester_kurzbz, ausbildungssemester,
				datum, insertamum, insertvon, updateamum, updatevon, ext_id, orgform_kurzbz, bestaetigtam, bestaetigtvon,
				anmerkung, bewerbung_abgeschicktamum, studienplan_id,  rt_stufe, statusgrund_id)
			VALUES('" . $prestudent_id . "','" . $status_kurzbz . "','" . $studiensemester_kurzbz . "','" . $ausbildungssemester . "',"
			. $quotes_datum . $datum . $quotes_datum . ","
			. $quotes_insertamum . $insertamum . $quotes_insertamum . ","
			. $quotes_insertvon . $insertvon . $quotes_insertvon . ","
			. $quotes_updateamum . $updateamum . $quotes_updateamum . ","
			. $quotes_updatevon . $updatevon . $quotes_updatevon . ","
			. $quotes_ext_id . $ext_id . $quotes_ext_id . ","
			. $quotes_orgform_kurzbz . $orgform_kurzbz . $quotes_orgform_kurzbz . ","
			. $quotes_bestaetigtam . $bestaetigtam . $quotes_bestaetigtam . ","
			. $quotes_bestaetigtvon . $bestaetigtvon . $quotes_bestaetigtvon . ","
			. $quotes_anmerkung . $anmerkung . $quotes_anmerkung . ","
			. $quotes_bewerbung_abgeschicktamum . $bewerbung_abgeschicktamum . $quotes_bewerbung_abgeschicktamum . ","
			. $quotes_studienplan_id . $studienplan_id . $quotes_studienplan_id . ","
			. $quotes_rt_stufe . $rt_stufe . $quotes_rt_stufe . ","
			. $quotes_statusgrund_id . $statusgrund_id . $quotes_statusgrund_id . ");
			";

		//Delete Status
		$result = $this->PrestudentstatusModel->delete(
			[
				'prestudent_id' => $prestudent_id,
				'status_kurzbz' => $status_kurzbz,
				'ausbildungssemester' => $ausbildungssemester,
				'studiensemester_kurzbz' => $studiensemester_kurzbz
			]
		);

		if($this->db->trans_status() === false || isError($result))
		{
			$this->db->trans_rollback();
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if(!hasData($result))
		{
			return $this->terminateWithError($this->p->t('lehre','error_noStatusFound'), self::ERROR_TYPE_GENERAL);

		}

		//save log delete prestudentstatus
		$uid = getAuthUID();
		$beschreibung = 'Loeschen der Rolle ' . $status_kurzbz . " bei " . $prestudent_id;
		$sql = "DELETE FROM public.tbl_prestudentstatus
                WHERE prestudent_id = " . $prestudent_id . "
			  AND status_kurzbz = '" . $status_kurzbz . "'
			  AND ausbildungssemester ='" . $ausbildungssemester . "'
			  AND studiensemester_kurzbz = '" . $studiensemester_kurzbz . "'";

		$this->load->model('system/Log_model', 'LogModel');
		$result = $this->LogModel->insert([
			'mitarbeiter_uid' => $uid,
			'beschreibung' => $beschreibung,
			'sql' => $sql,
			'sqlundo' => $sqlundo
		]);
		if (isError($result) || !hasData($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$logId = $result->retval;

		//Delete Studentlehrverband if no Status left
		$result = $this->PrestudentstatusModel->checkIfLastStatusEntry($prestudent_id, true);

		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		if ($result->retval == "1")
		{
			//get student_uid
			$this->load->model('crm/Student_model', 'StudentModel');
			$result = $this->StudentModel->checkIfUid($prestudent_id);
			if (isError($result))
			{
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
			if (!hasData($result))
			{
				return $this->terminateWithError($this->p->t('studierendenantrag','error_no_student_for_prestudent',['prestudent_id' => $prestudent_id]), self::ERROR_TYPE_GENERAL);
			}
			$student_uid = $result->retval;

			$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
			$result = $this->StudentlehrverbandModel->delete(
				array(
					'student_uid' => $student_uid,
					'studiensemester_kurzbz' => $studiensemester_kurzbz
				)
			);

			if ($this->db->trans_status() === false || isError($result) || !hasData($result))
			{
				$this->db->trans_rollback();

				return $this->terminateWithError($this->p->t('lehre','error_duringDeleteLehrverband'), self::ERROR_TYPE_GENERAL);
			}

		}
		//Delete Prestudent if no data is left
		if($deletePrestudent && $isBerechtigtAdmin)
		{
			//TODO(manu) check all connected tables, Handling of Deletion
			//check if existing dokumentprestudent
			$this->load->model('crm/Dokumentprestudent_model', 'DokumentprestudentModel');
			$result = $this->DokumentprestudentModel->loadWhere(
				array(
					'prestudent_id' => $prestudent_id
				)
			);
			if (isError($result))
			{
				$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);

			}
			if (hasData($result))
			{
				return $this->terminateWithError("Es sind noch zu loeschende Dokumente vorhanden: ", self::ERROR_TYPE_GENERAL);
			}

			//check if Anrechnungen tbl_anrechnung
			$output_anrechnungen = '';
			$this->load->model('education/Anrechnung_model', 'AnrechnungModel');
			$result = $this->AnrechnungModel->loadWhere(
				array(
					'prestudent_id' => $prestudent_id
				)
			);
			if (isError($result))
			{
				$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
			}
			if (hasData($result))
			{
				return $this->terminateWithError("Mit dieser Prestudent_id sind verbundene Anrechnungen vorhanden!", self::ERROR_TYPE_GENERAL);
			}

			//DELETE Prestudent

			//save log delete prestudent
			$uid = getAuthUID();
			$beschreibung = 'Loeschen der Prestudent ID ' . $prestudent_id;
			$sql = "DELETE FROM public.tbl_prestudent
                WHERE prestudent_id = " . $prestudent_id;

			//load prestudent für LOG sqlundo
			$this->load->model('crm/Prestudent_model', 'PrestudentModel');
			$result = $this->PrestudentModel->loadWhere(
				array(
					'prestudent_id' => $prestudent_id
				)
			);
			if (isError($result) || !hasData($result))
			{
				//TODO(Manu) in this case: löschen status erlauben aber rückmeldung, dass Prestudent nicht gelöscht wurde
				return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
			}

			//Data of current Prestudent
			$prestudentData = current(getData($result));

			$aufmerksamdurch_kurzbz = $prestudentData->aufmerksamdurch_kurzbz == '' ? 'null' : $prestudentData->aufmerksamdurch_kurzbz;
			$studiengang_kz = $prestudentData->studiengang_kz == '' ? 'null' : $prestudentData->studiengang_kz;
			$berufstaetigkeit_code = $prestudentData->berufstaetigkeit_code == '' ? 'null' : $prestudentData->berufstaetigkeit_code;
			$ausbildungcode = $prestudentData->ausbildungcode == '' ? 'null' : $prestudentData->ausbildungcode;
			$zgv_code = $prestudentData->zgv_code == '' ? 'null' : $prestudentData->zgv_code;
			$zgvort = $prestudentData->zgvort == '' ? 'null' : $prestudentData->zgvort;
			$zgvdatum = $prestudentData->zgvdatum == '' ? 'null' : $prestudentData->zgvdatum;
			$zgvnation = $prestudentData->zgvnation == '' ? 'null' : $prestudentData->zgvnation;
			$zgv_erfuellt = $prestudentData->zgv_erfuellt == '' ? 'null' : $prestudentData->zgv_erfuellt;
			$zgvmas_code = $prestudentData->zgvmas_code == '' ? 'null' : $prestudentData->zgvmas_code;
			$zgvmaort = $prestudentData->zgvmaort == '' ? 'null' : $prestudentData->zgvmaort;
			$zgvmadatum = $prestudentData->zgvmadatum == '' ? 'null' : $prestudentData->zgvmadatum;
			$zgvmanation = $prestudentData->zgvmanation == '' ? 'null' : $prestudentData->zgvmanation;
			$zgvmas_erfuellt = $prestudentData->zgvmas_erfuellt == '' ? 'null' : $prestudentData->zgvmas_erfuellt;
			$aufnahmeschluessel = $prestudentData->aufnahmeschluessel == '' ? 'null' : $prestudentData->aufnahmeschluessel;
			$facheinschlberuf = $prestudentData->facheinschlberuf == '' ? 'null' : $prestudentData->facheinschlberuf;
			$anmeldungreihungstest = $prestudentData->anmeldungreihungstest == '' ? 'null' : $prestudentData->anmeldungreihungstest;
			$reihungstestangetreten = $prestudentData->reihungstestangetreten == 1 ? 'true' : 'false';
			$reihungstest_id = $prestudentData->reihungstest_id == '' ? 'null' : $prestudentData->reihungstest_id;
			$rt_gesamtpunkte = $prestudentData->rt_gesamtpunkte == '' ? 'null' : $prestudentData->rt_gesamtpunkte;
			$rt_punkte1 = $prestudentData->rt_punkte1 == '' ? 'null' : $prestudentData->rt_punkte1;
			$rt_punkte2 = $prestudentData->rt_punkte2 == '' ? 'null' : $prestudentData->rt_punkte2;
			$rt_punkte3 = $prestudentData->rt_punkte3 == '' ? 'null' : $prestudentData->rt_punkte3;
			$bismelden = $prestudentData->bismelden == 1 ? 'true' : 'false';
			$person_id = $prestudentData->person_id == '' ? 'null' : $prestudentData->person_id;
			$anmerkung = $prestudentData->anmerkung == '' ? 'null' : $prestudentData->anmerkung;
			$mentor = $prestudentData->mentor == '' ? 'null' : $prestudentData->mentor;
			$ext_id = $prestudentData->ext_id == '' ? 'null' : $prestudentData->ext_id;
			$dual = $prestudentData->dual == 1 ? 'true' : 'false';
			$ausstellungsstaat = $prestudentData->ausstellungsstaat == '' ? 'null' : $prestudentData->ausstellungsstaat;
			$zgvdoktor_code = $prestudentData->zgvdoktor_code == '' ? 'null' : $prestudentData->zgvdoktor_code;
			$zgvdoktorort = $prestudentData->zgvdoktorort == '' ? 'null' : $prestudentData->zgvdoktorort;
			$zgvdoktordatum = $prestudentData->zgvdoktordatum == '' ? 'null' : $prestudentData->zgvdoktordatum;
			$zgvdoktornation = $prestudentData->zgvdoktornation == '' ? 'null' : $prestudentData->zgvdoktornation;
			$gsstudientyp_kurzbz = $prestudentData->gsstudientyp_kurzbz == '' ? 'null' : $prestudentData->gsstudientyp_kurzbz;
			$aufnahmegruppe_kurzbz = $prestudentData->aufnahmegruppe_kurzbz == '' ? 'null' : $prestudentData->aufnahmegruppe_kurzbz;
			$priorisierung = $prestudentData->priorisierung == '' ? 'null' : $prestudentData->priorisierung;
			$zgvdoktor_erfuellt= $prestudentData->zgvdoktor_erfuellt == '' ? 'null' : $prestudentData->zgvdoktor_erfuellt;

			$quotes_aufmerksamdurch_kurzbz = $aufmerksamdurch_kurzbz == "null" ? " " : "'";
			$quotes_studiengang_kz = $studiengang_kz == "null" ? " " : "'";
			$quotes_berufstaetigkeit_code = $berufstaetigkeit_code == "null" ? " " : "'";
			$quotes_ausbildungcode = $ausbildungcode == "null" ? " " : "'";
			$quotes_zgv_code = $zgv_code == "null" ? " " : "'";
			$quotes_zgvort = $zgvort == "null" ? " " : "'";
			$quotes_zgvdatum = $zgvdatum == "null" ? " " : "'";
			$quotes_zgvnation = $zgvnation == "null" ? " " : "'";
			$quotes_zgv_erfuellt = $zgv_erfuellt == "null" ? " " : "'";
			$quotes_zgvmas_code = $zgvmas_code == "null" ? " " : "'";
			$quotes_zgvmaort = $zgvmaort == "null" ? " " : "'";
			$quotes_zgvmadatum = $zgvmadatum == "null" ? " " : "'";
			$quotes_zgvmanation = $zgvmanation == "null" ? " " : "'";
			$quotes_zgvmas_erfuellt = $zgvmas_erfuellt == "null" ? " " : "'";
			$quotes_aufnahmeschluessel = $aufnahmeschluessel == "null" ? " " : "'";
			$quotes_facheinschlberuf = $facheinschlberuf == "null" ? " " : "'";
			$quotes_anmeldungreihungstest = $anmeldungreihungstest == "null" ? " " : "'";
			$quotes_reihungstestangetreten = $reihungstestangetreten == "null" ? " " : "'";
			$quotes_reihungstest_id = $reihungstest_id == "null" ? " " : "'";
			$quotes_rt_gesamtpunkte = $rt_gesamtpunkte == "null" ? " " : "'";
			$quotes_rt_punkte1 = $rt_punkte1 == "null" ? " " : "'";
			$quotes_rt_punkte2 = $rt_punkte2 == "null" ? " " : "'";
			$quotes_rt_punkte3 = $rt_punkte3 == "null" ? " " : "'";
			$quotes_bismelden = $bismelden == "null" ? " " : "'";
			$quotes_person_id = $person_id == "null" ? " " : "'";
			$quotes_anmerkung = $anmerkung == "null" ? " " : "'";
			$quotes_mentor = $mentor == "null" ? " " : "'";
			$quotes_ext_id = $ext_id == "null" ? " " : "'";
			$quotes_dual = $dual == "null" ? " " : "'";
			$quotes_ausstellungsstaat = $ausstellungsstaat == "null" ? " " : "'";
			$quotes_zgvdoktor_code = $zgvdoktor_code == "null" ? " " : "'";
			$quotes_zgvdoktorort = $zgvdoktorort == "null" ? " " : "'";
			$quotes_zgvdoktordatum = $zgvdoktordatum == "null" ? " " : "'";
			$quotes_zgvdoktornation = $zgvdoktornation == "null" ? " " : "'";
			$quotes_gsstudientyp_kurzbz = $gsstudientyp_kurzbz == "null" ? " " : "'";
			$quotes_aufnahmegruppe_kurzbz = $aufnahmegruppe_kurzbz == "null" ? " " : "'";
			$quotes_priorisierung = $priorisierung == "null" ? " " : "'";
			$quotes_zgvdoktor_erfuellt = $zgvdoktor_erfuellt == "null" ? " " : "'";


			$sqlundo =
				"
				INSERT INTO public.tbl_prestudent(prestudent_id, aufmerksamdurch_kurzbz, studiengang_kz, berufstaetigkeit_code, ausbildungcode,
				zgv_code, zgvort, zgvdatum, zgvnation,zgv_erfuellt, zgvmas_code, zgvmaort, zgvmadatum, zgvmanation,zgvmas_erfuellt,
				aufnahmeschluessel, facheinschlberuf, anmeldungreihungstest, reihungstestangetreten, reihungstest_id,
				rt_gesamtpunkte, rt_punkte1, rt_punkte2, rt_punkte3, bismelden, person_id, anmerkung, mentor, ext_id,
				dual, ausstellungsstaat, zgvdoktor_code, zgvdoktorort, zgvdoktordatum, zgvdoktornation,
				gsstudientyp_kurzbz, aufnahmegruppe_kurzbz, priorisierung, zgvdoktor_erfuellt)
				VALUES('" . $prestudent_id . "',"
				. $quotes_aufmerksamdurch_kurzbz . $aufmerksamdurch_kurzbz . $quotes_aufmerksamdurch_kurzbz . ","
				. $quotes_studiengang_kz . $studiengang_kz . $quotes_studiengang_kz . ","
				. $quotes_berufstaetigkeit_code . $berufstaetigkeit_code . $quotes_berufstaetigkeit_code . ","
				. $quotes_ausbildungcode . $ausbildungcode . $quotes_ausbildungcode . ","
				. $quotes_zgv_code . $zgv_code . $quotes_zgv_code . ","
				. $quotes_zgvort . $zgvort . $quotes_zgvort . ","
				. $quotes_zgvdatum . $zgvdatum . $quotes_zgvdatum . ","
				. $quotes_zgvnation . $zgvnation . $quotes_zgvnation . ","
				. $quotes_zgv_erfuellt . $zgv_erfuellt . $quotes_zgv_erfuellt . ","
				. $quotes_zgvmas_code . $zgvmas_code . $quotes_zgvmas_code . ","
				. $quotes_zgvmaort . $zgvmaort . $quotes_zgvmaort . ","
				. $quotes_zgvmadatum . $zgvmadatum . $quotes_zgvmadatum . ","
				. $quotes_zgvmanation . $zgvmanation . $quotes_zgvmanation . ","
				. $quotes_zgvmas_erfuellt . $zgvmas_erfuellt . $quotes_zgvmas_erfuellt . ","
				. $quotes_aufnahmeschluessel . $aufnahmeschluessel . $quotes_aufnahmeschluessel . ","
				. $quotes_facheinschlberuf . $facheinschlberuf . $quotes_facheinschlberuf . ","
				. $quotes_anmeldungreihungstest . $anmeldungreihungstest . $quotes_anmeldungreihungstest . ","
				. $quotes_reihungstestangetreten . $reihungstestangetreten . $quotes_reihungstestangetreten . ","
				. $quotes_reihungstest_id . $reihungstest_id . $quotes_reihungstest_id . ","
				. $quotes_rt_gesamtpunkte . $rt_gesamtpunkte . $quotes_rt_gesamtpunkte . ","
				. $quotes_rt_punkte1 . $rt_punkte1 . $quotes_rt_punkte1 . ","
				. $quotes_rt_punkte2 . $rt_punkte2 . $quotes_rt_punkte2 . ","
				. $quotes_rt_punkte3 . $rt_punkte3 . $quotes_rt_punkte3 . ","
				. $quotes_bismelden . $bismelden . $quotes_bismelden . ","
				. $quotes_person_id . $person_id . $quotes_person_id . ","
				. $quotes_anmerkung . $anmerkung . $quotes_anmerkung . ","
				. $quotes_mentor . $mentor . $quotes_mentor . ","
				. $quotes_ext_id . $ext_id . $quotes_ext_id . ","
				. $quotes_dual . $dual . $quotes_dual . ","
				. $quotes_ausstellungsstaat . $ausstellungsstaat . $quotes_ausstellungsstaat . ","
				. $quotes_zgvdoktor_code . $zgvdoktor_code . $quotes_zgvdoktor_code . ","
				. $quotes_zgvdoktorort . $zgvdoktorort . $quotes_zgvdoktorort . ","
				. $quotes_zgvdoktordatum . $zgvdoktordatum . $quotes_zgvdoktordatum . ","
				. $quotes_zgvdoktornation . $zgvdoktornation . $quotes_zgvdoktornation . ","
				. $quotes_gsstudientyp_kurzbz . $gsstudientyp_kurzbz . $quotes_gsstudientyp_kurzbz . ","
				. $quotes_aufnahmegruppe_kurzbz . $aufnahmegruppe_kurzbz . $quotes_aufnahmegruppe_kurzbz . ","
				. $quotes_priorisierung . $priorisierung . $quotes_priorisierung . ","
				. $quotes_zgvdoktor_erfuellt . $zgvdoktor_erfuellt . $quotes_zgvdoktor_erfuellt . ");
			";

			$this->load->model('system/Log_model', 'LogModel');
			$result = $this->LogModel->insert([
				'mitarbeiter_uid' => $uid,
				'beschreibung' => $beschreibung,
				'sql' => $sql,
				'sqlundo' => $sqlundo
			]);
			if (isError($result) || !hasData($result))
			{
				return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
			}

			//$logId = $result->retval;

			//independent transaction
			$this->db->trans_start();

			$this->load->model('crm/Prestudent_model', 'PrestudentModel');
			$result = $this->PrestudentModel->delete(
				array(
					'prestudent_id' => $prestudent_id
				)
			);
			if ($this->db->trans_status() === false)
			{
				$this->db->trans_rollback();
				$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
			}
			$this->db->trans_commit();
			$this->terminateWithSuccess($result);
		}
		$this->db->trans_commit();

		return $this->terminateWithSuccess(true);
	}

	public function updateStatus($key_prestudent_id, $key_status_kurzbz, $key_studiensemester_kurzbz, $key_ausbildungssemester)
	{
		$isStudent = false;
		$isBerechtigtNoStudstatusCheck =  $this->permissionlib->isBerechtigt('student/keine_studstatuspruefung');

		//get Studiengang von prestudent_id
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$result = $this->PrestudentModel->load([
			'prestudent_id'=> $key_prestudent_id,
		]);
		if(isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$result = current(getData($result));

		$stg = $result->studiengang_kz;

		if (!$this->permissionlib->isBerechtigt('admin', 'suid', $stg) && !$this->permissionlib->isBerechtigt('assistenz', 'suid', $stg))
		{
			return $this->terminateWithError($this->p->t('lehre','error_keineSchreibrechte'), self::ERROR_TYPE_GENERAL);
		}

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

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

		//check if Bismeldestichtag erreicht
		if(!$isBerechtigtNoStudstatusCheck)
		{
			$result = $this->prestudentstatuschecklib->checkIfMeldestichtagErreicht($datum);
			if (isError($result))
			{
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
		}

		//check if Rolle already exists
		if(($key_studiensemester_kurzbz != $studiensemester_kurzbz)
			|| ($key_ausbildungssemester != $ausbildungssemester))
		{
			$result = $this->prestudentstatuschecklib->checkIfExistingPrestudentRolle(
				$prestudent_id,
				$status_kurzbz,
				$studiensemester_kurzbz,
				$ausbildungssemester
			);
			if (isError($result))
			{
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
		}

		//check if studentrolle already exists
		if($status_kurzbz == Prestudentstatus_model::STATUS_STUDENT || $status_kurzbz == Prestudentstatus_model::STATUS_DIPLOMAND )
		
		//TODO(manu): check why $lastStatusDAta
		// if($status_kurzbz == Prestudentstatus_model::STATUS_STUDENT || $status_kurzbz == Prestudentstatus_model::STATUS_DIPLOMAND || $lastStatusData->status_kurzbz == Prestudentstatus_model::STATUS_STUDENT)
		{
			$result = $this->prestudentstatuschecklib->checkIfExistingStudentRolle($prestudent_id);
			if (isError($result))
			{
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
			$isStudent = true;
		}


		if(!$isBerechtigtNoStudstatusCheck)
		{
			//Block STATUSCHECKS

			$result = $this->prestudentstatuschecklib->checkStatusUpdate(
				$prestudent_id,
				$status_kurzbz,
				$studiensemester_kurzbz,
				$datum,
				$ausbildungssemester,
				$studienplan_id,
				$key_studiensemester_kurzbz,
				$key_ausbildungssemester
			);

			if (isError($result))
			{
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
		}


		// Start DB transaction
		$this->db->trans_begin();

		//update status
		$result = $this->PrestudentstatusModel->update(
			[
				'prestudent_id' => $key_prestudent_id,
				'status_kurzbz' => $key_status_kurzbz,
				'studiensemester_kurzbz' => $key_studiensemester_kurzbz,
				'ausbildungssemester' => $key_ausbildungssemester,
			],
			[
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
			$this->db->trans_rollback();
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}


		if($isStudent)
		{
			//check Studentlehrverband
			$this->load->model('crm/Student_model', 'StudentModel');
			$result = $this->StudentModel->checkIfUid($prestudent_id);
			if (isError($result))
			{
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
			if (!hasData($result))
			{
				$this->terminateWithError($this->p->t('studierendenantrag','error_no_student_for_prestudent',['prestudent_id' => $prestudent_id]), self::ERROR_TYPE_GENERAL);
			}
			$student_uid = $result->retval;

			//process studentlehrverband
			$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
			$result = $this->StudentlehrverbandModel->loadWhere(
				array(
					'student_uid' => $student_uid,
					'studiensemester_kurzbz' => $studiensemester_kurzbz
				)
			);
			if (isError($result))
			{
				$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
			}

			//Data of current Semester
			$studentlvbData = current(getData($result) ? : []);

			$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
			$result = $this->StudentlehrverbandModel->processStudentlehrverband(
				$student_uid,
				$studentlvbData->studiengang_kz,
				$ausbildungssemester,
				$studentlvbData->verband,
				$studentlvbData->gruppe,
				$studiensemester_kurzbz
			);
			if ($this->db->trans_status() === false || isError($result))
			{
				$this->db->trans_rollback();
				return $this->terminateWithError($this->p->t('lehre','error_duringInsertUpdateLehrverband'), self::ERROR_TYPE_GENERAL);
			}
		}
		$this->db->trans_commit();
		return $this->outputJsonSuccess(true);
	}

	public function advanceStatus($key_prestudent_id, $key_status_kurzbz, $key_studiensemester_kurzbz, $key_ausbildungssemester)
	{
		//get Studiengang von prestudent_id
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$result = $this->PrestudentModel->load([
			'prestudent_id'=> $key_prestudent_id,
		]);
		if(isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$result = current(getData($result));

		$stg = $result->studiengang_kz;

		if(!$this->permissionlib->isBerechtigt('admin', 'suid', $stg) && !$this->permissionlib->isBerechtigt('assistenz', 'suid', $stg))
		{
			return $this->terminateWithError($this->p->t('lehre','error_keineSchreibrechte'), self::ERROR_TYPE_GENERAL);
		}

		//Data Vorrücken
		$result = $this->PrestudentstatusModel->loadWhere(
			array(
				'prestudent_id' => $key_prestudent_id,
				'status_kurzbz' => $key_status_kurzbz,
				'ausbildungssemester' => $key_ausbildungssemester,
				'studiensemester_kurzbz' => $key_studiensemester_kurzbz
			)
		);

		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		elseif (!hasData($result))
		{
			return $this->terminateWithError($this->p->t('lehre','error_noStatusFound'), self::ERROR_TYPE_GENERAL);
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
		$result = $this->prestudentstatuschecklib->checkIfExistingPrestudentRolle(
			$key_prestudent_id,
			$key_status_kurzbz,
			$studiensem_next,
			$ausbildungssem_next
		);
		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		//check if studentrolle already exists
		// if($key_status_kurzbz == Prestudentstatus_model::STATUS_STUDENT)
		// {
		// 	$this->load->model('crm/Student_model', 'StudentModel');
		// 	$result = $this->StudentModel->checkIfExistingStudentRolle($key_prestudent_id);
		// 	if (isError($result))
		// 	{
		// 		return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		// 	}
		// 	if ($result->retval == "0")
		// 	{
		// 		return $this->terminateWithError($this->p->t('lehre','error_noStudstatus'), self::ERROR_TYPE_GENERAL);
		// 	}
		// }
		if($key_status_kurzbz == Prestudentstatus_model::STATUS_STUDENT || $key_status_kurzbz == Prestudentstatus_model::STATUS_DIPLOMAND || $lastStatusData->status_kurzbz == Prestudentstatus_model::STATUS_STUDENT)
		{
			$result = $this->prestudentstatuschecklib->checkIfExistingStudentRolle($key_prestudent_id);
			if (isError($result))
			{
				return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
		}

		// Start DB transaction
		$this->db->trans_begin();

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

		if (isError($result))
		{
			$this->db->trans_rollback();
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$this->load->model('crm/Student_model', 'StudentModel');
		$result = $this->StudentModel->checkIfUid($key_prestudent_id);
		if(isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result))
		{
			$this->terminateWithError($this->p->t('studierendenantrag','error_no_student_for_prestudent', ['prestudent_id' => $prestudent_id]), self::ERROR_TYPE_GENERAL);
		}
		$student_uid = $result->retval;

		//process studentlehrverband
		$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
		$result = $this->StudentlehrverbandModel->loadWhere(
			array(
				'student_uid' => $student_uid,
				'studiensemester_kurzbz' => $key_studiensemester_kurzbz
			)
		);
		if (isError($result))
		{
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		//Data of current Semester
		$result = getData($result) ? : [];
		$studentlvbData = current($result);

		$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
		$result = $this->StudentlehrverbandModel->processStudentlehrverband(
			$student_uid,
			$studentlvbData->studiengang_kz,
			$ausbildungssem_next,
			$studentlvbData->verband,
			$studentlvbData->gruppe,
			$studiensem_next
		);
		if ($this->db->trans_status() === false || isError($result))
		{
			$this->db->trans_rollback();
			return $this->terminateWithError($this->p->t('lehre','error_duringInsertUpdateLehrverband'), self::ERROR_TYPE_GENERAL);
		}

		$this->db->trans_commit();

		return $this->outputJsonSuccess(true);
	}

	public function confirmStatus($key_prestudent_id, $key_status_kurzbz, $key_studiensemester_kurzbz, $key_ausbildungssemester)
	{
		$uid = getAuthUID();

		//get Studiengang von prestudent_id
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$result = $this->PrestudentModel->load([
			'prestudent_id'=> $key_prestudent_id,
		]);
		if(isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$result = current(getData($result));

		$stg = $result->studiengang_kz;

		if (!$this->permissionlib->isBerechtigt('admin', 'suid', $stg) && !$this->permissionlib->isBerechtigt('assistenz', 'suid', $stg))
		{
			return $this->terminateWithError($this->p->t('lehre','error_keineSchreibrechte'), self::ERROR_TYPE_GENERAL);
		}

		$result = $this->PrestudentstatusModel->loadWhere(
			array(
				'prestudent_id' => $key_prestudent_id,
				'status_kurzbz' => $key_status_kurzbz,
				'ausbildungssemester' => $key_ausbildungssemester,
				'studiensemester_kurzbz' => $key_studiensemester_kurzbz
			)
		);

		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		elseif (!hasData($result))
		{
			return $this->terminateWithError($this->p->t('lehre','error_noStatusFound'), self::ERROR_TYPE_GENERAL);
		}

		$statusData = current(getData($result));

		//check if Status is unconfirmed..
		if($statusData->bestaetigtam != null)
		{
			//return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			return $this->terminateWithError($this->p->t('lehre','error_statusConfirmedYet'), self::ERROR_TYPE_GENERAL);
		}

		//check if Bewerbung abgeschickt
		if($statusData->bewerbung_abgeschicktamum == null)
		{
			return $this->terminateWithError($this->p->t('lehre','error_bewerbungNochNichtAbgeschickt'), self::ERROR_TYPE_GENERAL);
		}

		$result = $this->PrestudentstatusModel->update(
			[
				'prestudent_id' => $key_prestudent_id,
				'status_kurzbz' => $key_status_kurzbz,
				'studiensemester_kurzbz' => $key_studiensemester_kurzbz,
				'ausbildungssemester' => $key_ausbildungssemester,
			],
			[
				'prestudent_id' => $key_prestudent_id,
				'status_kurzbz' => $key_status_kurzbz,
				'studiensemester_kurzbz' => $key_studiensemester_kurzbz,
				'ausbildungssemester' => $key_ausbildungssemester,
				'bestaetigtam' => date('c'),
				'bestaetigtvon' => $uid,
				'updateamum' => date('c'),
				'updatevon' => $uid
			]
		);
		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->outputJsonSuccess(true);
	}

	private function _sanitizeAliasName($str)
	{
		$str = sanitizeProblemChars($str);
		return mb_strtolower(str_replace(' ','_', $str));
	}
}
