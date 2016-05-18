<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if(!defined('BASEPATH')) exit('No direct script access allowed');

class Prestudentstatus extends APIv1_Controller
{
	/**
	 * Prestudentstatus API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PrestudentstatusModel
		$this->load->model('crm/prestudentstatus_model', 'PrestudentstatusModel');
		// Load set the uid of the model to let to check the permissions
		$this->PrestudentstatusModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getPrestudentstatus()
	{
		$ausbildungssemester = $this->get('ausbildungssemester');
		$studiensemester_kurzbz = $this->get('studiensemester_kurzbz');
		$status_kurzbz = $this->get('status_kurzbz');
		$prestudent_id = $this->get('prestudent_id');
		
		if(isset($ausbildungssemester) && isset($studiensemester_kurzbz) && isset($status_kurzbz) && isset($prestudent_id))
		{
			$result = $this->PrestudentstatusModel->load(array($ausbildungssemester, $studiensemester_kurzbz, $status_kurzbz, $prestudent_id));
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function postPrestudentstatus()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['prestudentstatus_id']))
			{
				$result = $this->PrestudentstatusModel->update($this->post()['prestudentstatus_id'], $this->post());
			}
			else
			{
				$result = $this->PrestudentstatusModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($prestudentstatus = NULL)
	{
		return true;
	}
}