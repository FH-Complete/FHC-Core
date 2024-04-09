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

		// Load configs
		$this->load->config('anrechnung');
		
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
		$is_expired = $this->_isExpired($studiensemester_kurzbz);
		
		// Check if Lehrveranstaltung was already graded with application blocking grades
		$is_blocked = self::_LVhasBlockingGrades($studiensemester_kurzbz, $lehrveranstaltung_id);
		
		// Get Anrechung data
		$anrechnungData = $this->anrechnunglib->getAnrechnungDataByLv($lehrveranstaltung_id, $studiensemester_kurzbz, $prestudent_id);

		// Get Antrag data
		$antragData = $this->anrechnunglib->getAntragData($prestudent_id, $studiensemester_kurzbz, $lehrveranstaltung_id, $anrechnungData->anrechnung_id);
		
		$viewData = array(
			'antragData' => $antragData,
			'anrechnungData' => $anrechnungData,
			'is_expired' => $is_expired,
			'is_blocked' => $is_blocked
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
		$bestaetigung = $this->input->post('bestaetigung');
		$begruendung_ects = $this->input->post('begruendung_ects');
		$begruendung_lvinhalt = $this->input->post('begruendung_lvinhalt');

		// Validate data
		if (empty($_FILES['uploadfile']['name']))
		{
			return $this->outputJsonError($this->p->t('ui', 'errorUploadFehltOderZuGross'));
		}

		if (isEmptyString($begruendung_id) ||
			isEmptyString($anmerkung) ||
			isEmptyString($lehrveranstaltung_id) ||
			isEmptyString($studiensemester_kurzbz) ||
			isEmptyString($begruendung_ects) ||
			isEmptyString($begruendung_lvinhalt))
		{
			return $this->outputJsonError($this->p->t('ui', 'errorFelderFehlen'));
		}
		
		if (isEmptyString($bestaetigung))
		{
			return $this->outputJsonError($this->p->t('ui', 'errorBestaetigungFehlt'));
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
		
		// Exit if application is a past ( < actual ) studysemester
		if (self::_applicationIsPastSS($studiensemester_kurzbz))
		{
			return $this->outputJsonError($this->p->t('anrechnung', 'antragNichtFuerVerganganeSS'));
		}
		
		// Upload document
		$result = self::_uploadFile();

		if (isError($result))
		{
			return $this->outputJsonError($result->retval);
		}
		
		// Hold just inserted DMS ID
		$lastInsert_dms_id = $result->retval['dms_id'];
		
		// Save Anrechnung and Anrechnungstatus
		$result = $this->AnrechnungModel->createAnrechnungsantrag(
			$prestudent_id,
			$studiensemester_kurzbz,
			$lehrveranstaltung_id,
			$begruendung_id,
			$lastInsert_dms_id,
			$anmerkung,
            $begruendung_ects,
            $begruendung_lvinhalt
		);
		
		if (isError($result))
		{
			$this->terminateWithJsonError(getError($result));
		}
		
		// Output to AJAX
		$this->outputJsonSuccess(array(
			'antragdatum' => (new DateTime())->format('d.m.Y'),
			'dms_id' => $lastInsert_dms_id,
			'filename' => $_FILES['uploadfile']['name']
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
		$this->_checkIfEntitledToReadDMSDoc($dms_id);

		// Get file to be downloaded from DMS
        $download = $this->dmslib->download($dms_id);
        if (isError($download)) return $download;

        // Download file
        $this->outputFile(getData($download));
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
	 * @param $start Start date for application submission.
	 * @param $ende End date for application submission.
	 * @param $studiensemester_kurzbz
	 * @return bool True if deadline is expired
	 * @throws Exception
	 */
	private function _isExpired($studiensemester_kurzbz)
	{
        $today = new DateTime('today midnight');

        // Load all Anrechnungszeitfenster for this Studiensemester
        $this->load->model('education/Anrechnungszeitraum_model', 'AnrechnungszeitraumModel');
        $result = $this->AnrechnungszeitraumModel->loadWhere(array('studiensemester_kurzbz' => $studiensemester_kurzbz));

        if (hasData($result))
        {
            // Loop through Anrechnungszeitfenster
            foreach (getData($result) as $azrObj)
            {
                $start = new DateTime($azrObj->anrechnungstart);
                $ende = new DateTime($azrObj->anrechnungende);

                // Return false if today is at least within one Anrechnungszeitraum
                if (($today >= $start && $today <= $ende)) return false;

            }
        }

        // Return true if today is in none Anrechnungszeitraum
        return true;
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
	 * Check if applications' study semester is < actual study semester.
	 *
	 * @param $studiensemester_kurzbz
	 * @return bool
	 */
	private function _applicationIsPastSS($studiensemester_kurzbz)
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$result = $this->StudiensemesterModel->getNearest();
		$actual_ss = getData($result)[0];

		$result = $this->StudiensemesterModel->load($studiensemester_kurzbz);
		$anrechnung_ss = getData($result)[0];

		return $anrechnung_ss->ende < $actual_ss->start;
	}

	private function _LVhasBlockingGrades($studiensemester_kurzbz, $lehrveranstaltung_id)
	{
		// Get Note of Lehrveranstaltung
		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');
		$result = $this->ZeugnisnoteModel->load(array(
				'student_uid' => $this->_uid,
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'lehrveranstaltung_id' => $lehrveranstaltung_id
			)
		);

		// If Lehrveranstaltung has Note
		if (hasData($result))
		{
			$note = getData($result)[0]->note;

			// Check if Note is a blocking grade
			if (in_array($note, $this->config->item('grades_blocking_application')))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Upload file via DMS library.
	 *
	 * @return mixed
	 * @throws Exception
	 */
	private function _uploadFile()
	{
		$dms = array(
			'kategorie_kurzbz'  => 'anrechnung',
			'version'           => 0,
			'name'              => $_FILES['uploadfile']['name'],
			'mimetype'          => $_FILES['uploadfile']['type'],
			'insertamum'        => (new DateTime())->format('Y-m-d H:i:s'),
			'insertvon'         => $this->_uid
		);

		// Upload document
		return $this->dmslib->upload($dms, 'uploadfile', array('pdf'));
	}
}
