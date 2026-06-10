<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class approveAnrechnungDetail extends Auth_Controller
{
	const BERECHTIGUNG_ANRECHNUNG_GENEHMIGEN = 'lehre/anrechnung_genehmigen';

	const REVIEW_ANRECHNUNG_URI = '/lehre/anrechnung/ReviewAnrechnungUebersicht';

	const ANRECHNUNGSTATUS_PROGRESSED_BY_STGL = 'inProgressDP';
	const ANRECHNUNGSTATUS_PROGRESSED_BY_KF = 'inProgressKF';
	const ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR = 'inProgressLektor';
	const ANRECHNUNGSTATUS_APPROVED = 'approved';
	const ANRECHNUNGSTATUS_REJECTED = 'rejected';

	const ANRECHNUNG_NOTIZTITEL_NOTIZ_BY_STGL = 'AnrechnungNotizSTGL';
	const ANRECHNUNG_NOTIZTITEL_EMPFEHLUNGSNOTIZ_BY_STGL = 'AnrechnungEmpfehlungsnotizSTGL';

	public function __construct()
	{
		// Set required permissions
		parent::__construct(
			array(
				'index'     => 'lehre/anrechnung_genehmigen:r',
				'download'  => 'lehre/anrechnung_genehmigen:r',
				'approve'   => 'lehre/anrechnung_genehmigen:rw',
				'reject'    => 'lehre/anrechnung_genehmigen:rw',
				'requestRecommendation' => 'lehre/anrechnung_genehmigen:rw',
				'withdraw' => 'lehre/anrechnung_genehmigen:rw',
				'withdrawRequestRecommendation' => 'lehre/anrechnung_genehmigen:rw',
				'saveEmpfehlungsNotiz' => 'lehre/anrechnung_genehmigen:rw'
			)
		);

		//Load configs
		$this->load->config('anrechnung');

		// Load models
		$this->load->model('education/Anrechnung_model', 'AnrechnungModel');
		$this->load->model('education/Anrechnungstatus_model', 'AnrechnungstatusModel');
		$this->load->model('content/DmsVersion_model', 'DmsVersionModel');
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('person/Notiz_model', 'NotizModel');
		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

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
		$anrechnung_id = $this->input->get('anrechnung_id');

		if (!is_numeric($anrechnung_id))
		{
			show_error('Missing correct parameter');
		}

		// Check if user is entitled to read the Anrechnung
        $this->_checkIfEntitledToReadAnrechnung($anrechnung_id);

		// Get Anrechung data
		$anrechnungData = $this->anrechnunglib->getAnrechnungData($anrechnung_id);

		// Get Antrag data
		$antragData = $this->anrechnunglib->getAntragData(
			$anrechnungData->prestudent_id,
			$anrechnungData->studiensemester_kurzbz,
			$anrechnungData->lehrveranstaltung_id,
            $anrechnungData->anrechnung_id
		);

		// Get Empfehlung data
		$empfehlungData = $this->anrechnunglib->getEmpfehlungData($anrechnung_id);

		// Get Genehmigung data
		$genehmigungData = $this->anrechnunglib->getGenehmigungData($anrechnung_id);

        $hasReadOnlyAccess =
            $this->permissionlib->isBerechtigt(self::BERECHTIGUNG_ANRECHNUNG_GENEHMIGEN, 's', $antragData->studiengang_kz)
            && !$this->permissionlib->isBerechtigt(self::BERECHTIGUNG_ANRECHNUNG_GENEHMIGEN, 'suid', $antragData->studiengang_kz);

		$viewData = array(
			'antragData' => $antragData,
			'anrechnungData' => $anrechnungData,
			'empfehlungData' => $empfehlungData,
			'genehmigungData' => $genehmigungData,
            'hasReadOnlyAccess' => $hasReadOnlyAccess
		);

		$this->load->view('lehre/anrechnung/approveAnrechnungDetail.php', $viewData);
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

		// Get STGLs person data
		if (!$person = getData($this->PersonModel->getByUID($this->_uid))[0])
		{
			show_error('Failed retrieving person data');
		}

		// Approve Anrechnung
		foreach ($data as $item)
		{
			if ($this->anrechnunglib->approveAnrechnung($item['anrechnung_id']))
			{
				$json[]= array(
					'anrechnung_id' => $item['anrechnung_id'],
					'status_kurzbz' => self::ANRECHNUNGSTATUS_APPROVED,
					'status_bezeichnung' => $this->anrechnunglib->getStatusbezeichnung(self::ANRECHNUNGSTATUS_APPROVED),
					'abgeschlossen_am'   => (new DateTime())->format('d.m.Y'),
					'abgeschlossen_von'  => $person->vorname. ' '. $person->nachname
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

		// Get STGLs person data
		if (!$person = getData($this->PersonModel->getByUID($this->_uid))[0])
		{
			show_error('Failed retrieving person data');
		}

		// Reject Anrechnung
		foreach ($data as $item)
		{
			if ($this->anrechnunglib->rejectAnrechnung($item['anrechnung_id'], $item['begruendung']))
			{
				$json[]= array(
					'anrechnung_id'         => $item['anrechnung_id'],
					'status_kurzbz'         => self::ANRECHNUNGSTATUS_REJECTED,
					'status_bezeichnung'    => $this->anrechnunglib->getStatusbezeichnung(self::ANRECHNUNGSTATUS_REJECTED),
					'abgeschlossen_am'      => (new DateTime())->format('d.m.Y'),
					'abgeschlossen_von'     => $person->vorname. ' '. $person->nachname
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
			return $this->outputJsonError($this->p->t('ui', 'errorNichtAusgefuehrt'));
		}
	}

	/**
	 * Request recommendation for Anrechnungen.
	 */
	public function requestRecommendation()
	{
		$anrechnung_id = $this->input->post('anrechnung_id');

		if(isEmptyString($anrechnung_id))
		{
			return $this->outputJsonError('Fehler beim Übertragen der Daten.');
		}

		$retval = array();
        
        // Check if Anrechnungs-LV has lector
        if (!$this->anrechnunglib->LVhasLector($anrechnung_id))
        {
            $this->terminateWithJsonError('LV has no lector');
        }

        // Get Fachbereichsleitung or LV Leitung.
        if($this->config->item('fbl') === TRUE)
        {
            $result = $this->anrechnunglib->getLeitungOfLvOe($anrechnung_id);
        }
        else
        {
            // If LV Leitung is not present, gets all LV lectors.
            $result = $this->anrechnunglib->getLectors($anrechnung_id);
        }

        $empfehlungsanfrage_an = !isEmptyArray($result) ? implode(', ', array_column($result, 'fullname')) : '';

        // Request Recommendation
        if ($this->anrechnunglib->requestRecommendation($anrechnung_id))
        {
            $retval[]= array(
                'anrechnung_id' => $anrechnung_id,
                'status_kurzbz' => self::ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR,
                'status_bezeichnung' => $this->anrechnunglib->getStatusbezeichnung(self::ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR),
                'empfehlung_anrechnung' => null,
                'empfehlungsanfrageAm' => (new DateTime())->format('d.m.Y'),
                'empfehlungsanfrageAn' => $empfehlungsanfrage_an
            );
        }

		// Output json to ajax
        if ($empfehlungsanfrage_an == '')
        {
            $this->terminateWithJsonError(
                "Empfehlung wurde nicht angefordert,\nDer LV sind keine LektorInnen zugeteilt."
            );
        }

		if (isEmptyArray($retval))
		{
            $this->terminateWithJsonError("Empfehlung wurde nicht angefordert");
		}
        else
        {
            // Output json to ajax
            return $this->outputJsonSuccess($retval);
        }
	}

	/**
	 * Withdraw approved / rejected Anrechnung and reset to 'inProgressDP'.
	 */
	public function withdraw()
	{
		$anrechnung_id = $this->input->post('anrechnung_id');

		if (!is_numeric($anrechnung_id))
		{
			$this->terminateWithJsonError($this->p->t('ui', 'errorFelderFehlen'));
		}

		// Delete last status approved / rejected.
		// If last status is 'approved', Genehmigung is resetted.
		$result = $this->AnrechnungModel->withdrawApprovement($anrechnung_id);

		if (isError($result))
		{
			$this->terminateWithJsonError(getError($result));
		}

		// Success output to AJAX
		$this->outputJsonSuccess(array(
			'status_bezeichnung' => $this->anrechnunglib->getLastAnrechnungstatus($anrechnung_id))
		);
	}

	/**
	 * Withdraw request for reommendation and reset to 'inProgressDP'.
	 * This is only possible if the lector has not provided a recommendation yet.
	 */
	public function withdrawRequestRecommendation()
	{
		$anrechnung_id = $this->input->post('anrechnung_id');

		if (!is_numeric($anrechnung_id))
		{
			show_error('Wrong parameter.');
		}

		// Get boolean empfehlung of given Anrechnung
		if (!$result = getData($this->AnrechnungModel->load($anrechnung_id))[0])
		{
			show_error('Failed loading Anrechnung');
		}

		$empfehlung = $result->empfehlung_anrechnung;

		// Get last Anrechnungstatus
		if (!$result = getData($this->AnrechnungModel->getLastAnrechnungstatus($anrechnung_id))[0])
		{
			show_error('Failed loading last Anrechnungstatus');
		}

		$last_status = $result->status_kurzbz;
		$anrechnungstatus_id = $result->anrechnungstatus_id;

		// Return if Anrechnung was not waiting for recommendation or if Anrechnung has already been recommended
		if ($last_status != self::ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR || !is_null($empfehlung))
		{
			return $this->outputJsonError('No recommendation to withdraw.');
		}

		// Reset status to 'inProgressDP'
		$result = $this->AnrechnungModel->deleteAnrechnungstatus($anrechnungstatus_id);

		if (isError($result))
		{
			return $this->outputJsonError('Could not withdraw this application.');
		}

		// Success output to AJAX
		return $this->outputJsonSuccess(array(
				'status_bezeichnung' => $this->anrechnunglib->getLastAnrechnungstatus($anrechnung_id))
		);
	}

	public function saveEmpfehlungsNotiz()
	{
		$anrechnung_id = $this->input->post('anrechnung_id');
		$notiz_id = $this->input->post('notiz_id');
		$empfehlungstext = $this->input->post('empfehlung_text');

		// Validate data
		if (isEmptyString($anrechnung_id))
		{
			$this->terminateWithJsonError($this->p->t('ui', 'systemFehler'));
		}

		// Save Empfehlungstext
		$result = self::_saveEmpfehlungsNotiz($anrechnung_id, $empfehlungstext, $notiz_id);

		if (isError($result))
		{
			$this->terminateWithJsonError($this->p->t('ui', 'fehlerBeimSpeichern'));
		}

		// Output success message
		$this->outputJsonSuccess($this->p->t('ui', 'gespeichert'));
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

		// Set filename to be used on downlaod
		$filename = $this->anrechnunglib->setFilenameOnDownload($dms_id);

		// Get file to be downloaded from DMS
		$download = $this->dmslib->download($dms_id, $filename);
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
	 * Check if user is entitled to read this Anrechnung
	 * @param $anrechnung_id
	 */
	private function _checkIfEntitledToReadAnrechnung($anrechnung_id)
	{
		$result = $this->AnrechnungModel->load($anrechnung_id);

		if(!hasData($result))
		{
			show_error('Failed loading Anrechnung');
		}
		
		$result = $this->LehrveranstaltungModel->loadWhere(array(
			'lehrveranstaltung_id' => getData($result)[0]->lehrveranstaltung_id
		));

	    $studiengang_kz = getData($result)[0]->studiengang_kz;

        // Check if user is entitled
        if (!$this->permissionlib->isBerechtigt(self::BERECHTIGUNG_ANRECHNUNG_GENEHMIGEN, 's', $studiengang_kz))
        {
            show_error('You are not entitled to read this page');
        }
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

		$studiengang_kz = getData($result)[0]->studiengang_kz;

        // Check if user is entitled
        if (!$this->permissionlib->isBerechtigt(self::BERECHTIGUNG_ANRECHNUNG_GENEHMIGEN, 's', $studiengang_kz))
        {
            show_error('You are not entitled to read this document');
        }
	}

	/**
	 * Send mail to lectors asking for recommendation. (first to LV-Leitung, if not present to all lectors of lv)
	 * @param $anrechnung_id
	 * @return bool
	 */
	private function _sendSanchoMailToLectors($anrechnung_id)
	{
        $lehrveranstaltung_id = $this->AnrechnungModel->load($anrechnung_id)->retval[0]->lehrveranstaltung_id;
        $studiensemester_kurzbz =  $this->AnrechnungModel->load($anrechnung_id)->retval[0]->studiensemester_kurzbz;

		/**
         * Get mail receivers.
		 * If config is default (lectors): prio for LV-Leitung, if not present to all lectors of LV.
		 * Anyway this function will receive a unique array to avoid sending more mails to one and the same lector.
		 * **/
        if ($this->config->item('fbl') === TRUE)
        {
            $receiver_arr = $this->_getLeitungOfLvOe($lehrveranstaltung_id);
        }
        else
        {
            $receiver_arr = $this->_getLectors($studiensemester_kurzbz, $lehrveranstaltung_id);
        }

		// Send mail
		foreach ($receiver_arr as $receiver)
		{
			$to = $receiver->uid. '@'. DOMAIN;;
			$vorname = $receiver->vorname;

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
	 * Get unique array of LV lectors.
	 * Only get LV Leitung if present, otherwise all lectors of LV.
	 * @param $anrechnung_arr
	 * @return array
	 */
	private function _getLectors($studiensemester_kurzbz, $lehrveranstaltung_id)
	{
		$lector_arr = array();

        $result = $this->LehrveranstaltungModel->getLecturersByLv($studiensemester_kurzbz, $lehrveranstaltung_id);

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

		/**
		 * NOTE: This step is only done to make the array unique by uid, vorname and nachname in the following step
		 * (e.g. if same lector is ones LV-Leitung and another time not, then array_unique would leave both.
		 * But we wish to send only one email by to that one person)
		 * **/
		foreach ($lector_arr as $lector)
		{
			unset($lector->lvleiter);
		}

		// Make the lector array unique
		$lector_arr = array_unique($lector_arr, SORT_REGULAR);

		return $lector_arr;

	}

    // Get Leitungen of Lehrveranstaltungs-Organisationseinheit
    private function _getLeitungOfLvOe($lehrveranstaltung_id)
    {
        $result = $this->LehrveranstaltungModel->getLeitungOfLvOe($lehrveranstaltung_id);

        return hasData($result) ? getData($result) : show_error('Failed retrieving Leitung of Lehrveranstaltungs-Organisationseinheit');
    }

	private function _saveEmpfehlungsNotiz($anrechnung_id, $empfehlungstext, $notiz_id)
	{
		$this->load->model('person/Notiz_model', 'NotizModel');

		if (!isEmptyString($notiz_id))
		{
			return $this->NotizModel->update(
				$notiz_id,
				array(
					'text' => $empfehlungstext,
					'updateamum' => (new DateTime())->format('Y-m-d H:i:s'),
					'updatevon' => $this->_uid
				)
			);
		}

		return $this->NotizModel->addNotizForAnrechnung(
			$anrechnung_id,
			self::ANRECHNUNG_NOTIZTITEL_EMPFEHLUNGSNOTIZ_BY_STGL,
			trim($empfehlungstext),
			$this->_uid
		);
	}
}
