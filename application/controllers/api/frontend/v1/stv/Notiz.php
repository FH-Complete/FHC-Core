<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Notiz extends Notiz_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getUid' => ['admin:r', 'assistenz:r'],
			'getNotizen' => ['admin:r', 'assistenz:r'],
			'loadNotiz' => ['admin:r', 'assistenz:r'], // TODO(manu): self::PERM_LOGGED
			'addNewNotiz' => ['admin:rw', 'assistenz:rw'], // TODO(manu): self::PERM_LOGGED
			'updateNotiz' => ['admin:rw', 'assistenz:rw'], // TODO(manu): self::PERM_LOGGED
			'deleteNotiz' => ['admin:r', 'assistenz:r'],
			'loadDokumente' => ['admin:r', 'assistenz:r'],
			'getMitarbeiter' => ['admin:r', 'assistenz:r']
		]);

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

/*	public function getUid()
	{
		$this->terminateWithSuccess(getAuthUID());
	}*/


	public function getNotizen($id, $type)
	{

		//check if valid type
		$result = $this->NotizzuordnungModel->isValidType($type);
		if(isError($result))
			$this->terminateWithError($result->retval, self::ERROR_TYPE_GENERAL);

		//$this->terminateWithError(" after check type not valid", self::ERROR_TYPE_GENERAL);
		$result = $this->NotizModel->getNotizWithDocEntries($id, $type);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);

		//	return $this->terminateWithError("type not valid", self::ERROR_TYPE_GENERAL);

	}

/*	public function loadNotiz()
	{
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$notiz_id = $this->input->post('notiz_id');

		//$this->load->model('person/Notiz_model', 'NotizModel');
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


	public function updateNotiz()
	{
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

		$notiz_id = $this->input->post('notiz_id');

		if(!$notiz_id)
		{
			$this->terminateWithError($this->p->t('ui','error_missingId',['id'=>'Notiz_id']), self::ERROR_TYPE_GENERAL);
		}

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
		$titel = $this->input->post('titel');
		$text = $this->input->post('text');
		$verfasser_uid = $this->input->post('verfasser');
		$bearbeiter_uid = isset($_POST['bearbeiter']) ? $_POST['bearbeiter'] : $uid;
		$erledigt = $this->input->post('erledigt');
		$start = $this->input->post('start');
		$ende = $this->input->post('ende');

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
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		//update(1) laden aller bereits mit dieser notiz_id verknüpften DMS-Einträge
		$this->load->model('person/Notizdokument_model', 'NotizdokumentModel');
		$this->NotizdokumentModel->addJoin('campus.tbl_dms_version', 'dms_id');
		$dms_uploaded = null;

		$result = $this->NotizdokumentModel->loadWhere(array('notiz_id' => $notiz_id));
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		elseif (!hasData($result))
		{
			$dms_id_arr = null;
		}
		else
		{
			$result = getData($result);
			foreach($result as $doc) {
				$dms_id_arr[] = array(
					'name' => $doc->name,
					'dms_id' => $doc->dms_id
				);
			}
		}

		foreach ($_FILES as $k => $file)
		{
			//update(2) alle neuen files (alle außer type application/x.fhc-dms+json) anhängen
			if($file["type"] == 'application/x.fhc-dms+json')
			{
				$dms_uploaded[] = array(
					'name' => $file["name"]
				);
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
				$result = $this->dmslib->upload($dms, $k, array('*'));

				if (isError($result))
				{
					return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
				}
				$dms_id = $result->retval['dms_id'];

				$result = $this->NotizdokumentModel->insert(array('notiz_id' => $notiz_id, 'dms_id' => $dms_id));
				if (isError($result))
				{
					return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
				}
			}
		}

		//update(3) check if Dateien gelöscht wurden
		if(count($dms_uploaded) != count($dms_id_arr))
		{
			if (count($dms_uploaded) == 0)
			{
				$filesDeleted = $dms_id_arr;
			}
			else
			{
				$upload_new_names = array_column($dms_uploaded, "name");

				$filesDeleted = array_filter($dms_id_arr, function ($file) use ($upload_new_names) {
					return !in_array($file["name"], $upload_new_names);
				});
			}

			foreach ($filesDeleted as $file)
			{
				$result = $this->dmslib->removeAll($file['dms_id']);

				if (isError($result))
				{
					return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
				}
				else
					$this->outputJson($result);
			}
		}
		return $this->terminateWithSuccess($result);
	}*/


/*	public function deleteNotiz()
	{
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);
		$notiz_id = $this->input->post('notiz_id');
		$type = $this->input->post('type_id');
		$id = $this->input->post('id');

		//dms_id auslesen aus notizdokument wenn vorhanden
		$dms_id_arr = [];
		$this->load->model('person/Notizdokument_model', 'NotizdokumentModel');

		$result = $this->NotizdokumentModel->loadWhere(array('notiz_id' => $notiz_id));

		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		if(hasData($result))
		{
			$result = getData($result);
			foreach ($result as $doc) {
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
					return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
				}

				$this->outputJson($result);
			}
		}

		//delete Notizzuordnung
		if($type == "software_id")
		{
			// Loads extension Model
			$this->load->model('extensions/FHC-Core-Softwarebereitstellung/Softwarenotizzuordnung_model', 'ExtensionnotizzuordnungModel');
			$result = $this->ExtensionnotizzuordnungModel->delete([
				'notiz_id' => $notiz_id,
				'id' => strval($id)
				],
				[
				'type_id' => $type
			]);

		}
		else
		{
			//notizzuordnungsid!
			$result = $this->NotizzuordnungModel->delete(['notiz_id' => $notiz_id, $type => $id]);
		}


		$this->load->model('person/Notiz_model', 'NotizModel');
		//$this->NotizModel->addJoin('public.tbl_notizzuordnung', 'notiz_id');

		//TODO (erweitern um Type_id) für Extensions, damit auch Notizzuordnung gelöscht werden kann

		//Löschen von Notiz
		$result = $this->NotizModel->delete(
			array('notiz_id' => $notiz_id)
		);

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if(!hasData($result))
		{
			return $this->terminateWithError($this->p->t('ui','error_missingId', ['id'=> 'Notiz_id']), self::ERROR_TYPE_GENERAL);
		}

		return $this->terminateWithSuccess(current(getData($result)));
	}*/

/*	public function loadDokumente()
	{
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);
		$notiz_id = $this->input->post('notiz_id');

		$this->NotizModel->addSelect('campus.tbl_dms_version.*');

		$this->NotizModel->addJoin('public.tbl_notiz_dokument', 'ON (public.tbl_notiz_dokument.notiz_id = public.tbl_notiz.notiz_id)');
		$this->NotizModel->addJoin('campus.tbl_dms_version', 'ON (public.tbl_notiz_dokument.dms_id = campus.tbl_dms_version.dms_id)');

		$result = $this->NotizModel->loadWhere(
			array('public.tbl_notiz.notiz_id' => $notiz_id)
		);
		if (isError($result)) {
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		if(!hasData($result))
		{
			return $this->terminateWithError($this->p->t('ui','error_missingId', ['id'=> 'Notiz_id']), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result));
	}*/

/*	public function getMitarbeiter($searchString)
	{
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		$result = $this->MitarbeiterModel->searchMitarbeiter($searchString);
		if (isError($result)) {
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess($result);
	}*/

	public function isBerechtigt($id, $typeId)
	{
		if(!$this->permissionlib->isBerechtigt('admin', 'suid') && !$this->permissionlib->isBerechtigt('assistenz', 'suid'))
		{
			$result =  $this->p->t('lehre','error_keineSchreibrechte');

			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		return success("berechtigt in überschreibender Funktion");
/*		return $this->terminateWithError('keine Berechtigung bro', self::ERROR_TYPE_GENERAL);*/
	}

}