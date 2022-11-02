<?php
class Mobilitaet_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_mobilitaet';
		$this->pk = 'mobilitaet_id';
	}
}
