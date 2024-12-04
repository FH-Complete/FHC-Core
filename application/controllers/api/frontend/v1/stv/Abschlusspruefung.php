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
			'getPruefer' => ['admin:rw', 'assistenz:rw'],
			'getTypStudiengang' => ['admin:rw', 'assistenz:rw'],

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



		$_POST['pruefungstyp_kurzbz'] = $formData['pruefungstyp_kurzbz'];
		$_POST['akadgrad_id']= $formData['akadgrad_id'];
		$_POST['vorsitz'] = isset($formData['vorsitz']['mitarbeiter_uid']) ? $formData['vorsitz']['mitarbeiter_uid'] :  $formData['vorsitz'];
		$_POST['pruefer1'] = isset($formData['pruefer1']['person_id']) ? $formData['pruefer1']['person_id'] : $formData['pruefer1'];
		$_POST['pruefer2'] = isset($formData['pruefer2']['person_id']) ? $formData['pruefer2']['person_id'] : $formData['pruefer2'];
		$_POST['pruefer3'] = isset($formData['pruefer3']['person_id']) ? $formData['pruefer3']['person_id'] : $formData['pruefer3'];
		$_POST['pruefungsantritt_kurzbz'] = $formData['pruefungsantritt_kurzbz'];
		$_POST['abschlussbeurteilung_kurzbz'] = $formData['abschlussbeurteilung_kurzbz'];
		$_POST['datum']= $formData['datum'];
		$_POST['sponsion']= $formData['sponsion'];
		$_POST['anmerkung'] = $formData['anmerkung'];
		$_POST['protokoll']= $formData['protokoll'];
		$_POST['note'] = $formData['note'];

		$this->form_validation->set_rules('pruefungstyp_kurzbz', 'Typ', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Typ'])
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
			'pruefungstyp_kurzbz' => $this->input->post('pruefungstyp_kurzbz'),
			'akadgrad_id' => $this->input->post('akadgrad_id'),
			'vorsitz' => $this->input->post('vorsitz'),
			'pruefungsantritt_kurzbz' => $this->input->post('pruefungsantritt_kurzbz'),
			'abschlussbeurteilung_kurzbz' => $this->input->post('abschlussbeurteilung_kurzbz'),
			'datum' => $this->input->post('datum'),
			'sponsion' => $this->input->post('sponsion'),
			'pruefer1' => $this->input->post('pruefer1'),
			'pruefer2' => $this->input->post('pruefer2'),
			'pruefer3' => $this->input->post('pruefer3'),
			'protokoll' => $this->input->post('protokoll'),
			'note' => $this->input->post('note'),
			'anmerkung' => $this->input->post('anmerkung'),
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
		$_POST['student_uid'] = $formData['student_uid'];
		$_POST['pruefungstyp_kurzbz'] = $formData['pruefungstyp_kurzbz'];
		$_POST['akadgrad_id']= $formData['akadgrad_id'];
		$_POST['vorsitz'] =  isset($formData['vorsitz']['mitarbeiter_uid']) ? $formData['vorsitz']['mitarbeiter_uid'] :  $formData['vorsitz'];
		$_POST['pruefer1'] = isset($formData['pruefer1']['person_id']) ? $formData['pruefer1']['person_id'] : $formData['pruefer1'];
		$_POST['pruefer2'] = isset($formData['pruefer2']['person_id']) ? $formData['pruefer2']['person_id'] : $formData['pruefer2'];
		$_POST['pruefer3'] = isset($formData['pruefer3']['person_id']) ? $formData['pruefer3']['person_id'] : $formData['pruefer3'];
		$_POST['pruefungsantritt_kurzbz'] = $formData['pruefungsantritt_kurzbz'];
		$_POST['abschlussbeurteilung_kurzbz'] = $formData['abschlussbeurteilung_kurzbz'];
		$_POST['datum']= $formData['datum'];
		$_POST['sponsion']= $formData['sponsion'];
		$_POST['anmerkung'] = $formData['anmerkung'];
		$_POST['protokoll']= $formData['protokoll'];
		$_POST['note'] = $formData['note'];

		$this->form_validation->set_rules('pruefungstyp_kurzbz', 'Typ', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Typ'])
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
			'student_uid' => $this->input->post('student_uid'),
			'pruefungstyp_kurzbz' => $this->input->post('pruefungstyp_kurzbz'),
			'akadgrad_id' => $this->input->post('akadgrad_id'),
			'vorsitz' => $this->input->post('vorsitz'),
			'pruefungsantritt_kurzbz' => $this->input->post('pruefungsantritt_kurzbz'),
			'abschlussbeurteilung_kurzbz' => $this->input->post('abschlussbeurteilung_kurzbz'),
			'datum' => $this->input->post('datum'),
			'sponsion' => $this->input->post('sponsion'),
			'pruefer1' => $this->input->post('pruefer1'),
			'pruefer2' => $this->input->post('pruefer2'),
			'pruefer3' => $this->input->post('pruefer3'),
			'protokoll' => $this->input->post('protokoll'),
			'note' => $this->input->post('note'),
			'anmerkung' => $this->input->post('anmerkung'),
			'insertamum' => date('c'),
			'insertvon' => getAuthUID()
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
}
