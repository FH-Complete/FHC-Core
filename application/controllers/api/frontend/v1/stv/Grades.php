<?php
/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller operates between (interface) the JS (GUI) and the back-end
 * Provides data to the ajax get calls about grades
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Grades extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'list' => 'student/noten:r',
			'getCertificate' => 'student/noten:r',
			'getTeacherProposal' => 'student/noten:r',
			'getRepeaterGrades' => 'student/noten:r',
			'updateCertificate' => ['admin:w', 'assistenz:w'],
			'deleteCertificate' => ['admin:w', 'assistenz:w'],
			'copyTeacherProposalToCertificate' => 'student/noten:w',
			'copyRepeaterGradeToCertificate' => 'student/noten:w',
			'getGradeFromPoints' => 'student/noten:r'
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		// Load Phrases
		$this->loadPhrases([
			'stv',
			'person',
			'lehre'
		]);
	}

	/**
	 * List all possible grades
	 * (Entries in lehre.tbl_note)
	 *
	 * @return void
	 */
	public function list()
	{
		$this->load->model('codex/Note_model', 'NoteModel');

		$this->NoteModel->addOrder('note');
		
		$result = $this->NoteModel->load();

		$grades = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($grades);
	}

	/**
	 * List grades for the certificate of a prestudent.
	 * (Entries in lehre.tbl_zeugnisnote)
	 *
	 * @param string				$prestudent_id
	 * @param string|null			$studiensemester_kurzbz If studiensemester_kurzbz only this semesters grades will be loaded, otherwise all semesters grades will be loaded.
	 *
	 * @return void
	 */
	public function getCertificate($prestudent_id, $studiensemester_kurzbz = null)
	{
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$result = $this->StudentModel->loadWhere([
			'prestudent_id' => $prestudent_id
		]);

		$student = $this->getDataOrTerminateWithError($result);
		if (!$student)
			$this->terminateWithSuccess([]);
		
		$student_uid = current($student)->student_uid;

		if ($studiensemester_kurzbz !== null && !$this->StudiensemesterModel->isValidStudiensemester($studiensemester_kurzbz))
		{
			$this->terminateWithError($studiensemester_kurzbz . ' - ' . $this->p->t('lehre', 'error_noStudiensemester'));
		}

		$result = $this->ZeugnisnoteModel->getZeugnisnoten($student_uid, $studiensemester_kurzbz);

		$grades = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($grades);
	}

	/**
	 * List grades of a prestudent that teachers gave.
	 * (Entries in campus.tbl_lvgesamtnote)
	 *
	 * @param string				$prestudent_id
	 * @param string|null			$studiensemester_kurzbz If studiensemester_kurzbz only this semesters grades will be loaded, otherwise all semesters grades will be loaded.
	 *
	 * @return void
	 */
	public function getTeacherProposal($prestudent_id, $studiensemester_kurzbz = null)
	{
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('education/Lvgesamtnote_model', 'LvgesamtnoteModel');
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$result = $this->StudentModel->loadWhere([
			'prestudent_id' => $prestudent_id
		]);

		$student = $this->getDataOrTerminateWithError($result);
		if (!$student)
			$this->terminateWithSuccess([]);
		
		
		$student_uid = current($student)->student_uid;

		if ($studiensemester_kurzbz !== null && !$this->StudiensemesterModel->isValidStudiensemester($studiensemester_kurzbz))
		{
			$this->terminateWithError($studiensemester_kurzbz . ' - ' . $this->p->t('lehre', 'error_noStudiensemester'));
		}

		$result = $this->LvgesamtnoteModel->getLvGesamtNoten(null, $student_uid, $studiensemester_kurzbz);

		$grades = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($grades);
	}

	/**
	 * List grades of a prestudent that an assistant marked as already done
	 * or as not allowed because of the repeating of a semester.
	 *
	 * @param string				$prestudent_id
	 * @param string|false			$studiensemester_kurzbz If studiensemester_kurzbz only this semesters grades will be loaded, otherwise all semesters grades will be loaded.
	 *
	 * @return void
	 */
	public function getRepeaterGrades($prestudent_id, $studiensemester_kurzbz = false)
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->load->library('AntragLib');

		if ($studiensemester_kurzbz !== false && !$this->StudiensemesterModel->isValidStudiensemester($studiensemester_kurzbz))
		{
			$this->terminateWithError($studiensemester_kurzbz . ' - ' . $this->p->t('lehre', 'error_noStudiensemester'));
		}

		$result = $this->antraglib->getLvsForPrestudent($prestudent_id, $studiensemester_kurzbz);

		$grades = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($grades);
	}

	/**
	 * Update or Insert a grade for the certificate of a prestudent.
	 * (Entry in lehre.tbl_zeugnisnote)
	 *
	 * @return void
	 */
	public function updateCertificate()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules("lehrveranstaltung_id", $this->p->t('lehre', 'lehrveranstaltung'), "required|integer");
		$this->form_validation->set_rules("student_uid", $this->p->t('person', 'student'), "required");
		$this->form_validation->set_rules("studiensemester_kurzbz", $this->p->t('lehre', 'studiensemester'), "required");
		$this->form_validation->set_rules('note', $this->p->t('lehre', 'note'), 'required|numeric');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());


		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		$student_uid = $this->input->post('student_uid');
		$lehrveranstaltung_id = $this->input->post('lehrveranstaltung_id');
		$note = $this->input->post('note');
		$authUID = getAuthUID();
		$now = date('c');

		// NOTE(chris): Stg Permissions
		if (!$this->hasPermissionUpdate($lehrveranstaltung_id, $student_uid))
			return $this->_outputAuthError([$this->router->method => ['admin', 'assistenz']]);

		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');

		$result = $this->ZeugnisnoteModel->load([
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'student_uid' => $student_uid,
			'lehrveranstaltung_id' => $lehrveranstaltung_id
		]);
		$current = $this->getDataOrTerminateWithError($result);

		if ($current) {
			$result = $this->ZeugnisnoteModel->update([
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'student_uid' => $student_uid,
				'lehrveranstaltung_id' => $lehrveranstaltung_id
			], [
				'note' => $note,
				'benotungsdatum' => $now,
				'updateamum' => $now,
				'updatevon' => $authUID
			]);
		} else {
			$result = $this->ZeugnisnoteModel->insert([
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'student_uid' => $student_uid,
				'lehrveranstaltung_id' => $lehrveranstaltung_id,
				'note' => $note,
				'benotungsdatum' => $now,
				'insertamum' => $now,
				'insertvon' => $authUID
			]);
		}
		$this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(true);
	}

	/**
	 * Delete a grade from the certificate of a prestudent.
	 * (Entry in lehre.tbl_zeugnisnote)
	 *
	 * @return void
	 */
	public function deleteCertificate()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules("lehrveranstaltung_id", $this->p->t('lehre', 'lehrveranstaltung'), "required|integer");
		$this->form_validation->set_rules("student_uid", $this->p->t('person', 'student'), "required");
		$this->form_validation->set_rules("studiensemester_kurzbz", $this->p->t('lehre', 'studiensemester'), "required");

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());


		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		$student_uid = $this->input->post('student_uid');
		$lehrveranstaltung_id = $this->input->post('lehrveranstaltung_id');

		// NOTE(chris): Stg Permissions
		if (!$this->hasPermissionDelete($lehrveranstaltung_id, $student_uid))
			return $this->_outputAuthError([$this->router->method => ['admin', 'assistenz']]);

		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');

		$result = $this->ZeugnisnoteModel->delete([
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'student_uid' => $student_uid,
			'lehrveranstaltung_id' => $lehrveranstaltung_id
		]);
		$this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(true);
	}

	/**
	 * Copy a grade that teachers gave to the certificate of a prestudent.
	 * (Entry in campus.tbl_lvgesamtnote to an entry in lehre.tbl_zeugnisnote)
	 *
	 * @return void
	 */
	public function copyTeacherProposalToCertificate()
	{
		$this->load->library('form_validation');
	
		$this->form_validation->set_rules("lehrveranstaltung_id", $this->p->t('lehre', 'lehrveranstaltung'), "required|integer");
		$this->form_validation->set_rules("student_uid", $this->p->t('person', 'student'), "required");
		$this->form_validation->set_rules("studiensemester_kurzbz", $this->p->t('lehre', 'studiensemester'), "required");

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$lehrveranstaltung_id = $this->input->post('lehrveranstaltung_id');
		$student_uid = $this->input->post('student_uid');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		$authUID = getAuthUID();
		
		// NOTE(chris): Stg Permissions
		if (!$this->hasPermissionCopy($lehrveranstaltung_id, $student_uid))
			return $this->_outputAuthError([$this->router->method => 'student/noten']);

		$this->load->model('education/Lvgesamtnote_model', 'LvgesamtnoteModel');

		$result = $this->LvgesamtnoteModel->load([
			'student_uid' => $student_uid,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'lehrveranstaltung_id' => $lehrveranstaltung_id
		]);
		$teacherGrade = $this->getDataOrTerminateWithError($result);

		if (!$teacherGrade)
			show_404();

		$teacherGrade = current($teacherGrade);

		$data = [
			'note' => $teacherGrade->note,
			'punkte' => $teacherGrade->punkte,
			'uebernahmedatum' => date('c'),
			'benotungsdatum' => $teacherGrade->benotungsdatum,
			'bemerkung' => $teacherGrade->bemerkung
		];

		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');

		$this->ZeugnisnoteModel->addJoin('lehre.tbl_note n', 'note');
		$result = $this->ZeugnisnoteModel->load([
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'student_uid' => $student_uid,
			'lehrveranstaltung_id' => $lehrveranstaltung_id
		]);
		$certificateGrade = $this->getDataOrTerminateWithError($result);

		if ($certificateGrade) {
			$certificateGrade = current($certificateGrade);
			
			if (!$certificateGrade->lkt_ueberschreibbar)
				$this->terminateWithError($this->p->t("stv", "grades_error_overwrite"));

			// NOTE(chris): update
			$data['updateamum'] = $data['uebernahmedatum'];
			$data['updatevon'] = $authUID;

			$this->ZeugnisnoteModel->update([
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'student_uid' => $student_uid,
				'lehrveranstaltung_id' => $lehrveranstaltung_id
			], $data);
		} else {
			// NOTE(chris): insert
			$data['insertamum'] = $data['uebernahmedatum'];
			$data['insertvon'] = $authUID;
			$data['lehrveranstaltung_id'] = $lehrveranstaltung_id;
			$data['student_uid'] = $student_uid;
			$data['studiensemester_kurzbz'] = $studiensemester_kurzbz;
			
			$this->ZeugnisnoteModel->insert($data);

			if (defined('FAS_PRUEFUNG_BEI_NOTENEINGABE_ANLEGEN')
				&& FAS_PRUEFUNG_BEI_NOTENEINGABE_ANLEGEN) {
				$result = $this->addTestsForGrade(
					$studiensemester_kurzbz,
					$student_uid,
					$lehrveranstaltung_id,
					$teacherGrade->note,
					$teacherGrade->punkte
				);
				$this->getDataOrTerminateWithError($result);
			}
		}

		
		$this->terminateWithSuccess(true);
	}

	/**
	 * Copy a grade that was marked by an assistant as already done or not
	 * allowed because of the repeating of a semester to the certificate of a
	 * prestudent.
	 *
	 * @return void
	 */
	public function copyRepeaterGradeToCertificate()
	{
		$this->load->library('form_validation');
	
		$this->form_validation->set_rules("studierendenantrag_lehrveranstaltung_id", "studierendenantrag_lehrveranstaltung_id", "required|integer");

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$id = $this->input->post('studierendenantrag_lehrveranstaltung_id');
		$authUID = getAuthUID();
		
		$this->load->model('education/Studierendenantraglehrveranstaltung_model', 'StudierendenantraglehrveranstaltungModel');

		$this->StudierendenantraglehrveranstaltungModel->addSelect("tbl_studierendenantrag_lehrveranstaltung.*");
		$this->StudierendenantraglehrveranstaltungModel->addSelect("student_uid");
		$this->StudierendenantraglehrveranstaltungModel->addJoin("campus.tbl_studierendenantrag", "studierendenantrag_id");
		$this->StudierendenantraglehrveranstaltungModel->addJoin("public.tbl_student", "prestudent_id", "LEFT");
		
		$result = $this->StudierendenantraglehrveranstaltungModel->load($id);
		$repeaterGrade = $this->getDataOrTerminateWithError($result);

		if (!$repeaterGrade)
			show_404();

		$repeaterGrade = current($repeaterGrade);

		// NOTE(chris): Stg Permissions
		if (!$this->hasPermissionCopy($repeaterGrade->lehrveranstaltung_id, $repeaterGrade->student_uid))
			return $this->_outputAuthError([$this->router->method => 'student/noten']);

		$data = [
			'note' => $repeaterGrade->note,
			'uebernahmedatum' => date('c'),
			'benotungsdatum' => $repeaterGrade->insertamum,
			'bemerkung' => $repeaterGrade->anmerkung
		];

		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');

		$result = $this->ZeugnisnoteModel->load([
			$repeaterGrade->studiensemester_kurzbz,
			$repeaterGrade->student_uid,
			$repeaterGrade->lehrveranstaltung_id
		]);
		$certificateGrade = $this->getDataOrTerminateWithError($result);

		if ($certificateGrade) {
			// NOTE(chris): update
			$data['updateamum'] = $data['uebernahmedatum'];
			$data['updatevon'] = $authUID;

			$this->ZeugnisnoteModel->update([
				$repeaterGrade->studiensemester_kurzbz,
				$repeaterGrade->student_uid,
				$repeaterGrade->lehrveranstaltung_id
			], $data);
		} else {
			// NOTE(chris): insert
			$data['insertamum'] = $data['uebernahmedatum'];
			$data['insertvon'] = $authUID;
			$data['lehrveranstaltung_id'] = $repeaterGrade->lehrveranstaltung_id;
			$data['student_uid'] = $repeaterGrade->student_uid;
			$data['studiensemester_kurzbz'] = $repeaterGrade->studiensemester_kurzbz;
			
			$this->ZeugnisnoteModel->insert($data);
		}

		
		$this->terminateWithSuccess(true);
	}

	/**
	 * Loads the grade from the points using the gradingkey
	 *
	 * @return void
	 */
	public function getGradeFromPoints()
	{
		$this->load->library('form_validation');
	
		$this->form_validation->set_rules("lehrveranstaltung_id", $this->p->t('lehre', 'lehrveranstaltung'), "required|integer");
		$this->form_validation->set_rules("points", $this->p->t("stv", "grades_points"), "required|numeric");
		$this->form_validation->set_rules("studiensemester_kurzbz", $this->p->t("lehre", "studiensemester"), "required|regex_match[/^[WS]S[0-9]{4}$/]");

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		if (!$this->StudiensemesterModel->isValidStudiensemester($studiensemester_kurzbz))
		{
			$this->terminateWithError($studiensemester_kurzbz . ' - ' . $this->p->t('lehre', 'error_noStudiensemester'));
		}

		$this->load->model('education/Notenschluesselaufteilung_model', 'NotenschluesselaufteilungModel');
		
		$result = $this->NotenschluesselaufteilungModel->getNote(
			$this->input->post('points'),
			$this->input->post('lehrveranstaltung_id'),
			$studiensemester_kurzbz
		);

		$note = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($note);
	}

	/**
	 * Helper function that adds tests for a student
	 * (Entries in lehre.tbl_pruefung)
	 *
	 * @param string				$studiensemester_kurzbz
	 * @param string				$student_uid
	 * @param integer				$lehrveranstaltung_id
	 * @param integer				$note
	 * @param numeric				$punkte
	 *
	 * @return stdClass
	 */
	protected function addTestsForGrade($studiensemester_kurzbz, $student_uid, $lehrveranstaltung_id, $note, $punkte)
	{
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		// Get Lehreinheit
		$result = $this->LehrveranstaltungModel->getLeByStudent($student_uid, $studiensemester_kurzbz, $lehrveranstaltung_id);

		if (isError($result))
			return $result;
		if (!hasData($result))
			return error($this->p->t("stv", "grades_error_lehreinheit_id"));
		$le = current(getData($result));

		// Prepare
		$this->load->model('education/LePruefung_model', 'LePruefungModel');
		$data = [
			"student_uid" => $student_uid,
			"lehreinheit_id" => $le->lehreinheit_id,
			"datum" => date('Y-m-d'),
			"pruefungstyp_kurzbz" => "Termin1",
			"note" => $note
		];

		if (defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE)
			$data["punkte"] = $punkte;
		
		// Get Anwesenheit
		$this->load->model('education/Anwesenheit_model', 'AnwesenheitModel');
		$result = $this->AnwesenheitModel->loadAnwesenheitStudiensemester($studiensemester_kurzbz, $student_uid, $lehrveranstaltung_id);
		if (isError($result))
			return $result;
		$anwesenheit = getData($result);

		if ($anwesenheit && (float)current($anwesenheit)->prozent < FAS_ANWESENHEIT_ROT) {
			// Get Anwesenheitsbefreiung
			$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
			$result = $this->BenutzerfunktionModel->getBenutzerFunktionByUidInStdsem($student_uid, $studiensemester_kurzbz, 'awbefreit');

			if (isError($result))
				return $result;

			$anwesenheitsbefreit = hasData($result);

			// Wenn nicht Anwesenheitsbefreit und Anwesenheit unter einem bestimmten Prozentsatz fällt dann wird ein Pruefungsantritt abgezogen
			if (!$anwesenheitsbefreit) {
				$data2 = $data;
				$data2["note"] = 7;
				if (isset($data2["punkte"]))
					unset($data2["punkte"]);

				$result = $this->LePruefungModel->insert($data2);

				if (isError($result))
					return $result;

				$data["pruefungstyp_kurzbz"] = "Termin2";
			}
		}

		return $this->LePruefungModel->insert($data);
	}

	/**
	 * Helper function to check permissions for updateCertificate()
	 *
	 * @param integer				$lehrveranstaltung_id
	 * @param string				$student_uid
	 *
	 * @return boolean
	 */
	protected function hasPermissionUpdate($lehrveranstaltung_id, $student_uid)
	{
		if ($lehrveranstaltung_id === null || $student_uid === null)
			return true;

		$this->load->model('crm/Student_model', 'StudentModel');
		
		$result = $this->StudentModel->load([$student_uid]);
		if (isError($result) || !hasData($result))
			return false;

		$student = current(getData($result));

		if ($this->permissionlib->isBerechtigt('admin', 'suid', $student->studiengang_kz))
			return true;
		if ($this->permissionlib->isBerechtigt('assistenz', 'suid', $student->studiengang_kz))
			return true;

		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');

		$result = $this->StudienplanModel->getAllOesForLv($lehrveranstaltung_id);
		if (isError($result))
			return false;

		$oes = getData($result) ?: [];

		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		$result = $this->LehrveranstaltungModel->getStg($lehrveranstaltung_id);
		if (isError($result))
			return false;

		if (hasData($result))
			$oes[] = current(getData($result));

		foreach ($oes as $oe) {
			if ($this->permissionlib->isBerechtigt('admin', 'suid', $oe->oe_kurzbz))
				return true;
			if ($this->permissionlib->isBerechtigt('assistenz', 'suid', $oe->oe_kurzbz))
				return true;
		}

		return false;
	}

	/**
	 * Helper function to check permissions for deleteCertificate()
	 *
	 * @param integer				$lehrveranstaltung_id
	 * @param string				$student_uid
	 *
	 * @return boolean
	 */
	protected function hasPermissionDelete($lehrveranstaltung_id, $student_uid)
	{
		if ($lehrveranstaltung_id === null || $student_uid === null)
			return true;

		$this->load->model('crm/Student_model', 'StudentModel');
		
		$result = $this->StudentModel->load([$student_uid]);
		if (isError($result) || !hasData($result))
			return false;

		$student = current(getData($result));

		if ($this->permissionlib->isBerechtigt('admin', 'suid', $student->studiengang_kz))
			return true;
		if ($this->permissionlib->isBerechtigt('assistenz', 'suid', $student->studiengang_kz))
			return true;

		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		$result = $this->LehrveranstaltungModel->load($lehrveranstaltung_id);
		if (isError($result) || !hasData($result))
			return false;

		$oe = current(getData($result));

		if ($this->permissionlib->isBerechtigt('admin', 'suid', $oe->oe_kurzbz))
			return true;
		if ($this->permissionlib->isBerechtigt('assistenz', 'suid', $oe->oe_kurzbz))
			return true;

		return false;
	}

	/**
	 * Helper function to check permissions for
	 * copyTeacherProposalToCertificate() and copyRepeaterGradeToCertificate()
	 *
	 * @param integer				$lehrveranstaltung_id
	 * @param string				$student_uid
	 *
	 * @return boolean
	 */
	protected function hasPermissionCopy($lehrveranstaltung_id, $student_uid)
	{
		if ($lehrveranstaltung_id === null || $student_uid === null)
			return true;

		$this->load->model('crm/Student_model', 'StudentModel');
		
		$result = $this->StudentModel->load([$student_uid]);
		if (isError($result) || !hasData($result))
			return false;
		
		$student = current(getData($result));

		if ($this->permissionlib->isBerechtigt('student/noten', 'suid', $student->studiengang_kz))
			return true;

		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		
		$result = $this->LehrveranstaltungModel->load($lehrveranstaltung_id);
		if (isError($result) || !hasData($result))
			return false;
		
		$oe = current(getData($result));

		if ($this->permissionlib->isBerechtigt('student/noten', 'suid', $oe->oe_kurzbz))
			return true;

		return false;
	}
}
