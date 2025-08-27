<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \CI3_Events as Events;
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
			'getDocumentDropDown' => ['admin:rw', 'assistenz:rw'],
			'getDocumentDropDownMulti' => ['admin:rw', 'assistenz:rw'],
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');
		$this->load->library('DmsLib', array('who' => getAuthUID()));

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

		$uid = getAuthUID();

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
				'updatevon' => $uid,
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

		$uid = getAuthUid();

		//check if more than 1 dokumentkurzbz
		//if()

		$result = $this->DokumentprestudentModel->insert(
			[
				'prestudent_id' => $prestudent_id,
				'dokument_kurzbz' => $dokument_kurzbz,
				'mitarbeiter_uid' => $uid,
				'datum' => date('c'),
				'insertamum' => date('c'),
				'insertvon' =>  $uid,
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
		$uid = getAuthUid();

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
			$result = $this->dmslib->delete($person_id, $dms_id);
			$this->getDataOrTerminateWithError($result);

			//LOGGING Dms ID
			$this->load->model('system/Log_model', 'LogModel');
			$result = $this->LogModel->insert([
				'executetime' => date('c'),
				'mitarbeiter_uid' => $uid,
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
				'mitarbeiter_uid' => $uid,
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
				'mitarbeiter_uid' => $uid,
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
		$uid = getAuthUID();

		$dms = array(
			'kategorie_kurzbz'  => 'Akte',
			'version'           => 0,
			'name'              => $_FILES['anhang']['name'],
			'mimetype'          => $_FILES['anhang']['type'],
			'insertamum'        => date('c'),
			'insertvon'         => $uid
		);

		$result = $this->dmslib->upload($dms, 'anhang', array("jpg", "png", "pdf"));

		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$dms_id = $result->retval['dms_id'];

		$person_id = $this->_getPersonId($prestudent_id);

		$result = $this->DokumentModel->load($dokument_kurzbz);
		$data = $this->getDataOrTerminateWithError($result);

		$bezeichnung = current($data)->bezeichnung;

		//save entry in akte
		if($dms_id)
		{
			$result = $this->AkteModel->insert([
				'person_id' => $person_id,
				'dms_id' => $dms_id,
				'dokument_kurzbz' => $dokument_kurzbz,
				'mimetype' => $_FILES['anhang']['type'],
				'insertamum' => date('c'),
				'erstelltam' => date('c'),
				'insertvon' => $uid,
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

		if(intval($data->dms_id) > 0)
		{
			$dmsdokres = $this->dmslib->read($data->dms_id);
			if (!hasData($dmsdokres)) $this->terminateWithError('DMS File not found');
			$dmsdok = getData($dmsdokres)[0];

			$mimetype = $dmsdok->mimetype;
			$filecontentbase64 = $dmsdok->file_content;
			$filename = $dmsdok->name;
		}

		$filecontent = '';

		if (!empty($filecontentbase64)) {
			$filecontent = base64_decode($filecontentbase64, true);

			if ($filecontent === false) {
				$this->terminateWithError('Base64-Dekodierung failed.');
			}
		}

		$this->terminateWithFileOutput($mimetype, $filecontent, $filename);
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

	public function getDocumentDropDown($prestudent_id, $studiensemester_kurzbz, $studiengang_kz)
	{
		//TODO(Manu) Berechtigungen hasPermissionOutputformat
		//TODO(Manu) remove: just for test ouput
		$hasPermissionOutputformat = false;

		//TODO(Manu) Validierungen
		if (!$prestudent_id) {
			$this->terminateWithError('Prestudent id is required.');
		}
		if (!$studiensemester_kurzbz)
			$this->terminateWithError("kein Studiensemester");
		if (!$studiengang_kz)
			$this->terminateWithError("kein Studiengang_kz");

		$uid = $this->_loadUIDFromPrestudent($prestudent_id);
		$semArray = $this->_getEntriesStudiensemester();
		$stgTyp = $this->_getStudiengangstyp($studiengang_kz);

		//TODO(Manu) check if if Array[0] bis Array[4] befüllt
		//TODO(Manu) handling stgTyp ungleich b,m,d

		//	$semString = implode(";", $semArray);
		//	$this->terminateWithError("Semester " . $semString . " " . $semArray[0] . " " . $semArray[1]);

		$documents = [
			$this->buildDropdownEntry("accountinfo", "Accountinfoblatt", "xml=accountinfoblatt.xml.php&xsl=AccountInfo&output=pdf", $uid, 10, null),
			$this->buildDropdownEntry("ausbildungsvertrag", "Ausbildungsvertrag", "xml=ausbildungsvertrag.xml.php&xsl=Ausbildungsver&output=pdf", $uid, 20, null),
			$this->buildDropdownEntry("ausbildungsvertrag_en", "Ausbildungsvertrag Englisch", "xml=ausbildungsvertrag.xml.php&xsl=AusbVerEng&output=pdf", $uid, 21, null),
			$this->buildDropdownEntry("studienbestaetigung", "Studienbestätigung", "xml=student.rdf.php&xsl=Inskription&output=pdf", $uid, 40, null),
			$this->buildDropdownEntry("studienbestaetigung_en", "Studienbestätigung Englisch", "xml=student.rdf.php&xsl=InskriptionEng&output=pdf", $uid, 41, null),
			$this->buildDropdownEntry("zutrittskarte", "Zutrittskarte", "xsl=ZutrittskarteStud&output=pdf&data=$uid", $uid,100, "zutrittskarte.php"),
			$this->buildDropdownEntry("studienblatt", "Studienblatt", "xml=studienblatt.xml.php&xsl=Studienblatt&output=pdf&ss=$studiensemester_kurzbz", $uid, 60, null),
			$this->buildDropdownEntry("studienblatt_eng", "Studienblatt Englisch", "xml=studienblatt.xml.php&xsl=StudienblattEng&output=pdf&ss=$studiensemester_kurzbz", $uid, 61, null),

			// Studienerfolg Menüs automatisch
			$this->buildStudienerfolgSubmenu("de", $uid, $semArray, $studiensemester_kurzbz),
			$this->buildStudienerfolgSubmenu("en", $uid, $semArray, $studiensemester_kurzbz),
			$this->buildStudienerfolgSubmenu("de", $uid, $semArray, $studiensemester_kurzbz, true),
			$this->buildStudienerfolgSubmenu("en", $uid, $semArray, $studiensemester_kurzbz, true),

			[
				"id" => "submenu_studstatus",
				"type" => "submenu",
				"name" => "Verwaltung des StudierendenStatus",
				"order" => 110,
				"data" => [
					$this->buildDropdownEntry("Abmeldung", "Abmeldung", "xml=AntragAbmeldung.xml.php&xsl=AntragAbmeldungl&prestudent_id=$prestudent_id&output=pdf", $uid, null, null),
					$this->buildDropdownEntry("Abmeldung durch Stgl", "AntragAbmeldungStgl", "xml=AntragAbmeldungStgl.xml.php&xsl=AntragAbmeldungStgl&prestudent_id=$prestudent_id&output=pdf", $uid, null, null),
					$this->buildDropdownEntry("Unterbrechung", "Unterbrechung", "xml=AntragUnterbrechung.xml.php&xsl=AntragUnterbrechung&prestudent_id=$prestudent_id&output=pdf", $uid, null, null),
					$this->buildDropdownEntry("Wiederholung", "Abmeldung durch Ablauf der Wiederholungsfrist", "xml=AntragWiederholung.xml.php&xsl=AntragWiederholung&prestudent_id=$prestudent_id&output=pdf", $uid, null, null),
				]
			],

			$this->loadDropDownEntriesFinalExam($hasPermissionOutputformat, $stgTyp, $uid),

			$this->buildDropdownEntry("bescheid", "Bescheid (nur Voransicht)", "xml=abschlusspruefung.rdf.php&xsl_stg_kz=$studiengang_kz&xsl=Bescheid&output=pdf", $uid, 80, null),
			$this->buildDropdownEntry("diplomasupp", "Diploma Supplement (nur Voransicht)", "xml=diplomasupplement.xml.php&xsl_stg_kz=$studiengang_kz&xsl=DiplSupplement&output=pdf", $uid, 81, null)
		];

		Events::trigger('DocumentGenerationDropDown',
			// passing $menu per reference
			function & () use (&$documents) {
				return $documents;
			},
			$prestudent_id,
			$studiensemester_kurzbz,
			$studiengang_kz
		);

		usort($documents, function ($a, $b) {
			$orderA = isset($a['order']) ? (int)$a['order'] : PHP_INT_MAX;
			$orderB = isset($b['order']) ? (int)$b['order'] : PHP_INT_MAX;
			return $orderA <=> $orderB;
		});


		$this->terminateWithSuccess($documents);
		return $documents || null;
	}

	public function getDocumentDropDownMulti()
	{
		$studentUids = $this->input->get('studentUids');
		$prestudentIds = [];

		if (is_array($studentUids) && !empty($studentUids)) {
			foreach ($studentUids as $uid) {
				$prestudent_id = $this-> _loadPrestudentFromUid($uid);
				$prestudentIds[] = $prestudent_id;
			}
		} else {
			echo "No prestudent IDs received.";
		}

		$uidString = implode(";", $studentUids);
		$prestudentIdsString = implode(";", $prestudentIds);


		$documents = [
			[
				"id"   => "accountinfo1",
				"type" => "documenturl",
				"name" => "Accountinfoblatt",
				"url"  => "pdfExport.php?xml=accountinfoblatt.xml.php&xsl=AccountInfo&output=pdf&uid=" . $uidString,
				"scope" => "prestudent"
			],
			[
				"id"   => "ausbildungsvertrag1_de",
				"type" => "documenturl",
				"name" => "Ausbildungsvertrag Deutsch",
				"url"  => "pdfExport.php?xml=ausbildungsvertrag.xml.php&xsl=Ausbildungsver&output=pdf&uid=" . $uidString,
				"scope" => "prestudent"
			],
			[
				"id"   => "ausbildungsvertrag1_en",
				"type" => "documenturl",
				"name" => "Ausbildungsvertrag Englisch",
				"url"  => "pdfExport.php?xml=ausbildungsvertrag.xml.php&xsl=AusbVerEng&output=pdf&uid=" . $uidString,
				"scope" => "prestudent"
			],
			[
				"id"   => "submenu_studienerfolg_1",
				"type" => "submenu",
				"name" => "Studienerfolg",
				"data" => [
					[
						"id"   => "submenu_studienerfolg_sem1",
						"type" => "submenu",
						"name" => "Studienerfolg WS2025",
						"data" => [
							[
								"id"   => "studienerfolg_sem_alle_1",
								"type" => "submenu",
								"name" => "Studienerfolg WS2025 Alle",
								"data"  => [
									[
										"id"   => "studienerfolg_sem_alle_1_FA",
										"type" => "documenturl",
										"name" => "Studienerfolg Alle FINANZAMT",
										"url"  => "pdfExport.php?xml=ausbildungsvertrag.xml.php&xsl=AusbVerEng&output=pdf&uid=" . $uidString,
										"scope" => "prestudent"
									],
									[
										"id"   => "studienerfolg_sem_alle_1_nichtFA",
										"type" => "documenturl",
										"name" => "Studienerfolg Alle NICHT FINANZAMT",
										"url"  => "pdfExport.php?xml=ausbildungsvertrag.xml.php&xsl=AusbVerEng&output=pdf&uid=" . $uidString,
										"scope" => "prestudent"
									]
								]

							]
						]
					]
				]
			],
			[
				"id"   => "submenu_studstatus",
				"type" => "submenu",
				"name" => "Verwaltung des StudierendenStatus",
				"data" => [
					[
						"id"   => "Abmeldung",
						"type" => "documenturl",
						"name" => "Abmeldung",
						"url"  => "pdfExport.php?xml=AntragAbmeldung.xml.php&xsl=AntragAbmeldung&output=pdf&uid=" . $uidString,
						"scope" => "prestudent"
					],
					[
						"id"   => "Abmeldung durch Stg",
						"type" => "documenturl",
						"name" => "AntragAbmeldungStgl",
						"url"  => "pdfExport.php?xml=AntragAbmeldungStgl.xml.php&xsl=AntragAbmeldungStgl&output=pdf&uid=" . $uidString,
						"scope" => "prestudent"
					]
				]
			],
			[
				"id"   => "zutrittskarte",
				"type" => "documenturl",
				"name" => "Zutrittskarte",
				"url"  => "zutrittskarte.php?xsl=ZutrittskarteStud&output=pdf&data=" . $uidString,
				"scope" => "prestudent"
			],
			[
				"id"   => "zutrittskarte",
				"type" => "parameterurl",
				"name" => "Zutrittskarte",
				"baseurl" => "zutrittskarte.php",
 				"parameterurl" =>"xsl=ZutrittskarteStud&output=pdf&data=" . $uidString,
				"scope" => "prestudent"
			],
			[
				"id"   => "studienbestaetigung",
				"type" => "documenturl",
				"name" => "Studienbestätigung",
				"url"  => "pdfExport.php?xml=ausbildungsvertrag.xml.php&xsl=AusbVerEng&output=pdf&uid=" . $uidString,
				"scope" => "prestudent"
			],
			[
				"id"   => "studienerfolg",
				"type" => "documenturl",
				"name" => "Studienbestätigung",
				"url"  => "pdfExport.php?xml=ausbildungsvertrag.xml.php&xsl=AusbVerEng&output=pdf&uid=" . $uidString,
				"scope" => "prestudent"
			],

		];

/*		Events::trigger('DocumentGenerationDropDownMulti',
			// passing $menu per reference
			function & () use (&$documents) {
				return $documents;
			},
			$prestudent_id,
			$studiensemester_kurzbz,
			$studiengang_kz
		);*/

		usort($documents, function ($a, $b) {
			$orderA = isset($a['order']) ? (int)$a['order'] : PHP_INT_MAX;
			$orderB = isset($b['order']) ? (int)$b['order'] : PHP_INT_MAX;
			return $orderA <=> $orderB;
		});

	//	FireEvent(DocumentGenerationDropDownMulti(&$documents);

		$this->terminateWithSuccess($documents);

		return $documents || null;
	}

	private function _loadUIDFromPrestudent($prestudent_id)
	{
		if(!$prestudent_id){
			return $this->terminateWithError("no prestudent ID received.");
		}
		$this->load->model('crm/Student_model', 'StudentModel');
		$result = $this->StudentModel->loadWhere(
			['prestudent_id' => $prestudent_id]
		);

		$data = $this->getDataOrTerminateWithError($result);
		$student = current($data);

		return $student->student_uid;
	}

	private function _loadPrestudentFromUid($studentUid)
	{

		$this->load->model('crm/Student_model', 'StudentModel');
		$result = $this->StudentModel->loadWhere(
			['student_uid' => $studentUid]
		);

		$data = $this->getDataOrTerminateWithError($result);
		$student = current($data);


		return $student->prestudent_id;
	}

	/**
	 * is building an array with studiensemesterkurzb
	 * actual studiensemester plus the 5 studiensemester in the past

	 * @return Array Studiensemester_kurzbz
	 */
	private function _getEntriesStudiensemester(){
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$this->StudiensemesterModel->addPlusMinus(1, 5);
		$this->StudiensemesterModel->addOrder('ende', 'DESC');
		$result = $this->StudiensemesterModel->load();
		$data = $this->getDataOrTerminateWithError($result);

		foreach($data as $sem)
		{
			$semArray[] = $sem->studiensemester_kurzbz;
		}

		array_shift($semArray);

		return $semArray;
	}
	/**
	 * is returning the typ of Studiengang (Bakk oder Master)

	 * @return character  eg. 'b' or 'm'
	 */
	private function _getStudiengangstyp($studiengang_kz)
	{
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$result = $this->StudiengangModel->loadWhere(
			array('studiengang_kz' => $studiengang_kz)
		);
		$data = $this->getDataOrTerminateWithError($result);

		$typStudiengang = current($data)->typ;

		return $typStudiengang;
	}

	//TODO(Manu) make helperfunction
	/**
	 * is building an array for Dropdown Entry in Print Dropdown
	 * @param $id id for the Document to add to the Document Array
	 * @param $name name of the dropdownEntry
	 * @param $parameterUrl url of parameters xml, xsl,format as needed
	 * 	WITHOUT BASEURL eg. "xml=abschlusspruefung.rdf.php&xsl_stg_kz=$studiengang_kz&xsl=Bescheid&output=pdf"
	 * @param $uid default parameter, if null only parameterurl will be added
	 * 	additional needed parameter: put in the parameterUrl
	 * @param $alternativeBaseUrl: if baseUrl not pdfExport.php, put here alternative without ? char, eg. "zutrittskarte.php"

	 * @return Array
	 */
	private function buildDropdownEntry($id, $name, $parameterurl, $uid=null, $order=null, $alternativeBaseUrl=null)
	{
		//DEFAULT BASEURL
		$baseurl = "pdfExport.php?";

		$uidString = $uid ? "&uid=" . $uid : "";

		if($alternativeBaseUrl)
		{
			return [
				"id"    => $id,
				"type"  => "documenturl",
				"name"  => $name,
				"url"   => $alternativeBaseUrl . "?" . $parameterurl . $uidString,
				"order" => $order
			];
		}
		else
			return [
				"id"    => $id,
				"type"  => "documenturl",
				"name"  => $name,
				"url"   => $baseurl . $parameterurl . "&uid=" . $uid,
				"order" => $order
			];

	}

	/**
	 * helper function to create ArrayStructure
	 * actual studiensemester plus the 5 studiensemester in the past

	 * @return Array Studiensemester_kurzbz
	 */
	private function buildStudienerfolgSubmenu($lang, $uid, $semArray, $studiensemester_kurzbz, $fa = false)
	{
		$entries = [];

		$xsl = $lang === "de" ? "Studienerfolg" : "StudienerfolgEng";
		$idPrefix = "submenu_studienerfolg_" . $lang . ($fa ? "_fa" : "");

		$entries[] = $this->buildDropdownEntry(
			$idPrefix . "_aktuell",
			"ausgewähltes Semester",
			"xml=studienerfolg.rdf.php&xsl=$xsl&ss=$studiensemester_kurzbz" . ($fa ? "&typ=finanzamt" : ""),
			$uid
		);

		//all semester
		$entries[] = $this->buildDropdownEntry(
			$idPrefix . "_all",
			"alle Semester",
			"xml=studienerfolg.rdf.php&xsl=$xsl&ss=$studiensemester_kurzbz&all=true" . ($fa ? "&typ=finanzamt" : ""),
			$uid
		);

		//sem from array
		foreach ($semArray as $i => $sem) {
			$entries[] = $this->buildDropdownEntry(
				$idPrefix . ($i === 0 ? "_akt" : "_minus" . $i),
				$sem,
				"xml=studienerfolg.rdf.php&xsl=$xsl&ss=$sem" . ($fa ? "&typ=finanzamt" : ""),
				$uid
			);

		}
		$order = 0;
		if ($lang === "de" && !$fa) $order = 75; // Studienerfolg
		if ($lang === "en" && !$fa) $order = 76; // Studienerfolg Englisch
		if ($lang === "de" &&  $fa) $order = 77; // Studienerfolg Finanzamt
		if ($lang === "en" &&  $fa) $order = 78; // Studienerfolg Finanzamt Englisch

		return [
			"id"   => $idPrefix,
			"type" => "submenu",
			"name" => "Studienerfolg " . ($fa ? " Finanzamt" : "") . ($lang === "de" ? "" : "Englisch") ,
			"order" => $order,
			"data" => $entries,
		];
	}

	private function loadDropDownEntriesFinalExam($hasPermissionOutputformat, $stgTyp, $uid)
	{
		if ($stgTyp == 'b')
			$postfix = 'Bakk';
		else if ($stgTyp == 'm' || $stgTyp == 'd')
			$postfix = 'Master';
		else
			//TODO(Manu) sollte nicht null sein!! -> dropdown wird  im Falle von Lehrgängen nicht erstellt
			return null;


		$arrayFinalExam = [
			'pruefungsprotokoll' => [
				'de' => [
					'Bakk' => 'PrProtBA',
					'Master' => 'PrProtMA',
				],
				'en' => [
					'Bakk' => 'PrProtBAEng',
					'Master' => 'PrProtMAEng',
				],
			],
			'pruefungszeugnis' => [
				'de' => [
					'Bakk' => 'Bakkzeugnis',
					'Master' => 'Diplomzeugnis',
				],
				'en' => [
					'Bakk' => 'BakkzeugnisEng',
					'Master' => 'DiplomzeugnisEng',
				],
			],
			'urkunde' => [
				'de' => [
					'Bakk' => 'Bakkurkunde',
					'Master' => 'Diplomurkunde',
				],
				'en' => [
					'Bakk' => 'BakkurkundeEng',
					'Master' => 'DiplomurkundeEng',
				],
			],
		];

		$langLabels = [
			"de" => "Deutsch",
			"en" => "Englisch"
		];

		$docLabels = [
			"pruefungsprotokoll" => "Prüfungsprotokoll",
			"pruefungszeugnis" => "Zeugnis",
			"urkunde" => "Urkunde"
		];

		$submenuData = [];
		if ($hasPermissionOutputformat) {
			foreach ($arrayFinalExam as $docType => $langs) {
				foreach ($langs as $lang => $types) {
					$xsl = $types[$postfix];
					$idPrefix = $docType . "_" . $lang;

					$baseName = $docLabels[$docType] . " " . $langLabels[$lang];
					$baseUrl = "xml=abschlusspruefung.rdf.php&xsl={$xsl}";

					//3 outputformates
					foreach (["pdf", "odt", "docx"] as $format) {
						$submenuData[] = $this->buildDropdownEntry(
							$idPrefix . "_" . $format,
							$baseName . " (" . strtoupper($format) . ")",
							$baseUrl . "&output=" . $format,
							$uid
						);
					}
				}
			}
		}
		else
		{
			foreach ($arrayFinalExam as $docType => $langs) {
				foreach ($langs as $lang => $types) {
					$xsl = $types[$postfix]; // Auswahl Bakk/Master für jeweilige Sprache
					$id = $docType . "_" . $lang;

					$name = $docLabels[$docType] . " " . $langLabels[$lang];

					$url = "xml=abschlusspruefung.rdf.php&xsl=" . $xsl . "&output=pdf";

					$submenuData[] = $this->buildDropdownEntry($id, $name, $url, $uid);
				}
			}
		}
		return [
			"id" => "submenu_finalexam",
			"type" => "submenu",
			"name" => "Abschlussprüfung",
			"data" => $submenuData,
			"order" => null,
			"order" => 80,
		];
	}

}
