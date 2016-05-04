<?php
class Lehrmittel_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lehrmittel';
		$this->pk = 'lehrmittel_kurzbz';
	}
}
