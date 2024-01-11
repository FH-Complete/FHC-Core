<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');


class Notiz extends FHC_Controller
{
	public function __construct()
	{
		parent::__construct();

		// Load Libraries
		$this->load->library('AuthLib');
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	public function getNotizen($person_id)
	{
		$this->load->model('person/Notiz_model', 'NotizModel');
		$type = 'person';

		$result = $this->NotizModel->getNotizWithDocEntries($person_id);

		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} else {
			$this->outputJson(getData($result) ?: []);
		}
	}

	public function loadNotiz($notiz_id)
	{
		$this->load->model('person/Notiz_model', 'NotizModel');
		$this->NotizModel->addJoin('public.tbl_notiz_dokument', 'notiz_id', 'LEFT');
		$this->NotizModel->addSelect('*');
		$this->NotizModel->addLimit(1);

		$result = $this->NotizModel->loadWhere(
			array('notiz_id' => $notiz_id)
		);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}
		elseif (!hasData($result)) {
			$this->outputJson($result); //success mit Wert null
			//	$this->outputJson(getData($result) ?: []);
		}
		else
		{
			$this->outputJsonSuccess(current(getData($result)));
		}
	}

	public function addNewNotiz($id)
	{
		$this->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');
		//$this->load->library('form_validation');
		//$_POST = json_decode($this->input->raw_input_stream, true);

		//TODO(Manu) Validation

/*		$this->form_validation->set_rules('titel', 'titel', 'required');
		$this->form_validation->set_rules('text', 'Text', 'required');*/

		//TODO(Manu) form validation - schon für type hier?,

		//Speichern der Files
		$this->load->library('DmsLib');
		$uid = getAuthUID();

		//multiple files
		$dms_id_arr = [];
		$notiz_id = null;
		foreach ($_FILES as $k => $file)
		{
			$dms = array(
				'kategorie_kurzbz'  => 'notiz',
				'version'           => 0,
				'name'              => $file["name"],
				'mimetype'          => $file["type"],
				'insertamum'        => date('c'),
				'insertvon'         => $uid
			);

			$result = $this->dmslib->upload($dms, $k, array('pdf'));

			if (isSuccess($result))
			{
				$dms_id_arr[] = $result->retval['dms_id'];
			}
/*			else {
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

				//TODO(manu) error handling
				//feedback, dass ein File nicht erfolgreich gespeichert werden konnte
				//$this->outputJson($result);
				//$this->outputJsonError(['filetype nicht erlaubt' => getError($result)]);
			}*/


		}

		//Speichern der Notiz mit Notizzuordnung
		$this->load->model('person/Notiz_model', 'NotizModel');

		$uid = getAuthUID();
		$verfasser_uid = isset($_POST['verfasser_uid']) ? $_POST['verfasser_uid'] : $uid;
		$titel = $this->input->post('titel');
		$text = $this->input->post('text');
		$bearbeiter_uid = $this->input->post('bearbeiter_uid');
		$erledigt = $this->input->post('erledigt');
		$type = $this->input->post('typeId');

		//get rid of null value error
		$start = $this->input->post(date('von'));
		$ende = $this->input->post(date('bis'));

		$result = $this->NotizModel->addNotizForType($type, $id, $titel, $text, $uid, $start, $ende, $erledigt, $verfasser_uid, $bearbeiter_uid);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$notiz_id = $result->retval;

		//Zuordnung speichern
/*		$result = $this->NotizzuordnungModel->insert(array('notiz_id' => $notiz_id, $type=> $id));
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}*/


		//Eintrag in Notizdokument speichern
		if($dms_id_arr)
		{
			// Loads model Notizdokument_model
			$this->load->model('person/Notizdokument_model', 'NotizdokumentModel');
			//Todo(manu) change for multiple files
			foreach($dms_id_arr as $dms_id)
			{
				$result = $this->NotizdokumentModel->insert(array('notiz_id' => $notiz_id, 'dms_id' => $dms_id));
				if (isError($result))
				{
					$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
					return $this->outputJson(getError($result));
				}
			}

		}

		//$result = $this->NotizModel->addNotizForType($type, $id, $titel, $text, $uid, $dms_id, $start, $ende, $erledigt, $verfasser_uid, $bearbeiter_uid);
/*
		$result = $this->NotizModel->addNotizForPerson($id, $titel, $text, $erledigt, $verfasser_uid);
		if (isError($result))
		{
			//$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}*/
		return $this->outputJsonSuccess(true);
	}

	public function updateNotizOldVersion($notiz_id)
	{
		$uid = getAuthUID();
		$this->load->library('form_validation');
		//$_POST = json_decode($this->input->raw_input_stream, true);

		$this->form_validation->set_rules('titel', 'titel', 'required');
		$this->form_validation->set_rules('text', 'Text', 'required');

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$this->load->model('person/Notiz_model', 'NotizModel');

		if(!$notiz_id)
		{
			return $this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

	//	$person_id = isset($_POST['person_id']) ? $_POST['person_id'] : null;
		$uid = getAuthUID();
		$titel = isset($_POST['titel']) ? $_POST['titel'] : null;
		$text = isset($_POST['text']) ? $_POST['text'] : null;
		$verfasser_uid = isset($_POST['verfasser_uid']) ? $_POST['verfasser_uid'] : null;
		$bearbeiter_uid = $uid;
		$erledigt = $_POST['erledigt'];
		$start = $this->input->post(date('von'));
		$ende = $this->input->post(date('bis'));



		$result = $this->NotizModel->update(
			[
				'notiz_id' => $notiz_id
			],
			[
				'titel' =>  $titel,
				'updatevon' => $uid,
				'updateamum' => date('c'),
				'text' => $text,
				'verfasser_uid' => $verfasser_uid,
				'bearbeiter_uid' => $bearbeiter_uid,
				'start' => $start,
				'ende' => $ende,
				'erledigt' => $erledigt
			]
		);

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		return $this->outputJsonSuccess(true);
	}

	public function updateNotiz($notiz_id)
	{
		$uid = getAuthUID();
		$this->load->library('form_validation');
		//$_POST = json_decode($this->input->raw_input_stream, true);
	/*	$this->form_validation->set_rules('titel', 'titel', 'required');
		$this->form_validation->set_rules('text', 'Text', 'required');

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}*/

		$this->load->model('person/Notiz_model', 'NotizModel');

		if(!$notiz_id)
		{
			return $this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

		$uid = getAuthUID();
		$titel = $this->input->post('titel');
		$text = $this->input->post('text');
		$verfasser_uid = isset($_POST['verfasser_uid']) ? $_POST['verfasser_uid'] : null;
		$bearbeiter_uid = $uid;
		$erledigt = $this->input->post('erledigt');
		$type = $this->input->post('typeId');
		$start = $this->input->post(date('von'));
		$ende = $this->input->post(date('bis'));



		$result = $this->NotizModel->update(
			[
				'notiz_id' => $notiz_id
			],
			[
				'titel' =>  $titel,
				'updatevon' => $uid,
				'updateamum' => date('c'),
				'text' => $text,
				'verfasser_uid' => $verfasser_uid,
				'bearbeiter_uid' => $bearbeiter_uid,
				'start' => $start,
				'ende' => $ende,
				'erledigt' => $erledigt
			]
		);

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		return $this->outputJsonSuccess(true);
	}

	public function deleteNotiz ($notiz_id)
	{
		//dms_id auslesen aus notizdokument wenn vorhanden
		$dms_id = null;
		$this->load->model('person/Notizdokument_model', 'NotizdokumentModel');

		$result = $this->NotizdokumentModel->loadWhere(array('notiz_id' => $notiz_id));

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}
		elseif (!hasData($result))
		{
			$this->outputJson($result);
			$dms_id = null;
		}
		else
		{
			//Todo(manu( umbau auf array)
			$result = current(getData($result));
			$dms_id = $result->dms_id;
		}

		if($dms_id)
		{
			$this->load->library('DmsLib');
			$result = $this->dmslib->removeAll($dms_id);

			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				$this->outputJson($result);
			}
			else
				$this->outputJson($result);

			//return $this->outputJsonSuccess(current(getData($result)));

		}


		//Todo(manu) rollback?
		//delete Notiz und Notizzuordnung
		$this->load->model('person/Notiz_model', 'NotizModel');
		$this->NotizModel->addJoin('public.tbl_notizzuordnung', 'notiz_id');

		$result = $this->NotizModel->delete(
			array('notiz_id' => $notiz_id)
		);

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}
		elseif (!hasData($result)) {
			$this->outputJson($result);
		}

		return $this->outputJsonSuccess(current(getData($result)));
	}

	public function loadDokumente($notiz_id)
	{
		$this->load->model('person/Notiz_model', 'NotizModel');


		$this->NotizModel->addSelect('campus.tbl_dms_version.*');

		$this->NotizModel->addJoin('public.tbl_notiz_dokument','ON (public.tbl_notiz_dokument.notiz_id = public.tbl_notiz.notiz_id)');
		$this->NotizModel->addJoin('campus.tbl_dms_version','ON (public.tbl_notiz_dokument.dms_id = campus.tbl_dms_version.dms_id)');

		$result = $this->NotizModel->loadWhere(
			array('public.tbl_notiz.notiz_id' => $notiz_id)
		);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}

		elseif (!hasData($result)) {
			$this->outputJson($result);
		}
		else
		{
			$this->outputJsonSuccess(getData($result));
		}
	}

}