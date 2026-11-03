<?php

class TempusIssueResolver extends IssueResolver_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->model('system/Fehler_model', 'FehlerModel');
		$fehlerRes = $this->FehlerModel->getByApps(['tempus']);
		$this->_fehlercodes = hasData($fehlerRes) ? array_column(getData($fehlerRes), 'fehlercode') : [];
	}
}