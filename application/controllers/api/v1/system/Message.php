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
		// Load library MessageLib
		$this->load->library('MessageLib');
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
	public function getMessagesByUID()
	{
		$uid = $this->get('uid');
		$all = $this->get('all');
		
		if (isset($uid))
		{
			$result = $this->messagelib->getMessagesByUID($uid, $all);
			
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
	public function getMessagesByToken()
	{
		$token = $this->get('token');
		
		if (isset($token))
		{
			$result = $this->messagelib->getMessageByToken($token);
			
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
	public function getSentMessagesByPerson()
	{
		$person_id = $this->get('person_id');
		$all = $this->get('all');
		
		if (isset($person_id))
		{
			$result = $this->messagelib->getSentMessagesByPerson($person_id, $all);
			
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
		$validation = $this->_validatePostMessage($this->post());
		
		if (isSuccess($validation))
		{
			$result = $this->messagelib->sendMessage(
				isset($this->post()['person_id']) ? $this->post()['person_id'] : null,
				isset($this->post()['receiver_id']) ? $this->post()['receiver_id'] : null,
				$this->post()['subject'],
				$this->post()['body'],
				PRIORITY_NORMAL,
				isset($this->post()['relationmessage_id']) ? $this->post()['relationmessage_id'] : null,
				isset($this->post()['oe_kurzbz']) ? $this->post()['oe_kurzbz'] : null,
				isset($this->post()['multiPartMime']) ? $this->post()['multiPartMime'] : true
			);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response($validation, REST_Controller::HTTP_OK);
		}
	}
	
	/**
	 * @return void
	 */
	public function postMessageVorlage()
	{
		$validation = $this->_validatePostMessageVorlage($this->post());
		
		if (isSuccess($validation))
		{
			$result = $this->messagelib->sendMessageVorlage(
				isset($this->post()['sender_id']) ? $this->post()['sender_id'] : null,
				isset($this->post()['receiver_id']) ? $this->post()['receiver_id'] : null,
				$this->post()['vorlage_kurzbz'],
				isset($this->post()['oe_kurzbz']) ? $this->post()['oe_kurzbz'] : null,
				$this->post()['data'],
				isset($this->post()['relationmessage_id']) ? $this->post()['relationmessage_id'] : null,
				isset($this->post()['orgform_kurzbz']) ? $this->post()['orgform_kurzbz'] : null,
				isset($this->post()['multiPartMime']) ? $this->post()['multiPartMime'] : true
			);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response($validation, REST_Controller::HTTP_OK);
		}
	}
	
	/**
	 * @return void
	 */
	public function postChangeStatus()
	{
		$person_id = $this->post()['person_id'];
		$message_id = $this->post()['message_id'];
		$status = $this->post()['status'];
		
		if (isset($person_id) && isset($message_id) && isset($status) &&
			in_array($status, array(MSG_STATUS_UNREAD, MSG_STATUS_READ, MSG_STATUS_ARCHIVED, MSG_STATUS_DELETED)))
		{
			$result = $this->messagelib->updateMessageStatus($message_id, $person_id, $status);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validatePostMessage($message = null)
	{
		if (!isset($message))
		{
			return error('Parameter is null');
		}
		if (!isset($message['subject']))
		{
			return error('subject is not set');
		}
		if( !isset($message['body']))
		{
			return error('body is not set');
		}
		if (!isset($message['receiver_id']) && !isset($message['oe_kurzbz']))
		{
			return error('If a receiver_id is not given a oe_kurzbz must be specified');
		}
		
		return success('Input data are valid');
	}
	
	private function _validatePostMessageVorlage($message = null)
	{
		if (!isset($message))
		{
			return error('Parameter is null');
		}
		if (!isset($message['vorlage_kurzbz']))
		{
			return error('vorlage_kurzbz is not set');
		}
		if (!isset($message['data']))
		{
			return error('data is not set');
		}
		if (!isset($message['receiver_id']) && !isset($message['oe_kurzbz']))
		{
			return error('If a receiver_id is not given a oe_kurzbz must be specified');
		}
		
		return success('Input data are valid');
	}
}