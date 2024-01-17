<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Noten extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct([
			'get' => 'student/noten:r',
			'getZeugnis' => 'student/noten:r',
			'update' => ['admin:w', 'assistenz:w']
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
	}

	public function get()
	{
		$this->load->model('codex/Note_model', 'NoteModel');

		$result = $this->NoteModel->addOrder('note');
		
		$result = $this->NoteModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		
		return $this->outputJson($result);
	}

	public function getZeugnis($prestudent_id, $all = null)
	{
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');

		$result = $this->StudentModel->loadWhere([
			'prestudent_id' => $prestudent_id
		]);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}
		if (!hasData($result))
			return $this->outputJsonSuccess(null);
		
		$student_uid = current(getData($result))->student_uid;

		$studiensemester_kurzbz = ($all === null) ? $this->variablelib->getVar('semester_aktuell') : null;

		$result = $this->ZeugnisnoteModel->getZeugnisnoten($student_uid, $studiensemester_kurzbz);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		
		return $this->outputJson($result);
	}

	public function update()
	{
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');

		$this->load->library('form_validation');

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);
		
		if (empty($_POST) || !is_array(current($_POST))) {
			$result = $this->hasPermissionUpdate($this->input->post('lehrveranstaltung_id'), $this->input->post('student_uid'));
			if (isError($result)) {
				$this->output->set_status_header(REST_Controller::HTTP_FORBIDDEN);
				return $this->outputJson($result);
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
					$this->output->set_status_header(REST_Controller::HTTP_FORBIDDEN);
					return $this->outputJson($result);
				}

				$this->form_validation->set_rules($i . '[lehrveranstaltung_id]', '#' . $i . ' Lehrverantaltung ID', 'required|numeric');
				$this->form_validation->set_rules($i . '[student_uid]', '#' . $i . ' Student UID', 'required');
				$this->form_validation->set_rules($i . '[studiensemester_kurzbz]', '#' . $i . ' Studiensemester Kurzbezeichnung', 'required');
				$this->form_validation->set_rules($i . '[note]', '#' . $i . ' Note', 'required|numeric');
			}
			$post = $_POST;
		}
		if ($this->form_validation->run() == false) {
			$this->output->set_status_header(REST_Controller::HTTP_BAD_REQUEST);
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$final_result = success();
		$this->ZeugnisnoteModel->db->trans_start();

		foreach ($post as $i => $data) {
			$note = $data['note'];
			unset($data['note']);
			$result = $this->ZeugnisnoteModel->update($data, [
				'note' => $note,
				'benotungsdatum' => date('c'),
				'updateamum' => date('c'),
				'updatevon' => getAuthUID()
			]);
			if (isError($result)) {
				$final_result = $result;
				break;
			}
		}

		$this->ZeugnisnoteModel->db->trans_complete();

		if (isError($final_result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->outputJson($final_result);
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
