<?php
class Projektphase_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'fue.tbl_projektphase';
		$this->pk = 'projektphase_id';
	}
}
