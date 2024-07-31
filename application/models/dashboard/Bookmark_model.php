<?php
class Bookmark_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'dashboard.tbl_bookmark';
		$this->pk = 'bookmark_id';
	}


	

}
