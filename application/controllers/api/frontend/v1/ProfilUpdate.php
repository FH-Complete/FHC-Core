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
			
		]);

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

	/* public function insertFile($replace)
	{
		$replace = json_decode($replace);

		if (!count($_FILES)) {
			$this->terminateWithSuccess([]);
			return;
		}

		//? if replace is set it contains the profil_update_id in which the attachment_id has to be replaced
		if (isset($replace)) {
			$this->ProfilUpdateModel->addSelect(["attachment_id"]);
			$profilUpdate = $this->ProfilUpdateModel->load([$replace]);
			if (isError($profilUpdate)) {
				$this->terminateWithError(error($this->p->t('profilUpdate', 'profilUpdate_loading_error')),self::ERROR_TYPE_GENERAL);
			}
			//? get the attachmentID
			$dms_id = $this->getDataOrTerminateWithError($profilUpdate)[0];

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

			$tmp_res = $this->getDataOrTerminateWithError($tmp_res);
			array_push($res, $tmp_res);
		}

		$this->terminateWithSuccess($res);
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
		$dmsVersions = $this->getDataOrTerminateWithError($dmsVersions);
		if (isset($dmsVersions)) {
			$zwischen_res = array_map(function ($item) {
				return $item->version;
			}, $dmsVersions);
			foreach ($zwischen_res as $version) {
				array_push($res, $this->DmsVersionModel->delete([$dms_id, $version]));
			}
		} else {
			$this->terminateWithError(error($this->p->t('profilUpdate', 'profilUpdate_dmsVersion_error')), self::ERROR_TYPE_GENERAL);
		}

		//? returns a result for each deleted dms_file
		return $res;
	}
 */
}
	
   