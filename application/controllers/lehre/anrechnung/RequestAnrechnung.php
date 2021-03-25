<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class requestAnrechnung extends Auth_Controller
{
	const REQUEST_ANRECHNUNG_URI = '/lehre/anrechnung/RequestAnrechnung';
	const APPROVE_ANRECHNUNG_URI = '/lehre/anrechnung/ApproveAnrechnungUebersicht';
	
	const ANRECHNUNGSTATUS_PROGRESSED_BY_STGL = 'inProgressDP';
	const ANRECHNUNGSTATUS_PROGRESSED_BY_KF = 'inProgressKF';
	const ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR = 'inProgressLektor';
	const ANRECHNUNGSTATUS_APPROVED = 'approved';
	const ANRECHNUNGSTATUS_REJECTED = 'rejected';
	
	const DEADLINE_INTERVAL_NACH_SEMESTERSTART = 'P1M'; // Deadline for application
	
	public function __construct()
	{
		// Set required permissions
		parent::__construct(
			array(
				'index'     => 'student/anrechnung_beantragen:rw',
				'apply'     => 'student/anrechnung_beantragen:rw',
				'download'  => 'student/anrechnung_beantragen:rw',
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
		
		if (isEmptyString($lehrveranstaltung_id) || isEmptyString($studiensemester_kurzbz))
		{
			show_error('Missing correct parameter');
		}
		
		// Exit if user is not a student
		$result = $this->StudentModel->load(array('student_uid' => $this->_uid));
		
		if (!hasData($result))
		{
			show_error('Cant load user');
		}
		
		// Get Prestudent ID
		$prestudent_id = getData($result)[0]->prestudent_id;
		
		// Check if application deadline is expired
		// $is_expired = self::_checkAntragDeadline($studiensemester_kurzbz);
		$is_expired = false;
		
		// Get Anrechung data
		$anrechnungData = $this->anrechnunglib->getAnrechnungDataByLv($lehrveranstaltung_id, $studiensemester_kurzbz, $prestudent_id);

		// Get Antrag data
		$antragData = $this->anrechnunglib->getAntragData($this->_uid, $studiensemester_kurzbz, $lehrveranstaltung_id);
		
		$viewData = array(
			'antragData' => $antragData,
			'anrechnungData' => $anrechnungData,
			'is_expired' => $is_expired
		);
		
		$this->load->view('lehre/anrechnung/requestAnrechnung.php', $viewData);
	}
	
	/**
	 * Apply Anrechnungsantrag and send to STGL
	 */
	public function apply()
	{
		$anmerkung = $this->input->post('anmerkung');
		$begruendung_id = $this->input->post('begruendung');
		$lehrveranstaltung_id = $this->input->post('lv_id');
		$studiensemester_kurzbz = $this->input->post('studiensemester');

		// Validate data
		if (empty($_FILES['uploadfile']['name']))
		{
			show_error('Missing upload file');
		}

		if (isEmptyString($begruendung_id) || isEmptyString($lehrveranstaltung_id) || isEmptyString($studiensemester_kurzbz))
		{
			show_error('Missing correct parameter');
		}
		
		// Exit if user is not a student
		$result = $this->StudentModel->load(array('student_uid' => $this->_uid));
		
		if (!hasData($result))
		{
			return $this->outputJsonError('Cant load user');
		}
		
		// Get Prestudent ID
		$prestudent_id = getData($result)[0]->prestudent_id;
		
		// Exit if application already exists
		if (self::_applicationExists($lehrveranstaltung_id, $studiensemester_kurzbz, $prestudent_id))
		{
			return $this->outputJsonError($this->p->t('anrechnung', 'antragBereitsGestellt'));
		}
		
		// Exit if application is not for actual studysemester
		if (!self::_applicationIsForActualSS($studiensemester_kurzbz))
		{
			return $this->outputJsonError($this->p->t('anrechnung', 'antragNurImAktSS'));
		}
		
		// Upload document
		$lastInsert_dms_id = self::_uploadFile();
		
		// Start DB transaction
		$this->db->trans_start(false);
		
		// Save Anrechnung
		$result = $this->AnrechnungModel->insert(array(
			'prestudent_id' => $prestudent_id,
			'lehrveranstaltung_id' => $lehrveranstaltung_id,
			'begruendung_id' => $begruendung_id,
			'dms_id' => $lastInsert_dms_id,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'anmerkung_student' => $anmerkung,
			'insertvon' => $this->_uid
		));
		
		// Save Anrechnungstatus 'inProgressSTGL'
		$this->AnrechnungModel->saveAnrechnungstatus($result->retval, self::ANRECHNUNGSTATUS_PROGRESSED_BY_STGL);
		
		// Transaction complete
		$this->db->trans_complete();

		if ($this->db->trans_status() === false)
		{
			$this->db->trans_rollback();
			show_error('Failed inserting Anrechnung', EXIT_ERROR);
		}
		
		// Output to AJAX
		return $this->outputJsonSuccess(array(
			'antragdatum' => (new DateTime())->format('d.m.Y')
		));
	}

	/**
	 * Download and open uploaded document (Nachweisdokument).
	 */
	public function download()
	{
		$dms_id = $this->input->get('dms_id');

		if (!is_numeric($dms_id))
		{
			show_error('Wrong parameter');
		}

		// Check if user is entitled to read dms doc
		self::_checkIfEntitledToReadDMSDoc($dms_id);

		$this->dmslib->download($dms_id);
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
	 * Check if application deadline is expired.
	 *
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
	 * Check if user is entitled to read dms doc.
	 *
	 * @param $dms_id
	 */
	private function _checkIfEntitledToReadDMSDoc($dms_id)
	{
		if (!$student = getData($this->StudentModel->load(array('student_uid' => $this->_uid)))[0])
		{
			show_error('Failed loading Student');
		}
		
		$result = $this->AnrechnungModel->loadWhere(array('dms_id' => $dms_id));
		
		if($result = getData($result)[0])
		{
			if ($result->prestudent_id == $student->prestudent_id)
			{
				return;
			}
		}
		
		show_error('You are not entitled to read this document');
	}
	
	/**
	 * Check if application already exists.
	 *
	 * @param $lehrveranstaltung_id
	 * @param $studiensemester_kurzbz
	 * @param $prestudent_id
	 * @return bool
	 */
	private function _applicationExists($lehrveranstaltung_id, $studiensemester_kurzbz, $prestudent_id)
	{
		$result = $this->AnrechnungModel->loadWhere(array(
			'lehrveranstaltung_id' => $lehrveranstaltung_id,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'prestudent_id' => $prestudent_id
		));
		
		if (isError($result))
		{
			show_error(getError($result));
		}
		
		return hasData($result);
	}
	
	/**
	 * Check if applications' study semester is actual study semester.
	 *
	 * @param $studiensemester_kurzbz
	 * @return bool
	 */
	private function _applicationIsForActualSS($studiensemester_kurzbz)
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$result = $this->StudiensemesterModel->getNearest();
		$actual_ss = getData($result)[0]->studiensemester_kurzbz;
		
		return $studiensemester_kurzbz == $actual_ss;
	}
	
	private function _uploadFile(){
		
		if (empty($_FILES['uploadfile']['name']))
		{
			show_error('Missing upload file');
		}
		
		// Upload document
		$dms = array(
			'kategorie_kurzbz'  => 'anrechnung',
			'version'           => 0,
			'name'              => $_FILES['uploadfile']['name'],
			'mimetype'          => $_FILES['uploadfile']['type'],
			'insertamum'        => (new DateTime())->format('Y-m-d H:i:s'),
			'insertvon'         => $this->_uid
		);
		
		if (isError($uploaddata = $this->dmslib->upload($dms, array('pdf'))))
		{
			show_error(getError($uploaddata));
		}
		
		return $uploaddata->retval['dms_id'];
	}
}