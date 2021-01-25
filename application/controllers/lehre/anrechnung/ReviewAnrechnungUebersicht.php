<?php

//if (! defined('BASEPATH')) exit('No direct script access allowed');

class reviewAnrechnungUebersicht extends Auth_Controller
{
	const BERECHTIGUNG_ANRECHNUNG_EMPFEHLEN = 'lehre/anrechnung_empfehlen';
	
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
		$studiensemester_kurzbz = $this->input->get('studiensemester');

		if (!is_string($studiensemester_kurzbz))
		{
			$studiensemester = $this->StudiensemesterModel->getNearest();
			if (hasData($studiensemester))
			{
				$studiensemester_kurzbz = $studiensemester->retval[0]->studiensemester_kurzbz;
			}
			elseif (isError($studiensemester))
			{
				show_error(getError($studiensemester));
			}
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
		
		// Get statusbezeichnung for 'inProgressDP'
		$this->AnrechnungstatusModel->addSelect('bezeichnung_mehrsprachig');
		$inProgressDP = getData($this->AnrechnungstatusModel->load('inProgressDP'))[0];
		$inProgressDP = getUserLanguage() == 'German'
			? $inProgressDP->bezeichnung_mehrsprachig[0]
			: $inProgressDP->bezeichnung_mehrsprachig[1];

		foreach ($data as $item)
		{
			// Approve Anrechnung
			if(getData($this->anrechnunglib->recommendAnrechnung($item['anrechnung_id'])))
			{
				$json[]= array(
					'anrechnung_id'         => $item['anrechnung_id'],
					'empfehlung_anrechnung' => 'true',
					'status_kurzbz'         => self::ANRECHNUNGSTATUS_PROGRESSED_BY_STGL,
					'status_bezeichnung'    => $inProgressDP
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
			return $this->outputJsonError('Empfehlungen wurden nicht durchgeführt');
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
		
		// Get statusbezeichnung for 'inProgressDP'
		$this->AnrechnungstatusModel->addSelect('bezeichnung_mehrsprachig');
		$inProgressDP = getData($this->AnrechnungstatusModel->load('inProgressDP'))[0];
		$inProgressDP = getUserLanguage() == 'German'
			? $inProgressDP->bezeichnung_mehrsprachig[0]
			: $inProgressDP->bezeichnung_mehrsprachig[1];
		
		foreach ($data as $item)
		{
			// Approve Anrechnung
			if(getData($this->anrechnunglib->dontRecommendAnrechnung($item['anrechnung_id'])))
			{
				$json[]= array(
					'anrechnung_id'         => $item['anrechnung_id'],
					'empfehlung_anrechnung' => 'false',
					'status_kurzbz'         => self::ANRECHNUNGSTATUS_PROGRESSED_BY_STGL,
					'status_bezeichnung'    => $inProgressDP
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
			return $this->outputJsonError('Empfehlungen wurden nicht durchgeführt');
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
}