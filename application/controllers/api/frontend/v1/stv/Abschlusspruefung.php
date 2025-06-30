<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Abschlusspruefung extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getAbschlusspruefung' => ['admin:r', 'assistenz:r'],
			'loadAbschlusspruefung' => ['admin:r', 'assistenz:r'],
			'insertAbschlusspruefung' => ['admin:rw', 'assistenz:rw'],
			'updateAbschlusspruefung' => ['admin:rw', 'assistenz:rw'],
			'deleteAbschlusspruefung' => ['admin:rw', 'assistenz:rw'],
			'getTypenAbschlusspruefung' => ['admin:rw', 'assistenz:rw'],
			'getNoten' => ['admin:rw', 'assistenz:rw'],
			'getTypenAntritte' => ['admin:rw', 'assistenz:rw'],
			'getBeurteilungen' => ['admin:rw', 'assistenz:rw'],
			'getAkadGrade' => ['admin:rw', 'assistenz:rw'],
			'getMitarbeiter' => ['admin:rw', 'assistenz:rw'],
			'getAllMitarbeiter' => ['admin:rw', 'assistenz:rw'],
			'getAllPersons' => ['admin:rw', 'assistenz:rw'],
			'getPruefer' => ['admin:rw', 'assistenz:rw'],
			'getTypStudiengang' => ['admin:rw', 'assistenz:rw'],
			'checkForExistingExams' => ['admin:rw', 'assistenz:rw'],
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');

		// Load language phrases
		$this->loadPhrases([
			'ui',
			'person',
			'abschlusspruefung'
		]);

		// Load models
		$this->load->model('education/Abschlusspruefung_model', 'AbschlusspruefungModel');


		//Permission checks for Studiengangsarray
		$allowedStgs = $this->permissionlib->getSTG_isEntitledFor('assistenz') ?: [];

		if ($this->router->method == 'insertAbschlusspruefung' || $this->router->method == 'updateAbschlusspruefung')
		{
			$student_uid = $this->input->post('uid') ?: ($this->input->post('formData')['student_uid'] ?? null);

			if(!$student_uid)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Student UID']), self::ERROR_TYPE_GENERAL);
			}
			$this->_checkAllowedStgsFromUid($student_uid, $allowedStgs);
		}

		if ($this->router->method == 'deleteAbschlusspruefung')
		{
			$abschlusspruefung_id = $this->input->post('id');

			if(!$abschlusspruefung_id)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Abschlusspruefung ID']), self::ERROR_TYPE_GENERAL);
			}
			$result = $this->AbschlusspruefungModel->load(
				array('abschlusspruefung_id' => $abschlusspruefung_id)
			);
			$data = $this->getDataOrTerminateWithError($result);
			$student_uid = current($data)->student_uid;

			$this->_checkAllowedStgsFromUid($student_uid, $allowedStgs);
		}
	}

	private function _checkAllowedStgsFromUid($student_uid, $allowedStgs)
	{
		$this->load->model('crm/Student_model', 'StudentModel');
		$result = $this->StudentModel->loadWhere(['student_uid' => $student_uid]);
		$data = $this->getDataOrTerminateWithError($result);
		$studiengang_kz = current($data)->studiengang_kz;

		if (!in_array($studiengang_kz, $allowedStgs))
		{
			return $this->terminateWithError($this->p->t('ui', 'error_keineBerechtigungStg'), self::ERROR_TYPE_GENERAL);
		}
	}

	public function getAbschlusspruefung($student_uid)
	{
		$result = $this->AbschlusspruefungModel->getAbschlusspruefungForPrestudent($student_uid);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function loadAbschlusspruefung()
	{
		$abschlusspruefung_id = $this->input->post('id');

		$this->AbschlusspruefungModel->addSelect('lehre.tbl_abschlusspruefung.*');
		$this->AbschlusspruefungModel->addSelect("
			CASE
				WHEN pruefer1 IS NOT NULL
				THEN CONCAT(p1.nachname, ' ', p1.vorname, COALESCE(' ' || p1.titelpre, ''))
				ELSE NULL
   			 END AS p1
   		");
		$this->AbschlusspruefungModel->addSelect("
			CASE
				WHEN pruefer2 IS NOT NULL
				THEN CONCAT(p2.nachname, ' ', p2.vorname, COALESCE(' ' || p2.titelpre, ''))
				ELSE NULL
   			 END AS p2
		");
		$this->AbschlusspruefungModel->addSelect("
			CASE
				WHEN pruefer3 IS NOT NULL
				THEN CONCAT(p3.nachname, ' ', p3.vorname, COALESCE(' ' || p3.titelpre, ''))
				ELSE NULL
   			 END AS p3
		");
		$this->AbschlusspruefungModel->addSelect("
			CASE
				WHEN vorsitz IS NOT NULL
				THEN CONCAT(pv.nachname, ' ', pv.vorname, COALESCE(' ' || pv.titelpre, ''), ' (', ben.uid , ')' )
				ELSE NULL
   			 END AS pv
		");
		$this->AbschlusspruefungModel->addJoin('public.tbl_benutzer ben', 'ON (ben.uid = lehre.tbl_abschlusspruefung.vorsitz)', 'LEFT');
		$this->AbschlusspruefungModel->addJoin('public.tbl_person pv', 'ON (pv.person_id = ben.person_id)', 'LEFT');
		$this->AbschlusspruefungModel->addJoin('public.tbl_person p1', 'ON (p1.person_id = lehre.tbl_abschlusspruefung.pruefer1)', 'LEFT');
		$this->AbschlusspruefungModel->addJoin('public.tbl_person p2', 'ON (p2.person_id = lehre.tbl_abschlusspruefung.pruefer2)', 'LEFT');
		$this->AbschlusspruefungModel->addJoin('public.tbl_person p3', 'ON (p3.person_id = lehre.tbl_abschlusspruefung.pruefer3)', 'LEFT');
		$result = $this->AbschlusspruefungModel->loadWhere(
			array('abschlusspruefung_id' => $abschlusspruefung_id)
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}

	public function getTypenAbschlusspruefung()
	{
		$this->load->model('education/Pruefungstyp_model', 'PruefungstypModel');

		$result = $this->PruefungstypModel->loadWhere(
			array('abschluss' => true)
		);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getTypenAntritte()
	{
		$this->load->model('education/Pruefungsantritt_model', 'PruefungsantrittModel');

		$result = $this->PruefungsantrittModel->load();
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getBeurteilungen()
	{
		$this->load->model('education/Abschlussbeurteilung_model', 'AbschlussbeurteilungModel');

		$result = $this->AbschlussbeurteilungModel->load();
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getAkadGrade()
	{
		$studiengang_kz= $this->input->post('studiengang_kz');


		$this->load->model('education/Akadgrad_model', 'AkadgradModel');

		$result = $this->AkadgradModel->loadWhere(
			array('studiengang_kz' => $studiengang_kz)
		);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getTypStudiengang()
	{
		$studiengang_kz= $this->input->post('studiengang_kz');

		/*		if (!$studiengang_kzs || !is_array($studiengang_kzs)) {
					$this->load->library('form_validation');

					$this->form_validation->set_rules('studiengang_kzs', '', 'required|is_null', [
						'is_null' => $this->p->t('ui', 'error_fieldMustBeArray')
					]);

					if (!$this->form_validation->run())
						$this->terminateWithValidationErrors($this->form_validation->error_array());
				}*/


		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$result = $this->StudiengangModel->loadWhere(
			array('studiengang_kz' => $studiengang_kz)
		);
		$data = $this->getDataOrTerminateWithError($result);

		$typStudiengang = current($data)->typ;

		$this->terminateWithSuccess($typStudiengang);
	}

	public function getMitarbeiter($searchString)
	{
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');

		$result = $this->MitarbeiterModel->searchMitarbeiter($searchString, 'mitAkadGrad');

		if (isError($result)) {
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess($result ?: []);
	}

	public function getPruefer($searchString)
	{
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');

		$result = $this->MitarbeiterModel->searchMitarbeiter($searchString, 'ohneMaUid');

		if (isError($result)) {
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		$this->terminateWithSuccess($result ?: []);
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

	public function insertAbschlusspruefung()
	{
		$this->load->library('form_validation');

		$student_uid = $this->input->post('uid');

		if(!$student_uid)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Student UID']), self::ERROR_TYPE_GENERAL);
		}

		$formData = $this->input->post('formData');

		$this->form_validation->set_data($formData);

		$this->form_validation->set_rules('pruefungstyp_kurzbz', 'Typ', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Typ'])
		]);

		$this->form_validation->set_rules('akadgrad_id', 'AkadGrad', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'AkadGrad'])
		]);

		$this->form_validation->set_rules('datum', 'Datum', 'is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'Datum'])
		]);

		$this->form_validation->set_rules('sponsion', 'Sponsion', 'is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'Sponsion'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->AbschlusspruefungModel->insert([
			'student_uid' => $student_uid,
			'pruefungstyp_kurzbz' => $formData['pruefungstyp_kurzbz'],
			'akadgrad_id' => $formData['akadgrad_id'],
			'vorsitz' => $formData['vorsitz'],
			'pruefungsantritt_kurzbz' => $formData['pruefungsantritt_kurzbz'],
			'abschlussbeurteilung_kurzbz' => $formData['abschlussbeurteilung_kurzbz'],
			'datum' => $formData['datum'], //TODO(Manu) check if minute format like FAS
			'sponsion' => $formData['sponsion'],
			'pruefer1' => $formData['pruefer1'],
			'pruefer2' => $formData['pruefer2'],
			'pruefer3' => $formData['pruefer3'],
			'protokoll' => $formData['protokoll'],
			'note' => $formData['note'],
			'anmerkung' => $formData['anmerkung'],
			'insertamum' => date('c'),
			'insertvon' => getAuthUID()
		]);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function updateAbschlusspruefung()
	{
		$this->load->library('form_validation');

		$abschlusspruefung_id = $this->input->post('id');

		if(!$abschlusspruefung_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Abschlussprüfung ID']), self::ERROR_TYPE_GENERAL);
		}

		$formData = $this->input->post('formData');
		$vorsitz = isset($formData['vorsitz']['mitarbeiter_uid']) ? $formData['vorsitz']['mitarbeiter_uid'] :  $formData['vorsitz'];
		$pruefer1 = isset($formData['pruefer1']['person_id']) ? $formData['pruefer1']['person_id'] : $formData['pruefer1'];
		$pruefer2 = isset($formData['pruefer2']['person_id']) ? $formData['pruefer2']['person_id'] : $formData['pruefer2'];
		$pruefer3 = isset($formData['pruefer3']['person_id']) ? $formData['pruefer3']['person_id'] : $formData['pruefer3'];

		$this->form_validation->set_data($formData);

		$this->form_validation->set_rules('pruefungstyp_kurzbz', 'Typ', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Typ'])
		]);

		$this->form_validation->set_rules('akadgrad_id', 'AkadGrad', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'AkadGrad'])
		]);

		$this->form_validation->set_rules('datum', 'Datum', 'is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'Datum'])
		]);

		$this->form_validation->set_rules('sponsion', 'Sponsion', 'is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'Sponsion'])
		]);


		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->AbschlusspruefungModel->update(
			[
				'abschlusspruefung_id' => $abschlusspruefung_id
			],
			[
				'student_uid' => $formData['student_uid'],
				'pruefungstyp_kurzbz' => $formData['pruefungstyp_kurzbz'],
				'akadgrad_id' => $formData['akadgrad_id'],
				'vorsitz' => $vorsitz,
				'pruefungsantritt_kurzbz' => $formData['pruefungsantritt_kurzbz'],
				'abschlussbeurteilung_kurzbz' => $formData['abschlussbeurteilung_kurzbz'],
				'datum' => $formData['datum'],
				'sponsion' => $formData['sponsion'],
				'pruefer1' => $pruefer1,
				'pruefer2' => $pruefer2,
				'pruefer3' => $pruefer3,
				'protokoll' => $formData['protokoll'],
				'note' => $formData['note'],
				'anmerkung' => $formData['anmerkung'],
				'updateamum' => date('c'),
				'updatevon' => getAuthUID()
			]
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function deleteAbschlusspruefung()
	{
		$abschlusspruefung_id = $this->input->post('id');

		$result = $this->AbschlusspruefungModel->delete(
			array('abschlusspruefung_id' => $abschlusspruefung_id)
		);

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result))
		{
			$this->outputJson($result);
		}
		return $this->terminateWithSuccess(current(getData($result)) ? : null);
	}

	public function checkForExistingExams()
	{
		$warning = false;
		$output = [];

		$student_uids = $this->input->post('uids');

		if (empty($student_uids)) {
			throw new InvalidArgumentException("Keine UID(s) übergeben.");
		}

		if( !is_array($student_uids) )
		{
			$student_uids = array($student_uids);
		}

		foreach ($student_uids as $uid)
		{
			$result = $this->AbschlusspruefungModel->loadWhere(
				array('student_uid' => $uid)
			);
			if (isError($result)) {
				$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
			if (!hasData($result))
			{
				$warning = true;
				$output[] = $uid;
			}
		}
		if($warning)
		{
			$uids = is_array($output) ? implode(", ", $output) : $output;
			return $this->terminateWithError($this->p->t('abschlusspruefung', 'error_studentOhneFinalExam', ['id'=> $uids]), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess('step3');
	}

	/*
	* returns list of all Mitarbeiter
	* as key value list to be used in select or autocomplete
	*/
	public function getAllMitarbeiter()
	{
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');

		$sql = "
			SELECT
			    ma.mitarbeiter_uid as mitarbeiter_uid,
				CONCAT(p.nachname, ' ', p.vorname, ' (', ma.mitarbeiter_uid, ')') as label
			FROM
			  public.tbl_mitarbeiter ma
			  JOIN public.tbl_benutzer bn ON (bn.uid = ma.mitarbeiter_uid)
			  JOIN public.tbl_person p ON (p.person_id = bn.person_id)
			ORDER BY
			p.nachname ASC
			";

		$result = $this->MitarbeiterModel->execReadOnlyQuery($sql);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/*
	* returns list of all Persons
	* as key value list to be used in select or autocomplete
	*/
	public function getAllPersons()
	{
		$this->load->model('person/Person_model', 'PersonModel');

		$sql = "
			SELECT
			    p.vorname, p.nachname, p.person_id,
				CONCAT(p.nachname, ' ', p.vorname) as label
			FROM
			  public.tbl_person p
			 -- JOIN public.tbl_benutzer bn ON (p.person_id = bn.person_id)
			  -- and bn.aktiv = 'true'
			ORDER BY
			p.nachname ASC
			";

		//TODO(manu) check if filter active benutzer

		$result = $this->PersonModel->execReadOnlyQuery($sql);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}
}
