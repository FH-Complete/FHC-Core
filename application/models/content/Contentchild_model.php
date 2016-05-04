<?php
class Contentchild_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_contentchild';
		$this->pk = 'contentchild_id';
	}
}
