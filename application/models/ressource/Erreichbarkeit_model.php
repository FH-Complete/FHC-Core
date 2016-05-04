<?php
class Erreichbarkeit_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_erreichbarkeit';
		$this->pk = 'erreichbarkeit_kurzbz';
	}
}
