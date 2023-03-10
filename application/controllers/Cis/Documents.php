<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

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
			'index' => ['student/anrechnung_beantragen:r','user:r'], // TODO(chris): permissions?
			'Semester' => ['student/anrechnung_beantragen:r','user:r'] // TODO(chris): permissions?
		]);

		$this->loadPhrases([
			'global',
			'tools'
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @param string		$stsem
	 * @return void
	 */
	public function index($stsem = null)
	{
		return $this->Semester($stsem);
	}

	/**
	 * @param string		$stsem
	 * @return void
	 */
	public function Semester($stsem = null)
	{
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
		$this->load->model('crm/Konto_model', 'KontoModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$uid = getAuthUID();
		$studiengaenge = [];

		$stati = $this->PrestudentstatusModel->loadWhereUid($uid, null, true);
		if (isError($stati))
			return $this->view->load('errors/html/error_db.php', [
				'heading' => 'Database Error',
				'message' => getError($stati)
			]);
		$stati = getData($stati);
		if (!$stati)
			return $this->view->load('errors/html/error_general.php', [
				'heading' => 'User ist kein Student',
				'message' => 'Es konnten keine Studiensemester gefunden werden in denen der User als Student inskripiert ist'
			]);

		$stsemArray = [];
		foreach ($stati as $status) {
			if (!isset($studiengaenge[$status->studiengang_kz])) {
				$stg = $this->StudiengangModel->load($status->studiengang_kz);
				if (isError($stg))
					return $this->view->load('errors/html/error_db.php', [
						'heading' => 'Database Error',
						'message' => getError($stg)
					]); 
				$stg = getData($stg);
				if (!$stg)
					return $this->view->load('errors/html/error_db.php', [
						'heading' => 'Database Error',
						'message' => 'No Studiengang found for studiengang_kz ' . $status->studiengang_kz
					]);
				$studiengaenge[$status->studiengang_kz] = current($stg);
			}
			if (!isset($stsemArray[$status->studiensemester_kurzbz]))
				$stsemArray[$status->studiensemester_kurzbz] = [];
			// TODO(chris): maybe just use prestudent_id?
			$stsemArray[$status->studiensemester_kurzbz][] = $status;
		}

		$hasSemester = true;
		$inskriptionsbestaetigungen = [];
		
		if ($stsem && !isset($stsemArray[$stsem])) {
			$hasSemester = false;
		} else {
			if (!$stsem)
				$stsem = end($stsemArray);

			#$stsemArray[$stsem] = array_unique($stsemArray[$stsem]);
			
			$buchungstypen = implode('\',\'', defined("CIS_DOKUMENTE_STUDIENBEITRAG_TYPEN") ? unserialize(CIS_DOKUMENTE_STUDIENBEITRAG_TYPEN) : []);

			foreach ($stsemArray[$stsem] as $status) {
				// NOTE(chris): multiple prestudentstatus for prestudent and semester
				if (isset($inskriptionsbestaetigungen[$status->studiengang_kz]))
					continue;
				
				$inskriptionsbestaetigungen[$status->studiengang_kz] = (boolean) getData($this->KontoModel->checkStudienbeitragFromPrestudent($status->prestudent_id, $stsem, $buchungstypen));
			}
		}

		
		// TODO(chris): in array stsem stsemArray:
		// TODO(chris): - inskriptionsbestaetigung (konto)
		// TODO(chris): - CIS_DOKUMENTE_STUDIENBUCHLBATT_DRUCKEN? studienbuchblatt
		// TODO(chris): - CIS_DOKUMENTE_STUDIENERFOLGSBESTAETIGUNG_DRUCKEN? studienerfolgsbestaetigung
		// TODO(chris): else: msg

		// TODO(chris): CIS_DOKUMENTE_SELFSERVICE?: ...abschlussdokumente (akte)

		$stsemArray = array_keys($stsemArray);
		if (!$hasSemester)
			array_unshift($stsemArray, $stsem);

		$this->load->view('Cis/Documents', [
			'uid' => $uid,
			'stsem' => $stsem,
			'stsemArray' => $stsemArray,
			'hasSemester' => $hasSemester,
			'studiengaenge' => $studiengaenge,
			'inskriptionsbestaetigungen' => $inskriptionsbestaetigungen,
			'studienbuchblatt' => defined('CIS_DOKUMENTE_STUDIENBUCHLBATT_DRUCKEN') && CIS_DOKUMENTE_STUDIENBUCHLBATT_DRUCKEN,
			'studienerfolgsbestaetigung' => defined('CIS_DOKUMENTE_STUDIENERFOLGSBESTAETIGUNG_DRUCKEN') && CIS_DOKUMENTE_STUDIENERFOLGSBESTAETIGUNG_DRUCKEN
		]);
	}

}
