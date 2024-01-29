<?php
class Zgvgruppe_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_zgvgruppe';
		$this->pk = 'gruppe_kurzbz';
	}
}