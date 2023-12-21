<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \REST_Controller as REST_Controller;

/**
 *
 */
class Wiederholung extends FHC_Controller
{

	/**
	 * Calls the parent's constructor and loads the FilterCmptLib
	 */
	public function __construct()
	{
		parent::__construct();

		// Configs
		$this->load->config('studierendenantrag');

		// Libraries
		$this->load->library('AuthLib');
		$this->load->library('PermissionLib');
		$this->load->library('AntragLib');

		$requiredPermissions = [
			'saveLvs' => ['student/studierendenantrag:w'],
			'getLvsAsRdf' => ['student/studierendenantrag:r', 'student/noten:r'],
			'moveLvsToZeugnis' => ['student/studierendenantrag:w', 'student/noten:w']
		];

		if (isset($requiredPermissions[$this->router->method])) {
			if (!$this->permissionlib->isEntitled($requiredPermissions, $this->router->method)) {
				$this->output->set_status_header(REST_Controller::HTTP_FORBIDDEN);
				$this->outputJson('Forbidden');
				exit;
			}
		}

		// Load language phrases
		$this->loadPhrases([
			'global',
			'studierendenantrag'
		]);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Retrieves data of the current studiengang for the current user
	 */

	public function getDetailsForNewAntrag($prestudent_id)
	{
		if (!$this->antraglib->isEntitledToCreateAntragFor($prestudent_id, false)) {
			$this->output->set_status_header(REST_Controller::HTTP_FORBIDDEN);
			return $this->outputJsonError('Forbidden');
		}
		$result = $this->antraglib->getPrestudentWiederholungsBerechtigt($prestudent_id);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJsonError(getError($result));
		}
		$result = $result->retval;
		if (!$result) {
			$this->output->set_status_header(REST_Controller::HTTP_FORBIDDEN);
			return $this->outputJsonError($this->p->t('studierendenantrag', 'error_no_student_no_failed_exam'));
		}
		elseif ($result == -1)
		{
			$result = $this->antraglib->getDetailsForLastAntrag($prestudent_id, Studierendenantrag_model::TYP_WIEDERHOLUNG);
			if (isError($result)) {
				return $this->outputJsonError(getError($result));
			}
			$data = getData($result);

			$result = $this->antraglib->getFailedExamForPrestudent($prestudent_id, $data->datum, $data->studiensemester_kurzbz);
			// NOTE(chris): error handling for this function should already happenden in antraglib->getPrestudentWiederholungsBerechtigt()
			$pruefungsdata = current(getData($result));

			$data->studiensemester_kurzbz = $pruefungsdata->studiensemester_kurzbz;
			$data->lvbezeichnung = $pruefungsdata->lvbezeichnung;
			$data->pruefungsdatum = $pruefungsdata->datum;

			return $this->outputJsonSuccess($data);
		}
		elseif ($result == -2)
		{
			$result = $this->antraglib->getDetailsForLastAntrag($prestudent_id);
			if (isError($result)) {
				return $this->outputJsonError(getError($result));
			}

			$result = getData($result);
			$this->output->set_status_header(REST_Controller::HTTP_BAD_REQUEST);
			return $this->outputJsonError($this->p->t('studierendenantrag', 'error_antrag_pending', [
                'typ' => $this->p->t('studierendenantrag', 'antrag_typ_' . $result->typ)
            ]));
		}
		elseif ($result == -3)
		{
			$this->output->set_status_header(REST_Controller::HTTP_BAD_REQUEST);
			return $this->outputJsonError($this->p->t('studierendenantrag', 'error_stg_blacklist'));
		}

		$result = $this->antraglib->getDetailsForNewAntrag($prestudent_id);
		if (isError($result)) {
			return $this->outputJsonError(getError($result));
		}

		$data = getData($result);

		$result = $this->antraglib->getFailedExamForPrestudent($prestudent_id);
		// NOTE(chris): error handling for this function should already happenden in antraglib->getPrestudentWiederholungsBerechtigt()
		$pruefungsdata = current(getData($result));

		$data->studiensemester_kurzbz = $pruefungsdata->studiensemester_kurzbz;
		$data->lvbezeichnung = $pruefungsdata->lvbezeichnung;
		$data->pruefungsdatum = $pruefungsdata->datum;

		$this->outputJsonSuccess($data);
	}

	public function createAntrag()
	{
		$this->createAntragWithStatus(true);
	}

	public function cancelAntrag()
	{
		$this->createAntragWithStatus(false);
	}

	protected function createAntragWithStatus($repeat)
	{
		$this->load->library('form_validation');

		$_POST = json_decode($this->input->raw_input_stream, true);

		$this->form_validation->set_rules('prestudent_id', 'Prestudent ID', 'required');
		$this->form_validation->set_rules('studiensemester', 'Studiensemester', 'required');

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$prestudent_id = $this->input->post('prestudent_id');
		$studiensemester = $this->input->post('studiensemester');

		$result = $this->antraglib->getPrestudentWiederholungsBerechtigt($prestudent_id);
		if (isError($result)) {
			return $this->outputJsonError(['db' => getError($result)]);
		}
		$result = $result->retval;
		if (!$result)
		{
			return $this->outputJsonError(['db' => $this->p->t('studierendenantrag', 'error_no_student')]);
		}
		elseif ($result == -2)
		{
			return $this->outputJsonError(['db' => $this->p->t('studierendenantrag', 'error_antrag_exists')]);
		}
		elseif ($result == -3)
		{
			return $this->outputJsonError(['db' => $this->p->t('studierendenantrag', 'error_stg_blacklist')]);
		}

		$result = $this->antraglib->createWiederholung($prestudent_id, $studiensemester, getAuthUID(), $repeat);
		if(isError($result))
		{
			return $this->outputJsonError(['db' => getError($result)]);
		}

		$antragId = getData($result);
		$result = $this->antraglib->getDetailsForAntrag($antragId);

		if(!hasData($result))
			return $this->outputJsonSuccess(true);

		$data = getData($result);

		$result = $this->antraglib->getFailedExamForPrestudent($prestudent_id);
		// NOTE(chris): error handling for this function should already happenden in antraglib->getPrestudentWiederholungsBerechtigt()
		$pruefungsdata = current(getData($result));

		$data->studiensemester_kurzbz = $pruefungsdata->studiensemester_kurzbz;
		$data->lvbezeichnung = $pruefungsdata->lvbezeichnung;
		$data->pruefungsdatum = $pruefungsdata->datum;

		$this->outputJsonSuccess($data);
	}


	public function getLvs($antrag_id)
	{
		$result = $this->antraglib->getLvsForAntrag($antrag_id);
		if (isError($result)) {
			$error = getError($result);
			if ($error == 'Forbidden')
				$this->output->set_status_header(REST_Controller::HTTP_FORBIDDEN);
			return $this->outputJsonError(getError($result));
		}
		$lvs = getData($result);

		$this->outputJsonSuccess($lvs);
	}

	public function saveLvs()
	{
		$result = $this->getPostJSON();
		$antragsLvs = array_merge($result->forbiddenLvs, $result->mandatoryLvs);

		$insert = array_map(function ($lv) {
			return [
				'studierendenantrag_id' => $lv->studierendenantrag_id,
				'lehrveranstaltung_id' => $lv->lehrveranstaltung_id,
				'note' => $lv->zugelassen
                    ? ($lv->zugelassen == 1 ? 0 : $this->config->item('wiederholung_note_angerechnet'))
                    : $this->config->item('wiederholung_note_nicht_zugelassen'),
				'anmerkung' => $lv->anmerkung,
				'insertvon' => getAuthUID(),
				'studiensemester_kurzbz' => $lv->studiensemester_kurzbz
			];
		}, $antragsLvs);

		$antrag_ids = array_unique(array_map(function ($lv) {
			return $lv['studierendenantrag_id'];
		}, $insert));

		foreach ($antrag_ids as $antrag_id) {
			$result = $this->StudierendenantragModel->loadIdAndStatusWhere([
				'studierendenantrag_id' => $antrag_id
			]);
			if (isError($result))
				return $this->outputJsonError(getError($result));
			if (!hasData($result))
				return $this->outputJsonError($this->p->t('studierendenantrag', 'error_no_antrag_found', ['id' => $antrag_id]));
			$antrag = current(getData($result));
			if ($antrag->status != Studierendenantragstatus_model::STATUS_CREATED && $antrag->status != Studierendenantragstatus_model::STATUS_LVSASSIGNED)
				return $this->outputJsonError($this->p->t('studierendenantrag', 'error_antrag_locked'));
		}

		if(!$antragsLvs)
			return $this->outputJsonError($this->p->t('studierendenantrag', 'error_no_lv'));

		$result = $this->antraglib->saveLvs($insert);

		if (isError($result))
			return $this->outputJsonError(getError($result));

		$this->outputJsonSuccess(getData($result));
	}

	public function getLvsAsRdf($prestudent_id)
	{
		// header für no cache
		$this->output->set_header("Cache-Control: no-cache");
		$this->output->set_header("Cache-Control: post-check=0, pre-check=0", false);
		$this->output->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		$this->output->set_header("Pragma: no-cache");
		$this->output->set_header("Content-type: application/xhtml+xml");

		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$sem_akt = $this->variablelib->getVar('semester_aktuell');


		$result = $this->antraglib->getLvsForPrestudent($prestudent_id, $sem_akt);
		if (isError($result)) {
			return $this->outputJsonError(getError($result));
		}

		$lvs = getData($result) ?: [];
		$rdf_url = 'http://www.technikum-wien.at/antragnote';

		$this->load->view('lehre/Antrag/Wiederholung/getLvs.rdf.php', [
			'url' => $rdf_url,
			'lvs' => $lvs
		]);
	}

	public function moveLvsToZeugnis()
	{
		$anzahl = $this->input->post('anzahl');
		$student_uid = $this->input->post('student_uid');
		$this->load->model('education/Studierendenantraglehrveranstaltung_model', 'StudierendenantraglehrveranstaltungModel');
		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');

		$errormsg = array();

		for($i=0; $i<$anzahl; $i++)
		{
			$id = $this->input->post('studierendenantrag_lehrveranstaltung_id_' . $i);
			$result =$this->StudierendenantraglehrveranstaltungModel->load($id);
			if(isError($result))
			{
				$errormsg[] = getError($result);
			}
			elseif(!hasData($result))
			{
				$errormsg[] = $this->p->t('studierendenantrag', 'error_no_lv_in_application');
			}
			else
			{
				$antragLv = getData($result)[0];
				$result= $this->ZeugnisnoteModel->load([
					'lehrveranstaltung_id'=> $antragLv->lehrveranstaltung_id,
					'student_uid'=> $student_uid,
					'studiensemester_kurzbz' => $antragLv->studiensemester_kurzbz
				]);
				if(isError($result))
				{
					$errormsg[] = getError($result);
				}
				else
				{
					if (hasData($result))
					{
						$result = $this->ZeugnisnoteModel->update(
							[
								'lehrveranstaltung_id'=> $antragLv->lehrveranstaltung_id,
								'student_uid'=> $student_uid,
								'studiensemester_kurzbz' => $antragLv->studiensemester_kurzbz
							],
							[
								'note'=> $antragLv->note,
								'uebernahmedatum' => date('c'),
								'benotungsdatum' => $antragLv->insertamum,
								'updateamum' => date('c'),
								'bemerkung'=>$antragLv->anmerkung,
								'updatevon'=>getAuthUID()
							]
						);
					}
					else
					{
						$result = $this->ZeugnisnoteModel->insert([
							'lehrveranstaltung_id'=> $antragLv->lehrveranstaltung_id,
							'student_uid'=> $student_uid,
							'studiensemester_kurzbz' => $antragLv->studiensemester_kurzbz,
							'note'=> $antragLv->note,
							'uebernahmedatum' => date('c'),
							'benotungsdatum' => $antragLv->insertamum,
							'insertamum' => date('c'),
							'bemerkung'=>$antragLv->anmerkung,
							'insertvon'=>getAuthUID()
						]);
					}
					if(isError($result))
					{
						$errormsg[] = getError($result);
					}
				}
			}
		}

		if($errormsg)
			$return = false;
		else
			$return = true;

		$this->load->view('lehre/Antrag/Wiederholung/moveLvs.rdf.php', [
			'return' => $return,
			'errormsg' => $errormsg
		]);
	}
}
