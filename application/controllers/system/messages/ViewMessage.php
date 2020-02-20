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

		// Phrases used in loaded views
		$this->loadPhrases(
			array(
				'global',
				'ui'
			)
		);
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
			$this->load->view('system/messages/htmlMessageSentSuccess');
		}
		else
		{
			$this->load->view('system/messages/htmlMessageSentError');
		}
	}

	/**
	 * With the given token redirects the user to reply page configured in the config/message.php file
	 */
	public function redirectByToken($token)
	{
		// Loads model MessageTokenModel
		$this->load->model('system/MessageToken_model', 'MessageTokenModel');

		// Retrieves the single message data using the given token
		$msg = $this->MessageTokenModel->getMessageByToken($token);
		// If it is an error or it does not contain data show an error
		if (!hasData($msg)) show_error('MSG-ERR-0001: An error occurred while redirecting, please contact the administrator');
		// else
		$oe_kurzbz = getData($msg)[0]->oe_kurzbz;

		$organisationRoot = null; // by default is null

		// If an organisation unit is present in the message tries to retrieve the root organisation unit
		// from the one found in the message
		if (!isEmptyString($oe_kurzbz))
		{
			// Retrieves the root organisation unit from the one found in the message
			$getOERoot = $this->MessageTokenModel->getOERoot($oe_kurzbz);
			// If it is an error or it does not contain data show an error
			if (!hasData($getOERoot)) show_error('MSG-ERR-0002: An error occurred while redirecting, please contact the administrator');
			// else
			$organisationRoot = getData($getOERoot)[0]->oe_kurzbz;
		}

		// Retrieves the possible redirecting URLs array from configs
		$messageRedirectUrls = $this->config->item('message_redirect_url');
		// If it is not a valid array then show an error
		if (isEmptyArray($messageRedirectUrls)) show_error('MSG-ERR-0003: An error occurred while redirecting, please contact the administrator');

		// If this organisation unit root is not configured as an entry in the possible redirecting URLs array,
		// then tries to use the default one...
		if (!isset($messageRedirectUrls[$organisationRoot]))
		{
			$organisationRoot = 'fallback';

			// ...if even the default one is not present show an error
			if (!isset($messageRedirectUrls[$organisationRoot]))
			{
				show_error('MSG-ERR-0004: An error occurred while redirecting, please contact the administrator');
			}
		}

		// Finally if everything was right then the user can be redirected
		redirect($messageRedirectUrls[$organisationRoot] . '?token=' . $token);
	}
}
