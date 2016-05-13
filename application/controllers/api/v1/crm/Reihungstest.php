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

class Reihungstest extends APIv1_Controller
{
	/**
	 * Reihungstest API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model ReihungstestModel
		$this->load->model('crm/reihungstest_model', 'ReihungstestModel');
		// Load set the uid of the model to let to check the permissions
		$this->ReihungstestModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getReihungstest()
	{
		$reihungstestID = $this->get('reihungstest_id');
		
		if(isset($reihungstestID))
		{
			$result = $this->ReihungstestModel->load($reihungstestID);
			
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
	public function postReihungstest()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['reihungstest_id']))
			{
				$result = $this->ReihungstestModel->update($this->post()['reihungstest_id'], $this->post());
			}
			else
			{
				$result = $this->ReihungstestModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($reihungstest = NULL)
	{
		return true;
	}
}