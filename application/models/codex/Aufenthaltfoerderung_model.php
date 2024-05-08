<?php
class Aufenthaltfoerderung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_aufenthaltfoerderung';
		$this->pk = 'aufenthaltfoerderung_code';
	}
}
