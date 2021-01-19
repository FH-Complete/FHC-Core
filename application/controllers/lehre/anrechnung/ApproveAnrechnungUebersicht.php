<?php

//if (! defined('BASEPATH')) exit('No direct script access allowed');

class approveAnrechnungUebersicht extends Auth_Controller
{
	const BERECHTIGUNG_ANRECHNUNG_GENEHMIGEN = 'lehre/anrechnung_genehmigen';
	public function __construct()
	{
		// Set required permissions
		parent::__construct(
			array(
				'index'     => 'lehre/anrechnung_genehmigen:rw',
				'download'  => 'lehre/anrechnung_genehmigen:rw'
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