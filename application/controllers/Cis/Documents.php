<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \stdClass as stdClass;

/**
 *
 */
class Documents extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct([
			'index' => [self::PERM_LOGGED],
			'student' => ['admin:r'],
			'download' => [self::PERM_LOGGED]
		]);

		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$this->loadPhrases([
			'global',
			'tools'
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index()
	{
		return $this->showDocuments(getAuthUID());
	}

	/**
	 * @param string		$uid		Administratoren dürfen die UID als Parameter übergeben um die Dokumente von anderen Personen anzuzeigen
	 * @return void
	 */
	public function student($uid)
	{
		return $this->showDocuments($uid);
	}

	/**
	 * @param string		$uid
	 * @return void
	 */
	protected function showDocuments($uid)
	{
		$this->load->model('crm/Konto_model', 'KontoModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$stati = $this->PrestudentstatusModel->loadWhereUid($uid, null, true);
		if (isError($stati))
			return $this->load->view('errors/html/error_db.php', [
				'heading' => 'Database Error',
				'message' => getError($stati)
			]);
		$stati = getData($stati);
		if (!$stati)
			return $this->load->view('errors/html/error_general.php', [
				'heading' => 'User ist kein Student',
				'message' => 'Es konnten keine Studiensemester gefunden werden in denen der User als Student inskripiert ist'
			]);

		$stgs = [];
		$stsemArray = [];
		$buchungstypen = implode('\',\'', defined("CIS_DOKUMENTE_STUDIENBEITRAG_TYPEN") ? unserialize(CIS_DOKUMENTE_STUDIENBEITRAG_TYPEN) : []);
		$person_ids = [];
		foreach ($stati as $status) {
			$person_ids[] = $status->person_id;

			if(!in_array($status->studiensemester_kurzbz, $stsemArray)) {
				$stsemArray[] = $status->studiensemester_kurzbz;
			}
			
			if (!isset($stgs[$status->studiengang_kz])) {
				$stg = $this->StudiengangModel->load($status->studiengang_kz);
				if (isError($stg))
					return $this->load->view('errors/html/error_db.php', [
						'heading' => 'Database Error',
						'message' => getError($stg)
					]);
				$stg = getData($stg);
				if (!$stg)
					return $this->load->view('errors/html/error_db.php', [
						'heading' => 'Database Error',
						'message' => 'No Studiengang found for studiengang_kz ' . $status->studiengang_kz
					]);
				$stgs[$status->studiengang_kz] = current($stg);
				$stgs[$status->studiengang_kz]->studiensemester = [];
			}
			if (!isset($stgs[$status->studiengang_kz]->studiensemester[$status->studiensemester_kurzbz])) {
				$stgs[$status->studiengang_kz]->studiensemester[$status->studiensemester_kurzbz] = new stdClass();
				$stgs[$status->studiengang_kz]->studiensemester[$status->studiensemester_kurzbz]->inskriptionsbestaetigung = (boolean)getData(
					$this->KontoModel->checkStudienbeitragFromPrestudent(
						$status->prestudent_id,
						$status->studiensemester_kurzbz,
						$buchungstypen
					)
				);
			}
		}
		$person_ids = array_unique($person_ids);

		$selfservice = null;
		if (!defined('CIS_DOKUMENTE_SELFSERVICE') || CIS_DOKUMENTE_SELFSERVICE) {
			$this->load->model('crm/Akte_model', 'AkteModel');
			$selfservice = [];
			foreach ($person_ids as $person_id) {
				$result = $this->AkteModel->getArchiv($person_id, null, true);
				if (isError($result))
					return $this->load->view('errors/html/error_db.php', [
						'heading' => 'Database Error',
						'message' => getError($result)
					]);
				$selfservice = array_merge($selfservice, getData($result) ?: []);
			}
		}

		
		$this->load->view('Cis/Documents', [
			'stsemArray' => $stsemArray,
			'stgs' => $stgs,
			'uid' => $uid,
			'studienbuchblatt' => defined('CIS_DOKUMENTE_STUDIENBUCHLBATT_DRUCKEN') && CIS_DOKUMENTE_STUDIENBUCHLBATT_DRUCKEN,
			'studienerfolgsbestaetigung' => defined('CIS_DOKUMENTE_STUDIENERFOLGSBESTAETIGUNG_DRUCKEN') && CIS_DOKUMENTE_STUDIENERFOLGSBESTAETIGUNG_DRUCKEN,
			'selfservice' => $selfservice
		]);
	}

	/**
	 * @param integer		$akte_id
	 * @param string		$uid		(optional) Administratoren dürfen die UID als Parameter übergeben um die Dokumente von anderen Personen anzuzeigen
	 *
	 * @return void
	 */
	public function download($akte_id, $uid = null)
	{
		if (!is_numeric($akte_id))
			return show_404();

		$this->load->model('crm/Akte_model', 'AkteModel');
		$result = $this->AkteModel->load($akte_id);
		if (isError($result))
			return show_error(getError($result));
		$akte = getData($result);
		if (!$akte)
			return show_404();
		$akte = current($akte);

		$admin_access = false;
		if ($uid !== null && $this->permissionlib->isBerechtigt('admin')) {
			$stati = $this->PrestudentstatusModel->loadWhereUid($uid, null, true);
			if (hasData($stati)) {
				$person_ids = array_map(function ($status) {
					return $status->person_id;
				}, getData($stati));
				$person_ids = array_unique($person_ids);
				if (count($person_ids) == 1 && current($person_ids) == $akte->person_id) {
					$admin_access = true;
				}
			}
		}

		if (!$admin_access && ($akte->person_id != getAuthPersonId() || !$akte->stud_selfservice))
			return show_error('Forbidden', 403);

		// NOTE(chris): Log bei einem Download vom Becheid
		if (isset($akte->dokument_kurzbz) && ($akte->dokument_kurzbz === 'Bescheid' || $akte->dokument_kurzbz === 'BescheidEng')) {
			$this->load->model('system/Webservicelog_model', 'WebservicelogModel');
			$this->WebservicelogModel->insert([
				'webservicetyp_kurzbz' => 'content',
				'request_id' => (isset($akte->akte_id) && !empty($akte->akte_id)) ? $akte->akte_id : null,
				'beschreibung' => 'Bescheidbestaetigungsdownload',
				'request_data' => $_SERVER['QUERY_STRING'],
				'execute_time' => date('c'),
				'execute_user' => getAuthUID()
			]);
		}

		$this->output->set_content_type($akte->mimetype);
		$this->output->set_output(base64_decode($akte->inhalt));
	}
}
