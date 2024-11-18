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
			'updateCertificate' => ['admin:w', 'assistenz:w'],
			'getGradeFromPoints' => 'student/noten:r'
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
	}

	public function list()
	{
		$this->load->model('codex/Note_model', 'NoteModel');

		$this->NoteModel->addOrder('note');
		
		$result = $this->NoteModel->load();

		$grades = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($grades);
	}

	public function getCertificate($prestudent_id, $all = null)
	{
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');

		$result = $this->StudentModel->loadWhere([
			'prestudent_id' => $prestudent_id
		]);

		$student = $this->getDataOrTerminateWithError($result);
		if (!$student)
			$this->terminateWithSuccess([]);
		
		
		$student_uid = current($student)->student_uid;

		$studiensemester_kurzbz = ($all === null) ? $this->variablelib->getVar('semester_aktuell') : null;

		
		$result = $this->ZeugnisnoteModel->getZeugnisnoten($student_uid, $studiensemester_kurzbz);

		$grades = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($grades);
	}

	public function updateCertificate()
	{
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');

		$this->load->library('form_validation');

		if (empty($_POST) || !is_array(current($_POST))) {
			$result = $this->hasPermissionUpdate($this->input->post('lehrveranstaltung_id'), $this->input->post('student_uid'));
			if (isError($result)) {
				$this->terminateWithError(getError($result), self::ERROR_TYPE_AUTH, REST_Controller::HTTP_FORBIDDEN);
			}

			$this->form_validation->set_rules('lehrveranstaltung_id', 'Lehrverantaltung ID', 'required|numeric');
			$this->form_validation->set_rules('student_uid', 'Student UID', 'required');
			$this->form_validation->set_rules('studiensemester_kurzbz', 'Studiensemester Kurzbezeichnung', 'required');
			$this->form_validation->set_rules('note', 'Note', 'required|numeric');
			$post = [$_POST];
		} else {
			foreach ($_POST as $i => $data) {
				$lvid = isset($data['lehrveranstaltung_id']) ? $data['lehrveranstaltung_id'] : null;
				$uid = isset($data['student_uid']) ? $data['student_uid'] : null;
				$result = $this->hasPermissionUpdate($lvid, $uid);
				if (isError($result)) {
					$this->terminateWithError(getError($result), self::ERROR_TYPE_AUTH, REST_Controller::HTTP_FORBIDDEN);
				}

				$this->form_validation->set_rules($i . '[lehrveranstaltung_id]', '#' . $i . ' Lehrverantaltung ID', 'required|numeric');
				$this->form_validation->set_rules($i . '[student_uid]', '#' . $i . ' Student UID', 'required');
				$this->form_validation->set_rules($i . '[studiensemester_kurzbz]', '#' . $i . ' Studiensemester Kurzbezeichnung', 'required');
				$this->form_validation->set_rules($i . '[note]', '#' . $i . ' Note', 'required|numeric');
			}
			$post = $_POST;
		}
		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$this->ZeugnisnoteModel->db->trans_start();
		$authUID = getAuthUID();

		foreach ($post as $i => $data) {
			$note = $data['note'];
			unset($data['note']);
			$result = $this->ZeugnisnoteModel->update($data, [
				'note' => $note,
				'benotungsdatum' => date('c'),
				'updateamum' => date('c'),
				'updatevon' => $authUID
			]);
			$this->getDataOrTerminateWithError($result);
		}

		$this->ZeugnisnoteModel->db->trans_complete();

		$this->terminateWithSuccess(true);
	}

	public function getGradeFromPoints()
	{
		$this->load->library('form_validation');
	
		$this->form_validation->set_rules("lehrveranstaltung_id", "Lehrverantaltung ID", "required|integer"); // TODO(chris): phrase
		$this->form_validation->set_rules("points", "Punkte", "required|numeric"); // TODO(chris): phrase

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		
		$this->load->model('education/Notenschluesselaufteilung_model', 'NotenschluesselaufteilungModel');

		$studiensemester_kurzbz = $this->variablelib->getVar('semester_aktuell');
		
		$result = $this->NotenschluesselaufteilungModel->getNote(
			$this->input->post('points'),
			$this->input->post('lehrveranstaltung_id'),
			$studiensemester_kurzbz
		);

		$note = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($note);
	}

	protected function hasPermissionUpdate($lehrveranstaltung_id, $student_uid)
	{
		// TODO(chris): error phrases!
		if ($lehrveranstaltung_id === null || $student_uid === null)
			return success();
		$result = $this->StudentModel->load([$student_uid]);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return error('Fehler beim Ermitteln des Studenten');
		$student = current(getData($result));

		if ($this->permissionlib->isBerechtigt('admin', 'suid', $student->studiengang_kz))
			return success();
		if ($this->permissionlib->isBerechtigt('assistenz', 'suid', $student->studiengang_kz))
			return success();

		$result = $this->StudienplanModel->getAllOesForLv($lehrveranstaltung_id);
		if (isError($result))
			return $result;
		$oes = getData($result) ?: [];
		$result = $this->LehrveranstaltungModel->getStg($lehrveranstaltung_id);
		if (isError($result))
			return $result;
		if (hasData($result))
			$oes[] = current(getData($result));

		foreach ($oes as $oe) {
			if ($this->permissionlib->isBerechtigt('admin', 'suid', $oe->oe_kurzbz))
				return success();
			if ($this->permissionlib->isBerechtigt('assistenz', 'suid', $oe->oe_kurzbz))
				return success();
		}

		return error('Forbidden');
	}
}
