<?php
class Contentlog_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_contentlog';
		$this->pk = 'contentlog_id';
	}
}
