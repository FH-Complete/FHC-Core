<?php
class Lvangebot_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lvangebot';
		$this->pk = 'lvangebot_id';
	}
}
