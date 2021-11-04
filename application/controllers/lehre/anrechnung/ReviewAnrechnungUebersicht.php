<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class reviewAnrechnungUebersicht extends Auth_Controller
{
	const BERECHTIGUNG_ANRECHNUNG_EMPFEHLEN = 'lehre/anrechnung_empfehlen';
	
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
				'index'     => 'lehre/anrechnung_empfehlen:rw',
				'download'  => 'lehre/anrechnung_empfehlen:rw',
				'recommend'   => 'lehre/anrechnung_empfehlen:rw',
				'dontRecommend'   => 'lehre/anrechnung_empfehlen:rw'
			)
		);
		
		// Load models
		$this->load->model('education/Anrechnung_model', 'AnrechnungModel');
		$this->load->model('education/Anrechnungstatus_model', 'AnrechnungstatusModel');
		$this->load->model('content/DmsVersion_model', 'DmsVersionModel');
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		
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
				'lehre',
				'table'
			)
		);
		
		$this->_setAuthUID();
		
		$this->setControllerId();
	}
	
	public function index()
	{
		// Get study semester
		$studiensemester_kurzbz = $this->input->get('studiensemester');

		if (isEmptyString($studiensemester_kurzbz))
		{
			$result = $this->StudiensemesterModel->getNearest();
			$studiensemester_kurzbz = getData($result)[0]->studiensemester_kurzbz;
		}
		
		$viewData = array(
			'studiensemester_selected' => $studiensemester_kurzbz
		);
		
		$this->load->view('lehre/anrechnung/reviewAnrechnungUebersicht.php', $viewData);
	}
	
	/**
	 * Recommend Anrechnungen.
	 */
	public function recommend()
	{
		$data = $this->input->post('data');

		if(isEmptyArray($data))
		{
			return $this->outputJsonError('Fehler beim Übertragen der Daten.');
		}

		foreach ($data as $item)
		{
			// Approve Anrechnung
			if($this->anrechnunglib->recommendAnrechnung($item['anrechnung_id']))
			{
				$json[]= array(
					'anrechnung_id'         => $item['anrechnung_id'],
					'empfehlung_anrechnung' => 'true',
					'status_kurzbz'         => self::ANRECHNUNGSTATUS_PROGRESSED_BY_STGL,
					'status_bezeichnung'    => $this->anrechnunglib->getStatusbezeichnung(self::ANRECHNUNGSTATUS_PROGRESSED_BY_STGL)
				);
			}
		}
		
		// Output json to ajax
		if (isset($json) && !isEmptyArray($json))
		{
			/**
			 * Send mails to STGL (if not present STGL, send to STGL assistance)
			 * NOTE: mails are sent at the end to ensure sending only one mail to each STGL
			 * */
			if (!$this->_sendSanchoMails($json, true))
			{
				show_error('Failed sending emails');
			}

			return $this->outputJsonSuccess($json);
		}
		else
		{
			return $this->outputJsonError($this->p->t('ui', 'errorNichtAusgefuehrt'));
		}
	}
	
	/**
	 * Dont recommend Anrechnungen.
	 */
	public function dontRecommend()
	{
		$data = $this->input->post('data');
		
		if(isEmptyArray($data))
		{
			return $this->outputJsonError('Fehler beim Übertragen der Daten.');
		}
		
		foreach ($data as $item)
		{
			// Approve Anrechnung
			if($this->anrechnunglib->dontRecommendAnrechnung($item['anrechnung_id'], $item['begruendung']))
			{
				$json[]= array(
					'anrechnung_id'         => $item['anrechnung_id'],
					'empfehlung_anrechnung' => 'false',
					'status_kurzbz'         => self::ANRECHNUNGSTATUS_PROGRESSED_BY_STGL,
					'status_bezeichnung'    => $this->anrechnunglib->getStatusbezeichnung(self::ANRECHNUNGSTATUS_PROGRESSED_BY_STGL)
				);
			}
		}
		
		// Output json to ajax
		if (isset($json) && !isEmptyArray($json))
		{
			// Send mails to STGL (if not present STGL, send to STGL assistance)
			if (!$this->_sendSanchoMails($json, false))
			{
				show_error('Failed sending emails');
			}
			
			return $this->outputJsonSuccess($json);
		}
		else
		{
			return $this->outputJsonError($this->p->t('ui', 'errorNichtAusgefuehrt'));
		}
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
		
		// Set filename to be used on downlaod
		$filename = $this->anrechnunglib->setFilenameOnDownload($dms_id);
		
		// Download file
		$this->dmslib->download($dms_id, $filename);
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
	 * Check if user is entitled to read dms doc
	 * @param $dms_id
	 */
	private function _checkIfEntitledToReadDMSDoc($dms_id)
	{
		$result = $this->AnrechnungModel->loadWhere(array('dms_id' => $dms_id));
		
		if(!$result = getData($result)[0])
		{
			show_error('Failed retrieving Anrechnung');
		}
		
		$result = $this->LehrveranstaltungModel
			->getLecturersByLv($result->studiensemester_kurzbz, $result->lehrveranstaltung_id);
		
		if($result = getData($result))
		{
			$entitled_lector_arr = array_column($result, 'uid');

			if (in_array($this->_uid, $entitled_lector_arr))
			{
				return;
			}
		}
		
		show_error('You are not entitled to read this document');
	}
	
	/**
	 * Send mails to STGL (if not present then to STGL assistance)
	 * @param $mail_params
	 * @param $empfehlung
	 * @return bool
	 */
	private function _sendSanchoMails($mail_params, $empfehlung)
	{
		// Get studiengaenge
		$studiengang_kz_arr = array();
		
		foreach ($mail_params as $item)
		{
			$this->AnrechnungModel->addSelect('studiengang_kz');
			$this->AnrechnungModel->addJoin('public.tbl_prestudent', 'prestudent_id');
			
			$studiengang_kz_arr[]= $this->AnrechnungModel->load($item['anrechnung_id'])->retval[0]->studiengang_kz;
		}
		
		$studiengang_kz_arr = array_unique($studiengang_kz_arr);
		
		// Send mail to STGL of each studiengang
		foreach ($studiengang_kz_arr as $studiengang_kz)
		{
			// Get STGL mail address, if available, otherwise get assistance mail address
			list ($to, $vorname) = $this->_getSTGLMailAddress($studiengang_kz);
			
			// Get full name of lector
			$this->load->model('person/Person_model', 'PersonModel');
			if (!$lector_name = getData($this->PersonModel->getFullName($this->_uid)))
			{
				show_error ('Failed retrieving person');
			}
			
			// Link to Antrag genehmigen
			$url =
				CIS_ROOT. 'cis/index.php?menu='.
				CIS_ROOT. 'cis/menu.php?content_id=&content='.
				CIS_ROOT. index_page(). self::APPROVE_ANRECHNUNG_URI;
			
			// Prepare mail content
			$body_fields = array(
				'vorname'                       => $vorname,
				'lektor_name'                   => $lector_name,
				'empfehlung'                    => $empfehlung ? 'positive' : 'negative',
				'link'		                    => anchor($url, 'Anrechnungsanträge Übersicht')
			);
			
			sendSanchoMail(
				'AnrechnungEmpfehlungAbgeben',
				$body_fields,
				$to,
				'Anerkennung nachgewiesener Kenntnisse: Empfehlung wurde abgegeben'
			);
		}
		
		return true;
	}
	
	// Get STGL mail address, if available, otherwise get assistance mail address
	private function _getSTGLMailAddress($stg_kz)
	{
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$result = $this->StudiengangModel->getLeitung($stg_kz);
		
		// Get STGL mail address, if available
		if (hasData($result))
		{
			return array(
				$result->retval[0]->uid. '@'. DOMAIN,
				$result->retval[0]->vorname
			);
		}
		// ...otherwise get assistance mail address
		else
		{
			$result = $this->StudiengangModel->load($stg_kz);
			
			if (hasData($result))
			{
				return array(
					$result->retval[0]->email,
					''
				);
			}
		}
	}
	
}