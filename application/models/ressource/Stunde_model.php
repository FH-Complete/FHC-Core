<?php
class Stunde_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_stunde';
		$this->pk = 'stunde';
	}
}
