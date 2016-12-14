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
		
		// Load model MessageToken_model
		$this->load->model('system/MessageToken_model', 'MessageTokenModel');
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
			$data = array (
				'message' => $msg->retval[0]
			);
			
			$this->load->view('system/messageHTML.php', $data);
		}
	}
}