<?php

//if (! defined('BASEPATH')) exit('No direct script access allowed');

class requestAnrechnung extends Auth_Controller
{
	const REQUEST_ANRECHNUNG_URI = '/lehre/anrechnung/RequestAnrechnung';
	
	const ANRECHNUNGSTATUS_PROGRESSED_BY_STGL = 'inProgressDP';
	const ANRECHNUNGSTATUS_PROGRESSED_BY_KF = 'inProgressKF';
	const ANRECHNUNGSTATUS_APPROVED = 'approved';
	const ANRECHNUNGSTATUS_REJECTED = 'rejected';
	
	const DEADLINE_INTERVAL_NACH_SEMESTERSTART = 'P1M'; // Deadline for application
	
	public function __construct()
	{
		// Set required permissions
		parent::__construct(
			array(
				'index' => 'admin:rw',
				'uploadFile' => 'admin:rw',
			)
		);
		
		// Load models
		$this->load->model('education/Anrechnung_model', 'AnrechnungModel');
		$this->load->model('content/DmsVersion_model', 'DmsVersionModel');
		
		// Load libraries
		$this->load->library('WidgetLib');
		$this->load->library('PermissionLib');
		$this->load->library('AnrechnungLib');
		$this->load->library('DmsLib');
		
		// Load helpers
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->helper('hlp_sancho_helper');
		
		// Load language phrases
		$this->loadPhrases(
			array(
				'global',
				'ui',
				'anrechnung',
				'person',
				'lehre'
			)
		);
		
		$this->_setAuthUID();
		
		$this->setControllerId();
	}
	
	public function index()
	{
		$studiensemester_kurzbz = $this->input->get('studiensemester');
		$lehrveranstaltung_id = $this->input->get('lv_id');
		
		if (!is_numeric($lehrveranstaltung_id) || !is_string($studiensemester_kurzbz))
		{
			show_error('Missing correct parameter');
		}
		
		// Check if application deadline is expired
		$is_expired = $this->_checkAntragDeadline($studiensemester_kurzbz);
		
		// Get Anrechung data
		$result = $this->anrechnunglib->getAnrechnungData($lehrveranstaltung_id);
		if (!$anrechnungData = getData($result))
		{
			show_error(getError($anrechnungData));
		}
		
		// Overwrite progress status for student view. If no Anrechnung exists yet, set to new.
		$anrechnungData->status = empty($anrechnungData->status) 
			? getUserLanguage() == 'German' ? 'neu' : 'new'
			: $this->_getLastAnrechnungstatus($anrechnungData->anrechnung_id);
		
		$viewData = array(
			'antragData' => $this->anrechnunglib->getAntragData($this->_uid, $studiensemester_kurzbz, $lehrveranstaltung_id),
			'anrechnungData' => $anrechnungData,
			'is_expired' => $is_expired,
			'disabled' => $is_expired && empty($anrechnungData->anrechnung_id) || !empty($anrechnungData->anrechnung_id)
				? 'disabled'
				: ''
		);
		
		$this->load->view('lehre/anrechnung/requestAnrechnung.php', $viewData);
	}
	
	public function apply()
	{
		$anmerkung = $this->input->post('anmerkung');
		$begruendung_id = $this->input->post('begruendung');
		$lehrveranstaltung_id = $this->input->post('lehrveranstaltung_id');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');

		if (empty($_FILES['uploadfile']['name']))
		{
			show_error('Missing upload file');
		}
		
		if (!is_numeric($begruendung_id) || !is_numeric($lehrveranstaltung_id) || !is_string($studiensemester_kurzbz))
		{
			show_error('Missing correct parameter');
		}
		
		$result = $this->_getAnrechnung($lehrveranstaltung_id);
		if (hasData($result))
		{
			show_error('Der Antrag wurde bereits gestellt');
		}
		
		// Start DB transaction
		$this->db->trans_start(false);
		
		// Upload document
		$dms = array(
			'kategorie_kurzbz'  => 'anrechnung',
			'version'           => 0,
			'name'              => $_FILES['uploadfile']['name'],
			'mimetype'          => $_FILES['uploadfile']['type'],
			'insertamum'        => (new DateTime())->format('Y-m-d H:i:s'),
			'insertvon'         => $this->_uid
		);

		if(isError($uploaddata = $this->dmslib->upload($dms, array('jpg', 'pdf'))))
		{
			show_error(getError($uploaddata));
		}

		// Get PrestudentID
		$result = $this->_loadPrestudent($this->_uid, $studiensemester_kurzbz);
		
		if (!$prestudent = getData($result)[0])
		{
			show_error('Failed retrieving prestudent');
		}

		// Save Anrechnung with Status 'inProgressSTGL'
		$result = $this->AnrechnungModel->insert(array(
			'prestudent_id' => $prestudent->prestudent_id,
			'lehrveranstaltung_id' => $lehrveranstaltung_id,
			'begruendung_id' => $begruendung_id,
			'dms_id' => $uploaddata->retval['dms_id'],
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'anmerkung_student' => $anmerkung
		));
		
		if (isError($result))
		{
			show_error('Failed inserting Anrechnung');
		}
		
		$result = $this->AnrechnungModel->saveAnrechnungstatus($result->retval, self::ANRECHNUNGSTATUS_PROGRESSED_BY_STGL);
		
		if (isError($result))
		{
			show_error('Failed saving Anrechnungstatus');
		}
		
		// Transaction complete!
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === false || isError($result))
		{
			$this->db->trans_rollback();
			show_error($result->msg, EXIT_ERROR);
		}
		
		// Send mail to STGL
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$studiengang = $this->StudiengangModel->load($prestudent->studiengang_kz);
		
		// Send mail
		$this->load->library('MailLib');
		if(!$this->maillib->send(
			"noreply@". DOMAIN,
			$studiengang->retval[0]->email,
			'Neuer LV-Anrechnungsantrag',
			'Eine neuer LV Anrechnungsantrag steht zur Prüfung bereit.'))
		{
			show_error('Failed sending email to STGL');
		}
		else
		{
			redirect(site_url(). self::REQUEST_ANRECHNUNG_URI. '?studiensemester='. $studiensemester_kurzbz. '&lv_id='. $lehrveranstaltung_id);
		}
	}
	
	
	/**
	 * Retrieve the UID of the logged user and checks if it is valid
	 */
	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();
		
		if (!$this->_uid) show_error('User authentification failed');
	}
	
	/**
	 * Load Prestudent by uid and Studiensemester.
	 * @param $uid
	 * @param $studiensemester_kurzbz
	 * @return mixed
	 */
	private function _loadPrestudent($uid, $studiensemester_kurzbz)
	{
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
		$this->load->model('crm/Student_model', 'StudentModel');
		
		$this->PrestudentstatusModel->addJoin('public.tbl_student', 'prestudent_id');
		return $this->PrestudentstatusModel->loadWhere(array(
				'student_uid' => $uid,
				'studiensemester_kurzbz' => $studiensemester_kurzbz
			)
		);
	}
	
	/**
	 * Check if application deadline is expired.
	 * @param $studiensemester_kurzbz
	 * @return bool True if semester start is more then 1 week ago
	 * @throws Exception
	 */
	private function _checkAntragDeadline($studiensemester_kurzbz)
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->StudiensemesterModel->addSelect('start');
		if (!$start = getData($this->StudiensemesterModel->load($studiensemester_kurzbz)))
		{
			show_error(getError($start));
		}

		$start = new DateTime($start[0]->start);
		$today = new DateTime('today midnight');
		
		// True if today > application deadline
		return ($today > $start->add((new DateInterval(self::DEADLINE_INTERVAL_NACH_SEMESTERSTART))));
	}
	
	/**
	 * Get Anrechnung by Lehrveranstaltung
	 * @param $lehrveranstaltung_id
	 * @return mixed
	 */
	private function _getAnrechnung($lehrveranstaltung_id)
	{
		$result = $this->AnrechnungModel->loadWhere(array(
			'lehrveranstaltung_id' => $lehrveranstaltung_id
		));
		
		if (isError($result))
		{
			show_error(getError($result));
		}
		
		return $result;
	}
	
	/**
	 * Get last Anrechnungstatus. Modify progress status for student view.
	 * @param $anrechnung_id
	 * @return string
	 */
	private function _getLastAnrechnungstatus($anrechnung_id)
	{
		$result = $this->AnrechnungModel->getLastAnrechnungstatus($anrechnung_id);
		$status_kurzbz = getData($result)[0]->status_kurzbz;
		
		// Dont show who is progressing the application
		if ($status_kurzbz == 'inProgressDP' || $status_kurzbz == 'inProgressKF')
		{
			return getUserLanguage() == 'German' ? 'in Bearbeitung' : 'in process';
		}
		else
		{
			$status_mehrsprachig = getData($result)[0]->bezeichnung_mehrsprachig;
			return getUserLanguage() == 'German' ? $status_mehrsprachig[0] : $status_mehrsprachig[1];
		}
	}
}