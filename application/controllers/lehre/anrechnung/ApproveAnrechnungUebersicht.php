<?php

//if (! defined('BASEPATH')) exit('No direct script access allowed');

class approveAnrechnungUebersicht extends Auth_Controller
{
	const BERECHTIGUNG_ANRECHNUNG_GENEHMIGEN = 'lehre/anrechnung_genehmigen';
	
	const ANRECHNUNGSTATUS_PROGRESSED_BY_STGL = 'inProgressDP';
	const ANRECHNUNGSTATUS_PROGRESSED_BY_KF = 'inProgressKF';
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
				'reject'   => 'lehre/anrechnung_genehmigen:rw'
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
		$studiensemester_kurzbz = $this->input->get('studiensemester');
		
		// Retrieve studiengaenge the user is entitled for
		if (!$studiengang_kz_arr = $this->permissionlib->getSTG_isEntitledFor(self::BERECHTIGUNG_ANRECHNUNG_GENEHMIGEN))
		{
			show_error(getError($studiengang_kz_arr));
		}
		
		if (!is_string($studiensemester_kurzbz))
		{
			$studiensemester = $this->StudiensemesterModel->getNearest(); // TODO check
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
			'studiensemester_selected' => $studiensemester_kurzbz,
			'studiengaenge_entitled' => $studiengang_kz_arr
		);
		
		$this->load->view('lehre/anrechnung/approveAnrechnungUebersicht.php', $viewData);
	}
	
	/**
	 * Approve multiple Anrechnungen.
	 */
	public function approve()
	{
		$data = $this->input->post('data');

		if(isEmptyArray($data))
		{
			return $this->outputJsonError('Fehler beim Übertragen der Daten.');
		}
		
		// Get statusbezeichnung for 'approved'
		$this->AnrechnungstatusModel->addSelect('bezeichnung_mehrsprachig');
		$approved = getData($this->AnrechnungstatusModel->load('approved'))[0];

		foreach ($data as $item)
		{
			// Approve Anrechnung
			if(getData($this->anrechnunglib->approveAnrechnung($item['anrechnung_id'])))
			{
				$json[]= array(
					'anrechnung_id' => $item['anrechnung_id'],
					'status_bezeichnung' => getUserLanguage() == 'German'
						? $approved->bezeichnung_mehrsprachig[0]
						: $approved->bezeichnung_mehrsprachig[1]
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
	
	public function reject()
	{
		$data = $this->input->post('data');
		
		if(isEmptyArray($data))
		{
			return $this->outputJsonError('Fehler beim Übertragen der Daten.');
		}
		
		// Get statusbezeichnung for 'approved'
		$this->AnrechnungstatusModel->addSelect('bezeichnung_mehrsprachig');
		$rejected = getData($this->AnrechnungstatusModel->load('rejected'))[0];
		$rejected = getUserLanguage() == 'German'
			? $rejected->bezeichnung_mehrsprachig[0]
			: $rejected->bezeichnung_mehrsprachig[1];
		
		foreach ($data as $item)
		{
			// Approve Anrechnung
			if(getData($this->anrechnunglib->rejectAnrechnung($item['anrechnung_id'])))
			{
				$json[]= array(
					'anrechnung_id' => $item['anrechnung_id'],
					'status_bezeichnung' => $rejected
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