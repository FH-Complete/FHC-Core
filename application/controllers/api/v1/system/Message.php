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

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Message extends APIv1_Controller
{
	/**
	 * Message API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model MessageModel
		$this->load->library('MessageLib', array('uid' => $this->_getUID()));
	}

	/**
	 * @return void
	 */
	public function getMessagesByPersonID()
	{
		$person_id = $this->get('person_id');
		$all = $this->get('all');
		
		if (isset($person_id))
		{
			$result = $this->messagelib->getMessagesByPerson($person_id, $all);
			
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
		$validation = $this->_validate($this->post());
		
		if (is_object($validation) && $validation->error == EXIT_SUCCESS)
		{
			$this->messagelib->addRecipient($this->post()['person_id']);
			$result = $this->messagelib->sendMessage(
				$this->post()['person_id'],
				$this->post()['subject'],
				$this->post()['body'],
				PRIORITY_NORMAL,
				NULL,
				$this->post()['oe_kurzbz']
			);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response($validation, REST_Controller::HTTP_OK);
		}
	}
	
	private function _validate($message = null)
	{
		if (!isset($message))
		{
			return $this->_error('Parameter is null');
		}
		if (!isset($message['person_id']))
		{
			return $this->_error('person_id is not set');
		}
		if (!isset($message['subject']))
		{
			return $this->_error('subject is not set');
		}
		if( !isset($message['body']))
		{
			return $this->_error('body is not set');
		}
		if (!isset($message['oe_kurzbz']))
		{
			return $this->_error('oe_kurzbz is not set');
		}
		
		return $this->_success('Input data are valid');
	}
}