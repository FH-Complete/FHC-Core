<?php
class Contentsprache_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_contentsprache';
		$this->pk = 'contentsprache_id';
	}
}
