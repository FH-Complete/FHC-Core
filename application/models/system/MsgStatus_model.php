<?php 

if ( ! defined("BASEPATH")) exit("No direct script access allowed");

class MsgStatus_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "public.tbl_msg_status";
		$this->pk = array("message_id", "person_id", "status");
		$this->hasSequence = false;
	}
}