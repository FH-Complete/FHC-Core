<?php
class Bisioaufenthaltfoerderung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_bisio_aufenthaltfoerderung';
		$this->pk = array('bisio_id', 'aufenthaltfoerderung_code');
		$this->hasSequence = false;
	}
}
