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
 * NOTE: it extends FHC_Controller instead of Auth_Controller because authentication is not needed
 */
class ViewMessage extends FHC_Controller
{
	public function __construct()
	{
		parent::__construct();

		// Loading config file message
		$this->config->load('message');

		// Load model MessageToken_model, not calling the authentication system
		$this->load->model('CL/Messages_model', 'CLMessagesModel');
	}

	/**
	 * Display a message in read mode only using the specified token
	 */
	public function toHTML($token)
	{
		// Loads the view to read a received message using its token as identifier
		$this->load->view('system/messages/htmlRead', $this->CLMessagesModel->prepareHtmlRead($token));
	}

	/**
	 * Write a reply message to a received one using its token as identifier
	 */
	public function writeReply()
	{
		$token = $this->input->get('token'); // gets received message token

		// Loads the view to write a reply message
		$this->load->view('system/messages/htmlWriteReply', $this->CLMessagesModel->prepareHtmlWriteReply($token));
	}

	/**
	 * Send a reply message (no templates are used)
	 */
	public function sendReply()
	{
		$subject = $this->input->post('subject');
		$body = $this->input->post('body');
		$receiver_id = $this->input->post('receiver_id');
		$relationmessage_id = $this->input->post('relationmessage_id');
		$token = $this->input->post('token');

		$sendReply = $this->CLMessagesModel->sendReply($receiver_id, $subject, $body, $relationmessage_id, $token);
		if (isSuccess($sendReply))
		{
			$this->load->view('system/messages/htmlSuccess');
		}
		else
		{
			$this->load->view('system/messages/htmlError');
		}
	}
}
