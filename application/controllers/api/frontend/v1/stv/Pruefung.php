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
 * Provides data to the ajax get calls about addresses
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Pruefung extends FHCAPI_Controller
{
	public function __construct()
	{
		//TODO(Manu) Berechtigungen
		parent::__construct([
			'getPruefungen' => ['admin:r', 'assistenz:r'],
			'loadPruefung' => ['admin:r', 'assistenz:r'],
			'getTypenPruefungen' => self::PERM_LOGGED,
			'getLehreinheiten' => self::PERM_LOGGED,
			'getAllLehreinheiten' => self::PERM_LOGGED,
			'getLvsByStudent' => self::PERM_LOGGED,
			'getLvsandLesByStudent' => self::PERM_LOGGED,
			'getLvsAndMas' => self::PERM_LOGGED,
			'getMitarbeiterLv' => self::PERM_LOGGED,
			'getNoten' => self::PERM_LOGGED,
			'insertPruefung' => ['admin:r', 'assistenz:r'],
			'updatePruefung' =>['admin:r', 'assistenz:r'],
			'deletePruefung' =>['admin:r', 'assistenz:r'],
		]);

		//Load Models
		$this->load->model('education/LePruefung_model', 'PruefungModel');

		//TODO(Manu) check
		// Additional Permission Checks
		//not working if activated?
/*		if ($this->router->method == 'insertPruefung'
			|| $this->router->method == 'updatePruefung'
			|| $this->router->method == 'deletePruefung'
		) {
			$prestudent_id = current(array_slice($this->uri->rsegments, 2));
			$this->checkPermissionsForPrestudent($prestudent_id, ['admin:rw', 'assistenz:rw']);
		}*/

		// Load language phrases
		$this->loadPhrases([
			'global', 'ui','lehre'
		]);
	}

	public function getPruefungen($student_uid, $studiensemester_kurzbz = null)
	{
		$result = $this->PruefungModel->getPruefungenByStudentuid($student_uid);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function loadPruefung($pruefung_id)
	{
		$this->PruefungModel->addSelect('tbl_pruefung.datum');
		$this->PruefungModel->addSelect("TO_CHAR(tbl_pruefung.datum::timestamp, 'DD.MM.YYYY') AS format_datum");
		$this->PruefungModel->addSelect('tbl_pruefung.anmerkung');
		$this->PruefungModel->addSelect('tbl_pruefung.pruefungstyp_kurzbz');
		$this->PruefungModel->addSelect('tbl_pruefung.pruefung_id');
		$this->PruefungModel->addSelect('tbl_pruefung.lehreinheit_id');
		$this->PruefungModel->addSelect('tbl_pruefung.student_uid');
		$this->PruefungModel->addSelect('tbl_pruefung.mitarbeiter_uid');
		$this->PruefungModel->addSelect('tbl_pruefung.punkte');
		$this->PruefungModel->addSelect('tbl_pruefung.note');

		$this->PruefungModel->addSelect('tbl_lehrveranstaltung.bezeichnung as lehrveranstaltung_bezeichnung');
		$this->PruefungModel->addSelect('tbl_lehrveranstaltung.lehrveranstaltung_id');
		$this->PruefungModel->addSelect('tbl_lehrveranstaltung.semester');
		$this->PruefungModel->addSelect('tbl_lehrveranstaltung.lehrform_kurzbz');
		$this->PruefungModel->addSelect('tbl_note.bezeichnung as note_bezeichnung');
		$this->PruefungModel->addSelect('tbl_pruefungstyp.beschreibung as typ_beschreibung');
		$this->PruefungModel->addSelect('tbl_lehreinheit.studiensemester_kurzbz as studiensemester_kurzbz');

		$this->PruefungModel->addJoin('lehre.tbl_lehreinheit',  'lehre.tbl_pruefung.lehreinheit_id=lehre.tbl_lehreinheit.lehreinheit_id');
		$this->PruefungModel->addJoin('lehre.tbl_lehrveranstaltung', 'lehrveranstaltung_id');
		$this->PruefungModel->addJoin('lehre.tbl_note', 'note');
		$this->PruefungModel->addJoin('lehre.tbl_pruefungstyp', 'pruefungstyp_kurzbz');


		$this->PruefungModel->addLimit(1);

		$result = $this->PruefungModel->loadWhere(
			array('pruefung_id' => $pruefung_id)
		);
		if (isError($result)) {
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		if (!hasData($result))
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Pruefung_id']), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess(current(getData($result)) ? : null);
	}

	public function insertPruefung(){

		$authUID = getAuthUID();
		$lehreinheit_id = $this->input->post('lehreinheit_id');
		$student_uid = $this->input->post('student_uid');
		$mitarbeiter_uid = $this->input->post('mitarbeiter_uid');
		$datum = $this->input->post('datum');
		$note = $this->input->post('note');
		$pruefungstyp_kurzbz = $this->input->post('pruefungstyp_kurzbz');
		$anmerkung = $this->input->post('anmerkung');

		$this->load->library('form_validation');

		$this->form_validation->set_rules('lehrveranstaltung_id', $this->p->t('lehre', 'lehrveranstaltung'), 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => $this->p->t('lehre','lehrveranstaltung')]),
		]);

		$this->form_validation->set_rules('lehreinheit_id', $this->p->t('lehre', 'lehreinheit'), 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => $this->p->t('lehre','lehreinheit')]),
		]);

		$this->form_validation->set_rules('pruefungstyp_kurzbz', $this->p->t('lehre', 'pruefung'), 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => $this->p->t('global','typ')]),
		]);

		$this->form_validation->set_rules(
			'datum', $this->p->t('global', 'datum'), ['is_valid_date']
		);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->PruefungModel->insert([
			'lehreinheit_id' => $lehreinheit_id,
			'student_uid' => $student_uid,
			'mitarbeiter_uid' => $mitarbeiter_uid,
			'datum' => $datum,
			'pruefungstyp_kurzbz' => $pruefungstyp_kurzbz,
			'note' => $note,
			'anmerkung' => $anmerkung,
			'insertamum' => date('c'),
			'insertvon' => $authUID,
		]);

		$this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(true);

	}

	public function updatePruefung($pruefung_id){

		$result = $this->PruefungModel->load($pruefung_id);

		$oldpruefung = $this->getDataOrTerminateWithError($result);
		if (!$oldpruefung)
			show_404(); // Pruefung that should be updated does not exist

		$authUID = getAuthUID();

		$lehreinheit_id = $this->input->post('lehreinheit_id');
		$student_uid = $this->input->post('student_uid');
		$mitarbeiter_uid = $this->input->post('mitarbeiter_uid');
		$datum = $this->input->post('datum');
		$note = $this->input->post('note');
		$pruefungstyp_kurzbz = $this->input->post('pruefungstyp_kurzbz');
		$anmerkung = $this->input->post('anmerkung');

		$this->load->library('form_validation');

		$this->form_validation->set_rules('lehrveranstaltung_id', $this->p->t('lehre', 'lehrveranstaltung'), 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => $this->p->t('lehre','lehrveranstaltung')]),
		]);

		$this->form_validation->set_rules('lehreinheit_id', $this->p->t('lehre', 'lehreinheit'), 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => $this->p->t('lehre','lehreinheit')]),
		]);

		$this->form_validation->set_rules('pruefungstyp_kurzbz', $this->p->t('lehre', 'pruefung'), 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => $this->p->t('global','typ')]),
		]);

		$this->form_validation->set_rules(
			'datum', $this->p->t('global', 'datum'), ['is_valid_date']
		);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->PruefungModel->update(
			[
				'pruefung_id' => $pruefung_id
			],
			[	'lehreinheit_id' => $lehreinheit_id,
				'student_uid' =>  $student_uid,
				'mitarbeiter_uid' =>  $mitarbeiter_uid,
				'note' => $note,
				'pruefungstyp_kurzbz' => $pruefungstyp_kurzbz,
				'datum' => $datum,
				'anmerkung' => $anmerkung,
				'updatevon' => $authUID,
				'updateamum' => date('c'),
			]
		);

		$this->getDataOrTerminateWithError($result);

		return $this->outputJsonSuccess(true);
	}

	public function deletePruefung($pruefung_id){
		$result = $this->PruefungModel->load($pruefung_id);

		$oldpruefung = $this->getDataOrTerminateWithError($result);
		if (!$oldpruefung)
			show_404(); // Pruefung that should be deleted does not exist

		$result = $this->PruefungModel->delete(
			[
				'pruefung_id' => $pruefung_id
			]
		);

		$this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess(true);
	}

	public function getTypenPruefungen()
	{
		$this->load->model('education/Pruefungstyp_model', 'PruefungtypModel');

		//TODO(Manu) sort Termin3
		$this->PruefungtypModel->addOrder('sort', 'ASC');
		$result = $this->PruefungtypModel->loadWhere(
			array('abschluss' => 'false')
		);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getAllLehreinheiten(){

		//TODO MANU (validations)

		$lv_id = $this->input->post('lv_id');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');

		$this->load->model('education/Lehreinheit_model', 'LehreinheitModel');

		$result = $this->LehreinheitModel->getLesFromLvIds($lv_id, $studiensemester_kurzbz);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getLvsandLesByStudent($student_uid)
	{
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		$result = $this->LehrveranstaltungModel->getLvsByStudent($student_uid);

		$data = $this->getDataOrTerminateWithError($result);

		$lv_ids = array();
		$allData = array();

		foreach ($data as $lehrveranstaltung) {
			$lv_ids[] = $lehrveranstaltung->lehrveranstaltung_id;
		}

		$this->load->model('education/Lehreinheit_model', 'LehreinheitModel');

		foreach ($lv_ids as $id)
		{
			$result = $this->LehreinheitModel->getLesFromLvIds($id);
			$data = $this->getDataOrTerminateWithError($result);

			if (is_array($data)) {
				$allData = array_merge($allData, $data);
			}
		}

		return $this->terminateWithSuccess($allData);
	}

	public function getLvsAndMas($student_uid)
	{
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		$result = $this->LehrveranstaltungModel->getLvsByStudent($student_uid);

		$data = $this->getDataOrTerminateWithError($result);

		$lv_ids = array();
		$allDataMa = array();

		foreach ($data as $lehrveranstaltung) {
			$lv_ids[] = $lehrveranstaltung->lehrveranstaltung_id;
		}

		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');

		foreach ($lv_ids as $id) {

			$resultMa = $this->MitarbeiterModel->getMitarbeiterFromLV($id);
			$dataMa = $this->getDataOrTerminateWithError($resultMa);

			if (is_array($dataMa)) {
				$allDataMa = array_merge($allDataMa, $dataMa);
			}
		}

		return $this->terminateWithSuccess($allDataMa);
	}

	public function getLvsByStudent($student_uid, $studiensemester_kurzbz=null )
	{
		//bei post request
/*		$student_uid = $this->input->post('student_uid');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');*/

		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		$result = $this->LehrveranstaltungModel->getLvsByStudent($student_uid, $studiensemester_kurzbz);

		$data = $this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess($data);
	}

	public function getMitarbeiterLv($lv_id)
	{
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');

		$result = $this->MitarbeiterModel->getMitarbeiterFromLV($lv_id);

		$data = $this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess($data);
	}

	public function getNoten()
	{
		$this->load->model('education/Note_model', 'NoteModel');

		$this->NoteModel->addOrder('note', 'ASC');
		$result = $this->NoteModel->load();

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}


}
