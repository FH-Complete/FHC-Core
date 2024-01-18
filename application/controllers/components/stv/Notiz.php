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

	public function getUid()
	{
		// Load Libraries
		$this->load->library('AuthLib');
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$result = getAuthUid();

		$this->outputJsonError($result);
	}

	public function getNotizen($id, $type)
	{
		$this->load->model('person/Notiz_model', 'NotizModel');
		$this->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');

		//check if valid type
		$isValidType = $this->NotizzuordnungModel->isValidType($type);

		if($isValidType)
		{
			$result = $this->NotizModel->getNotizWithDocEntries($id, $type);

			if (isError($result)) {
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				$this->outputJson(getError($result));
			} else {
				$this->outputJson(getData($result) ?: []);
			}
		}
		else
		{
			//Todo manu (correct return to ajax)
			$result = "datatype not yet implemented for notes";
			$this->outputJson(getError($result));
		}
	}

	public function loadNotiz($notiz_id)
	{
		$this->load->model('person/Notiz_model', 'NotizModel');
		$this->NotizModel->addJoin('public.tbl_notiz_dokument', 'notiz_id', 'LEFT');
		$this->NotizModel->addSelect('*');
		$this->NotizModel->addSelect("TO_CHAR(CASE WHEN public.tbl_notiz.updateamum >= public.tbl_notiz.insertamum 
			THEN public.tbl_notiz.updateamum ELSE public.tbl_notiz.insertamum END::timestamp, 'DD.MM.YYYY HH24:MI:SS') AS lastUpdate");
		$this->NotizModel->addLimit(1);

		$result = $this->NotizModel->loadWhere(
			array('notiz_id' => $notiz_id)
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
			$this->outputJsonSuccess(current(getData($result)));
		}
	}

	public function addNewNotiz($id, $paramTyp = null)
	{
		$this->load->model('person/Notiz_model', 'NotizModel');

		$this->load->library('DmsLib');
		$this->load->library('form_validation');

		$uid = getAuthUID();

		if (isset($_POST['data']))
		{
			$data = json_decode($_POST['data']);
			unset($_POST['data']);
			foreach ($data as $k => $v) {
				$_POST[$k] = $v;
			}
		}

		//Überprüfung ob type übergeben wurde (via Funktions- oder Postparameter)
		$type = null;
		if ($paramTyp)
			$type = $paramTyp;
		if(isset($_POST['typeId']))
			$type = $this->input->post('typeId');

		if(!$type)
		{
			$result = error('kein Type für ID vorhanden', EXIT_ERROR);
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

			return $this->outputJson(getError($result));
		}

		//Form Validation
		$this->form_validation->set_rules('titel', 'titel', 'required');
		$this->form_validation->set_rules('text', 'Text', 'required');

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$titel = $this->input->post('titel');
		$text = $this->input->post('text');
		$erledigt = $this->input->post('erledigt');
		$verfasser_uid = isset($_POST['verfasser_uid']) ? $_POST['verfasser_uid'] : $uid;
		$bearbeiter_uid = isset($_POST['bearbeiter']) ? $_POST['bearbeiter'] : null;
		$type = $this->input->post('typeId');
		$start = $this->input->post('von');
		$ende = $this->input->post('bis');

		//Speichern der Notiz und Notizzuordnung inkl Prüfung ob valid type
		$result = $this->NotizModel->addNotizForType($type, $id, $titel, $text, $uid, $start, $ende, $erledigt, $verfasser_uid, $bearbeiter_uid);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$notiz_id = $result->retval;

		//Speichern der Files
		$dms_id_arr = [];
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
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			$dms_id_arr[] = $result->retval['dms_id'];
		}

		//Eintrag in Notizdokument speichern
		if($dms_id_arr)
		{
			// Loads model Notizdokument_model
			$this->load->model('person/Notizdokument_model', 'NotizdokumentModel');
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

		return $this->outputJsonSuccess(true);
	}

	public function updateNotiz($notiz_id)
	{
		$this->load->model('person/Notiz_model', 'NotizModel');
		$this->load->model('person/Notizdokument_model', 'NotizdokumentModel');

		$this->load->library('form_validation');
		$this->load->library('DmsLib');

		if (isset($_POST['data']))
		{
			$data = json_decode($_POST['data']);
			unset($_POST['data']);
			foreach ($data as $k => $v) {
				$_POST[$k] = $v;
			}
		}

		if(!$notiz_id)
		{
			return $this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

		//Form Validation
		$this->form_validation->set_rules('titel', 'titel', 'required');
		$this->form_validation->set_rules('text', 'Text', 'required');

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		//update Notiz
		$uid = getAuthUID();
		$titel = $this->input->post('titel');
		$text = $this->input->post('text');
		$verfasser_uid = isset($_POST['verfasser_uid']) ? $_POST['verfasser_uid'] : null;
		$bearbeiter_uid = isset($_POST['bearbeiter']) ? $_POST['bearbeiter'] : $uid;
		$erledigt = $this->input->post('erledigt');
		$type = $this->input->post('typeId');
		$start = $this->input->post('von');
		$ende = $this->input->post('bis');

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

		//Todo(manu) update von Notizzuordnung?? typeId?

		//neue Files speichern
		//Todo(manu) update files
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
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			$dms_id = $result->retval['dms_id'];

			$result = $this->NotizdokumentModel->insert(array('notiz_id' => $notiz_id, 'dms_id' => $dms_id));
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
		}
		return $this->outputJsonSuccess(true);
	}

	public function deleteNotiz($notiz_id)
	{
		//dms_id auslesen aus notizdokument wenn vorhanden
		$dms_id_arr = [];
		$this->load->model('person/Notizdokument_model', 'NotizdokumentModel');

		$result = $this->NotizdokumentModel->loadWhere(array('notiz_id' => $notiz_id));

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		}
		elseif (!hasData($result))
		{
			$this->outputJson($result);
		}
		else
		{
			$result = getData($result);
			foreach($result as $doc) {
				$dms_id_arr[] = $doc->dms_id;
			}
		}

		if($dms_id_arr)
		{
			$this->load->library('DmsLib');
			foreach($dms_id_arr as $dms_id)
			{
				$result = $this->dmslib->removeAll($dms_id);

				if (isError($result))
				{
					$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
					return $this->outputJson(getError($result));
				}
				else
					$this->outputJson($result);
			}
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

		$this->NotizModel->addJoin('public.tbl_notiz_dokument', 'ON (public.tbl_notiz_dokument.notiz_id = public.tbl_notiz.notiz_id)');
		$this->NotizModel->addJoin('campus.tbl_dms_version', 'ON (public.tbl_notiz_dokument.dms_id = campus.tbl_dms_version.dms_id)');

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

	public function getMitarbeiter($searchString)
	{
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');

		$result = $this->MitarbeiterModel->searchMitarbeiter($searchString);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}
}
