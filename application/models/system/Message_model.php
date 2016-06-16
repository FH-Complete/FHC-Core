<?php 
if ( ! defined('BASEPATH')) 
	exit('No direct script access allowed');

class Message_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		require_once APPPATH.'config/message.php';
		//$this->lang->load('message');
		$this->dbTable = 'public.tbl_msg_message';
		$this->pk = 'message_id';
	}
	
}
/* end of file Message_model.php */
