<?php
/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller operates between (interface) the JS (GUI) and the SearchBarLib (back-end)
 * Provides data to the ajax get calls about the searchbar component
 * This controller works with JSON calls on the HTTP GET and the output is always JSON
 */
class ProfilUpdate extends FHCAPI_Controller
{

    public static $STATUS_PENDING = NULL;
	public static $STATUS_ACCEPTED = NULL;
	public static $STATUS_REJECTED = NULL;

	public static $TOPICS = [];

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
            'getStatus' => self::PERM_LOGGED,
            'getTopic' => self::PERM_LOGGED,
			'getProfilRequestFiles' => self::PERM_LOGGED,
			'denyProfilRequest' => ['student/stammdaten:rw', 'mitarbeiter/stammdaten:rw'],
			'acceptProfilRequest' => ['student/stammdaten:rw', 'mitarbeiter/stammdaten:rw'],
			'selectProfilRequest' => self::PERM_LOGGED,
			
		]);

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

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

    public function getStatus()
	{
		$this->terminateWithSuccess([self::$STATUS_PENDING => self::$STATUS_PENDING, self::$STATUS_ACCEPTED => self::$STATUS_ACCEPTED, self::$STATUS_REJECTED => self::$STATUS_REJECTED]);
	}


    public function getTopic()
	{
        if(!count(self::$TOPICS)){
            $this->terminateWithError('No topics found');
        }
		$this->terminateWithSuccess(self::$TOPICS);
	}

	public function selectProfilRequest()
	{

		$uid = $this->input->get('uid',true);
		$id = $this->input->get('id',true);
		$whereClause = ['uid' => $this->uid];
		
		if (isset($uid))
			$whereClause['uid'] = $uid;
		if (isset($id))
			$whereClause['id'] = $id;

		$res = $this->ProfilUpdateModel->getProfilUpdatesWhere($whereClause);
		$res = $this->getDataOrTerminateWithError($res);
		$this->terminateWithSuccess($res);

	}

	public function getProfilRequestFiles($id)
	{
		 if(!$id){
			$this->terminateWithError("parameter id is missing");
		 }

		$this->ProfilUpdateModel->addSelect(["attachment_id"]);
		$attachmentID = $this->ProfilUpdateModel->load([$id]);
		if (isError($attachmentID)) {
			$this->terminateWithError(error($this->p->t('profilUpdate', 'profilUpdate_loading_error')),self::ERROR_TYPE_GENERAL);
		}
		//? get the attachmentID
		$dms_id = $this->getDataOrTerminateWithError($attachmentID)[0]->attachment_id;

		//? get the name to the file
		$this->DmsVersionModel->addSelect(["name", "dms_id"]);
		$attachment = $this->DmsVersionModel->load([$dms_id, 0]);
		if (isError($attachment)) {
			$this->terminateWithError(error($this->p->t('profilUpdate', 'profilUpdate_dmsVersion_error')),self::ERROR_TYPE_GENERAL);
		}
		$attachment = $this->getDataOrTerminateWithError($attachment);
		//? returns {name:..., dms_id:...}
		$this->terminateWithSuccess($attachment);
	}

	public function denyProfilRequest()
	{
		$id = $this->input->post('profil_update_id', true);
		$uid = $this->input->post('uid', true);
		$topic = $this->input->post('topic', true);
		$status_message = $this->input->post('status_message', true); //optional

		if(!isset($id) || !isset($uid) || !isset($topic)){
			$this->terminateWithError("parameter id, uid, topic or status_message is missing");
		}

		$is_mitarbeiter = $this->MitarbeiterModel->isMitarbeiter($uid);
		$is_mitarbeiter = $this->getDataOrTerminateWithError($is_mitarbeiter);
		
		$is_student = $this->StudentModel->isStudent($uid);
		$is_student = $this->getDataOrTerminateWithError($is_student);

		if (
			$is_student && $this->permissionlib->isBerechtigt('student/stammdaten', "suid", $this->getOE_from_student($uid)) ||
			$is_mitarbeiter && $this->permissionlib->isBerechtigt('mitarbeiter/stammdaten', "suid") 
		) {
			$this->sendEmail_onProfilUpdate_response($uid, $topic, self::$STATUS_REJECTED);
			$this->terminateWithSuccess($this->setStatusOnUpdateRequest($id, self::$STATUS_REJECTED, $status_message));
		} else {
			$this->terminateWithError($this->p->t('profilUpdate', 'profilUpdate_permission_error'),self::ERROR_TYPE_GENERAL);
		}
	}

	public function acceptProfilRequest()
	{
		$id = $this->input->post('profil_update_id', true);
		$uid = $this->input->post('uid', true);
		$topic = $this->input->post('topic', true);
		$requested_change = $this->input->post('requested_change');
		$status_message = $this->input->post('status_message', true); //optional

		//? fetching person_id using UID
		$personID = $this->PersonModel->getByUid($uid);
		$personID = $this->getDataOrTerminateWithError($personID)[0]->person_id;

		//! check for required information
		if (!isset($id) || !isset($uid) || !isset($personID) || !isset($requested_change) || !isset($topic)) {
			$this->terminateWithError(error($this->p->t('profilUpdate', 'profilUpdate_requiredInformation_error')));
		}

		$is_mitarbeiter = $this->MitarbeiterModel->isMitarbeiter($uid);
		$is_mitarbeiter = $this->getDataOrTerminateWithError($is_mitarbeiter);

		$is_student = $this->StudentModel->isStudent($uid);
		$is_student = $this->getDataOrTerminateWithError($is_student);


		//? check if the permissions are set correctly
		if (
			$is_student && $this->permissionlib->isBerechtigt('student/stammdaten', "suid", $this->getOE_from_student($uid)) ||
			$is_mitarbeiter && $this->permissionlib->isBerechtigt('mitarbeiter/stammdaten', "suid") 
		) {

			if (is_array($requested_change) && array_key_exists("adresse_id", $requested_change)) {
				$insertID = $this->handleAdresse($requested_change, $personID);
				$insertID = getData($insertID);
				if (isset($insertID)) {
					$requested_change['adresse_id'] = $insertID;
					$update_res = $this->updateRequestedChange($id, $requested_change);
					if (isError($update_res)) {
						$this->terminateWithError(error($this->p->t('profilUpdate', 'profilUpdate_address_error', [$insertID])));
					}
				}

			} else if (is_array($requested_change) && array_key_exists("kontakt_id", $requested_change)) {
				$insertID = $this->handleKontakt($requested_change, $personID);
				$insertID = getData($insertID);
				if (isset($insertID)) {
					$requested_change['kontakt_id'] = $insertID;
					$update_res = $this->updateRequestedChange($id, $requested_change);
					if (isError($update_res)) {
						$this->terminateWithError(error($this->p->t('profilUpdate', 'profilUpdate_kontakt_error', [$insertID])));
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
						$this->terminateWithError($this->p->t('profilUpdate', 'profilUpdate_topic_error', [$topic]));
				}

				$result = $this->PersonModel->update($personID, [$topic => $requested_change["value"]]);
				if (isError($result)) $this->terminateWithError(error($this->p->t('profilUpdate', 'profilUpdate_insert_error')));
			
			}
			$this->sendEmail_onProfilUpdate_response($uid, $topic, self::$STATUS_ACCEPTED);

			$this->terminateWithSuccess($this->setStatusOnUpdateRequest($id, self::$STATUS_ACCEPTED, $status_message, $requested_change));
		} else {
			$this->terminateWithError($this->p->t('profilUpdate', 'profilUpdate_permission_error'));
		}


	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods


	private function sendEmail_onProfilUpdate_response($uid, $topic, $status)
	{
		$this->load->helper('hlp_sancho_helper');
		$email = $uid . "@" . DOMAIN;


		function languageQuery($language)
		{
			return "select index from public.tbl_sprache where sprache = '" + $language + "'";
		}

		$this->ProfilUpdateStatusModel->addSelect(["bezeichnung_mehrsprachig[(" . languageQuery('German') . ")] as status_de", "bezeichnung_mehrsprachig[(" . languageQuery('English') . ")] as status_en"]);
		
		$status_translation = $this->ProfilUpdateStatusModel->loadWhere(["status_kurzbz" => $status]);
		
		if (isError($status_translation)) {
			$this->terminateWithError($this->p->t('profilUpdate', 'ProfilUpdateStatusTranslationError'));
		}

		$status_translation = hasData($status_translation) ? getData($status_translation)[0] : null;

		if (isset($status_translation)) {
			$mail_res = sendSanchoMail("profil_update_response", ['topic' => $topic, 'status_de' => $status_translation->status_de, 'status_en' => $status_translation->status_en, 'href' => APP_ROOT . 'Cis/Profil'], $email, ("Profil Änderung " . $this->p->t('profilUpdate', 'pending')));
			if (!$mail_res) {
				$this->addError($this->p->t('profilUpdate', 'profilUpdate_email_error'));
			}
		}
	}

	private function setStatusOnUpdateRequest($id, $status, $status_message)
	{
		return $this->ProfilUpdateModel->update([$id], ["status" => $status, "status_timestamp" => "NOW()", "status_message" => $status_message]);
	}
	
	private function updateRequestedChange($id, $requested_change)
	{
		return $this->ProfilUpdateModel->update([$id], ['requested_change' => json_encode($requested_change)]);
	}

	private function handleAdresse($requested_change, $personID)
	{
		$this->AdressenTypModel->addSelect(["adressentyp_kurzbz"]);
		$adr_kurzbz = $this->AdressenTypModel->loadWhere(["bezeichnung" => $requested_change['typ']]);
		$adr_kurzbz = $this->getDataOrTerminateWithError($adr_kurzbz)[0]->adressentyp_kurzbz;
		
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
				$this->terminateWithError($this->p->t('profilUpdate', 'profilUpdate_insertAdresse_error'));
			}
			$insert_adresse_id = $this->getDataOrTerminateWithError($insert_adresse_id);
			if ($insert_adresse_id) {
				$this->handleDupplicateZustellAdressen($requested_change['zustelladresse'], $insert_adresse_id);
			}

		}
		//! DELETE
		elseif (array_key_exists('delete', $requested_change) && $requested_change['delete']) {
			$result = $this->AdresseModel->delete($adresse_id);
			if(isError($result)){
				$this->terminateWithError(error($result));
			}
		}
		//! UPDATE
		else {
			$requested_change['updateamum'] = "NOW()";
			$requested_change['updatevon'] = getAuthUID();
			$update_adresse_id = $this->AdresseModel->update($adresse_id, $requested_change);
			if (isError($update_adresse_id)) {
				$this->terminateWithError($this->p->t('profilUpdate', 'profilUpdate_updateAdresse_error'));
			}
			$update_adresse_id = $this->getDataOrTerminateWithError($update_adresse_id);
			$this->handleDupplicateZustellAdressen($requested_change['zustelladresse'], $update_adresse_id);
			
		}
		return $insertID ?? null;
	}


	private function handleDupplicateZustellAdressen($zustellung, $adresse_id)
	{
		if ($zustellung) {
			$this->PersonModel->addSelect("public.tbl_adresse.adresse_id");
			$this->PersonModel->addJoin("public.tbl_adresse", "public.tbl_adresse.person_id = public.tbl_person.person_id");
			$zustellAdressenArray = $this->PersonModel->loadWhere(["public.tbl_person.person_id" => $this->pid, "zustelladresse" => TRUE]);
			if (isError($zustellAdressenArray)) {
				$this->terminateWithError($this->p->t('profilUpdate', 'profilUpdate_loadingZustellAdressen_error'));
			}
			$zustellAdressenArray = $this->getDataOrTerminateWithError($zustellAdressenArray);

			if (count($zustellAdressenArray) > 0) {

				$zustellAdressenArray = array_filter($zustellAdressenArray, function ($adresse) use ($adresse_id) {

					return $adresse->adresse_id != $adresse_id;
				});

				// remove the zustelladresse from all other zustelladressen
				foreach ($zustellAdressenArray as $adresse) {
					$this->AdresseModel->update($adresse->adresse_id, ["zustelladresse" => FALSE]);
				}

			}
		}
	}

}


