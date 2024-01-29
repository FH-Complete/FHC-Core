<?php
class Scrumsprint_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'fue.tbl_scrumsprint';
		$this->pk = 'scrumsprint_id';
	}
}
