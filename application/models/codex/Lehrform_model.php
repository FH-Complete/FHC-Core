<?php
class Lehrform_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lehrform';
		$this->pk = 'lehrform_kurzbz';
	}
}
