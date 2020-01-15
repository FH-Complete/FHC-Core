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

/**
 * Handles sending messages with token
 * NOTE: in this controller is not possible to include/call everything
 * that automatically call the authentication system, like the most of models or libraries
 */
class ViewMessage extends FHC_Controller
{
	/**
	 * API constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Loading config file message
		$this->config->load('message');

		// Load model MessageToken_model, not calling the authentication system
		$this->load->model('system/MessageToken_model', 'MessageTokenModel');
		$this->load->model('CL/Messages_model', 'CLMessagesModel');
	}

	/**
	 * Using the MessageTokenModel instead of MessageLib to allow
	 * viewing the message without prompting the login
	 */
	public function toHTML($token)
	{
		$msg = $this->MessageTokenModel->getMessageByToken($token);
		if (isError($msg))
		{
			show_error(getError($msg));
		}

		if (is_array(getData($msg)) && count(getData($msg)) > 0)
		{
			$setReadMessageStatusByToken = $this->MessageTokenModel->setReadMessageStatusByToken($token);
			if (isError($setReadMessageStatusByToken))
			{
				show_error(getError($setReadMessageStatusByToken));
			}

			$sender_id = getData($msg)[0]->sender_id;
			$receiver_id = getData($msg)[0]->receiver_id;
			$sender = $this->MessageTokenModel->getSenderData($sender_id);

			// To decide how to change the redirection
			$isEmployee = $this->MessageTokenModel->isEmployee($receiver_id);
			if (isError($isEmployee))
			{
				show_error(getError($isEmployee));
			}

			if($this->config->item('redirect_view_message_url') != '')
				$href = $this->config->item('message_server').$this->config->item('redirect_view_message_url').$token;
			else
				$href = '';

			$data = array (
				'sender_id' => $sender_id,
				'sender' => getData($sender)[0],
				'message' => getData($msg)[0],
				'isEmployee' => hasData($isEmployee),
				'href' => $href
			);

			$this->load->view('system/messages/messageHTML.php', $data);
		}
	}

	/**
	 * write the reply
	 */
	public function writeReply()
	{
		$token = $this->input->get('token');

		if (isEmptyString($token))
		{
			show_error('No token supplied');
		}

		$msg = null;

		// Get message data if possible
		$msg = $this->MessageTokenModel->getMessageByToken($token);
		if (!hasData($msg))
		{
			show_error('No message found');
		}

		$msg = getData($msg)[0];

		// Get variables
		$receiverData = $this->MessageTokenModel->getPersonData($msg->sender_id);
		if (!hasData($receiverData))
		{
			show_error('No sender found');
		}

		$data = array (
			'receivers' => getData($receiverData),
			'message' => $msg,
			'token' => $token
		);

		$this->load->view('system/messages/messageWriteReply', $data);
	}

	/**
	 * Send a reply
	 */
	public function sendReply()
	{
		$subject = $this->input->post('subject');
		$body = $this->input->post('body');
		$persons = $this->input->post('persons');
		$relationmessage_id = $this->input->post('relationmessage_id');
		$token = $this->input->post('token');

		if (!isset($relationmessage_id) || $relationmessage_id == '' || !isset($token) || $token == '')
		{
			show_error('Error while sending reply');
		}

		$sendReply = $this->CLMessagesModel->sendReply($subject, $body, $persons, $relationmessage_id, $token);
		if (isError($sendReply))
		{
			show_error(getError($sendReply));
		}

		$this->load->view('system/messages/messageReplySent');
	}
}
