<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class MessageClient extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct(
			array(
				'read' => array('basis/message:r'),
				'listMessages' => array('basis/message:r')
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
	 * Returns JSON that that contains all the received messages by the currently logged user
	 * This JSON structure is nested data used by tabulator
	 */
	public function listMessages()
	{
		$jsonNestedData = $this->CLMessagesModel->prepareAjaxRead();

		$this->outputJson($jsonNestedData);
	}
}
