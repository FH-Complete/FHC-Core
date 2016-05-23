<?php
class Thread_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_msg_thread';
		$this->pk = 'thread_id';
	}
}
