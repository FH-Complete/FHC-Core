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
			'addNewAbschlusspruefung' => ['admin:rw', 'assistenz:rw'],
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
		$result = $this->AbschlusspruefungModel->loadWhere(
			array('abschlusspruefung_id' => $abschlusspruefung_id)
		);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$this->terminateWithSuccess((getData($result) ?: []));
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

}
