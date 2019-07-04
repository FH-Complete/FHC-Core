<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class MessageClient extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct(
			array(
				'read' => array('basis/person:r'),
				'write' => array('basis/person:r'),
				'listReceivedMessages' => array('basis/person:r'),
				'listSentMessages' => array('basis/person:r'),
				'sendMessageToOU' => array('basis/person:r'),
				'setMessageRead' => array('basis/person:r')
			)
		);

		// Loads model CLMessagesModel which contains the GUI logic
		$this->load->model('CL/Messages_model', 'CLMessagesModel');
	}

	/**
	 * Starts the GUI used to read all the personal messages
	 */
	public function read()
	{
		// Loads the view to read messages
		$this->load->view('system/messages/ajaxRead');
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
	 * Set a message as read
	 */
	public function setMessageRead()
	{
		$message_id = $this->input->post('message_id');
		$statusPersonId = $this->input->post('statusPersonId');

		$this->outputJson($this->CLMessagesModel->setMessageRead($message_id, $statusPersonId));
	}
}
