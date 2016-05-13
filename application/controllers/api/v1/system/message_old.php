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

class message_old extends APIv1_Controller
{
	/**
	 * message_old API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model message_oldModel
		$this->load->model('system/message_old_model', 'message_oldModel');
		// Load set the uid of the model to let to check the permissions
		$this->message_oldModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getmessage_old()
	{
		$message_oldID = $this->get('message_old_id');
		
		if(isset($message_oldID))
		{
			$result = $this->message_oldModel->load($message_oldID);
			
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
	public function postmessage_old()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['message_old_id']))
			{
				$result = $this->message_oldModel->update($this->post()['message_old_id'], $this->post());
			}
			else
			{
				$result = $this->message_oldModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($message_old = NULL)
	{
		return true;
	}
}