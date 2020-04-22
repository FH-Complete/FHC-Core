<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * NOTE: MessageClient extends FHC_Controller and NOT Auth_Controller to be able to use
 * 		the authentication system without the need to load the permissions system
 */
class MessageClient extends FHC_Controller
{
	public function __construct()
	{
		parent::__construct();

		// Loads authentication library and starts authentication
		// NOTE: it is loaded here because the controller extends FHC_Controller and NOT Auth_Controller
		$this->load->library('AuthLib');

		// Loads model CLMessagesModel which contains the GUI logic
		$this->load->model('CL/Messages_model', 'CLMessagesModel');

		// Phrases used in loaded views
		$this->loadPhrases(
			array(
				'global',
				'ui'
			)
		);
	}

	/**
	 * Starts the GUI used to read all the personal messages
	 */
	public function read()
	{
		// Loads the view to read messages
		$this->load->view('system/messages/ajaxRead', $this->CLMessagesModel->prepareAjaxRead());
	}

	/**
	 * Starts the GUI used to write a personal message to an organisation unit
	 */
	public function write()
	{
		// Loads the view to write a message
		$this->load->view('system/messages/ajaxWrite', $this->CLMessagesModel->prepareAjaxWrite());
	}

	/**
	 * Starts the GUI used to reply to a received personal message
	 */
	public function writeReply()
	{
		$token = $this->input->get('token');

		// Loads the view to reply to a message
		$this->load->view('system/messages/ajaxWriteReply', $this->CLMessagesModel->prepareAjaxWriteReply($token));
	}

	/**
	 * Returns JSON that that contains all the received messages by the currently logged user
	 */
	public function listReceivedMessages()
	{
		$this->outputJson($this->CLMessagesModel->prepareAjaxReadReceived());
	}

	/**
	 * Returns JSON that that contains all the sent messages by the currently logged user
	 */
	public function listSentMessages()
	{
		$this->outputJson($this->CLMessagesModel->prepareAjaxReadSent());
	}

	/**
	 * Sends a message to an organisation unit
	 */
	public function sendMessageToOU()
	{
		$receiverOU = $this->input->post('receiverOU');
		$subject = $this->input->post('subject');
		$body = $this->input->post('body');

		$this->outputJson($this->CLMessagesModel->sendToOrganisationUnit($receiverOU, $subject, $body));
	}

	/**
	 * Sends a message to an organisation unit
	 */
	public function sendMessageReply()
	{
		$receiver_id = $this->input->post('receiver_id');
		$relationmessage_id = $this->input->post('relationmessage_id');
		$token = $this->input->post('token');
		$subject = $this->input->post('subject');
		$body = $this->input->post('body');

		$this->outputJson($this->CLMessagesModel->sendReply($receiver_id, $subject, $body, $relationmessage_id, $token));
	}

	/**
	 * Set a message as read
	 */
	public function setMessageRead()
	{
		$message_id = $this->input->post('message_id');
		$statusPersonId = $this->input->post('statusPersonId');

		$this->outputJson($this->CLMessagesModel->setMessageRead($message_id, $statusPersonId));
	}
}
