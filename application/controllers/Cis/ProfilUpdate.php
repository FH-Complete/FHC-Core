<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 *
 */
class ProfilUpdate extends Auth_Controller
{

	public static $STATUS_PENDING = NULL;
	public static $STATUS_ACCEPTED = NULL;
	public static $STATUS_REJECTED = NULL;

	public static $TOPICS = [];


	public function __construct()
	{
		parent::__construct([
			'index' => ['student/stammdaten:r', 'mitarbeiter/stammdaten:r'],
			'id' => ['student/stammdaten:r', 'mitarbeiter/stammdaten:r'],
			'getProfilUpdateWithPermission' => ['student/stammdaten:r', 'mitarbeiter/stammdaten:r'],
			'acceptProfilRequest' => ['student/stammdaten:rw', 'mitarbeiter/stammdaten:rw'],
			'denyProfilRequest' => ['student/stammdaten:rw', 'mitarbeiter/stammdaten:rw'],
			'show' => ['basis/cis:r'],

			'insertProfilRequest' => ['basis/cis:rw'],
			'updateProfilRequest' => ['basis/cis:rw'],
			'deleteProfilRequest' => ['basis/cis:rw'],
			'selectProfilRequest' => ['basis/cis:r'],
			'insertFile' => ['basis/cis:rw'],
			'getProfilRequestFiles' => ['basis/cis:r'],
			'getStatus' => ['basis/cis:r'],
			'getTopic' => ['basis/cis:r'],
		]);

		$this->load->config('cis');

		$this->load->model('person/Profil_update_model', 'ProfilUpdateModel');
		$this->load->model('person/Kontakt_model', 'KontaktModel');
		$this->load->model('person/Adresse_model', 'AdresseModel');
		$this->load->model('person/Adressentyp_model', 'AdressenTypModel');
		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$this->load->model('system/Sprache_model', 'SpracheModel');
		$this->load->model('person/Profil_update_status_model', 'ProfilUpdateStatusModel');
		$this->load->model('person/Profil_update_topic_model', 'ProfilUpdateTopicModel');

		// Load language phrases
		$this->loadPhrases(
			array(
				'ui',
				'global',
				'person',
				'profil',
				'profilUpdate'
			)
		);

		$this->load->library('DmsLib');
		$this->load->library('PermissionLib');

		//? put the uid and pid inside the controller for reusability
		$this->uid = getAuthUID();
		$this->pid = getAuthPersonID();

		// setup the ProfilUpdate states
		$this->ProfilUpdateStatusModel->addSelect(['status_kurzbz']);
		$status_kurzbz = $this->ProfilUpdateStatusModel->load();
		if (hasData($status_kurzbz)) {
			list($status_pending, $status_accepted, $status_rejected) = getData($status_kurzbz);

			self::$STATUS_PENDING = $status_pending->status_kurzbz;
			self::$STATUS_ACCEPTED = $status_accepted->status_kurzbz;
			self::$STATUS_REJECTED = $status_rejected->status_kurzbz;
		}
		// setup the ProfilUpdate topics
		$this->ProfilUpdateTopicModel->addSelect(['topic_kurzbz']);
		$topic_kurzbz = $this->ProfilUpdateTopicModel->load();

		if (hasData($topic_kurzbz)) {
			foreach (getData($topic_kurzbz) as $topic) {
				self::$TOPICS[$topic->topic_kurzbz] = $topic->topic_kurzbz;
			}
		}
	}


	public function index()
	{
		$this->load->view('Cis/ProfilUpdate');
	}

	public function id($profil_update_id = null)
	{
		$this->load->view('Cis/ProfilUpdate', ['profil_update_id' => $profil_update_id]);
	}

	public function getStatus()
	{
		echo json_encode([self::$STATUS_PENDING => self::$STATUS_PENDING, self::$STATUS_ACCEPTED => self::$STATUS_ACCEPTED, self::$STATUS_REJECTED => self::$STATUS_REJECTED]);
	}

	public function getTopic()
	{
		echo json_encode(self::$TOPICS);
	}

	private function sendEmail_onProfilUpdate_response($uid, $topic, $status)
	{
		if($this->config->item('cis_send_profil_update_mails') === false)
		{
			return;
		}

		$this->load->helper('hlp_sancho_helper');
		$email = $uid . "@" . DOMAIN;


		function languageQuery($language)
		{
			return "select index from public.tbl_sprache where sprache = '" + $language + "'";
		}
		$this->ProfilUpdateStatusModel->addSelect(["bezeichnung_mehrsprachig[(" . languageQuery('German') . ")] as status_de", "bezeichnung_mehrsprachig[(" . languageQuery('English') . ")] as status_en"]);
		$status_translation = $this->ProfilUpdateStatusModel->loadWhere(["status_kurzbz" => $status]);
		if (isError($status_translation)) {
			show_error($this->p->t('profilUpdate', 'ProfilUpdateStatusTranslationError'));
		}
		$status_translation = hasData($status_translation) ? getData($status_translation)[0] : null;
		if (isset($status_translation)) {
			$mail_res = sendSanchoMail("profil_update_response", ['topic' => $topic, 'status_de' => $status_translation->status_de, 'status_en' => $status_translation->status_en, 'href' => APP_ROOT . 'Cis/Profil'], $email, ("Profil Änderung " . $this->p->t('profilUpdate', 'pending')));
			if (!$mail_res) {
				show_error($this->p->t('profilUpdate', 'profilUpdate_email_error'));
			}
		}

	}


	private function sendEmail_onProfilUpdate_insertion($uid, $profil_update_id, $topic)
	{
		if($this->config->item('cis_send_profil_update_mails') === false)
		{
			return;
		}

		$this->load->helper('hlp_sancho_helper');
		$emails = [];

		$isMitarbeiter_res = $this->MitarbeiterModel->isMitarbeiter($uid);
		if (isError($isMitarbeiter_res)) {
			show_error($this->p->t('profilUpdate', 'profilUpdate_mitarbeiterCheck_error'));
		}
		$isMitarbeiter_res = getData($isMitarbeiter_res);

		//! if the $uid is a mitarbeiter and student, only the hr is notified by email
		if ($isMitarbeiter_res) {
			//? user is not a student therefore he is a mitarbeiter, send email to Personalverwaltung
			//? use constant variable MAIL_GST to mail to the personalverwaltung
			$this->MitarbeiterModel->addSelect([TRUE]);
			$this->MitarbeiterModel->addJoin("public.tbl_benutzer", "public.tbl_benutzer.uid = public.tbl_mitarbeiter.mitarbeiter_uid");
			//? check if the the userID is a mitarbeiter and if the benutzer is active
			$res = $this->MitarbeiterModel->loadWhere(["public.tbl_mitarbeiter.mitarbeiter_uid" => $uid, "public.tbl_benutzer.aktiv" => TRUE]);
			if (isError($res)) {
				show_error("was not able to query the mitarbeiter and benutzer by the uid: " . $uid);
			}
			if (hasData($res)) {
				array_push($emails, MAIL_GST);
			} else {
				show_error($this->p->t('profilUpdate', 'profilUpdate_mitarbeiterCheck_error'));
			}
		} else {
			//? if it is not a mitarbeiter, check whether it is a student and send email to studiengang
			$isStudent_res = $this->StudentModel->isStudent($uid);
			if (isError($isStudent_res)) {
				show_error($this->p->t('profilUpdate', 'profilUpdate_studentCheck_error'));
			}
			$isStudent_res = getData($isStudent_res);
			if ($isStudent_res) {
				//? Send email to the Studiengangsassistentinnen
				$this->StudentModel->addSelect(["public.tbl_studiengang.email"]);
				$this->StudentModel->addJoin("public.tbl_benutzer", "public.tbl_benutzer.uid = public.tbl_student.student_uid");
				$this->StudentModel->addJoin("public.tbl_prestudent", "public.tbl_benutzer.person_id = public.tbl_prestudent.person_id");
				$this->StudentModel->addJoin("public.tbl_prestudentstatus", "public.tbl_prestudentstatus.prestudent_id = public.tbl_prestudent.prestudent_id");
				$this->StudentModel->addJoin("public.tbl_studiengang", "public.tbl_studiengang.studiengang_kz = public.tbl_prestudent.studiengang_kz");
				//* check if the benutzer itself is active
				//* check if the student status is Student or Diplomand (active students)
				$this->StudentModel->db->where_in("public.tbl_prestudentstatus.status_kurzbz", ['Student', 'Diplomand']);
				$res = $this->StudentModel->loadWhere(["public.tbl_benutzer.aktiv" => TRUE, "public.tbl_student.student_uid" => $uid]);
				if (isError($res)) {
					show_error(getData($res));
				} else {
					$res = hasData($res) ? getData($res) : [];
					foreach ($res as $emailObj) {
						array_push($emails, $emailObj->email);
					}
				}
			}
		}
		$mail_res = [];
		//? sending email
		foreach ($emails as $email) {
			array_push($mail_res, sendSanchoMail("profil_update", ['uid' => $uid, 'topic' => $topic, 'href' => APP_ROOT . 'Cis/ProfilUpdate/id/' . $profil_update_id], $email, ("Profil Änderung von " . $uid)));
		}
		foreach ($mail_res as $m_res) {
			if (!$m_res) {
				show_error($this->p->t('profilUpdate', 'profilUpdate_email_error'));
			}
		}

	}


	public function show($dms_id)
	{

		$profil_update = $this->ProfilUpdateModel->loadWhere(['attachment_id' => $dms_id]);
		$profil_update = hasData($profil_update) ? getData($profil_update)[0] : null;

		//? checks if an profil update exists with the dms_id requested from the user
		if ($profil_update) {
			$is_mitarbeiter_profil_update = getData($this->MitarbeiterModel->isMitarbeiter($profil_update->uid));
			$is_student_profil_update = getData($this->StudentModel->isStudent($profil_update->uid));

			if (
				$this->permissionlib->isBerechtigt('student/stammdaten:r') && $is_student_profil_update ||
				$this->permissionlib->isBerechtigt('mitarbeiter/stammdaten:r') && $is_mitarbeiter_profil_update ||
				$this->uid == $profil_update->uid
			) {
				// Get file to be downloaded from DMS
				$newFilename = $this->uid . "/document_" . $dms_id;
				$download = $this->dmslib->download($dms_id);
				if (isError($download))
					return $download;

				// Download file
				$this->outputFile(getData($download));


			} else {
				show_error($this->p->t('profilUpdate', 'profilUpdate_permission_error'));
				return;
			}

		} else {
			show_error($this->p->t('profilUpdate', 'profilUpdate_dms_error'));
			return;
		}

	}


	public function insertFile($replace)
	{
		$replace = json_decode($replace);

		if (!count($_FILES)) {
			echo json_encode([]);
			return;
		}

		//? if replace is set it contains the profil_update_id in which the attachment_id has to be replaced
		if (isset($replace)) {
			$this->ProfilUpdateModel->addSelect(["attachment_id"]);
			$profilUpdate = $this->ProfilUpdateModel->load([$replace]);
			if (isError($profilUpdate)) {
				return json_encode(error($this->p->t('profilUpdate', 'profilUpdate_loading_error')));
			}
			//? get the attachmentID
			$dms_id = hasData($profilUpdate) ? getData($profilUpdate)[0]->attachment_id : null;

			//? delete old dms_file of Profil Update
			$this->deleteOldVersionFile($dms_id);
		}


		$files = $_FILES['files'];
		$file_count = count($files['name']);

		$res = [];

		for ($i = 0; $i < $file_count; $i++) {
			$_FILES['files']['name'] = $files['name'][$i];
			$_FILES['files']['type'] = $files['type'][$i];
			$_FILES['files']['tmp_name'] = $files['tmp_name'][$i];
			$_FILES['files']['error'] = $files['error'][$i];
			$_FILES['files']['size'] = $files['size'][$i];

			$dms = [
				"kategorie_kurzbz" => "profil_aenderung",
				"version" => 0,
				"name" => $_FILES['files']['name'],
				"mimetype" => $_FILES['files']['type'],
				"beschreibung" => $this->uid . " Profil Änderung",
				"insertvon" => $this->uid,
				"insertamum" => "NOW()",
			];

			$tmp_res = $this->dmslib->upload($dms, 'files', array("jpg", "png", "pdf"));

			$tmp_res = hasData($tmp_res) ? getData($tmp_res) : null;
			array_push($res, $tmp_res);
		}

		echo json_encode($res);
	}


	public function selectProfilRequest()
	{
		$_GET = json_decode($this->input->raw_input_stream, true);
		$uid = $this->input->get('uid');
		$id = $this->input->get('id');
		$whereClause = ['uid' => $this->uid];

		if (isset($uid))
			$whereClause['uid'] = $uid;
		if (isset($id))
			$whereClause['id'] = $id;

		$res = $this->ProfilUpdateModel->getProfilUpdatesWhere($whereClause);
		$res = hasData($res) ? getData($res) : null;
		echo json_encode($res);

	}


	public function getProfilRequestFiles()
	{
		$id = json_decode($this->input->raw_input_stream);

		$this->ProfilUpdateModel->addSelect(["attachment_id"]);
		$attachmentID = $this->ProfilUpdateModel->load([$id]);
		if (isError($attachmentID)) {
			return json_encode(error($this->p->t('profilUpdate', 'profilUpdate_loading_error')));
		}
		//? get the attachmentID
		$dms_id = hasData($attachmentID) ? getData($attachmentID)[0]->attachment_id : null;

		//? get the name to the file
		$this->DmsVersionModel->addSelect(["name", "dms_id"]);
		$attachment = $this->DmsVersionModel->load([$dms_id, 0]);
		if (isError($attachment)) {
			return json_encode(error($this->p->t('profilUpdate', 'profilUpdate_dmsVersion_error')));
		}
		$attachment = hasData($attachment) ? getData($attachment) : null;
		//? returns {name:..., dms_id:...}
		echo json_encode($attachment);
	}

	public function insertProfilRequest()
	{

		$json = json_decode($this->input->raw_input_stream);

		$payload = $json->payload;
		$identifier = property_exists($json->payload, "kontakt_id") ? "kontakt_id" : (property_exists($json->payload, "adresse_id") ? "adresse_id" : null);

		$data = ["topic" => $json->topic, "uid" => $this->uid, "requested_change" => json_encode($payload), "insertamum" => "NOW()", "insertvon" => $this->uid, "status" => self::$STATUS_PENDING ?: 'Pending'];

		//? insert fileID in the dataset if sent with post request
		if (isset($json->fileID)) {
			$data['attachment_id'] = $json->fileID;

		}

		//? loops over all updateRequests from a user to validate if the new request is valid
		$res = $this->ProfilUpdateModel->getProfilUpdatesWhere(["uid" => $this->uid]);
		if (isError($res)) {
			show_error($this->p->t('profilUpdate', 'profilUpdate_loading_error'));
		}
		$res = hasData($res) ? getData($res) : null;

		//? the user cannot delete a zustelladresse/kontakt
		if (isset($payload->delete) && $payload->{$identifier == "kontakt_id" ? "zustellung" : "zustelladresse"}) {
			echo json_encode(error($this->p->t('profilUpdate', 'profilUpdate_deleteZustellung_error')));
			return;
		}

		//? if the user tries to delete a adresse, checks whether the adresse is a heimatadresse, if so an error is raised
		if (isset($payload->delete) && $identifier == "adresse_id") {
			$adr = $this->AdresseModel->load($payload->$identifier);
			$adr = getData($adr)[0];
			if ($adr->heimatadresse) {
				echo json_encode(error($this->p->t('profilUpdate', 'profilUpdate_deleteZustellung_error')));
				return;
			}
		}

		if ($res) {
			$pending_changes = array_filter($res, function ($element) {
				return $element->status == (self::$STATUS_PENDING ?: "Pending");
			});

			foreach ($pending_changes as $update_request) {
				$existing_change = $update_request->requested_change;

				//? the user can add as many new kontakte/adressen as he likes
				if (!isset($payload->add) && property_exists($existing_change, $identifier) && property_exists($payload, $identifier) && $existing_change->$identifier == $payload->$identifier) {
					//? the kontakt_id / adresse_id of a change has to be unique 
					echo json_encode(error($this->p->t('profilUpdate', 'profilUpdate_changeTwice_error')));
					return;
				}

				//? if it is not updating any kontakt/adresse, the topic has to be unique
				elseif (!$identifier && $update_request->topic == $json->topic) {
					echo json_encode(error($this->p->t('profilUpdate', 'profilUpdate_changeTopicTwice_error', ['0' => $update_request->topic])));
					return;
				}
			}
		}

		$insertID = $this->ProfilUpdateModel->insert($data);

		if (isError($insertID)) {
			show_error(getData($insertID));
		} else {
			$insertID = hasData($insertID) ? getData($insertID) : null;

			//? sends emails to the correspondents of the $uid
			$this->sendEmail_onProfilUpdate_insertion($this->uid, $insertID, $json->topic);
			echo json_encode(success($insertID));
		}
	}

	public function updateProfilRequest()
	{
		$json = json_decode($this->input->raw_input_stream);

		$updateData = ["requested_change" => json_encode($json->payload), "updateamum" => "NOW()", "updatevon" => $this->uid];
		if (isset($json->fileID)) {
			$updateData['attachment_id'] = json_decode($json->fileID);
		}
		$updateID = $this->ProfilUpdateModel->update([$json->ID], $updateData);
		//? insert fileID in the dataset if sent with post request

		if (isError($updateID)) {
			//catch error
		} else {
			$updateID = hasData($updateID) ? getData($updateID)[0] : null;
			//TODO: should an email be sent to the responsable people when the user changes his profil update
			echo json_encode(success($updateID));
		}
	}

	public function deleteProfilRequest()
	{

		$json = json_decode($this->input->raw_input_stream);
		$delete_res = $this->ProfilUpdateModel->delete([$json]);
		echo json_encode($delete_res);
	}


	public function getProfilUpdateWithPermission($status = null)
	{
		// early return if no status has been passed as argument
		if (!isset($status)) {
			echo json_encode($this->ProfilUpdateModel->getProfilUpdateWithPermission());
			return;
		}

		// get the sprache of the user
		$sprachenIndex = $this->SpracheModel->loadWhere(["sprache" => getUserLanguage()]);
		$sprachenIndex = hasData($sprachenIndex) ? getData($sprachenIndex)[0]->index : null;

		if (isset($sprachenIndex) && isset($status)) {
			// get the corresponding status kurz_bz primary key out of the translation
			$status = $this->ProfilUpdateStatusModel->execReadOnlyQuery("select * from public.tbl_profil_update_status where ? = ANY(bezeichnung_mehrsprachig)", [$status]);
			$status = hasData($status) ? getData($status)[0]->status_kurzbz : null;
			$res = $this->ProfilUpdateModel->getProfilUpdateWithPermission(isset($status) ? ['status' => $status] : null);

			echo json_encode($res);
		}
	}



	private function getOE_from_student($student_uid)
	{

		//? returns the oe_einheit eines Studenten 
		$query = "SELECT public.tbl_studiengang.oe_kurzbz
		FROM public.tbl_student
		JOIN public.tbl_studiengang ON tbl_student.studiengang_kz = public.tbl_studiengang.studiengang_kz
		WHERE public.tbl_student.student_uid = ?;";

		$res = $this->StudentModel->execReadOnlyQuery($query, [$student_uid]);
		if (!isSuccess($res)) {
			show_error($this->p->t('profilUpdate', 'profilUpdate_loadingOE_error'));
		}
		$res = hasData($res) ? getData($res) : [];
		$res = array_map(
			function ($item) {
				return $item->oe_kurzbz;
			},
			$res
		);
		return $res;
	}


	public function acceptProfilRequest()
	{
		$_POST = json_decode($this->input->raw_input_stream, true);
		$id = $this->input->post('profil_update_id', true);
		$uid = $this->input->post('uid', true);

		//? fetching person_id using UID
		$personID = $this->PersonModel->getByUid($uid);
		$personID = hasData($personID) ? getData($personID)[0]->person_id : null;
		$status_message = $this->input->post('status_message', true);
		$topic = $this->input->post('topic', true);

		//! somehow the xss check converted boolean false to empty string
		$requested_change = $this->input->post('requested_change');

		//! check for required information
		if (!isset($id) || !isset($uid) || !isset($personID) || !isset($requested_change) || !isset($topic)) {
			return json_encode(error($this->p->t('profilUpdate', 'profilUpdate_requiredInformation_error')));
		}

		$is_mitarbeiter_profil_update = getData($this->MitarbeiterModel->isMitarbeiter($uid));
		$is_student_profil_update = getData($this->StudentModel->isStudent($uid));


		//? check if the permissions are set correctly
		if (
			$this->permissionlib->isBerechtigt('student/stammdaten', "suid", $this->getOE_from_student($uid)) && $is_student_profil_update ||
			$this->permissionlib->isBerechtigt('mitarbeiter/stammdaten', "suid") && $is_mitarbeiter_profil_update
		) {

			if (is_array($requested_change) && array_key_exists("adresse_id", $requested_change)) {
				$insertID = $this->handleAdresse($requested_change, $personID);
				$insertID = hasData($insertID) ? getData($insertID) : null;
				if (isset($insertID)) {
					$requested_change['adresse_id'] = $insertID;
					$update_res = $this->updateRequestedChange($id, $requested_change);
					if (isError($update_res)) {
						echo json_encode(error($this->p->t('profilUpdate', 'profilUpdate_address_error', [$insertID])));
						return;
					}
				}

			} else if (is_array($requested_change) && array_key_exists("kontakt_id", $requested_change)) {
				$insertID = $this->handleKontakt($requested_change, $personID);
				$insertID = hasData($insertID) ? getData($insertID) : null;
				if (isset($insertID)) {
					$requested_change['kontakt_id'] = $insertID;
					$update_res = $this->updateRequestedChange($id, $requested_change);
					if (isError($update_res)) {
						echo json_encode(error($this->p->t('profilUpdate', 'profilUpdate_kontakt_error', [$insertID])));
						return;
					}
				}


			} else {
				switch ($topic) {
					// mapping phrasen to database columns to make the update with the correct column names
					case self::$TOPICS['Titel']:
						$topic = "titelpre";
						break;
					case self::$TOPICS['Postnomen']:
						$topic = "titelpost";
						break;
					case self::$TOPICS['Vorname']:
						$topic = "vorname";
						break;
					case self::$TOPICS['Nachname']:
						$topic = "nachname";
						break;
					default:
						show_error($this->p->t('profilUpdate', 'profilUpdate_topic_error', [$topic]));
						return;
				}

				$result = $this->PersonModel->update($personID, [$topic => $requested_change["value"]]);
				if (isError($result)) {
					echo json_encode(error($this->p->t('profilUpdate', 'profilUpdate_insert_error')));
					return;
				}
			}
			$this->sendEmail_onProfilUpdate_response($uid, $topic, self::$STATUS_ACCEPTED);

			echo json_encode($this->setStatusOnUpdateRequest($id, self::$STATUS_ACCEPTED, $status_message, $requested_change));
		} else {
			show_error($this->p->t('profilUpdate', 'profilUpdate_permission_error'));
		}


	}

	public function denyProfilRequest()
	{

		$_POST = json_decode($this->input->raw_input_stream, true);
		$id = $this->input->post('profil_update_id', true);
		$uid = $this->input->post('uid', true);
		$topic = $this->input->post('topic', true);
		$status_message = $this->input->post('status_message', true);

		$is_mitarbeiter_profil_update = getData($this->MitarbeiterModel->isMitarbeiter($uid));
		$is_student_profil_update = getData($this->StudentModel->isStudent($uid));


		if (
			$this->permissionlib->isBerechtigt('student/stammdaten', "suid", $this->getOE_from_student($uid)) && $is_student_profil_update ||
			$this->permissionlib->isBerechtigt('mitarbeiter/stammdaten', "suid") && $is_mitarbeiter_profil_update
		) {
			$this->sendEmail_onProfilUpdate_response($uid, $topic, self::$STATUS_REJECTED);
			echo json_encode($this->setStatusOnUpdateRequest($id, self::$STATUS_REJECTED, $status_message));
		} else {
			show_error($this->p->t('profilUpdate', 'profilUpdate_permission_error'));
		}


	}

	private function updateRequestedChange($id, $requested_change)
	{
		return $this->ProfilUpdateModel->update([$id], ['requested_change' => json_encode($requested_change)]);
	}

	private function setStatusOnUpdateRequest($id, $status, $status_message)
	{
		return $this->ProfilUpdateModel->update([$id], ["status" => $status, "status_timestamp" => "NOW()", "status_message" => $status_message]);
	}

	private function deleteOldVersionFile($dms_id)
	{
		if (!isset($dms_id)) {
			return;
		}

		//? collect all the results of the deleted versions in an array 
		$res = array();

		//? delete all the different versions of the dms_file
		$dmsVersions = $this->DmsVersionModel->loadWhere(["dms_id" => $dms_id]);
		$dmsVersions = hasData($dmsVersions) ? getData($dmsVersions) : null;
		if (isset($dmsVersions)) {
			$zwischen_res = array_map(function ($item) {
				return $item->version;
			}, $dmsVersions);
			foreach ($zwischen_res as $version) {
				array_push($res, $this->DmsVersionModel->delete([$dms_id, $version]));
			}
		} else {
			echo json_encode(error($this->p->t('profilUpdate', 'profilUpdate_dmsVersion_error')));
		}

		//? returns a result for each deleted dms_file
		return $res;
	}


	private function handleKontakt($requested_change, $personID)
	{
		$kontakt_id = $requested_change["kontakt_id"];
		//? removes the kontakt_id because we don't want to update the kontakt_id in the database
		unset($requested_change["kontakt_id"]);


		//! ADD
		if (array_key_exists('add', $requested_change) && $requested_change['add']) {
			//? removes add flag
			unset($requested_change['add']);
			$requested_change['person_id'] = $personID;
			$requested_change['insertamum'] = "NOW()";
			$requested_change['insertvon'] = getAuthUID();
			$insertID = $this->KontaktModel->insert($requested_change);
			$insert_kontakt_id = $insertID;
			if (isError($insert_kontakt_id)) {
				show_error($this->p->t('profilUpdate', 'profilUpdate_insertKontakt_error'));
			}
			$insert_kontakt_id = hasData($insert_kontakt_id) ? getData($insert_kontakt_id) : null;
			if ($insert_kontakt_id) {
				$this->handleDupplicateZustellKontakte($requested_change['zustellung'], $insert_kontakt_id);
			}


		}
		//! DELETE
		elseif (array_key_exists('delete', $requested_change) && $requested_change['delete']) {
			$this->KontaktModel->delete($kontakt_id);
		}
		//! UPDATE
		else {
			$requested_change['updateamum'] = "NOW()";
			$requested_change['updatevon'] = getAuthUID();

			$update_kontakt_id = $this->KontaktModel->update($kontakt_id, $requested_change);

			if (isError($update_kontakt_id)) {
				show_error($this->p->t('profilUpdate', 'profilUpdate_updateKontakt_error'));
			}
			$update_kontakt_id = hasData($update_kontakt_id) ? getData($update_kontakt_id) : null;
			if ($update_kontakt_id) {
				$this->handleDupplicateZustellKontakte($requested_change['zustellung'], $update_kontakt_id);
			}
		}
		return isset($insertID) ? $insertID : null;
	}

	private function handleAdresse($requested_change, $personID)
	{

		$this->AdressenTypModel->addSelect(["adressentyp_kurzbz"]);
		$adr_kurzbz = $this->AdressenTypModel->loadWhere(["bezeichnung" => $requested_change['typ']]);
		$adr_kurzbz = hasData($adr_kurzbz) ? getData($adr_kurzbz)[0]->adressentyp_kurzbz : null;
		//? replace the address_typ with its correct kurzbz foreign key
		$requested_change['typ'] = $adr_kurzbz;

		$adresse_id = $requested_change["adresse_id"];
		//? removes the adresse_id because we don't want to update the kontakt_id in the database
		unset($requested_change["adresse_id"]);


		//! ADD
		if (array_key_exists('add', $requested_change) && $requested_change['add']) {

			//? removes add flag
			unset($requested_change['add']);
			$requested_change['insertamum'] = "NOW()";
			$requested_change['insertvon'] = getAuthUID();
			$requested_change['person_id'] = $personID;
			//TODO: zustelladresse, heimatadresse, rechnungsadresse und nation werden nicht beachtet
			$insertID = $this->AdresseModel->insert($requested_change);
			$insert_adresse_id = $insertID;
			if (isError($insert_adresse_id)) {
				show_error($this->p->t('profilUpdate', 'profilUpdate_insertAdresse_error'));
			}
			$insert_adresse_id = hasData($insert_adresse_id) ? getData($insert_adresse_id) : null;
			if ($insert_adresse_id) {
				$this->handleDupplicateZustellAdressen($requested_change['zustelladresse'], $insert_adresse_id);
			}

		}
		//! DELETE
		elseif (array_key_exists('delete', $requested_change) && $requested_change['delete']) {
			$this->AdresseModel->delete($adresse_id);
		}
		//! UPDATE
		else {
			$requested_change['updateamum'] = "NOW()";
			$requested_change['updatevon'] = getAuthUID();
			$update_adresse_id = $this->AdresseModel->update($adresse_id, $requested_change);
			if (isError($update_adresse_id)) {
				show_error($this->p->t('profilUpdate', 'profilUpdate_updateAdresse_error'));
			}
			$update_adresse_id = hasData($update_adresse_id) ? getData($update_adresse_id) : null;
			if ($update_adresse_id) {
				$this->handleDupplicateZustellAdressen($requested_change['zustelladresse'], $update_adresse_id);
			}
		}
		return isset($insertID) ? $insertID : null;
	}


	private function handleDupplicateZustellKontakte($zustellung, $kontakt_id)
	{
		if ($zustellung) {
			$this->PersonModel->addSelect("public.tbl_kontakt.kontakt_id");
			$this->PersonModel->addJoin("public.tbl_kontakt", "public.tbl_kontakt.person_id = public.tbl_person.person_id");
			$zustellKontakteArray = $this->PersonModel->loadWhere(["public.tbl_person.person_id" => $this->pid, "zustellung" => TRUE]);
			if (!isSuccess($zustellKontakteArray)) {
				return error($this->p->t('profilUpdate', 'profilUpdate_loadingZustellkontakte_error'));
			}
			$zustellKontakteArray = hasData($zustellKontakteArray) ? getData($zustellKontakteArray) : null;

			if ($zustellung && count($zustellKontakteArray) > 0) {
				$zustellKontakteArray = array_filter($zustellKontakteArray, function ($kontakt) use ($kontakt_id) {
					return $kontakt->kontakt_id != $kontakt_id;
				});
				foreach ($zustellKontakteArray as $kontakt) {
					$this->KontaktModel->update($kontakt->kontakt_id, ["zustellung" => FALSE]);
				}

			}
		}
	}

	private function handleDupplicateZustellAdressen($zustellung, $adresse_id)
	{
		if ($zustellung) {
			$this->PersonModel->addSelect("public.tbl_adresse.adresse_id");
			$this->PersonModel->addJoin("public.tbl_adresse", "public.tbl_adresse.person_id = public.tbl_person.person_id");
			$zustellAdressenArray = $this->PersonModel->loadWhere(["public.tbl_person.person_id" => $this->pid, "zustelladresse" => TRUE]);
			if (!isSuccess($zustellAdressenArray)) {
				return error($this->p->t('profilUpdate', 'profilUpdate_loadingZustellAdressen_error'));
			}
			$zustellAdressenArray = hasData($zustellAdressenArray) ? getData($zustellAdressenArray) : null;

			if ($zustellung && count($zustellAdressenArray) > 0) {

				$zustellAdressenArray = array_filter($zustellAdressenArray, function ($adresse) use ($adresse_id) {

					return $adresse->adresse_id != $adresse_id;
				});
				foreach ($zustellAdressenArray as $adresse) {
					$this->AdresseModel->update($adresse->adresse_id, ["zustelladresse" => FALSE]);
				}

			}
		}
	}


}