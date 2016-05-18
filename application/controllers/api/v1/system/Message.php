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

class Message extends APIv1_Controller
{
	/**
	 * Message API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model MessageModel
		$this->load->model('system/message_model', 'MessageModel');
		// Load set the uid of the model to let to check the permissions
		$this->MessageModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getMessage()
	{
		$messageID = $this->get('message_id');
		
		if(isset($messageID))
		{
			$result = $this->MessageModel->load($messageID);
			
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
	public function postMessage()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['message_id']))
			{
				$result = $this->MessageModel->update($this->post()['message_id'], $this->post());
			}
			else
			{
				$result = $this->MessageModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($message = NULL)
	{
		return true;
	}
}