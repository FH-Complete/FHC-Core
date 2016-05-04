<?php
class Archiv_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_archiv';
		$this->pk = 'archiv_id';
	}
}
