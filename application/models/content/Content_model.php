<?php
class Content_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_content';
		$this->pk = 'content_id';
	}
}
