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
class ViewMessage extends CI_Controller
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
	}

	/**
	 * Using the MessageTokenModel instead of MessageLib to allow
	 * viewing the message without prompting the login
	 */
	public function toHTML($token)
	{
		$msg = $this->MessageTokenModel->getMessageByToken($token);

		if ($msg->error)
		{
			show_error($msg->retval);
		}

		if (is_array($msg->retval) && count($msg->retval) > 0)
		{
			$setReadMessageStatusByToken = $this->MessageTokenModel->setReadMessageStatusByToken($token);

			if (isError($setReadMessageStatusByToken))
			{
				show_error($msg->$setReadMessageStatusByToken);
			}

			$sender_id = $msg->retval[0]->sender_id;
			$receiver_id = $msg->retval[0]->receiver_id;
			$sender = $this->MessageTokenModel->getSenderData($sender_id);

			// To decide how to change the redirection
			$isEmployee = $this->MessageTokenModel->isEmployee($receiver_id);
			if (!is_bool($isEmployee) && isError($isEmployee))
			{
				show_error($isEmployee);
			}

			if($this->config->item('redirect_view_message_url') != '')
				$href = APP_ROOT . $this->config->item('redirect_view_message_url') . $token;
			else
				$href = '';

			$data = array (
				'sender_id' => $sender_id,
				'sender' => $sender->retval[0],
				'message' => $msg->retval[0],
				'isEmployee' => $isEmployee,
				'href' => $href
			);

			$this->load->view('system/messageHTML.php', $data);
		}
	}

	/**
	 * write the reply
	 */
	public function writeReply()
	{
		$token = $this->input->get('token');

		if (empty($token))
		{
			show_error('no token supplied');
		}

		$msg = null;

		// Get message data if possible
		$msg = $this->MessageTokenModel->getMessageByToken($token);

		if (!hasData($msg))
		{
			show_error('no message found');
		}

		$msg = $msg->retval[0];

		// Get variables
		$receiverData = $this->MessageTokenModel->getPersonData($msg->sender_id);

		if (!hasData($receiverData))
		{
			show_error('no sender found');
		}

		$data = array (
			'receivers' => $receiverData->retval,
			'message' => $msg,
			'token' => $token
		);

		$v = $this->load->view('system/messageWriteReply', $data);
	}

	/**
	 * send reply
	 */
	public function sendReply()
	{
		$this->load->model('system/Message_model', 'MessageModel');
		$this->load->library('MessageLib');

		$error = false;

		$subject = $this->input->post('subject');
		$body = $this->input->post('body');
		$persons = $this->input->post('persons');
		$relationmessage_id = $this->input->post('relationmessage_id');
		$token = $this->input->post('token');

		if (!isset($relationmessage_id) || $relationmessage_id == '' || !isset($token) || $token == '')
		{
			show_error('Error while sending reply');
			$error = true;
		}

		$relationmsg = $this->MessageTokenModel->getMessageByToken($token);

		// check if correct message
		if (!hasData($relationmsg) || $relationmessage_id !== $relationmsg->retval[0]->message_id)
		{
			show_error('Error while sending reply');
			$error = true;
		}

		// get sender (receiver of previous msg)
		$sender_id = $relationmsg->retval[0]->receiver_id;

		// get message data of persons
		$data = $this->MessageTokenModel->getPersonData($persons);

		// send message(s)
		if (hasData($data))
		{
			for ($i = 0; $i < count($data->retval); $i++)
			{
				$dataArray = (array)$data->retval[$i];

				$msg = $this->messagelib->sendMessage($sender_id, $dataArray['person_id'], $subject, $body, PRIORITY_NORMAL, $relationmessage_id, null);
				if ($msg->error)
				{
					show_error($msg->retval);
					$error = true;
					break;
				}

				// Loads the person log library
				$this->load->library('PersonLogLib');

				// Write log entry for sender
				$logtype_kurzbz = 'Action';
				$logdata = array(
					'name' => 'Message sent',
					'message' => 'Message sent from person '.$sender_id.' to '.$dataArray['person_id'].', messageid '.$msg->retval,
					'success' => 'true'
				);
				$taetigkeit_kurzbz = 'kommunikation';
				$app = 'core';
				$oe_kurzbz = null;
				$insertvon = 'online';

				$this->personloglib->log(
					$sender_id,
					$logtype_kurzbz,
					$logdata,
					$taetigkeit_kurzbz,
					$app,
					$oe_kurzbz,
					$insertvon
				);
			}
		}

		if (!$error)
		{
			echo "Messages sent successfully";
		}
	}
}
