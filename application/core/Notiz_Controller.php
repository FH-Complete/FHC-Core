<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

abstract class Notiz_Controller extends FHCAPI_Controller
{
	const DEFAULT_PERMISSION_R  = 'admin:r';
	const DEFAULT_PERMISSION_RW = 'admin:rw';

	public function __construct($permissions)
	{
		$default_permissions = [
			'getUid' => self::DEFAULT_PERMISSION_R,
			'getNotizen' => self::DEFAULT_PERMISSION_R,
			'loadNotiz' => self::DEFAULT_PERMISSION_R,
			'addNewNotiz' => self::DEFAULT_PERMISSION_RW,
			'updateNotiz' => self::DEFAULT_PERMISSION_RW,
			'deleteNotiz' => self::DEFAULT_PERMISSION_RW,
			'loadDokumente' => self::DEFAULT_PERMISSION_R,
			'getMitarbeiter' => self::DEFAULT_PERMISSION_R,
			'isBerechtigt' => self::DEFAULT_PERMISSION_R,
			'getCountNotes' => self::DEFAULT_PERMISSION_R,
		];
		
		if(!is_array($permissions))
		{
		    $this->terminateWithError("Notiz_controller construct: permissions must be an array");
		}
		
		$merged_permissions = array_merge($default_permissions, $permissions);
		
		parent::__construct($merged_permissions);

		//Load Models
		$this->load->model('person/Notiz_model', 'NotizModel');
		$this->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	public function getUid()
	{
		$this->terminateWithSuccess(getAuthUID());
	}


	//Override function for extensions
	protected function assignNotiz($notiz_id, $id, $type)
	{
		$this->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');
		$result = $this->NotizzuordnungModel->isValidType($type);
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$result = $this->NotizzuordnungModel->insert(array('notiz_id' => $notiz_id, $type => $id));
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return success(getData($result));
	}

	//Override function for extensions
	protected function deleteNotizzuordnung($notiz_id, $id, $type)
	{
		$this->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');
		$result = $this->NotizzuordnungModel->isValidType($type);
		if (isError($result)) {
			$this->terminateWithError('type not in table notizzuordnung enthalten..', self::ERROR_TYPE_GENERAL);
		}

		$result = $this->NotizzuordnungModel->delete(['notiz_id' => $notiz_id, $type => $id]);
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return success(getData($result));
	}


	//Override function for extensions
	public function getNotizen($id, $type)
	{
		$result = $this->NotizzuordnungModel->isValidType($type);
		if(isError($result))
			$this->terminateWithError($result->retval, self::ERROR_TYPE_GENERAL);

		$result = $this->NotizModel->getNotizWithDocEntries($id, $type);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess(getData($result) ?: []);
	}


	//Override function
	protected function isBerechtigt($id, $typeId){
		$this->terminateWithError("in abstract function: define right in extension", self::ERROR_TYPE_GENERAL);
	}

	public function loadNotiz()
	{
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$notiz_id = $this->input->post('notiz_id');

		$this->NotizModel->addJoin('public.tbl_notiz_dokument', 'notiz_id', 'LEFT');
		$this->NotizModel->addSelect('*');
		$this->NotizModel->addSelect("TO_CHAR(CASE WHEN public.tbl_notiz.updateamum >= public.tbl_notiz.insertamum 
			THEN public.tbl_notiz.updateamum ELSE public.tbl_notiz.insertamum END::timestamp, 'DD.MM.YYYY HH24:MI:SS') AS lastUpdate");
		$this->NotizModel->addLimit(1);

		$result = $this->NotizModel->loadWhere(
			array('notiz_id' => $notiz_id)
		);
		if (isError($result))
		{
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		elseif (!hasData($result))
		{
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=>'Notiz_id']), self::ERROR_TYPE_GENERAL);
		}
		else
		{
			$this->terminateWithSuccess(current(getData($result)));
		}
	}

	public function addNewNotiz($id, $paramTyp = null)
	{
		$this->load->library('DmsLib');
		$this->load->library('form_validation');

		$uid = getAuthUID();

		$json = $this->input->post('data');
		$post_data = json_decode($json, true);
		$this->form_validation->set_data($post_data);

		//Form Validation
		$this->form_validation->set_rules('titel', 'Titel', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Titel'])
		]);

		$this->form_validation->set_rules('text', 'Text', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Text'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$titel =  $post_data['titel'];
		$text = $post_data['text'];
		$erledigt = $post_data['erledigt'];
		$bearbeiter_uid = isset($post_data['bearbeiter']) ? $post_data['bearbeiter'] : null;
		$type = $post_data['typeId'];
		$start = isset($post_data['start']) ? $post_data['start'] : null;
		$ende = isset($post_data['ende']) ? $post_data['ende'] : null;

		// Start DB transaction
		$this->db->trans_start();

		//Save note
		$result = $this->NotizModel->insert(array('titel' => $titel, 'text' => $text, 'erledigt' => $erledigt, 'verfasser_uid' => $uid,
			"insertvon" => $uid, 'start' => $start, 'ende' => $ende, 'bearbeiter_uid' => $bearbeiter_uid));

		if (isError($result))
		{
			$this->db->trans_rollback();
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$notiz_id = $result->retval;

		//save Notizzuordnung
		$result = $this->assignNotiz($notiz_id, $id, $type);

		if (isError($result))
		{
			$this->db->trans_rollback();
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		//save Documents
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

			//Todo(manu) check if filetypes weiter eingeschränkt werden sollen
			//Todo(manu)check name files: nicht gleiches file 2mal hochladen
			//Todo define in dms component: readFile, downloadFile
			$result = $this->dmslib->upload($dms, $k, ['*']);
			/*			$result = $this->dmslib->upload($dms, $k, ['application/pdf','application/x.fhc-dms+json']);*/
			if (isError($result))
			{
				$this->db->trans_rollback();
				$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
			$dms_id_arr[] = $result->retval['dms_id'];
		}

		//save entry in Notizdokument
		if($dms_id_arr)
		{
			$this->load->model('person/Notizdokument_model', 'NotizdokumentModel');
			foreach($dms_id_arr as $dms_id)
			{
				$result = $this->NotizdokumentModel->insert(array('notiz_id' => $notiz_id, 'dms_id' => $dms_id));
				if (isError($result))
				{
					$this->db->trans_rollback();
					$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
				}
			}
		}
		$this->db->trans_commit();
		$this->terminateWithSuccess($result);
	}

	public function updateNotiz()
	{

		$this->load->library('form_validation');
		$this->load->library('DmsLib');

		$json = $this->input->post('data');
		$post_data = json_decode($json, true);

		$this->form_validation->set_data($post_data);

		$this->form_validation->set_rules('notiz_id', 'Notiz ID', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'notiz_id'])
		]);

		//Form Validation
		$this->form_validation->set_rules('titel', 'Titel', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Titel'])
		]);

		$this->form_validation->set_rules('text', 'Text', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Text'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		//update Notiz
		$uid = getAuthUID();
		$titel = $post_data['titel'];
		$text = $post_data['text'];
		$bearbeiter_uid = isset($post_data['bearbeiter']) ? $post_data['bearbeiter'] : $post_data['bearbeiter_uid'];
		$erledigt = $post_data['erledigt'];
		$start = $post_data['start'];
		$ende = $post_data['ende'];

		$result = $this->NotizModel->update(
			[
				'notiz_id' => $post_data['notiz_id'],
			],
			[
				'titel' =>  $titel,
				'updatevon' => $uid,
				'updateamum' => date('c'),
				'text' => $text,
				'bearbeiter_uid' => isEmptyString($bearbeiter_uid) ? null : $bearbeiter_uid,
				'start' => $start,
				'ende' => $ende,
				'erledigt' => $erledigt
			]
		);
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		//update(1) loading all dms-entries with this notiz_id
		$dms_id_arr = [];
		$this->load->model('person/Notizdokument_model', 'NotizdokumentModel');
		$this->NotizdokumentModel->addJoin('campus.tbl_dms_version', 'dms_id');

		$result = $this->NotizdokumentModel->loadWhere(array('notiz_id' => $post_data['notiz_id']));
		$result = $this->getDataOrTerminateWithError($result);
		foreach ($result as $doc) {
			$dms_id_arr[$doc->dms_id] = array(
				'name' => $doc->name,
				'dms_id' => $doc->dms_id
			);
		}

		foreach ($_FILES as $k => $file)
		{
			//update(2) attach all new files (except type application/x.fhc-dms+json)
			if($file["type"] == 'application/x.fhc-dms+json')
			{
				$jsonFile = json_decode(file_get_contents($file['tmp_name']));
				unset($dms_id_arr[$jsonFile->dms_id]);
				#$dms_uploaded[] = $jsonFile->dms_id;
			}
			else
			{
				$dms = array(
					'kategorie_kurzbz'  => 'notiz',
					'version'           => 0,
					'name'              => $file["name"],
					'mimetype'          => $file["type"],
					'insertamum'        => date('c'),
					'insertvon'         => $uid
				);

				//Todo(manu) check if filetypes weiter eingeschränkt werden sollen
				//Todo(manu)check name files: nicht gleiches file 2mal hochladen
				//Todo define in dms component: readFile, downloadFile
				$result = $this->dmslib->upload($dms, $k, array('*'));

				$result = $this->getDataOrTerminateWithError($result);
				$dms_id = $result['dms_id'];

				$result = $this->NotizdokumentModel->insert(array('notiz_id' => $post_data['notiz_id'], 'dms_id' => $dms_id));

				$this->getDataOrTerminateWithError($result);
			}
		}

		//update(3) check if all files have been deleted
		foreach ($dms_id_arr as $file)
		{
			$result = $this->dmslib->removeAll($file['dms_id']);

			$this->getDataOrTerminateWithError($result);
		}

		$this->terminateWithSuccess($result);
	}

	public function deleteNotiz()
	{
		$this->load->library('DmsLib');

		$notiz_id = $this->input->post('notiz_id');
		$typeId = $this->input->post('type_id');
		$id = $this->input->post('id');

		//TODO(manu): define Permissions for deletion document if filecomponent finished

		//get dms_id from notizdokument
		$this->load->model('person/Notizdokument_model', 'NotizdokumentModel');

		$result = $this->NotizdokumentModel->loadWhere(array('notiz_id' => $notiz_id));

		$result = $this->getDataOrTerminateWithError($result);

		// Start DB transaction
		$this->db->trans_start();

		if ($result)
			$this->load->library('DmsLib');

		foreach ($result as $doc) {
			$res = $this->dmslib->removeAll($doc->dms_id);
			if (isError($res))
			{
				$this->db->trans_rollback();
				$this->terminateWithError(getError($res), self::ERROR_TYPE_GENERAL);
			}
		}

		//delete Notizzuordnung
		$result = $this-> deleteNotizzuordnung($notiz_id, $id, $typeId);
		if (isError($result))
		{
			$this->db->trans_rollback();
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$this->load->model('person/Notiz_model', 'NotizModel');

		//Delete Note
		$result = $this->NotizModel->delete($notiz_id);

		if (isError($result))
		{
			$this->db->trans_rollback();
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if(!hasData($result))
		{
			$this->terminateWithError($this->p->t('ui','error_missingId', ['id'=> 'Notiz_id']), self::ERROR_TYPE_GENERAL);
		}

		$this->db->trans_complete();
		$this->terminateWithSuccess(getData($result));
	}

	public function loadDokumente()
	{
		$notiz_id = $this->input->post('notiz_id');

		$this->NotizModel->addSelect('campus.tbl_dms_version.*');

		$this->NotizModel->addJoin('public.tbl_notiz_dokument', 'ON (public.tbl_notiz_dokument.notiz_id = public.tbl_notiz.notiz_id)');
		$this->NotizModel->addJoin('campus.tbl_dms_version', 'ON (public.tbl_notiz_dokument.dms_id = campus.tbl_dms_version.dms_id)');

		$result = $this->NotizModel->loadWhere(
			array('public.tbl_notiz.notiz_id' => $notiz_id)
		);
		if (isError($result)) {
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		if(!hasData($result))
		{
			$this->terminateWithError($this->p->t('ui','error_missingId', ['id'=> 'Notiz_id']), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess(getData($result));
	}

	public function getMitarbeiter($searchString)
	{
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		$result = $this->MitarbeiterModel->searchMitarbeiter($searchString);
		if (isError($result)) {
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess($result);
	}

	public function getCountNotes($person_id)
	{
		$this->NotizzuordnungModel->addSelect('COUNT(*) AS anzahl', false);

		$result = $this->NotizzuordnungModel->loadWhere(
			array('person_id' => $person_id)
		);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$anzahl = current(getData($result));
		return $this->terminateWithSuccess($anzahl->anzahl ?: 0);
	}

}
