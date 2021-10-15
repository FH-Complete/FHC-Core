<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class CreateAnrechnung extends Auth_Controller
{
	const BERECHTIGUNG_ANRECHNUNG_ANLEGEN = 'lehre/anrechnung_anlegen';
	
	const ANRECHNUNGSTATUS_PROGRESSED_BY_STGL = 'inProgressDP';
	
	public function __construct()
	{
		// Set required permissions
		parent::__construct(
			array(
				'index'     => 'lehre/anrechnung_anlegen:r',
				'getLVsByStudent' => 'lehre/anrechnung_anlegen:r',
				'create' => 'lehre/anrechnung_anlegen:rw'
			)
		);
		
		// Load models
		$this->load->model('education/Anrechnung_model', 'AnrechnungModel');
		$this->load->model('education/Anrechnungstatus_model', 'AnrechnungstatusModel');
		$this->load->model('content/DmsVersion_model', 'DmsVersionModel');
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		
		// Load libraries
		$this->load->library('WidgetLib');
		$this->load->library('PermissionLib');
		$this->load->library('AnrechnungLib');
		$this->load->library('DmsLib');
		
		// Load helpers
		$this->load->helper('form');
		$this->load->helper('url');
		
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
		
		// Load configs
		$this->load->config('anrechnung');
		
		$this->_setAuthUID();
		
		$this->setControllerId();
	}
	
	public function index()
	{
		// Get Studiensemester
		$studiensemester_kurzbz = $this->input->get('studiensemester');
		
		// If no Studiensemester is given
		if (isEmptyString($studiensemester_kurzbz))
		{
			//...use the nearest Studiensemester
			$result = $this->StudiensemesterModel->getNearest();
			$studiensemester_kurzbz = getData($result)[0]->studiensemester_kurzbz;
		}
		
		// Get Studiengaenge the user is entitled for
		if (!$studiengang_kz_arr = $this->permissionlib->getSTG_isEntitledFor(self::BERECHTIGUNG_ANRECHNUNG_ANLEGEN))
		{
			show_error('Failed retrieving Studiengaenge');
		}
		
		// Get Anrechnungsbegruendungen
		$this->load->model('education/Anrechnungbegruendung_model', 'AnrechnungbegruendungModel');
		$begruendung_arr = getData($this->AnrechnungbegruendungModel->load());
		
		$viewData = array(
			'studiensemester_selected' => $studiensemester_kurzbz,
			'studiengaenge_entitled' => $studiengang_kz_arr,
			'begruendungen' => $begruendung_arr
		);
		
		$this->load->view('lehre/anrechnung/createAnrechnung.php', $viewData);
	}
	
	/**
	 * Get Lehrveranstaltungen from Student.
	 */
	public function getLVsByStudent()
	{
		$prestudent_id = $this->input->post('prestudent_id');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		
		// Get Student UID
		$student_uid = $this->StudentModel->getUID($prestudent_id);
		
		// Retrieve Lehrveranstaltungen from student
		$result = $this->LehrveranstaltungModel->getLvsByStudent($student_uid, $studiensemester_kurzbz);
		
		// Exit, if student has no Lehrveranstaltungen
		if (!hasData($result))
		{
			$this->terminateWithJsonError($this->p->t('ui', 'keineLVzugeteilt'));
		}
		
		// Success response to AJAX
		$this->outputJsonSuccess(getData($result));
	}
	
	/**
	 * Create Anrechnungsantrag.
	 *
	 * Saves Anrechnung and Anrechnungstatus.
	 * Also saves Nachweisdokument to DMS.
	 */
	public function create()
	{
		$prestudent_id = $this->input->post('prestudent_id');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		$lehrveranstaltung_id = $this->input->post('lehrveranstaltung_id');
		$begruendung_id = $this->input->post('begruendung_id');
		$herkunftKenntnisse = $this->input->post('herkunftKenntnisse');
		
		// Validate upload file
		if (empty($_FILES['uploadfile']['name']))
		{
			$this->terminateWithJsonError($this->p->t('ui', 'errorUploadFehlt'));
		}
		
		// Validate required data
		if (isEmptyString($begruendung_id) || isEmptyString($lehrveranstaltung_id))
		{
			$this->terminateWithJsonError($this->p->t('ui', 'errorFelderFehlen'));
		}
		
		// Exit if application already exists
		if (self::_applicationExists($lehrveranstaltung_id, $studiensemester_kurzbz, $prestudent_id))
		{
			$this->terminateWithJsonError($this->p->t('global', 'antragBereitsGestellt'));
		}
		
		// Exit if Lehrveranstaltung was already graded with application blocking grades
		if (self::_LVhasBlockingGrades($studiensemester_kurzbz, $lehrveranstaltung_id, $prestudent_id))
		{
			$this->terminateWithJsonError($this->p->t('anrechnung', 'antragBenotungBlockiert'));
		}
	
		// Upload document
		$result = self::_uploadFile();
		
		if (isError($result))
		{
			$this->terminateWithJsonError($result->retval);
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
			$herkunftKenntnisse
		);
		
		if (isError($result))
		{
			$this->terminateWithJsonError(getError($result));
		}
		
		$lastInsert_anrechnung_id = getData($result);
		
		// Success response to AJAX
		$this->outputJsonSuccess(array(
			'anrechnung_id' => $lastInsert_anrechnung_id,
			'msg' => $this->p->t('global', 'antragWurdeAngelegt')
		));
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
	
	private function _LVhasBlockingGrades($studiensemester_kurzbz, $lehrveranstaltung_id, $prestudent_id)
	{
		// Get Student UID
		$student_uid = $this->StudentModel->getUID($prestudent_id);
		
		// Get Note of Lehrveranstaltung
		$this->load->model('education/Lvgesamtnote_model', 'LvgesamtnoteModel');
		$result = $this->LvgesamtnoteModel->load(array(
				'student_uid' => $student_uid,
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
	
}