<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class ZGVUeberpruefung extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Set required permissions
		parent::__construct(
			array(
				'index' => 'lehre/zgvpruefung:r',
				'getZgvStatusByPrestudent' => 'lehre/zgvpruefung:r'
			)
		);
		$this->load->model('crm/ZGVPruefungStatus_model', 'ZGVPruefungStatusModel');
		$this->load->model('crm/ZGVPruefung_model', 'ZGVPruefungModel');

		$this->load->library('WidgetLib');

		$this->setControllerId();
		$this->loadPhrases(
			array(
				'infocenter'
			)
		);
	}

	public function index()
	{
		$this->load->view('system/infocenter/infocenterZgvUeberpruefung.php');
	}

	public function getZgvStatusByPrestudent()
	{
		$prestudent_id = $this->input->get('prestudent_id');

		$zgvExist = $this->ZGVPruefungModel->loadWhere(array('prestudent_id' => $prestudent_id));

		if (!hasData($zgvExist))
			$this->terminateWithJsonError('no ZGV exist');

		$status = $this->ZGVPruefungStatusModel->getZgvStatus(getData($zgvExist)[0]->zgvpruefung_id);

		if (!hasData($status))
			$this->terminateWithJsonError('No status');

		$status = getData($status)[0]->status;

		$this->outputJsonSuccess($status);
	}
}