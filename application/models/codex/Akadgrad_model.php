<?php
class Akadgrad_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_akadgrad';
		$this->pk = 'akadgrad_id';
	}
}
