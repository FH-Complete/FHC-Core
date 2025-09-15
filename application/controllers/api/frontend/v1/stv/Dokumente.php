<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Dokumente extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getDocumentsUnaccepted' => ['admin:r', 'assistenz:r'],
			'getDocumentsAccepted' => ['admin:r', 'assistenz:r'],
			'deleteZuordnung' => ['admin:rw', 'assistenz:rw'],
			'createZuordnung' => ['admin:rw', 'assistenz:rw'],
			'loadAkte' => ['admin:rw', 'assistenz:rw'],
			'deleteAkte' => ['admin:rw', 'assistenz:rw'],
			'updateAkte' => ['admin:rw', 'assistenz:rw'],
			'getDoktypen' => ['admin:r', 'assistenz:r'],
			'uploadDokument' => ['admin:rw', 'assistenz:rw'],
			'download' => ['admin:rw', 'assistenz:rw'],
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');
		$this->load->library('AkteLib');
		$this->load->library('DmsLib');

		// Load language phrases
		$this->loadPhrases([
			'ui',
			'dokumente'
		]);

		// Load models
		$this->load->model('crm/Akte_model', 'AkteModel');
		$this->load->model('crm/Dokument_model', 'DokumentModel');
		$this->load->model('crm/Dokumentprestudent_model', 'DokumentprestudentModel');

		//TODO(Manu) check additional Berechtigungen
		//TODO(Manu) check if using dokument lib instead of dokument model?
	}

	public function getDocumentsUnaccepted($prestudent_id, $studiengang_kz)
	{
		if(!$prestudent_id)
			$this->terminateWithError($this->p->t('ui', 'errorMissingValue', ['value' => 'Prestudent ID']), self::ERROR_TYPE_GENERAL);

		if (!is_numeric($prestudent_id))
			$this->terminateWithError($this->p->t('ui', 'error_valueNotNumeric', ['value' => 'Prestudent ID']), self::ERROR_TYPE_GENERAL);

		if(!$studiengang_kz)
			$this->terminateWithError($this->p->t('ui', 'errorMissingValue', ['value' => 'Studiengang_kz']), self::ERROR_TYPE_GENERAL);

		$person_id = $this->_getPersonId($prestudent_id);
		$result = $this->DokumentModel->getUnacceptedDocuments($prestudent_id, $person_id);

		$dataAkteUnaccepted = $this->getDataOrTerminateWithError($result);
		$resultMd = $this->_getMissingDocuments($studiengang_kz, $prestudent_id);

		$data = $this->_mergeDocuments($dataAkteUnaccepted, $resultMd);

		$this->terminateWithSuccess($data);
	}

	public function getDocumentsAccepted($prestudent_id, $studiengang_kz)
	{
		if(!$prestudent_id)
			$this->terminateWithError($this->p->t('ui', 'errorMissingValue', ['value' => 'Prestudent ID']), self::ERROR_TYPE_GENERAL);

		if (!is_numeric($prestudent_id))
			$this->terminateWithError($this->p->t('ui', 'error_valueNotNumeric', ['value' => 'Prestudent ID']), self::ERROR_TYPE_GENERAL);

		if(!$studiengang_kz)
			$this->terminateWithError($this->p->t('ui', 'errorMissingValue', ['value' => 'Studiengang_kz']), self::ERROR_TYPE_GENERAL);

		$resultPreDoc = $this->_getPrestudentDokumente($prestudent_id);

		$arrayAccepted = [];
		$person_id = $this->_getPersonId($prestudent_id);

		$docNames = array_map(function ($item) {
			return $item->dokument_kurzbz;
		}, $resultPreDoc);

		foreach($docNames as $doc)
		{
			$result = $this->AkteModel->getAktenFAS($person_id, $doc, $studiengang_kz, $prestudent_id, true);

			if (isError($result))
			{
				return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
			}
			if (hasData($result))
			{
				$data = getData($result);
				foreach ($data as $value)
				{
					array_push($arrayAccepted, $value);
				}
			}
		}

		//Mapping with document_kurzbz
		$preDocMap = [];
		foreach ($resultPreDoc as $pre) {
			$preDocMap[$pre->dokument_kurzbz] = $pre;
		}

		$mergedArray = [];
		foreach ($arrayAccepted as $doc) {
			$merged = clone $doc;

			if (isset($preDocMap[$doc->dokument_kurzbz])) {
				$merged->docdatum = $preDocMap[$doc->dokument_kurzbz]->docdatum;
				$merged->insertvonma = $preDocMap[$doc->dokument_kurzbz]->insertvonma;
				$merged->bezeichnung = $preDocMap[$doc->dokument_kurzbz]->bezeichnung;
			} else {
				$merged->akzeptiertdatum = null;
				$merged->akzeptiertvon = null;
			}

			$mergedArray[] = $merged;
		}

		$this->terminateWithSuccess($mergedArray);
	}

	public function deleteZuordnung($prestudent_id, $dokument_kurzbz)
	{
		if(!$prestudent_id)
			$this->terminateWithError($this->p->t('ui', 'errorMissingValue', ['value' => 'Prestudent ID']), self::ERROR_TYPE_GENERAL);

		if (!is_numeric($prestudent_id))
			$this->terminateWithError($this->p->t('ui', 'error_valueNotNumeric', ['value' => 'Prestudent ID']), self::ERROR_TYPE_GENERAL);

		if(!$dokument_kurzbz)
			$this->terminateWithError($this->p->t('ui', 'errorMissingValue', ['value' => 'Dokument_kurzbz']), self::ERROR_TYPE_GENERAL);

		$result = $this->DokumentprestudentModel->delete(
			[
				'prestudent_id' => $prestudent_id,
				'dokument_kurzbz' => $dokument_kurzbz
			]
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function loadAkte($akte_id)
	{
		if (!$akte_id)
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'Akte ID']), self::ERROR_TYPE_GENERAL);

		$this->AkteModel->addSelect('public.tbl_akte.*');
		$this->AkteModel->addSelect("CONCAT(public.tbl_person.vorname, ' ' , public.tbl_person.nachname) AS namePerson");
		$this->AkteModel->addJoin('public.tbl_person', 'person_id');
		$result = $this->AkteModel->loadWhere(
			[
				'akte_id' => $akte_id,
			]
		);

		$data = $this->getDataOrTerminateWithError($result);
		$data = current($data);
		$this->terminateWithSuccess($data);
	}

	public function updateAkte()
	{
		$this->form_validation->set_rules('akte_id', 'Akte ID', 'required', [
			'required' => $this->p->t('dokumente', 'err_updateNotAllowed')
		]);

		$this->form_validation->set_rules('dokument_kurzbz', 'Dokumenttyp', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Dokumenttyp'])
		]);

		$this->form_validation->set_rules('nachreichung_am', 'Nachreichung am', 'is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'Nachreichung am'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->AkteModel->update(
			[
				'akte_id' => $this->input->post('akte_id'),
			],
			[
				'dokument_kurzbz' => $this->input->post('dokument_kurzbz'),
				'anmerkung_intern' => $this->input->post('anmerkung_intern'),
				'titel_intern' => $this->input->post('titel_intern'),
				'nachgereicht_am' => $this->input->post('nachgereicht_am'),
				'updateamum' => date('c'),
				'updatevon' => getAuthUID(),
			]
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}

	public function createZuordnung($prestudent_id, $dokument_kurzbz)
	{
		if (!$prestudent_id)
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'Prestudent ID']), self::ERROR_TYPE_GENERAL);

		if(!$dokument_kurzbz)
			$this->terminateWithError($this->p->t('ui', 'errorMissingValue', ['value' => 'Dokument_kurzbz']), self::ERROR_TYPE_GENERAL);

		//check if more than 1 dokumentkurzbz
		//if()

		$result = $this->DokumentprestudentModel->insert(
			[
				'prestudent_id' => $prestudent_id,
				'dokument_kurzbz' => $dokument_kurzbz,
				'mitarbeiter_uid' => getAuthUID(),
				'datum' => date('c'),
				'insertamum' => date('c'),
				'insertvon' =>  getAuthUID(),
			]
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function deleteAkte($akte_id)
	{
		if (!$akte_id)
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'Akte ID']), self::ERROR_TYPE_GENERAL);

		$result = $this->AkteModel->load($akte_id);
		$dataAkte = $this->getDataOrTerminateWithError($result);

		$logdata_akte = var_export($dataAkte, true);

		$dms_id = current($dataAkte)->dms_id;
		$nachgereicht = current($dataAkte)->nachgereicht;
		$inhalt = current($dataAkte)->inhalt;
		$inhaltVorhanden = $inhalt != '';

		$this->db->trans_start();

		if($dms_id)
		{
			$this->load->model('content/Dms_model', 'DmsModel');
			$result = $this->DmsModel->load($dms_id);
			$data = $this->getDataOrTerminateWithError($result);

			$logdata_dms = (array)$data;
			$logdata_dms = "Logdata: " . var_export($logdata_dms, true);

			//delete from dmsLib
			$this->load->library('DmsLib');
			$person_id = current($dataAkte)->person_id;
			$result = $this->aktelib->removeByPersonIdAndDmsId($person_id, $dms_id);
			$this->getDataOrTerminateWithError($result);

			//LOGGING Dms ID
			$this->load->model('system/Log_model', 'LogModel');
			$result = $this->LogModel->insert([
				'executetime' => date('c'),
				'mitarbeiter_uid' => getAuthUID(),
				'beschreibung' => "Löschen der DMS_ID ". $dms_id,
				'sql' => $logdata_dms
			]);
			$this->getDataOrTerminateWithError($result);

			//delete akte
			$result = $this->AkteModel->delete(
				[
					'akte_id' => $akte_id
				]
			);
			$data = $this->getDataOrTerminateWithError($result);

			//Logging Deletion Akte
			$result = $this->LogModel->insert([
				'executetime' => date('c'),
				'mitarbeiter_uid' => getAuthUID(),
				'beschreibung' => "Löschen der Akte ". $akte_id,
				'sql' => "DELETE FROM public.tbl_akte WHERE akte_id=" .$akte_id. " LogData: ". $logdata_akte
			]);
			$this->getDataOrTerminateWithError($result);
			$this->db->trans_complete();
			$this->terminateWithSuccess($data);
		}
		elseif (!!$dms_id || ($nachgereicht && !$inhaltVorhanden))
		{
			$result = $this->AkteModel->delete(
				[
					'akte_id' => $akte_id
				]
			);
			$data = $this->getDataOrTerminateWithError($result);

			$result = $this->LogModel->insert([
				'executetime' => date('c'),
				'mitarbeiter_uid' => getAuthUID(),
				'beschreibung' => "Löschen der Akte ". $akte_id,
				'sql' => "DELETE FROM public.tbl_akte WHERE akte_id=" .$akte_id. " LogData: ". $logdata_akte
			]);
			$this->getDataOrTerminateWithError($result);

			$this->db->trans_complete();
			$this->terminateWithSuccess($data);
		}
		else
			$this->terminateWithError($this->p->t('dokumente', 'err_deleteDokHere'), self::ERROR_TYPE_GENERAL);
	}

	public function uploadDokument()
	{
		$this->load->library('DmsLib');
		$prestudent_id = $this->input->post('prestudent_id');
		$anmerkung_intern = $this->input->post('anmerkung_intern');
		$titel_intern = $this->input->post('titel_intern');
		$dokument_kurzbz = $this->input->post('dokument_kurzbz');

		$this->form_validation->set_rules('prestudent_id', 'Prestudent_id', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Prestudent ID'])
		]);

		$this->form_validation->set_rules('dokument_kurzbz', 'Dokumenttyp', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Dokumenttyp'])
		]);

		//validation if attachment was added
		$this->form_validation->set_rules('anhang', 'Attachment', 'callback_file_check');

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$this->db->trans_start();

		$uploadDataResult = uploadFile('anhang', array('jpg', 'png', 'pdf'));
		if (isError($uploadDataResult))
		{
			return $this->terminateWithError(getError($uploadDataResult), self::ERROR_TYPE_GENERAL);
		}

		// If data exists
		if (hasData($uploadDataResult))
		{
			// Add file to the DMS (DB + file system)
			$dms_res = $this->dmslib->add(
				getData($uploadDataResult)['file_name'],
				getData($uploadDataResult)['file_type'],
				fopen(getData($uploadDataResult)['full_path'], 'r'),
				'Akte', // kategorie_kurzbz
				null, // dokument_kurzbz
				null, // beschreibung
				false, // cis_suche
				null, // schlagworte
				getAuthUID() // insertvon
			);
		}

		// Error occurred or data not found
		if (isError($dms_res) || !hasData($dms_res)) $this->terminateWithError(getError($dms_res), self::ERROR_TYPE_GENERAL);

		$dms_id = getData($dms_res)->dms_id;

		$person_id = $this->_getPersonId($prestudent_id);

		$dokumentResult = $this->DokumentModel->load($dokument_kurzbz);
		$data = $this->getDataOrTerminateWithError($dokumentResult);

		$bezeichnung = current($data)->bezeichnung;

		// Save entry in akte
		if ($dms_id)
		{
			$result = $this->AkteModel->insert([
				'person_id' => $person_id,
				'dms_id' => $dms_id,
				'dokument_kurzbz' => $dokument_kurzbz,
				'mimetype' => $_FILES['anhang']['type'],
				'insertamum' => date('c'),
				'erstelltam' => date('c'),
				'insertvon' => getAuthUID(),
				'anmerkung_intern' => $anmerkung_intern,
				'titel_intern' => $titel_intern,
				'bezeichnung' => $bezeichnung,
				'titel' => $_FILES['anhang']['name']
			]);

			$data = $this->getDataOrTerminateWithError($result);
			$this->db->trans_complete();
			$this->terminateWithSuccess($data);
		}
		$this->db->trans_complete();
		$this->terminateWithSuccess($data);
	}

	public function getDoktypen()
	{
		$this->DokumentModel->addSelect('dokument_kurzbz');
		$this->DokumentModel->addSelect('bezeichnung');
		$this->DokumentModel->addOrder('dokument_kurzbz', 'ASC');
		$result = $this->DokumentModel->load();

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function download()
	{
		//TODO(Manu) check filetype, Decoding
		$akte_id = $this->input->get('akte_id');

		if(!$akte_id)
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'Akte ID']), self::ERROR_TYPE_GENERAL);

		if (!is_numeric($akte_id))
			$this->terminateWithError($this->p->t('ui', 'error_valueNotNumeric', ['value' => 'Akte ID']), self::ERROR_TYPE_GENERAL);


		$result = $this->AkteModel->load($akte_id);
		if (!hasData($result)) $this->terminateWithError('Akte not found');
		$data = getData($result)[0];

		$mimetype = $data->mimetype;
		$filecontentbase64 = $data->inhalt;
		$filename = $data->titel;

		if (intval($data->dms_id) > 0)
		{
			$file = $this->dmslib->getOutputFileInfo($data->dms_id, $filename);

			$this->terminateWithFileOutput(getData($file)->mimetype, file_get_contents(getData($file)->file), $filename);
		}
		else
		{
			$filecontent = '';

			if (!empty($filecontentbase64)) {
				$filecontent = base64_decode($filecontentbase64, true);

				if ($filecontent === false) {
					$this->terminateWithError('Base64-Dekodierung failed.');
				}
			}

			$this->terminateWithFileOutput($mimetype, $filecontent, $filename);
		}
	}

	private function _getMissingDocuments($studiengang_kz, $prestudent_id)
	{
		$result = $this->DokumentModel->getMissingDocuments($studiengang_kz, $prestudent_id);

		$data = $this->getDataOrTerminateWithError($result);

		return $data;
	}

	private function _getUnacceptedDocuments($prestudent_id)
	{
		$person_id = $this->_getPersonId($prestudent_id);
		$result = $this->DokumentModel->getUnacceptedDocuments($prestudent_id, $person_id);

		$data = $this->getDataOrTerminateWithError($result);

		return $data;
	}

	/**
	 * helper function for merging objects
	 * sorts object after merging according to dokument_kurzbz
	 * @param $original object of documents of akte
	 * @param object $toMerge documents to merge (of dokumentprestudent, dokumentstudiengang)
	 * @return Array mergedObject
	 */
	private function _mergeDocuments($original, $toMerge)
	{
		$existingKurzbez = [];
		foreach ($original as $doc) {
			$existingKurzbez[$doc->dokument_kurzbz] = true;
		}

		foreach ($toMerge as $doc) {
			if (!isset($existingKurzbez[$doc->dokument_kurzbz])) {
				$original[] = $doc;
				$existingKurzbez[$doc->dokument_kurzbz] = true;
			}
			else
			{
				foreach($original as $docOriginal)
				{
					if ($docOriginal->dokument_kurzbz == $doc->dokument_kurzbz)
					{
						$docOriginal->pflicht = $doc->pflicht;
						$docOriginal->onlinebewerbung = $doc->onlinebewerbung;
					}
				}
			}
		}

		usort($original, function ($a, $b) {
			return strcmp($a->dokument_kurzbz, $b->dokument_kurzbz);
		});

		return $original;
	}

	private function _getDocumentsOfAkte($person_id)
	{
		$result = $this->AkteModel->getAktenFAS($person_id);

		$data = $this->getDataOrTerminateWithError($result);

		return $data;
	}

	private function _getPrestudentDokumente($prestudent_id)
	{
		$result = $this->DokumentprestudentModel->getPrestudentDokumente($prestudent_id);

		$data = $this->getDataOrTerminateWithError($result);

		return $data;
	}

	private function _getPersonId($prestudent_id)
	{
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$result = $this->PrestudentModel->loadWhere(
			['prestudent_id' => $prestudent_id]
		);

		$data = $this->getDataOrTerminateWithError($result);
		$person = current($data);

		return $person->person_id;
	}

	public function file_check($str)
	{
		if (isset($_FILES['anhang']) && $_FILES['anhang']['size'] > 0)
		{
			$allowed_mime_types = ['image/jpeg', 'image/png', 'application/pdf'];
			$mime = mime_content_type($_FILES['anhang']['tmp_name']);

			if (in_array($mime, $allowed_mime_types))
			{
				return true;
			} else
			{
				$this->form_validation->set_message('file_check', $this->p->t('dokumente', 'error_fileType'));
				return false;
			}
		}
		else
		{
			$this->form_validation->set_message('file_check', $this->p->t('dokumente', 'error_fileMissing'));
			return false;
		}
	}
}
