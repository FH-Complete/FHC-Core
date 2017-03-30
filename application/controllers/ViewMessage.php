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

		// Load model MessageToken_model
		$this->load->model('system/MessageToken_model', 'MessageTokenModel');
		$this->load->model('person/Person_model', 'PersonModel');
	}

	public function toHTML($token)
	{
		$msg = $this->MessageTokenModel->getMessageByToken($token);

		if ($msg->error)
		{
			show_error($msg->retval);
		}

		if (is_array($msg->retval) && count($msg->retval) > 0)
		{
			$sender_id = $msg->retval[0]->sender_id;
			$sender = $this->PersonModel->load($sender_id);
			$data = array (
				'sender_id' => $sender_id,
				'sender' => $sender->retval[0],
				'message' => $msg->retval[0],
				'href' => APP_ROOT . $this->config->item('redirect_view_message_url') . $token
			);

			$this->load->view('system/messageHTML.php', $data);
		}
	}
}
