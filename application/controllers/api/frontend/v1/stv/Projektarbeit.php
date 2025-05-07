<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Projektarbeit extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getProjektarbeit' => ['admin:r', 'assistenz:r'],
			'loadProjektarbeit' => ['admin:r', 'assistenz:r'],
			'insertProjektarbeit' => ['admin:rw', 'assistenz:rw'],
			'updateProjektarbeit' => ['admin:rw', 'assistenz:rw'],
			'deleteProjektarbeit' => ['admin:rw', 'assistenz:rw'],
			'getTypenProjektarbeit' => ['admin:r', 'assistenz:r'],
			'getFirmen' => ['admin:r', 'assistenz:r'],
			'getLehrveranstaltungen' => ['admin:r', 'assistenz:r'],
			'getNoten' => ['admin:rw', 'assistenz:rw']
		]);

		// Load Libraries
		$this->load->library('form_validation');

		// Load language phrases
		$this->loadPhrases([
			'ui',
			'person',
			'projektarbeit'
		]);

		// Load models
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		$this->load->model('education/Projekttyp_model', 'ProjekttypModel');
		$this->load->model('education/Paabgabe_model', 'PaabgabeModel');
		$this->load->model('ressource/Firma_model', 'FirmaModel');
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->load->model('education/Lehreinheit_model', 'LehreinheitModel');
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('education/Note_model', 'NoteModel');
		$this->load->model('education/Projektbetreuer_model', 'BetreuerModel');

		// load libraries
		$this->load->library('PermissionLib');
	}

	public function getProjektarbeit()
	{
		$student_uid = $this->input->get('uid');

		if (!isset($student_uid)) $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Student UID']), self::ERROR_TYPE_GENERAL);

		$result = $this->ProjektarbeitModel->getProjektarbeit($student_uid);

		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		if (!hasData($result)) $this->terminateWithSuccess([]);

		$projektarbeiten = getData($result);

		foreach ($projektarbeiten as $projektarbeit)
		{
			$projektarbeit_id = $projektarbeit->projektarbeit_id;
			$abgabeRes = $this->PaabgabeModel->getEndabgabe($projektarbeit_id);

			if (isError($abgabeRes)) $this->terminateWithError(getError($abgabeRes), self::ERROR_TYPE_GENERAL);

			if (hasData($abgabeRes))
			{
				$paabgabe = getData($abgabeRes)[0];
				$projektarbeit->abgabedatum = $paabgabe->abgabedatum;
			}
		}

		$this->terminateWithSuccess($projektarbeiten);
	}

	public function loadProjektarbeit()
	{
		$projektarbeit_id = $this->input->get('projektarbeit_id');

		if (!isset($projektarbeit_id) || !is_numeric($projektarbeit_id)) return $this->terminateWithError('Projektarbeit Id missing', self::ERROR_TYPE_GENERAL);

		$this->ProjektarbeitModel->addSelect(
			'lehre.tbl_projektarbeit.projektarbeit_id, titel, titel_english, themenbereich, projekttyp_kurzbz, firma_id,
			lehrveranstaltung_id, lehreinheit_id, beginn, note, final, freigegeben, tbl_projektarbeit.anmerkung, fa.name AS firma_name'
		);
		$this->ProjektarbeitModel->addJoin('lehre.tbl_lehreinheit le', 'lehreinheit_id');
		$this->ProjektarbeitModel->addJoin('lehre.tbl_lehrveranstaltung lv', 'lehrveranstaltung_id');
		$this->ProjektarbeitModel->addJoin('public.tbl_firma fa', 'firma_id');
		$result = $this->ProjektarbeitModel->loadWhere(
			array('projektarbeit_id' => $projektarbeit_id)
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}

	public function insertProjektarbeit()
	{
		$student_uid = $this->input->post('uid');

		if (!$student_uid) return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Student UID']), self::ERROR_TYPE_GENERAL);

		if (!$this->_hasBerechtigungForStudent($student_uid))
			return $this->_outputAuthError([$this->router->method => ['admin:rw', 'assistenz:rw']]);

		$formData = $this->input->post('formData');

		$this->addMeta('form', $formData);

		if ($this->_validate($formData) == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->ProjektarbeitModel->insert(
			array_merge($formData, ['insertamum' => date('c'), 'insertvon' => getAuthUID(), 'student_uid' => $student_uid])
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function updateProjektarbeit()
	{
		$projektarbeit_id = $this->input->post('projektarbeit_id');

		if (!$projektarbeit_id || !is_numeric($projektarbeit_id))
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Projektarbeit ID']), self::ERROR_TYPE_GENERAL);

		if (!$this->ProjektarbeitModel->hasBerechtigungForProjektarbeit($projektarbeit_id))
			return $this->_outputAuthError([$this->router->method => ['admin:rw', 'assistenz:rw']]);

		$formData = $this->input->post('formData');

		if ($this->_validate($formData) == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->ProjektarbeitModel->update(
			$projektarbeit_id,
			array_merge($formData, ['updateamum' => date('c'), 'updatevon' => getAuthUID()])
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function deleteProjektarbeit()
	{
		$projektarbeit_id = $this->input->post('projektarbeit_id');

		if (!isset($projektarbeit_id) || !is_numeric($projektarbeit_id))
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Projektarbeit ID'], self::ERROR_TYPE_GENERAL));

		if (!$this->ProjektarbeitModel->hasBerechtigungForProjektarbeit($projektarbeit_id))
			return $this->_outputAuthError([$this->router->method => ['admin:rw', 'assistenz:rw']]);

		$validate = $this->_validateDelete($projektarbeit_id);

		if (isError($validate)) return $this->terminateWithError(getError($validate), self::ERROR_TYPE_GENERAL);

		$result = $this->ProjektarbeitModel->delete(
			array('projektarbeit_id' => $projektarbeit_id)
		);

		if (isError($result)) return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);

		if (!hasData($result))
		{
			$this->outputJson($result);
		}

		return $this->terminateWithSuccess(current(getData($result)) ? : null);
	}

	public function getTypenProjektarbeit()
	{
		$result = $this->ProjekttypModel->loadWhere(['aktiv' => true]);

		if (isError($result)) return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);

		return $this->terminateWithSuccess(hasData($result) ? getData($result) : []);
	}

	public function getFirmen()
	{
		$searchString = $this->input->get('searchString');

		if (!isset($searchString)) $this->terminateWithError($this->p->t('projektarbeit', 'error_searchStringMissing', self::ERROR_TYPE_GENERAL));

		$result = $this->FirmaModel->searchFirmen($searchString, $aktiv = true);

		if (isError($result)) return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);

		return $this->terminateWithSuccess(hasData($result) ? getData($result) : []);
	}

	public function getLehrveranstaltungen()
	{
		$student_uid = $this->input->get('student_uid');
		$studiengang_kz = $this->input->get('studiengang_kz');
		$studiensemester_kurzbz = $this->input->get('studiensemester_kurzbz');
		$additional_lehrveranstaltung_id = $this->input->get('additional_lehrveranstaltung_id');

		if (!isset($student_uid)) $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Student UID']), self::ERROR_TYPE_GENERAL);
		if (!isset($studiensemester_kurzbz)) $this->terminateWithError('Studiensemster missing', self::ERROR_TYPE_GENERAL);

		$lvsResult = $this->LehrveranstaltungModel->getLvsForProjektarbeit($student_uid, $studiengang_kz, $additional_lehrveranstaltung_id);

		if (isError($lvsResult)) return $this->terminateWithError($lvsResult, self::ERROR_TYPE_GENERAL);

		$lvs = hasData($lvsResult) ? getData($lvsResult) : [];

		foreach ($lvs as $lv)
		{
			$lehreinheiten = $this->LehreinheitModel->getLesForLv(
				$lv->lehrveranstaltung_id, $studiensemester_kurzbz
			);

			foreach ($lehreinheiten as $lehreinheit)
			{
				if (!isEmptyArray($lehreinheit->lektoren))
				{
					$this->MitarbeiterModel->addSelect('kurzbz');
					$this->MitarbeiterModel->db->where_in('tbl_mitarbeiter.mitarbeiter_uid', $lehreinheit->lektoren);
					$maResult = $this->MitarbeiterModel->load();

					if (isError($maResult)) return $this->terminateWithError($lvsResult, self::ERROR_TYPE_GENERAL);

					$lehreinheit->lektoren = array_column(getData($maResult), 'kurzbz');
				}
			}

			$lv->lehreinheiten = $lehreinheiten;
		}

		return $this->terminateWithSuccess($lvs);
	}

	public function getNoten()
	{
		$result = $this->NoteModel->load();

		if (isError($result)) return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);

		return $this->terminateWithSuccess(hasData($result) ? getData($result) : []);
	}

	/**
	 *
	 * @param
	 * @return object success or error
	 */
	private function _validate($formData)
	{
		$this->form_validation->set_data($formData);

		$this->form_validation->set_rules('titel', 'Titel', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Titel'])
		]);

		$this->form_validation->set_rules('projekttyp_kurzbz', 'Projekttyp', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Projekttyp'])
		]);

		$this->form_validation->set_rules('lehreinheit_id', 'Lehreinheit', 'required|numeric', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Lehreinheit']),
			//'matches' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Lehreinheit']),
			'numeric' =>  $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Lehreinheit'])
		]);

		$this->form_validation->set_rules('beginn', 'Beginn', 'is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'Beginn'])
		]);

		return $this->form_validation->run();
	}

	/**
	 *
	 * @param
	 * @return object success or error
	 */
	private function _validateDelete($projektarbeit_id)
	{
		$this->BetreuerModel->addSelect('1');
		$result = $this->BetreuerModel->loadWhere(['projektarbeit_id' => $projektarbeit_id]);

		if (isError($result)) return $result;

		if (hasData($result)) return error($this->p->t('projektarbeit', 'error_betreuerNichtGeloescht'));

		$this->PaabgabeModel->addSelect('1');
		$result = $this->PaabgabeModel->loadWhere(['projektarbeit_id' => $projektarbeit_id]);

		if (isError($result)) return $result;

		if (hasData($result)) return error($this->p->t('projektarbeit', 'error_paabgabeNichtGeloescht'));

		return success();
	}

	private function _hasBerechtigungForStudent($student_uid)
	{
		if (!$student_uid)
			return false;

		$this->load->model('crm/Student_model', 'StudentModel');

		$this->StudentModel->addSelect('studiengang_kz');
		$result = $this->StudentModel->load([$student_uid]);
		if (isError($result) || !hasData($result))
			return false;

		$studiengang_kz = getData($result)[0]->studiengang_kz;

		if ($this->permissionlib->isBerechtigt('admin', 'suid', $studiengang_kz))
			return true;
		if ($this->permissionlib->isBerechtigt('assistenz', 'suid', $studiengang_kz))
			return true;

		return false;
	}
}
