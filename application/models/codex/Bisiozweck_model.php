<?php
class Bisiozweck_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_bisio_zweck';
		$this->pk = array('bisio_id', 'zweck_code');
		$this->hasSequence = false;
	}
}
