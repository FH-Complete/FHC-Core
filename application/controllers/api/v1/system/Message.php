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

class Message extends API_Controller
{
	/**
	 * Message API constructor.
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'MessagesByPersonID' => 'basis/message:r',
				'MessagesByUID' => 'basis/message:r',
				'MessagesByToken' => 'basis/message:r',
				'SentMessagesByPerson' => 'basis/message:r',
				'CountUnreadMessages' => 'basis/message:r',
				'Message' => 'basis/message:w',
				'MessageVorlage' => 'basis/message:w',
				'ChangeStatus' => 'basis/message:w'
			)
		);
		// Load library MessageLib
		$this->load->library('MessageLib');
	}

	/**
	 * getMessagesByPersonID
	 */
	public function getMessagesByPersonID()
	{
		$person_id = $this->get('person_id');
		$oe_kurzbz = $this->get('oe_kurzbz'); // root organisation unit
		$all = $this->get('all');

		if (isset($person_id))
		{
			$result = $this->messagelib->getMessagesByPerson($person_id, $oe_kurzbz, $all);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * getMessagesByUID
	 */
	public function getMessagesByUID()
	{
		$uid = $this->get('uid');
		$oe_kurzbz = $this->get('oe_kurzbz'); // root organisation unit
		$all = $this->get('all');

		if (isset($uid))
		{
			$result = $this->messagelib->getMessagesByUID($uid, $oe_kurzbz, $all);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * getMessagesByToken
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
	 * getSentMessagesByPerson
	 */
	public function getSentMessagesByPerson()
	{
		$person_id = $this->get('person_id');
		$oe_kurzbz = $this->get('oe_kurzbz'); // root organisation unit
		$all = $this->get('all');

		if (isset($person_id))
		{
			$result = $this->messagelib->getSentMessagesByPerson($person_id, $oe_kurzbz, $all);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * getCountUnreadMessages
	 */
	public function getCountUnreadMessages()
	{
		$person_id = $this->get('person_id');
		$oe_kurzbz = $this->get('oe_kurzbz'); // root organisation unit

		if (isset($person_id))
		{
			$result = $this->messagelib->getCountUnreadMessages($person_id, $oe_kurzbz);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * postMessage
	 */
	public function postMessage()
	{
		$postMessage = $this->_validatePostMessage($this->post());

		if (isSuccess($postMessage))
		{
			$result = $this->messagelib->sendMessageUser(
				$this->post()['receiver_id'],																// receiverPersonId
				$this->post()['subject'],																	// subject
				$this->post()['body'],																		// body
				$this->post()['person_id'] ? $this->post()['person_id'] : null,								// sender_id
				isset($this->post()['oe_kurzbz']) ? $this->post()['oe_kurzbz'] : null, 						// senderOU
				isset($this->post()['relationmessage_id']) ? $this->post()['relationmessage_id'] : null,	// relationmessage_id
				MSG_PRIORITY_NORMAL,																		// priority
				isset($this->post()['multiPartMime']) ? $this->post()['multiPartMime'] : true				// multiPartMime
			);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response($postMessage, REST_Controller::HTTP_OK);
		}
	}

	/**
	 * postMessageVorlage
	 */
	public function postMessageVorlage()
	{
		$postMessage = $this->_validatePostMessageVorlage($this->post());

		if (isSuccess($postMessage))
		{
			$result = $this->messagelib->sendMessageUserTemplate(
				isset($this->post()['receiver_id']) ? $this->post()['receiver_id'] : null,					// receiversPersonId
				$this->post()['vorlage_kurzbz'],															// vorlage
				$this->post()['data'],																		// parseData
				isset($this->post()['orgform_kurzbz']) ? $this->post()['orgform_kurzbz'] : null,			// orgform
				isset($this->post()['sender_id']) ? $this->post()['sender_id'] : null,						// sender_id
				isset($this->post()['oe_kurzbz']) ? $this->post()['oe_kurzbz'] : null,						// senderOU
				isset($this->post()['relationmessage_id']) ? $this->post()['relationmessage_id'] : null,	// relationmessage_id
				MSG_PRIORITY_NORMAL,																		// priority
				isset($this->post()['multiPartMime']) ? $this->post()['multiPartMime'] : true				// multiPartMime
			);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response($postMessage, REST_Controller::HTTP_OK);
		}
	}

	/**
	 * postChangeStatus
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

	/**
	 * _validatePostMessage
	 */
	private function _validatePostMessage($post = null)
	{
		if (!isset($post))
		{
			return error('Parameter is null');
		}
		if (!isset($post['subject']))
		{
			return error('subject is not set');
		}
		if (!isset($post['body']))
		{
			return error('body is not set');
		}
		if (!isset($post['receiver_id']))
		{
			return error('receiver_id is not set');
		}

		return success();
	}

	/**
	 * _validatePostMessageVorlage
	 */
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
