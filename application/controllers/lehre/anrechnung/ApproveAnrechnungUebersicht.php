<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class approveAnrechnungUebersicht extends Auth_Controller
{
	const BERECHTIGUNG_ANRECHNUNG_GENEHMIGEN = 'lehre/anrechnung_genehmigen';
	
	const REVIEW_ANRECHNUNG_URI = '/lehre/anrechnung/ReviewAnrechnungUebersicht';
	
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
				'index'     => 'lehre/anrechnung_genehmigen:rw',
				'download'  => 'lehre/anrechnung_genehmigen:rw',
				'approve'   => 'lehre/anrechnung_genehmigen:rw',
				'reject'    => 'lehre/anrechnung_genehmigen:rw',
				'requestRecommendation' => 'lehre/anrechnung_genehmigen:rw'
			)
		);
		
		// Load models
		$this->load->model('education/Anrechnung_model', 'AnrechnungModel');
		$this->load->model('education/Anrechnungstatus_model', 'AnrechnungstatusModel');
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
		
		// Get studiengaenge the user is entitled for
		if (!$studiengang_kz_arr = $this->permissionlib->getSTG_isEntitledFor(self::BERECHTIGUNG_ANRECHNUNG_GENEHMIGEN))
		{
			show_error(getError($studiengang_kz_arr));
		}
		
		$viewData = array(
			'studiensemester_selected' => $studiensemester_kurzbz,
			'studiengaenge_entitled' => $studiengang_kz_arr
		);
		
		$this->load->view('lehre/anrechnung/approveAnrechnungUebersicht.php', $viewData);
	}
	
	/**
	 * Approve Anrechnungen.
	 */
	public function approve()
	{
		$data = $this->input->post('data');

		// Validate data
		if (isEmptyArray($data))
		{
			return $this->outputJsonError('Fehler beim Übertragen der Daten.');
		}
		
		// Approve Anrechnung
		foreach ($data as $item)
		{
			if ($this->anrechnunglib->approveAnrechnung($item['anrechnung_id']))
			{
				$json[]= array(
					'anrechnung_id' => $item['anrechnung_id'],
					'status_kurzbz' => self::ANRECHNUNGSTATUS_APPROVED,
					'status_bezeichnung' => $this->anrechnunglib->getStatusbezeichnung(self::ANRECHNUNGSTATUS_APPROVED)
				);
			}
		}
		
		// Output json to ajax
		if (isset($json) && !isEmptyArray($json))
		{
			return $this->outputJsonSuccess($json);
		}
		else
		{
			return $this->outputJsonError('Es wurden keine Anrechnungen genehmigt.');
		}
	}
	
	/**
	 * Reject Anrechnungen.
	 */
	public function reject()
	{
		$data = $this->input->post('data');
		
		// Validate data
		if (isEmptyArray($data))
		{
			return $this->outputJsonError('Fehler beim Übertragen der Daten.');
		}
		
		// Reject Anrechnung
		foreach ($data as $item)
		{
			if ($this->anrechnunglib->rejectAnrechnung($item['anrechnung_id'], $item['begruendung']))
			{
				$json[]= array(
					'anrechnung_id' => $item['anrechnung_id'],
					'status_kurzbz' => self::ANRECHNUNGSTATUS_REJECTED,
					'status_bezeichnung' => $this->anrechnunglib->getStatusbezeichnung(self::ANRECHNUNGSTATUS_REJECTED)
				);
			}
		}
		
		// Output json to ajax
		if (isset($json) && !isEmptyArray($json))
		{
			return $this->outputJsonSuccess($json);
		}
		else
		{
			return $this->outputJsonError('Es wurden keine Anrechnungen genehmigt.');
		}
	}
	
	/**
	 * Request recommendation for Anrechnungen.
	 */
	public function requestRecommendation()
	{
		$data = $this->input->post('data');
		
		if(isEmptyArray($data))
		{
			return $this->outputJsonError('Fehler beim Übertragen der Daten.');
		}
		
		$retval = array();
		$counter = 0;
		
		foreach ($data as $item)
		{
			// Check if Anrechnungs-LV has lector
			if (!$this->anrechnunglib->LVhasLector($item['anrechnung_id']))
			{
				// Count up LV with no lector
				$counter++;
				
				// Continue loop, if LV has no lector
				continue;
			}

			// Request Recommendation
			if($this->anrechnunglib->requestRecommendation($item['anrechnung_id']))
			{
				// Get full name of LV Leitung.
				// If LV Leitung is not present, get full name of LV lectors.
				$lector_arr = $this->anrechnunglib->getLectors($item['anrechnung_id']);
				$empfehlungsanfrage_an = !isEmptyArray($lector_arr)
					? implode(', ', array_column($lector_arr, 'fullname'))
					: '';
				
				$retval[]= array(
					'anrechnung_id' => $item['anrechnung_id'],
					'status_kurzbz' => self::ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR,
					'status_bezeichnung' => $this->anrechnunglib->getStatusbezeichnung(self::ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR),
					'empfehlung_anrechnung' => null,
					'empfehlungsanfrageAm' => (new DateTime())->format('Y-m-d H:i:s'),
					'empfehlungsanfrageAn' => $empfehlungsanfrage_an
				);
			}
		}
		
		/**
		 * Send mails to lectors
		 * NOTE: mails are sent at the end to ensure sending only ONE mail to each LV-Leitung or lector
		 * even if they are required for more recommendations
		 * */
		if (!isEmptyArray($retval))
		{
			self::_sendSanchoMailToLectors($retval);
		}
		
		// Output json to ajax
		if (isEmptyArray($retval) && $counter == 0)
		{
			return $this->outputJsonError('Es wurden keine Empfehlungen angefordert');
		}
		
		return $this->outputJsonSuccess($retval);
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
		
		$result = $this->LehrveranstaltungModel->loadWhere(array(
			'lehrveranstaltung_id' => $result->lehrveranstaltung_id
		));
		
		
		if(!$result = getData($result)[0])
		{
			show_error('Failed loading Lehrveranstaltung');
		}
		
		// Get STGL
		$result = $this->StudiengangModel->getLeitung($result->studiengang_kz);
		
		if($result = getData($result)[0])
		{
			if ($result->uid == $this->_uid)
			{
				return;
			}
		}
		
		show_error('You are not entitled to read this document');
	}
	
	/**
	 * Send mail to lectors asking for recommendation. (first to LV-Leitung, if not present to all lectors of lv)
	 * @param $mail_params
	 * @return bool
	 */
	private function _sendSanchoMailToLectors($mail_params)
	{
		// Get Lehrveranstaltungen
		$anrechnung_arr = array();
		
		foreach ($mail_params as $item)
		{
			$this->AnrechnungModel->addSelect('lehrveranstaltung_id, studiensemester_kurzbz');
			$anrechnung_arr[]= array(
				'lehrveranstaltung_id' => $this->AnrechnungModel->load($item['anrechnung_id'])->retval[0]->lehrveranstaltung_id,
				'studiensemester_kurzbz' => $this->AnrechnungModel->load($item['anrechnung_id'])->retval[0]->studiensemester_kurzbz
			);
		}
		
		$anrechnung_arr = array_unique($anrechnung_arr, SORT_REGULAR);

	
		/**
		 * Get lectors (prio for LV-Leitung, if not present to all lectors of LV.
		 * Anyway this function will receive a unique array to avoid sending more mails to one and the same lector.
		 * **/
		$lector_arr = $this->_getLectors($anrechnung_arr);

		// Send mail to lectors
		foreach ($lector_arr as $lector)
		{
			$to = $lector->uid;
			$vorname = $lector->vorname;
			
			// Get full name of stgl
			$this->load->model('person/Person_model', 'PersonModel');
			if (!$stgl_name = getData($this->PersonModel->getFullName($this->_uid)))
			{
				show_error ('Failed retrieving person');
			}
			
			// Link to Antrag genehmigen
			$url =
				CIS_ROOT. 'cis/index.php?menu='.
				CIS_ROOT. 'cis/menu.php?content_id=&content='.
				CIS_ROOT. index_page(). self::REVIEW_ANRECHNUNG_URI;
			
			// Prepare mail content
			$body_fields = array(
				'vorname'       => $vorname,
				'stgl_name'     => $stgl_name,
				'link'          => anchor($url, 'Anrechnungsanträge Übersicht')
			);
			
			sendSanchoMail(
				'AnrechnungEmpfehlungAnfordern',
				$body_fields,
				$to,
				'Anerkennung nachgewiesener Kenntnisse: Deine Empfehlung wird benötigt'
			);
		}
		return true;
	}
	
	/**
	 * Get lectors (prio for LV-Leitung, if not present to all lectors of LV.
	 * Anyway this function will receive a unique array to avoid sending more mails to one and the same lector.
	 * @param $anrechnung_arr
	 * @return array
	 */
	private function _getLectors($anrechnung_arr)
	{
		$lector_arr = array();
		
		// Get lectors
		foreach($anrechnung_arr as $anrechnung)
		{
			$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
			$result = $this->LehrveranstaltungModel->getLecturersByLv($anrechnung['studiensemester_kurzbz'], $anrechnung['lehrveranstaltung_id']);
			
			if (!$result = getData($result))
			{
				show_error('Failed retrieving lectors of Lehrveranstaltung');
			}
			
			// Check if lv has LV-Leitung
			$key = array_search(true, array_column($result, 'lvleiter'));
			
			// If lv has LV-Leitung, keep only the one
			if ($key !== false)
			{
				$lector_arr[]= $result[$key];
			}
			// ...otherwise keep all lectors
			else
			{
				$lector_arr = array_merge($lector_arr, $result);
			}
		}
		
		/**
		 * NOTE: This step is only done to make the array unique by uid, vorname and nachname in the following step
		 * (e.g. if same lector is ones LV-Leitung and another time not, then array_unique would leave both.
		 * But we wish to send only one email by to that one person)
		 * **/
		foreach ($lector_arr as $lector)
		{
			unset($lector->lvleiter);
		}
		
		// Now make the lector array aka mail receivers unique
		$lector_arr = array_unique($lector_arr, SORT_REGULAR);
		
		return $lector_arr;
	}
}