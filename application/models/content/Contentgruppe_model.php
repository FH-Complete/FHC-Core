<?php
class Contentgruppe_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_contentgruppe';
		$this->pk = array('gruppe_kurzbz', 'content_id');
	}
}
