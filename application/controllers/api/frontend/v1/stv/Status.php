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
			'getMaxSemester' => ['admin:r', 'assistenz:r'],
			'changeStatus' => ['admin:rw', 'assistenz:rw'],
			'addStudent' => ['admin:rw', 'assistenz:rw'],
			'getStatusgruende' => self::PERM_LOGGED,
			'getLastBismeldestichtag' => self::PERM_LOGGED,
			'isLastStatus' => self::PERM_LOGGED,
			'hasStatusBewerber' => self::PERM_LOGGED,
			'getStatusarray' => self::PERM_LOGGED,
			'deleteStatus' => ['admin:rw','assistenz:rw'],
			'loadStatus' => ['admin:r', 'assistenz:r'],
			'insertStatus' => ['admin:rw', 'assistenz:rw'],
			'updateStatus' => ['admin:rw', 'assistenz:rw'],
			'advanceStatus' => ['admin:rw', 'assistenz:rw'],
			'confirmStatus' => ['admin:rw', 'assistenz:rw'],

		]);

		//Load Models
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
		$this->load->model('person/Person_model', 'PersonModel');

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('PrestudentstatusCheckLib');

		// Additional Permission Checks
		if ($this->router->method == 'insertStatus'
			|| $this->router->method == 'updateStatus'
			|| $this->router->method == 'confirmStatus'
			|| $this->router->method == 'deleteStatus'
			|| $this->router->method == 'advanceStatus'
			|| $this->router->method == 'changeStatus'
			|| $this->router->method == 'addStudent'
		) {
			$prestudent_id = current(array_slice($this->uri->rsegments, 2));
			$this->checkPermissionsForPrestudent($prestudent_id, ['admin:rw', 'assistenz:rw']);
		}

		// Load language phrases
		$this->loadPhrases([
			'global', 'ui', 'bismeldestichtag','lehre','studierendenantrag'
		]);
	}

	public function getHistoryPrestudent($prestudent_id)
	{
		$result = $this->PrestudentstatusModel->getHistoryPrestudent($prestudent_id);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * Gets the maximum possible semester for one or more Studiengaenge.
	 * If there are more than one Studiengang each maximum is calculated and
	 * the smallest result is returned.
	 *
	 * @return void
	 */
	public function getMaxSemester()
	{
		$studiengang_kzs = $this->input->post('studiengang_kzs');

		if (!$studiengang_kzs || !is_array($studiengang_kzs)) {
			$this->load->library('form_validation');

			$this->form_validation->set_rules('studiengang_kzs', '', 'required|is_null', [
				'is_null' => $this->p->t('ui', 'error_fieldMustBeArray')
			]);

			if (!$this->form_validation->run())
				$this->terminateWithValidationErrors($this->form_validation->error_array());
		}


		if (defined('VORRUECKUNG_STATUS_MAX_SEMESTER') && VORRUECKUNG_STATUS_MAX_SEMESTER == false)
			$this->terminateWithSuccess(100);

		$this->load->model('organisation/Lehrverband_model', 'LehrverbandModel');

		$result = $this->LehrverbandModel->getMaxSemester($studiengang_kzs);

		$maxsem = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($maxsem ? current($maxsem)->maxsem : 10);
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

		$result = $this->getDataOrTerminateWithError($result);

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

	public function getStudiensemesterOfStatus($prestudent_id, $status)
	{
		$result = $this->PrestudentstatusModel->loadWhere([
			"prestudent_id" => $prestudent_id,
			"status_kurzbz" => $status
		]);
		$this->PrestudentstatusModel->addLimit(1);
		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		$studiensem = current(getData($result));

		return $studiensem->studiensemester_kurzbz;
	}

	public function getStatusarray()
	{
		$this->load->model('crm/Status_model', 'StatusModel');
		$result = $this->StatusModel->getAllStatiWithStatusgruende();

		if(isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function changeStatus($prestudent_id)
	{
		$isBerechtigtNoStudstatusCheck = $this->permissionlib->isBerechtigt('student/keine_studstatuspruefung');

		//GET lastStatus
		$result = $this->PrestudentstatusModel->getLastStatus($prestudent_id);

		$lastStatusData = $this->getDataOrTerminateWithError($result);
		$lastStatusData = current($lastStatusData);

		$this->load->model('person/Person_model', 'PersonModel');
		$this->PersonModel->addJoin('public.tbl_prestudent', 'person_id');
		$result = $this->PersonModel->loadWhere(['prestudent_id' => $prestudent_id]);
		$prestudent_person = $this->getDataOrTerminateWithError($result);
		$prestudent_person = current($prestudent_person);

		$studentName = trim($prestudent_person->vorname . ' ' . $prestudent_person->nachname);

		$status_kurzbz = $this->input->post('status_kurzbz');

		$studiensemester_kurzbz = $lastStatusData->studiensemester_kurzbz;
		if ($status_kurzbz == Prestudentstatus_model::STATUS_ABSOLVENT
			|| $status_kurzbz == Prestudentstatus_model::STATUS_DIPLOMAND
		) {
			$this->load->library('VariableLib', ['uid' => getAuthUID()]);
			$studiensemester_kurzbz = $this->variablelib->getVar('semester_aktuell');
		}

		$ausbildungssemester = $lastStatusData->ausbildungssemester;
		if ($status_kurzbz == Prestudentstatus_model::STATUS_STUDENT) {
			$ausbildungssemester = $this->input->post('ausbildungssemester');
		}

		$statusgrund_id = $this->input->post('statusgrund_id');
		$datum_string = date('c');
		$datum = new DateTime($datum_string);

		//Form Validation
		$this->load->library('form_validation');

		$this->form_validation->set_rules(
			'status_kurzbz',
			$this->p->t('global', 'status'),
			[
				'required',
				//Check Reihungstest
				['reihungstest_check', function ($value) use ($prestudent_person) {
					if (!REIHUNGSTEST_CHECK)
						return true;
					if ($value != Prestudentstatus_model::STATUS_BEWERBER)
						return true;
					$result = $this->prestudentstatuschecklib->checkIfAngetreten($prestudent_person);
					return $this->getDataOrTerminateWithError($result);
				}],
				//Check ZGV
				['checkIfZGV', function ($value) use ($prestudent_person) {
					if (defined("ZGV_CHECK") && !ZGV_CHECK)
						return true;
					if ($value != Prestudentstatus_model::STATUS_BEWERBER)
						return true;
					$result = $this->prestudentstatuschecklib->checkIfZGVEingetragen($prestudent_person);
					return $this->getDataOrTerminateWithError($result);
				}],
				//Check ZGV Master
				['checkIfZGVMaster', function ($value) use ($prestudent_person) {
					if (defined("ZGV_CHECK") && !ZGV_CHECK)
						return true;
					if ($value != Prestudentstatus_model::STATUS_BEWERBER)
						return true;
					$result = $this->prestudentstatuschecklib->checkIfZGVEingetragenMaster($prestudent_person);
					return $this->getDataOrTerminateWithError($result);
				}],
				//Check Bewerberstatus
				['checkIfExistingBewerberstatus', function ($value) use ($prestudent_id) {
					if ($value != Prestudentstatus_model::STATUS_AUFGENOMMENER
						&& $value != Prestudentstatus_model::STATUS_WARTENDER
					)
						return true;
					$result = $this->prestudentstatuschecklib->checkIfExistingBewerberstatus($prestudent_id);
					return $this->getDataOrTerminateWithError($result);
				}],
				//Check If Student
				['status_stud_exists', function ($value) use ($prestudent_id) {
					if ($value != Prestudentstatus_model::STATUS_STUDENT
						&& $value != Prestudentstatus_model::STATUS_ABBRECHER
					)
						return true; // Only test if new status is Student

					$result = $this->prestudentstatuschecklib->checkIfExistingStudentRolle($prestudent_id);

					return $this->getDataOrTerminateWithError($result);
				}],
			],
			[
				'reihungstest_check' => $this->p->t('lehre', 'error_keinReihungstestverfahren', ['name' => $studentName]),
				'checkIfExistingBewerberstatus' => $this->p->t('lehre', 'error_keinBewerber', ['name' => $studentName]),
				'checkIfZGV' => $this->p->t('lehre', 'error_ZGVNichtEingetragen', ['name' => $studentName]),
				'checkIfZGVMaster' => $this->p->t('lehre', 'error_ZGVMasterNichtEingetragen', ['name' => $studentName]),
				'status_stud_exists' => $this->p->t('lehre', 'error_noStudstatus')
			]
		);

		if ($status_kurzbz == Prestudentstatus_model::STATUS_STUDENT)
			$this->form_validation->set_rules('ausbildungssemester', $this->p->t('lehre', 'ausbildungssemester'), 'required|integer', [
				'integer' => $this->p->t('ui', 'error_fieldNotInteger')
			]);
		else
			$this->form_validation->set_rules('ausbildungssemester', $this->p->t('lehre', 'ausbildungssemester'), 'integer', [
				'integer' => $this->p->t('ui', 'error_fieldNotInteger')
			]);
			
		$this->form_validation->set_rules('statusgrund_id', $this->p->t('international', 'grund'), 'integer', [
			'integer' => $this->p->t('ui', 'error_fieldNotInteger')
		]);

		$this->form_validation->set_rules('_default', '', [
			['meldestichtag_not_exceeded', function () use ($datum, $isBerechtigtNoStudstatusCheck) {
				if ($isBerechtigtNoStudstatusCheck)
					return true; // Skip if access right says so

				$result = $this->prestudentstatuschecklib->checkIfMeldestichtagErreicht($datum);

				return !$this->getDataOrTerminateWithError($result);
			}],
			//check if Rolle already exists
			['rolle_doesnt_exist', function () use ($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester) {
				if (!$status_kurzbz || !$studiensemester_kurzbz || !$ausbildungssemester)
					return true; // Error will be handled by the required statements above

				$result = $this->PrestudentstatusModel->load([$ausbildungssemester, $studiensemester_kurzbz, $status_kurzbz, $prestudent_id]);

				return !$this->getDataOrTerminateWithError($result);
			}],
			['history_timesequence', function () use (
				$isBerechtigtNoStudstatusCheck,
				$prestudent_id,
				$status_kurzbz,
				$datum,
				$studiensemester_kurzbz,
				$ausbildungssemester
			) {
				if ($isBerechtigtNoStudstatusCheck)
					return true; // Skip if access right says so
				if (!$status_kurzbz)
					return true; // Error will be handled by the required statement above

				$result = $this->prestudentstatuschecklib->checkStatusHistoryTimesequence(
					$prestudent_id,
					$status_kurzbz,
					$datum,
					$studiensemester_kurzbz,
					$ausbildungssemester,
					'',
					''
				);

				return $this->getDataOrTerminateWithError($result);
			}],
			['history_laststatus', function () use (
				$isBerechtigtNoStudstatusCheck,
				$prestudent_id,
				$status_kurzbz,
				$datum,
				$studiensemester_kurzbz,
				$ausbildungssemester
			) {
				if ($isBerechtigtNoStudstatusCheck)
					return true; // Skip if access right says so
				if (!$status_kurzbz)
					return true; // Error will be handled by the required statement above

				$result = $this->prestudentstatuschecklib->checkStatusHistoryLaststatus(
					$prestudent_id,
					$status_kurzbz,
					$datum,
					$studiensemester_kurzbz,
					$ausbildungssemester,
					'',
					''
				);

				return $this->getDataOrTerminateWithError($result);
			}],
			['history_unterbrecher', function () use (
				$isBerechtigtNoStudstatusCheck,
				$prestudent_id,
				$status_kurzbz,
				$datum,
				$studiensemester_kurzbz,
				$ausbildungssemester
			) {
				if ($isBerechtigtNoStudstatusCheck)
					return true; // Skip if access right says so
				if (!$status_kurzbz)
					return true; // Error will be handled by the required statement above

				$result = $this->prestudentstatuschecklib->checkStatusHistoryUnterbrechersemester(
					$prestudent_id,
					$status_kurzbz,
					$datum,
					$studiensemester_kurzbz,
					$ausbildungssemester,
					'',
					''
				);

				return $this->getDataOrTerminateWithError($result);
			}],
			['history_abbrecher', function () use (
				$isBerechtigtNoStudstatusCheck,
				$prestudent_id,
				$status_kurzbz,
				$datum,
				$studiensemester_kurzbz,
				$ausbildungssemester
			) {
				if ($isBerechtigtNoStudstatusCheck)
					return true; // Skip if access right says so
				if (!$status_kurzbz)
					return true; // Error will be handled by the required statement above

				$result = $this->prestudentstatuschecklib->checkStatusHistoryAbbrechersemester(
					$prestudent_id,
					$status_kurzbz,
					$datum,
					$studiensemester_kurzbz,
					$ausbildungssemester,
					'',
					''
				);

				return $this->getDataOrTerminateWithError($result);
			}],
			['history_diplomant', function () use (
				$isBerechtigtNoStudstatusCheck,
				$prestudent_id,
				$status_kurzbz,
				$datum,
				$studiensemester_kurzbz,
				$ausbildungssemester
			) {
				if ($isBerechtigtNoStudstatusCheck)
					return true; // Skip if access right says so
				if (!$status_kurzbz)
					return true; // Error will be handled by the required statement above

				$result = $this->prestudentstatuschecklib->checkStatusHistoryDiplomant(
					$prestudent_id,
					$status_kurzbz,
					$datum,
					$studiensemester_kurzbz,
					$ausbildungssemester,
					'',
					''
				);

				return $this->getDataOrTerminateWithError($result);
			}] // TODO(chris): history_student?
		], [
			'meldestichtag_not_exceeded' => $this->p->t('lehre', 'error_dataVorMeldestichtag'),
			'rolle_doesnt_exist' => $this->p->t('lehre', 'error_rolleBereitsVorhandenMitNamen', ['name' => $studentName]),
			'history_timesequence' => $this->p->t('lehre', 'error_statuseintrag_zeitabfolge'),
			'history_laststatus' => $this->p->t('lehre', 'error_endstatus'),
			'history_unterbrecher' => $this->p->t('lehre', 'error_consecutiveUnterbrecher'),
			'history_abbrecher' => $this->p->t('lehre', 'error_consecutiveUnterbrecherAbbrecher'),
			'history_diplomant' => $this->p->t('lehre', 'error_consecutiveDiplomandStudent')
		]);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		
		$this->load->library('PrestudentLib');

		$this->db->trans_start();
		
		switch($status_kurzbz){
			case Prestudentstatus_model::STATUS_ABBRECHER:
				$result = $this->prestudentlib->setAbbrecher(
					$prestudent_id,
					$studiensemester_kurzbz,
					null,
					$statusgrund_id,
					$datum_string,
					null,
					null
				);
				break;
			case Prestudentstatus_model::STATUS_UNTERBRECHER:
				$result = $this->prestudentlib->setUnterbrecher(
					$prestudent_id,
					$studiensemester_kurzbz,
					null,
					null,
					$ausbildungssemester,
					$statusgrund_id
				);
				break;
			case Prestudentstatus_model::STATUS_STUDENT:
				$result = $this->prestudentlib->setStudent(
					$prestudent_id,
					$studiensemester_kurzbz,
					$ausbildungssemester,
					$statusgrund_id
				);
				break;
			case Prestudentstatus_model::STATUS_DIPLOMAND:
				$result = $this->prestudentlib->setDiplomand(
					$prestudent_id,
					$studiensemester_kurzbz,
					$ausbildungssemester
				);
				break;
			case Prestudentstatus_model::STATUS_ABSOLVENT:
				$result = $this->prestudentlib->setAbsolvent(
					$prestudent_id,
					$studiensemester_kurzbz,
					$ausbildungssemester
				);
				break;
			case Prestudentstatus_model::STATUS_BEWERBER:
				$result = $this->prestudentlib->setBewerber(
					$prestudent_id,
					$studiensemester_kurzbz,
					$ausbildungssemester
				);
				break;
			case Prestudentstatus_model::STATUS_AUFGENOMMENER:
				$result = $this->prestudentlib->setAufgenommener(
					$prestudent_id,
					$studiensemester_kurzbz,
					$ausbildungssemester
				);
				break;
			case Prestudentstatus_model::STATUS_ABGEWIESENER:
				$this->load->library('PrestudentLib');
				$result = $this->prestudentlib->setAbgewiesener($prestudent_id, $studiensemester_kurzbz, $ausbildungssemester, $statusgrund_id);

				if (isError($result))
				{
					return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
				}
				else
					$this->terminateWithSuccess($prestudent_id);
				break;
			case Prestudentstatus_model::STATUS_WARTENDER:
				$this->load->library('PrestudentLib');
				$result = $this->prestudentlib->setWartender($prestudent_id, $studiensemester_kurzbz, $ausbildungssemester);

				if (isError($result))
				{
					return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
				}
				else
					$this->terminateWithSuccess($prestudent_id);
				break;
			default:
				$this->terminateWithError("Action not yet defined in Prestudentlib", self::ERROR_TYPE_GENERAL);
		}

		$this->getDataOrTerminateWithError($result);

		$this->trans_complete();

		$this->terminateWithSuccess($prestudent_id);
	}

	public function addStudent($prestudent_id)
	{

		$isBerechtigtBasisPrestudentstatus = $this->permissionlib->isBerechtigt('basis/prestudentstatus');

		if (!$isBerechtigtBasisPrestudentstatus)
			$this->form_validation->set_rules(
				'berechtigt',
				$this->p->t('ui', 'nurLeseberechtigung'),
				'is_null',
				[
					'is_null' => $this->p->t('ui', 'error_fieldWriteAccess')
				]
			);

		//get studentname for validations
		$this->load->model('person/Person_model', 'PersonModel');
		$this->PersonModel->addJoin('public.tbl_prestudent', 'person_id');
		$result = $this->PersonModel->loadWhere(['prestudent_id' => $prestudent_id]);
		$prestudent_person = $this->getDataOrTerminateWithError($result);
		$prestudent_person = current($prestudent_person);

		$studentName = trim($prestudent_person->vorname . ' ' . $prestudent_person->nachname);

		$status_kurzbz = $this->input->post('status_kurzbz');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		$ausbildungssemester = $this->input->post('ausbildungssemester');

		//Form Validation
		$this->load->library('form_validation');

		$statusgrund_id = $this->input->post('statusgrund_id');
		$datum = $this->input->post('datum');

		//Form Validation
		$this->load->library('form_validation');

		$this->form_validation->set_rules(
			'status_kurzbz',
			'Status',
			[
				'required',
				//Check Reihungstest
				['reihungstest_check', function ($value) use ($prestudent_person) {
					if (!REIHUNGSTEST_CHECK)
						return true;
					if ($value != Prestudentstatus_model::STATUS_BEWERBER)
						return true;
					$result = $this->prestudentstatuschecklib->checkIfAngetreten($prestudent_person);
					return $this->getDataOrTerminateWithError($result);
				}],
				//Check ZGV
				['checkIfZGV', function ($value) use ($prestudent_person) {
					if (!ZGV_CHECK)
						return true;
					if ($value != Prestudentstatus_model::STATUS_BEWERBER)
						return true;
					$result = $this->prestudentstatuschecklib->checkIfZGVEingetragen($prestudent_person);
					return $this->getDataOrTerminateWithError($result);
				}],
				//Check ZGV Master
				['checkIfZGVMaster', function ($value) use ($prestudent_person) {
					if (!ZGV_CHECK)
						return true;
					if ($value != Prestudentstatus_model::STATUS_BEWERBER)
						return true;
					$result = $this->prestudentstatuschecklib->checkIfZGVEingetragenMaster($prestudent_person);
					return $this->getDataOrTerminateWithError($result);
				}],
				//Check Bewerberstatus
				['checkIfExistingBewerberstatus', function () use ($prestudent_id, $status_kurzbz) {
					if ($status_kurzbz != Prestudentstatus_model::STATUS_AUFGENOMMENER &&
						$status_kurzbz != Prestudentstatus_model::STATUS_WARTENDER &&
						$status_kurzbz != Prestudentstatus_model::STATUS_ABGEWIESENER)
						return true;
					$result = $this->prestudentstatuschecklib->checkIfExistingBewerberstatus($prestudent_id);
					return $this->getDataOrTerminateWithError($result);
				}],
				//Check Aufgenommenerstatus
				['checkIfExistingAufgenommenerstatus', function () use ($prestudent_id, $status_kurzbz) {
					if ($status_kurzbz != Prestudentstatus_model::STATUS_STUDENT)
						return true;
					$result = $this->prestudentstatuschecklib->checkIfExistingAufgenommenerstatus($prestudent_id);
					return $this->getDataOrTerminateWithError($result);
				}],
				//Check If FirstStudent
				['check_isFirstStudStatus', function ($value) use ($prestudent_id) {
					if ($value != Prestudentstatus_model::STATUS_STUDENT)
						return true;

					$result = $this->prestudentstatuschecklib->checkIfIsErsterStudent($prestudent_id);

					return $this->getDataOrTerminateWithError($result);
				}],
				//Check If Existing PrestudentRolle
				['checkIfExistingPrestudentRolle', function () use (
					$prestudent_id,
					$status_kurzbz,
					$studiensemester_kurzbz,
					$ausbildungssemester
				) {
					$result = $this->prestudentstatuschecklib->checkIfExistingPrestudentRolle(
						$prestudent_id,
						$status_kurzbz,
						$studiensemester_kurzbz,
						$ausbildungssemester
					);
					return $this->getDataOrTerminateWithError($result);
				}]
			],
			[
				'reihungstest_check' => $this->p->t('lehre', 'error_keinReihungstestverfahren', ['name' => $studentName]),
				'checkIfExistingBewerberstatus' => $this->p->t('lehre', 'error_keinBewerber', ['name' => $studentName]),
				'checkIfExistingAufgenommenerstatus' => $this->p->t('lehre', 'error_keinAufgenommener', ['name' => $studentName]),
				'checkIfZGV' => $this->p->t('lehre', 'error_ZGVNichtEingetragen', ['name' => $studentName]),
				'checkIfZGVMaster' => $this->p->t('lehre', 'error_ZGVMasterNichtEingetragen', ['name' => $studentName]),
				'check_isFirstStudStatus' => $this->p->t('lehre', 'error_personBereitsStudent', ['name' => $studentName]),
				'checkIfExistingPrestudentRolle' => $this->p->t('lehre', 'error_rolleBereitsVorhandenMitNamen', ['name' => $studentName])
			]
		);

		$this->form_validation->set_rules('ausbildungssemester', $this->p->t('lehre', 'ausbildungssemester'), 'required|integer', [
			'integer' => $this->p->t('ui', 'error_fieldNotInteger')
		]);


		$this->form_validation->set_rules('ausbildungssemester', 'Ausbildungssemester', 'integer', [
			'integer' => $this->p->t('ui', 'error_fieldNotInteger', ['field' => 'Ausbildungssemester'])
		]);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		//TODO(Manu) check if necessary
		//Das Studiensemester oder Ausbildungsemester des Bewerberstatus und des Aufgenommenenstatus passen nicht überein"

		//Vor dem Studentenstatus müssen folgende Status eingetragen werden: {0}'
		//private $_statusAbfolgeVorStudent = [self::INTERESSENT_STATUS, self::BEWERBER_STATUS, self::AUFGENOMMENER_STATUS

		// Start DB transaction
		$this->db->trans_start();

		//getSemester of Status Aufgenommen
		$result = $this->PrestudentstatusModel->loadWhere([
			"prestudent_id" => $prestudent_id,
			"status_kurzbz" => Prestudentstatus_model::STATUS_AUFGENOMMENER
		]);

		$this->PrestudentstatusModel->addLimit(1);
		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$semesterAufgenommen = current(getData($result));
		$semesterAufgenommen = $semesterAufgenommen->studiensemester_kurzbz;


		//generate Personenkennzeichen(matrikelnr)
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
		$typ = $result->typ;
		$stgkzl = $result->kurzbz;
		$person_id = $result->person_id;
		$matrikelnr = false;

		Events::trigger('generate_personenkennzeichen', $stg, $semesterAufgenommen, $typ, function ($value) use ($matrikelnr) {
			$matrikelnr = $value;
		});
		if ($matrikelnr === false) {
			$resultMat = $this->StudentModel->generateMatrikelnummer2($stg, $semesterAufgenommen, $typ);
			if (isError($resultMat)) {
				return $this->terminateWithError($resultMat, self::ERROR_TYPE_GENERAL);
			}
			$matrikelnr = getData($resultMat);
		}
		$jahr = mb_substr($semesterAufgenommen, 4, 2);

		//generate UID
		$resultUid = $this->StudentModel->generateUID($stgkzl, $jahr, $typ, $matrikelnr);
		if (isError($resultUid)) {
			return $this->terminateWithError(getData($resultUid), self::ERROR_TYPE_GENERAL);
		}
		$uidStudent = getData($resultUid);

		//TODO(Manu)  Check for additional logic (config entries, addons)
		//check include/tw/generatematrikelnr.inc.php
		//(1) addons durchsuchen, ob eigene Logik für Matrikelnr-erstellung existiert
		//Default: keine Matrikelnummer wird generiert

		//(2) personenkz = uid
		/*		if (defined('SET_UID_AS_PERSONENKENNZEICHEN') && SET_UID_AS_PERSONENKENNZEICHEN) {
					$matrikelnr = $uidStudent;
				}*/

		//(3) Matrikelnummer = uid
		/*		if (defined('SET_UID_AS_MATRIKELNUMMER') && SET_UID_AS_MATRIKELNUMMER) {
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
				}*/

		//add benutzerdatensatz mit Aktierungscode
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$result = $this->BenutzerModel->checkIfExistingBenutzer($uidStudent);
		if (isError($result)) {
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		if ($result->retval != "0")
		{
			return $this->terminateWithError($this->p->t('lehre', 'error_benutzerBereitsVorhanden', ['uid' => $uidStudent]), self::ERROR_TYPE_GENERAL);
		}

		$aktivierungscode = null;
		$result = $this->BenutzerModel->generateActivationKey();
		if (isError($result)) {
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$aktivierungscode = getData($result);

		//generate Alias
		$result = $this->BenutzerModel->generateAliasByPersonId($person_id);
		$this->getDataOrTerminateWithError($result);
		$alias = getData($result);

		$result = $this->BenutzerModel->insert(
			[
				'person_id' => $person_id,
				'uid' => $uidStudent,
				'aktiv' => true,
				'aktivierungscode' => $aktivierungscode,
				'alias' => $alias,
				'insertvon' => getAuthUID(),
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
				'insertvon' => getAuthUID(),
				'insertamum' => date('c')
			]
		);
		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$this->load->library('PrestudentLib');
		$result = $this->prestudentlib->setFirstStudent(
			$prestudent_id,
			$studiensemester_kurzbz,
			$ausbildungssemester,
			$statusgrund_id,
			date('c'),
			getAuthUID(),
			$stg,
			$uidStudent
		);

		$this->getDataOrTerminateWithError($result);

		$this->db->trans_commit();

		return $this->outputJsonSuccess(true);
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
			$this->terminateWithError($this->p->t('lehre', 'error_noStatusFound'), self::ERROR_TYPE_GENERAL);
		}
		else
		{
			$this->terminateWithSuccess(current(getData($result)));
		}
	}

	/**
	 * Delete a status entry
	 *
	 * @param integer				$prestudent_id
	 * @param string				$status_kurzbz
	 * @param string				$studiensemester_kurzbz
	 * @param integer				$ausbildungssemester
	 *
	 * @return void
	 */
	public function deleteStatus($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester)
	{
		$result = $this->PrestudentstatusModel->load([
			$ausbildungssemester,
			$studiensemester_kurzbz,
			$status_kurzbz,
			$prestudent_id
		]);
		$oldstatus = $this->getDataOrTerminateWithError($result);
		if (!$oldstatus)
			show_404(); // Status that should be updated does not exist

		$oldstatus = current($oldstatus);


		$erweiterteBerechtigung =
			$this->permissionlib->isBerechtigt('admin', null, 'suid')
			|| $this->permissionlib->isBerechtigt('student/keine_studstatuspruefung', null, 'suid');


		//check if last status
		$result = $this->PrestudentstatusModel->checkIfLastStatusEntry($prestudent_id);

		$result = $this->getDataOrTerminateWithError($result);

		$deletePrestudent = $result;


		//Berechtigungen nach Check prüfen!
		if (!$erweiterteBerechtigung) {
			if ($status_kurzbz == Prestudentstatus_model::STATUS_STUDENT)
				$this->terminateWithError(
					$this->p->t('lehre', 'error_onlyAdminDeleteRolleStudent'),
					self::ERROR_TYPE_GENERAL,
					REST_Controller::HTTP_FORBIDDEN
				);

			if ($deletePrestudent)
				$this->terminateWithError(
					$this->p->t('lehre', 'error_onlyAdminDeleteLastStatus'),
					self::ERROR_TYPE_GENERAL,
					REST_Controller::HTTP_FORBIDDEN
				);

			$result = $this->prestudentstatuschecklib->checkIfMeldestichtagErreicht($oldstatus->datum);

			if (!$this->getDataOrTerminateWithError($result))
				$this->terminateWithError(
					$this->p->t('lehre', 'error_dataVorMeldestichtag'),
					self::ERROR_TYPE_GENERAL,
					REST_Controller::HTTP_FORBIDDEN
				);
		}

		// Start DB transaction
		$this->db->trans_begin();

		//Delete Status
		$result = $this->PrestudentstatusModel->delete(
			[
				'prestudent_id' => $prestudent_id,
				'status_kurzbz' => $status_kurzbz,
				'ausbildungssemester' => $ausbildungssemester,
				'studiensemester_kurzbz' => $studiensemester_kurzbz
			]
		);

		$this->getDataOrTerminateWithError($result);

		//Delete Studentlehrverband if no Status left in this semester
		$result = $this->PrestudentstatusModel->checkIfLastStatusEntry($prestudent_id, $studiensemester_kurzbz);

		$result = $this->getDataOrTerminateWithError($result);
		if ($result)
		{
			//get student_uid
			$this->load->model('crm/Student_model', 'StudentModel');
			$result = $this->StudentModel->loadWhere([
				'prestudent_id' => $prestudent_id
			]);

			$student = $this->getDataOrTerminateWithError($result);
			if ($student)
			{
				$student = current($student);
				$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
				$result = $this->StudentlehrverbandModel->delete(
					array(
						'student_uid' => $student->student_uid,
						'studiensemester_kurzbz' => $studiensemester_kurzbz
					)
				);

				$this->getDataOrTerminateWithError($result);
			}
		}

		//Delete Prestudent if no data is left
		if ($deletePrestudent)
		{
			$this->load->model('crm/Prestudent_model', 'PrestudentModel');
			$result = $this->PrestudentModel->delete($prestudent_id);

			$this->getDataOrTerminateWithError($result);
		}

		$this->db->trans_commit();

		return $this->terminateWithSuccess(true);
	}

	/**
	 * Inserts a status with less validations and extra logic for manual
	 * manipulation
	 *
	 * @param integer				$prestudent_id
	 *
	 * @return void
	 */
	public function insertStatus($prestudent_id)
	{
		$isBerechtigtNoStudstatusCheck = $this->permissionlib->isBerechtigt('student/keine_studstatuspruefung');
		$isBerechtigtBasisPrestudentstatus = $this->permissionlib->isBerechtigt('basis/prestudentstatus');


		$authUID = getAuthUID();
		$status_kurzbz = $this->input->post('status_kurzbz');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		$ausbildungssemester = $this->input->post('ausbildungssemester');
		$datum = $this->input->post('datum');
		$bestaetigtam = $this->input->post('bestaetigtam');
		$orgform_kurzbz = $this->input->post('orgform_kurzbz');
		$anmerkung = $this->input->post('anmerkung');
		$bewerbung_abgeschicktamum = $this->input->post('bewerbung_abgeschicktamum');
		$studienplan_id = $this->input->post('studienplan_id');
		$rt_stufe = $this->input->post('rt_stufe');
		$statusgrund_id = $this->input->post('statusgrund_id');


		$this->load->library('form_validation');

		if (!$isBerechtigtBasisPrestudentstatus)
			$this->form_validation->set_rules(
				'bewerbung_abgeschicktamum',
				$this->p->t('lehre', 'bewerbung_abgeschickt_am'),
				'is_null',
				[
					'is_null' => $this->p->t('ui', 'error_fieldWriteAccess')
				]
			);

		$this->form_validation->set_rules(
			'datum',
			$this->p->t('global', 'datum'),
			[
				'required', // In FAS empty datum results in todays
				'is_valid_date',
				['is_date_not_before_today', function ($value) {
					if (!is_valid_date($value))
						return true; // Error will be handled by the is_valid_date statement above
					$today = new DateTime('today');
					return (new DateTime($value) >= $today);
				}],
				['meldestichtag_not_exceeded', function ($value) use ($isBerechtigtNoStudstatusCheck) {
					if ($isBerechtigtNoStudstatusCheck)
						return true; // Skip if access right says so
					if (!$value)
						return true; // Error will be handled by the required statement above

					$result = $this->prestudentstatuschecklib->checkIfMeldestichtagErreicht($value);

					return !$this->getDataOrTerminateWithError($result);
				}]
			],
			[
				'meldestichtag_not_exceeded' => $this->p->t('lehre', 'error_dataVorMeldestichtag'),
				'is_date_not_before_today' => $this->p->t('lehre', 'error_entryInPast')
			]
		);

		$this->form_validation->set_rules(
			'status_kurzbz',
			$this->p->t('lehre', 'status_rolle'),
			[
				'required',
				['status_stud_exists', function ($value) use ($prestudent_id) {
					if ($value != Prestudentstatus_model::STATUS_STUDENT)
						return true; // Only test if new status is Student

					$result = $this->prestudentstatuschecklib->checkIfExistingStudentRolle($prestudent_id);

					return $this->getDataOrTerminateWithError($result);
				}]
			],
			[
				'status_stud_exists' => $this->p->t('lehre', 'error_noStudstatus')
			]
		);

		$this->form_validation->set_rules('studiensemester_kurzbz', $this->p->t('lehre', 'studiensemester'), 'required');

		$this->form_validation->set_rules('ausbildungssemester', $this->p->t('lehre', 'ausbildungssemester'), 'required');

		$this->form_validation->set_rules('bestaetigtam', $this->p->t('lehre', 'bestaetigt_am'), 'is_valid_date');

		// Set Datum to null to prevent multiple is_valid_date checks in the following validation rules
		if (!$datum || !is_valid_date($datum))
			$datum = null;

		$this->form_validation->set_rules('_default', '', [
			['rolle_doesnt_exist', function () use ($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester) {
				if (!$status_kurzbz || !$studiensemester_kurzbz || !$ausbildungssemester)
					return true; // Error will be handled by the required statements above

				$result = $this->PrestudentstatusModel->load([$ausbildungssemester, $studiensemester_kurzbz, $status_kurzbz, $prestudent_id]);

				return !$this->getDataOrTerminateWithError($result);
			}],
			['history_timesequence', function () use (
				$isBerechtigtNoStudstatusCheck,
				$prestudent_id,
				$status_kurzbz,
				$datum,
				$studiensemester_kurzbz,
				$ausbildungssemester
			) {
				if ($isBerechtigtNoStudstatusCheck)
					return true; // Skip if access right says so
				if (!$status_kurzbz || !$datum || !$studiensemester_kurzbz || !$ausbildungssemester)
					return true; // Error will be handled by the required statements above

				$result = $this->prestudentstatuschecklib->checkStatusHistoryTimesequence(
					$prestudent_id,
					$status_kurzbz,
					new DateTime($datum),
					$studiensemester_kurzbz,
					$ausbildungssemester,
					'',
					''
				);

				return $this->getDataOrTerminateWithError($result);
			}],
			['history_laststatus', function () use (
				$isBerechtigtNoStudstatusCheck,
				$prestudent_id,
				$status_kurzbz,
				$datum,
				$studiensemester_kurzbz,
				$ausbildungssemester
			) {
				if ($isBerechtigtNoStudstatusCheck)
					return true; // Skip if access right says so
				if (!$status_kurzbz || !$datum || !$studiensemester_kurzbz || !$ausbildungssemester)
					return true; // Error will be handled by the required statements above

				$result = $this->prestudentstatuschecklib->checkStatusHistoryLaststatus(
					$prestudent_id,
					$status_kurzbz,
					new DateTime($datum),
					$studiensemester_kurzbz,
					$ausbildungssemester,
					'',
					''
				);

				return $this->getDataOrTerminateWithError($result);
			}],
			['history_unterbrecher', function () use (
				$isBerechtigtNoStudstatusCheck,
				$prestudent_id,
				$status_kurzbz,
				$datum,
				$studiensemester_kurzbz,
				$ausbildungssemester
			) {
				if ($isBerechtigtNoStudstatusCheck)
					return true; // Skip if access right says so
				if (!$status_kurzbz || !$datum || !$studiensemester_kurzbz || !$ausbildungssemester)
					return true; // Error will be handled by the required statements above

				$result = $this->prestudentstatuschecklib->checkStatusHistoryUnterbrechersemester(
					$prestudent_id,
					$status_kurzbz,
					new DateTime($datum),
					$studiensemester_kurzbz,
					$ausbildungssemester,
					'',
					''
				);

				return $this->getDataOrTerminateWithError($result);
			}],
			['history_abbrecher', function () use (
				$isBerechtigtNoStudstatusCheck,
				$prestudent_id,
				$status_kurzbz,
				$datum,
				$studiensemester_kurzbz,
				$ausbildungssemester
			) {
				if ($isBerechtigtNoStudstatusCheck)
					return true; // Skip if access right says so
				if (!$status_kurzbz || !$datum || !$studiensemester_kurzbz || !$ausbildungssemester)
					return true; // Error will be handled by the required statements above

				$result = $this->prestudentstatuschecklib->checkStatusHistoryAbbrechersemester(
					$prestudent_id,
					$status_kurzbz,
					new DateTime($datum),
					$studiensemester_kurzbz,
					$ausbildungssemester,
					'',
					''
				);

				return $this->getDataOrTerminateWithError($result);
			}],
			['history_diplomant', function () use (
				$isBerechtigtNoStudstatusCheck,
				$prestudent_id,
				$status_kurzbz,
				$datum,
				$studiensemester_kurzbz,
				$ausbildungssemester
			) {
				if ($isBerechtigtNoStudstatusCheck)
					return true; // Skip if access right says so
				if (!$status_kurzbz || !$datum || !$studiensemester_kurzbz || !$ausbildungssemester)
					return true; // Error will be handled by the required statements above

				$result = $this->prestudentstatuschecklib->checkStatusHistoryDiplomant(
					$prestudent_id,
					$status_kurzbz,
					new DateTime($datum),
					$studiensemester_kurzbz,
					$ausbildungssemester,
					'',
					''
				);

				return $this->getDataOrTerminateWithError($result);
			}]
		], [
			'rolle_doesnt_exist' => $this->p->t('lehre', 'error_rolleBereitsVorhanden'),
			'history_timesequence' => $this->p->t('lehre', 'error_statuseintrag_zeitabfolge'),
			'history_laststatus' => $this->p->t('lehre', 'error_endstatus'),
			'history_unterbrecher' => $this->p->t('lehre', 'error_consecutiveUnterbrecher'),
			'history_abbrecher' => $this->p->t('lehre', 'error_consecutiveUnterbrecherAbbrecher'),
			'history_diplomant' => $this->p->t('lehre', 'error_consecutiveDiplomandStudent')
		]);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());


		// Start DB transaction
		$this->db->trans_start();

		$this->updateLehrverbandForInsertAndUpdate($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester, $authUID);

		//insert status
		$result = $this->PrestudentstatusModel->insert([
			'prestudent_id' => $prestudent_id,
			'status_kurzbz' => $status_kurzbz,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'ausbildungssemester' => $ausbildungssemester,
			'datum' => $datum,
			'insertamum' => date('c'),
			'insertvon' => $authUID,
			'orgform_kurzbz' => $orgform_kurzbz,
			'bestaetigtam' => $bestaetigtam,
			'bestaetigtvon' => $bestaetigtam ? $authUID : null,
			'anmerkung' => $anmerkung,
			'bewerbung_abgeschicktamum' => $bewerbung_abgeschicktamum,
			'studienplan_id' => $studienplan_id,
			'rt_stufe' => $rt_stufe,
			'statusgrund_id' => $statusgrund_id
		]);

		$this->getDataOrTerminateWithError($result);

		$this->db->trans_complete();

		$this->terminateWithSuccess(true);
	}

	/**
	 * Updates a status entry
	 *
	 * @param integer				$prestudent_id
	 * @param string				$status_kurzbz
	 * @param string				$key_studiensemester_kurzbz
	 * @param integer				$key_ausbildungssemester
	 *
	 * @return void
	 */
	public function updateStatus($prestudent_id, $status_kurzbz, $key_studiensemester_kurzbz, $key_ausbildungssemester)
	{
		$result = $this->PrestudentstatusModel->load([
			$key_ausbildungssemester,
			$key_studiensemester_kurzbz,
			$status_kurzbz,
			$prestudent_id
		]);
		$oldstatus = $this->getDataOrTerminateWithError($result);
		if (!$oldstatus)
			show_404(); // Status that should be updated does not exist

		$oldstatus = current($oldstatus);


		$isBerechtigtNoStudstatusCheck =  $this->permissionlib->isBerechtigt('student/keine_studstatuspruefung');
		$isBerechtigtBasisPrestudentstatus = $this->permissionlib->isBerechtigt('basis/prestudentstatus');


		$authUID = getAuthUID();
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz') ?: $oldstatus->studiensemester_kurzbz;
		$ausbildungssemester = $this->input->post('ausbildungssemester') ?: $oldstatus->ausbildungssemester;
		$datum = $this->input->post('datum') ?: $oldstatus->datum;


		//Form Validation
		$this->load->library('form_validation');

		if (!$isBerechtigtBasisPrestudentstatus)
			$this->form_validation->set_rules(
				'bewerbung_abgeschicktamum',
				$this->p->t('lehre', 'bewerbung_abgeschickt_am'),
				'is_null',
				[
					'is_null' => $this->p->t('ui', 'error_fieldWriteAccess')
				]
			);

		$this->form_validation->set_rules(
			'datum',
			$this->p->t('global', 'datum'),
			[
				'is_valid_date',
				['meldestichtag_not_exceeded', function ($value) use ($isBerechtigtNoStudstatusCheck) {
					if ($isBerechtigtNoStudstatusCheck)
						return true; // Skip if access right says so
					if (!$value)
						return true; // Error will be handled by the required statement above

					$result = $this->prestudentstatuschecklib->checkIfMeldestichtagErreicht($value);

					return !$this->getDataOrTerminateWithError($result);
				}]
			],
			[
				'meldestichtag_not_exceeded' => $this->p->t('lehre', 'error_dataVorMeldestichtag')
			]
		);

		$this->form_validation->set_rules('bestaetigtam', $this->p->t('lehre', 'bestaetigt_am'), 'is_valid_date');

		if (!is_valid_date($datum))
			$datum = null;

		// Set Datum to null to prevent multiple is_valid_date checks in the following validation rules
		$this->form_validation->set_rules('_default', '', [
			//check if Rolle already exists
			['new_rolle_doesnt_exist', function () use (
				$prestudent_id,
				$status_kurzbz,
				$studiensemester_kurzbz,
				$ausbildungssemester,
				$key_studiensemester_kurzbz,
				$key_ausbildungssemester
			) {
				if ($key_studiensemester_kurzbz == $studiensemester_kurzbz
					&& $key_ausbildungssemester == $ausbildungssemester
				)
					return true; // Primary key has not change we update in place

				$result = $this->PrestudentstatusModel->load([
					$ausbildungssemester,
					$studiensemester_kurzbz,
					$status_kurzbz,
					$prestudent_id
				]);
				return !$this->getDataOrTerminateWithError($result);
			}],
			['history_timesequence', function () use (
				$isBerechtigtNoStudstatusCheck,
				$prestudent_id,
				$status_kurzbz,
				$datum,
				$studiensemester_kurzbz,
				$ausbildungssemester,
				$key_studiensemester_kurzbz,
				$key_ausbildungssemester
			) {
				if ($isBerechtigtNoStudstatusCheck)
					return true; // Skip if access right says so
				if (!$datum)
					return true; // Error will be handled by the is_valid_date statement above

				$result = $this->prestudentstatuschecklib->checkStatusHistoryTimesequence(
					$prestudent_id,
					$status_kurzbz,
					new DateTime($datum),
					$studiensemester_kurzbz,
					$ausbildungssemester,
					$key_studiensemester_kurzbz,
					$key_ausbildungssemester
				);

				return $this->getDataOrTerminateWithError($result);
			}],
			['history_laststatus', function () use (
				$isBerechtigtNoStudstatusCheck,
				$prestudent_id,
				$status_kurzbz,
				$datum,
				$studiensemester_kurzbz,
				$ausbildungssemester,
				$key_studiensemester_kurzbz,
				$key_ausbildungssemester
			) {
				if ($isBerechtigtNoStudstatusCheck)
					return true; // Skip if access right says so
				if (!$datum)
					return true; // Error will be handled by the is_valid_date statement above

				$result = $this->prestudentstatuschecklib->checkStatusHistoryLaststatus(
					$prestudent_id,
					$status_kurzbz,
					new DateTime($datum),
					$studiensemester_kurzbz,
					$ausbildungssemester,
					$key_studiensemester_kurzbz,
					$key_ausbildungssemester
				);

				return $this->getDataOrTerminateWithError($result);
			}],
			['history_unterbrecher', function () use (
				$isBerechtigtNoStudstatusCheck,
				$prestudent_id,
				$status_kurzbz,
				$datum,
				$studiensemester_kurzbz,
				$ausbildungssemester,
				$key_studiensemester_kurzbz,
				$key_ausbildungssemester
			) {
				if ($isBerechtigtNoStudstatusCheck)
					return true; // Skip if access right says so
				if (!$datum)
					return true; // Error will be handled by the is_valid_date statement above

				$result = $this->prestudentstatuschecklib->checkStatusHistoryUnterbrechersemester(
					$prestudent_id,
					$status_kurzbz,
					new DateTime($datum),
					$studiensemester_kurzbz,
					$ausbildungssemester,
					$key_studiensemester_kurzbz,
					$key_ausbildungssemester
				);

				return $this->getDataOrTerminateWithError($result);
			}],
			['history_abbrecher', function () use (
				$isBerechtigtNoStudstatusCheck,
				$prestudent_id,
				$status_kurzbz,
				$datum,
				$studiensemester_kurzbz,
				$ausbildungssemester,
				$key_studiensemester_kurzbz,
				$key_ausbildungssemester
			) {
				if ($isBerechtigtNoStudstatusCheck)
					return true; // Skip if access right says so
				if (!$datum)
					return true; // Error will be handled by the is_valid_date statement above

				$result = $this->prestudentstatuschecklib->checkStatusHistoryAbbrechersemester(
					$prestudent_id,
					$status_kurzbz,
					new DateTime($datum),
					$studiensemester_kurzbz,
					$ausbildungssemester,
					$key_studiensemester_kurzbz,
					$key_ausbildungssemester
				);

				return $this->getDataOrTerminateWithError($result);
			}],
			['history_diplomant', function () use (
				$isBerechtigtNoStudstatusCheck,
				$prestudent_id,
				$status_kurzbz,
				$datum,
				$studiensemester_kurzbz,
				$ausbildungssemester,
				$key_studiensemester_kurzbz,
				$key_ausbildungssemester
			) {
				if ($isBerechtigtNoStudstatusCheck)
					return true; // Skip if access right says so
				if (!$datum)
					return true; // Error will be handled by the is_valid_date statement above

				$result = $this->prestudentstatuschecklib->checkStatusHistoryDiplomant(
					$prestudent_id,
					$status_kurzbz,
					new DateTime($datum),
					$studiensemester_kurzbz,
					$ausbildungssemester,
					$key_studiensemester_kurzbz,
					$key_ausbildungssemester
				);

				return $this->getDataOrTerminateWithError($result);
			}]
		], [
			'new_rolle_doesnt_exist' => $this->p->t('lehre', 'error_rolleBereitsVorhanden'),
			'history_timesequence' => $this->p->t('lehre', 'error_statuseintrag_zeitabfolge'),
			'history_laststatus' => $this->p->t('lehre', 'error_endstatus'),
			'history_unterbrecher' => $this->p->t('lehre', 'error_consecutiveUnterbrecher'),
			'history_abbrecher' => $this->p->t('lehre', 'error_consecutiveUnterbrecherAbbrecher'),
			'history_diplomant' => $this->p->t('lehre', 'error_consecutiveDiplomandStudent')
		]);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());


		// Start DB transaction
		$this->db->trans_start();

		$this->updateLehrverbandForInsertAndUpdate($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester, $authUID);

		//update status
		$updateData = [
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'ausbildungssemester' => $ausbildungssemester,
			'datum' => $datum,
			'updateamum' => date('c'),
			'updatevon' => $authUID
		];
		foreach ([
					 'orgform_kurzbz',
					 'anmerkung',
					 'bewerbung_abgeschicktamum',
					 'studienplan_id',
					 'rt_stufe',
					 'statusgrund_id'
				 ] as $key)
			if ($this->input->post($key))
				$updateData[$key] = $this->input->post($key);

		if ($this->input->post('bestaetigtam')) {
			$updateData['bestaetigtam'] = $this->input->post('bestaetigtam');
			$updateData['bestaetigtvon'] = $authUID;
		}

		$result = $this->PrestudentstatusModel->update([
			'prestudent_id' => $prestudent_id,
			'status_kurzbz' => $status_kurzbz,
			'studiensemester_kurzbz' => $key_studiensemester_kurzbz,
			'ausbildungssemester' => $key_ausbildungssemester,
		], $updateData);

		$this->getDataOrTerminateWithError($result);

		$this->db->trans_commit();

		return $this->outputJsonSuccess(true);
	}

	/**
	 * Advances a status entry
	 * must be of type Student, Diplomand or Unterbrecher
	 *
	 * @param integer				$prestudent_id
	 * @param string				$status_kurzbz
	 * @param string				$key_studiensemester_kurzbz
	 * @param integer				$key_ausbildungssemester
	 *
	 * @return void
	 */
	public function advanceStatus($prestudent_id, $status_kurzbz, $key_studiensemester_kurzbz, $key_ausbildungssemester)
	{
		$result = $this->PrestudentstatusModel->load([
			$key_ausbildungssemester,
			$key_studiensemester_kurzbz,
			$status_kurzbz,
			$prestudent_id
		]);
		$oldstatus = $this->getDataOrTerminateWithError($result);
		if (!$oldstatus)
			show_404(); // Status that should be updated does not exist

		$oldstatus = current($oldstatus);


		//Target studiensemester_kurzbz
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$result = $this->StudiensemesterModel->getNextFrom($key_studiensemester_kurzbz);

		$studiensemester_kurzbz = $this->getDataOrTerminateWithError($result);
		$studiensemester_kurzbz = current($studiensemester_kurzbz)->studiensemester_kurzbz;


		//Target ausbildungssemester
		$ausbildungssemester = $key_ausbildungssemester + 1;


		//Form Validation
		$this->load->library('form_validation');

		$this->form_validation->set_data([
			'status_kurzbz' => $status_kurzbz
		]);

		$this->form_validation->set_rules('status_kurzbz', $this->p->t('lehre', 'status_rolle'), [
			'in_list[' .
			Prestudentstatus_model::STATUS_STUDENT . ',' .
			Prestudentstatus_model::STATUS_DIPLOMAND . ',' .
			Prestudentstatus_model::STATUS_UNTERBRECHER . ']',
			['status_stud_exists', function ($value) use ($prestudent_id) {
				if ($value != Prestudentstatus_model::STATUS_STUDENT)
					return true;

				$result = $this->prestudentstatuschecklib->checkIfExistingStudentRolle($prestudent_id);

				return $this->getDataOrTerminateWithError($result);
			}]
		], [
			'status_stud_exists' => $this->p->t('lehre', 'error_noStudstatus')
		]);

		$this->form_validation->set_rules('_default', '', [
			//check if Rolle already exists
			['rolle_doesnt_exist', function () use (
				$prestudent_id,
				$status_kurzbz,
				$studiensemester_kurzbz,
				$ausbildungssemester
			) {
				$result = $this->PrestudentstatusModel->load([$ausbildungssemester, $studiensemester_kurzbz, $status_kurzbz, $prestudent_id]);

				return !$this->getDataOrTerminateWithError($result);
			}]
		], [
			'rolle_doesnt_exist' => $this->p->t('lehre', 'error_rolleBereitsVorhanden')
		]);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());


		// Start DB transaction
		$this->db->trans_begin();

		$authUID = getAuthUID();
		$now = date('c');

		//insert prestudentstatus
		$result = $this->PrestudentstatusModel->insert([
			'prestudent_id' => $prestudent_id,
			'status_kurzbz' => $status_kurzbz,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'ausbildungssemester' => $ausbildungssemester,
			'datum' => $now,
			'insertamum' => $now,
			'insertvon' => $authUID,
			'orgform_kurzbz' => $oldstatus->orgform_kurzbz,
			'studienplan_id' => $oldstatus->studienplan_id,
			'bestaetigtam' => $now,
			'bestaetigtvon' => $authUID,
			'anmerkung' => $oldstatus->anmerkung
		]);

		$this->getDataOrTerminateWithError($result);


		//get student_uid
		$this->load->model('crm/Student_model', 'StudentModel');
		$result = $this->StudentModel->loadWhere([
			'prestudent_id' => $prestudent_id
		]);

		$student = $this->getDataOrTerminateWithError($result);

		if (!$student)
			$this->terminateWithError($this->p->t('studierendenantrag', 'error_no_student_for_prestudent', ['prestudent_id' => $prestudent_id]));


		//process studentlehrverband
		$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
		$result = $this->StudentlehrverbandModel->load([
			$key_studiensemester_kurzbz,
			$student->student_uid
		]);

		$studentlvb = $this->getDataOrTerminateWithError($result);
		if (!$studentlvb)
			$this->terminateWithError($this->p->t('lehre', 'error_noStudentlehrverband'));

		//Data of current Semester
		$studentlvb = current($studentlvb);


		$newStudentlvb = [
			'student_uid' => $studentlvb->student_uid,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'studiengang_kz' => $studentlvb->studiengang_kz,
			'semester' => $studentlvb->semester,
			'verband' => $studentlvb->verband,
			'gruppe' => $studentlvb->gruppe,
			'insertamum' => $now,
			'insertvon' => $authUID,
			'ext_id' => $studentlvb->ext_id

		];


		$this->load->model('organisation/Lehrverband_model', 'LehrverbandModel');

		$result = $this->LehrverbandModel->load([
			$student->gruppe,
			$student->verband,
			$ausbildungssemester,
			$student->studiengang_kz
		]);

		$lv = $this->getDataOrTerminateWithError($result);
		if ($lv) {
			$newStudentlvb['semester'] = $ausbildungssemester;
		} // If there is no lehrverband just use the same as in the previous studiensemester


		//add studentlehrverband
		$result = $this->StudentlehrverbandModel->insert($newStudentlvb);

		$this->getDataOrTerminateWithError($result);

		$this->db->trans_commit();

		return $this->outputJsonSuccess(true);
	}

	/**
	 * Confirms a status entry
	 *
	 * @param integer				$prestudent_id
	 * @param string				$status_kurzbz
	 * @param string				$key_studiensemester_kurzbz
	 * @param integer				$key_ausbildungssemester
	 *
	 * @return void
	 */
	public function confirmStatus($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester)
	{
		$result = $this->PrestudentstatusModel->load([
			$ausbildungssemester,
			$studiensemester_kurzbz,
			$status_kurzbz,
			$prestudent_id
		]);
		$oldstatus = $this->getDataOrTerminateWithError($result);
		if (!$oldstatus)
			show_404(); // Status that should be updated does not exist

		$oldstatus = current($oldstatus);


		$authUID = getAuthUID();
		$now = date('c');

		//Form Validation
		$this->load->library('form_validation');

		$this->form_validation->set_rules('Status', '', [
			['status_not_yet_confirmed', function () use ($oldstatus) {
				return !$oldstatus->bestaetigtam;
			}],
			['bewerbung_abgeschickt', function () use ($oldstatus) {
				return !!$oldstatus->bewerbung_abgeschicktamum;
			}]
		], [
			'status_not_yet_confirmed' => $this->p->t('lehre', 'error_statusConfirmedYet'),
			'bewerbung_abgeschickt' => $this->p->t('lehre', 'error_bewerbungNochNichtAbgeschickt')
		]);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());


		//update status
		$result = $this->PrestudentstatusModel->update([
			'prestudent_id' => $prestudent_id,
			'status_kurzbz' => $status_kurzbz,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'ausbildungssemester' => $ausbildungssemester,
		], [
			'bestaetigtam' => $now,
			'bestaetigtvon' => $authUID,
			'updateamum' => $now,
			'updatevon' => $authUID
		]);

		$this->getDataOrTerminateWithError($result);


		//Send Message
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$this->PrestudentModel->addSelect('p.*');
		$this->PrestudentModel->addSelect('stg.oe_kurzbz');
		$this->PrestudentModel->addSelect('stg.bezeichnung AS stg_bezeichnung');
		$this->PrestudentModel->addSelect('stg.email AS stg_email');
		$this->PrestudentModel->addSelect('plan.orgform_kurzbz');
		$this->PrestudentModel->addSelect('typ.bezeichnung AS typ_bezeichnung');

		$this->PrestudentModel->addJoin('public.tbl_person p', 'person_id');
		$this->PrestudentModel->addJoin('public.tbl_studiengang stg', 'studiengang_kz');
		$this->PrestudentModel->addJoin('public.tbl_studiengangstyp typ', 'typ');
		$this->PrestudentModel->addJoin('public.tbl_studienplan plan', 'studienplan_id', 'LEFT');

		$result = $this->PrestudentModel->load($prestudent_id);

		$studentdata = $this->getDataOrTerminateWithError($result);

		$this->load->library('MessageLib');
		$result = $this->messagelib->sendMessageUserTemplate(
			$studentdata->person_id,				// receiversPersonId
			'MailStatConfirm' . $status_kurzbz,		// vorlage
			[
				'anrede' => $studentdata->anrede,
				'vorname' => $studentdata->vorname,
				'nachname' => $studentdata->nachname,
				'typ' => $studentdata->typ_bezeichnung,
				'studiengang' => $studentdata->stg_bezeichnung,
				'orgform' => $studentdata->orgform_kurzbz ?: $oldstatus->orgform_kurzbz,
				'stgMail' => $studentdata->stg_email
			],										// parseData
			null,									// orgform
			1,										// TODO
			$studentdata->oe_kurzbz,				// senderOU
			null,									// relationmessage_id
			MSG_PRIORITY_NORMAL,					// priority
			true									// multiPartMime
		);


		$this->terminateWithSuccess(true);
	}

	/**
	 * Helper function for insertStatus and updateStatus.
	 *
	 * @param integer					$prestudent_id
	 * @param string					$status_kurzbz
	 * @param string					$studiensemester_kurzbz
	 * @param integer					$ausbildungssemester
	 * @param string					$authUID
	 *
	 * @return void
	 */
	private function updateLehrverbandForInsertAndUpdate($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester, $authUID)
	{
		if (!in_array($status_kurzbz, [
			Prestudentstatus_model::STATUS_STUDENT,
			Prestudentstatus_model::STATUS_DIPLOMAND,
			Prestudentstatus_model::STATUS_ABSOLVENT,
			Prestudentstatus_model::STATUS_INCOMING,
			Prestudentstatus_model::STATUS_ABBRECHER,
			Prestudentstatus_model::STATUS_UNTERBRECHER
		]))
			return; // No Update necessary

		$result = $this->StudentModel->loadWhere([
			'prestudent_id' => $prestudent_id
		]);

		$student = $this->getDataOrTerminateWithError($result);

		if (!$student)
			return; // No Update necessary

		$student = current($student);

		$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
		$result = $this->StudentlehrverbandModel->loadWhere([
			'student_uid' => $student->student_uid,
			'studiensemester_kurzbz' => $studiensemester_kurzbz
		]);

		$studentlvb = $this->getDataOrTerminateWithError($result);

		// NOTE(chris): if $studentlvb then update to the same values as before (except updatevon). So do nothing?
		// TODO(chris): check if that is correct (in FAS)?
		if ($studentlvb)
			return; // No Update necessary

		$this->load->model('organisation/Lehrverband_model', 'LehrverbandModel');

		$this->LehrverbandModel->addLimit(1);
		$result = $this->LehrverbandModel->load([
			$student->gruppe,
			$student->verband,
			$ausbildungssemester,
			$student->studiengang_kz
		]);
		$lv = $this->getDataOrTerminateWithError($result);

		$this->StudentlehrverbandModel->insert([
			'student_uid' => $student->student_uid,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'studiengang_kz' => $student->studiengang_kz,
			'semester' => $lv ? $ausbildungssemester : $student->semester,
			'verband' => $student->verband,
			'gruppe' => $student->gruppe,
			'insertamum' => date('c'),
			'insertvon' => $authUID
		]);
	}

	/**
	 * Helper function for sanitizing Alias Name
	 * replaces empty spaces with underlines
	 *
	 * @param string					$str
	 *
	 * @return string
	 */
	private function _sanitizeAliasName($str)
	{
		$str = sanitizeProblemChars($str);
		return mb_strtolower(str_replace(' ', '_', $str));
	}
}
