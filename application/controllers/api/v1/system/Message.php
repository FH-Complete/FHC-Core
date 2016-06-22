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
		if ($this->_validate($this->post()))
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
			$this->response();
		}
	}
	
	private function _validate($message = null)
	{
		if (!isset($message['person_id']) || !isset($message['subject']) ||
			!isset($message['body']) || !isset($message['oe_kurzbz']))
		{
			return false;
		}
		
		return true;
	}
}