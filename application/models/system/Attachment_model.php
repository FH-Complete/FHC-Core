<?php 
if ( ! defined('BASEPATH')) 
	exit('No direct script access allowed');

class Attachment_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_msg_attachment';
		$this->pk = 'attachment_id';
	}
}
