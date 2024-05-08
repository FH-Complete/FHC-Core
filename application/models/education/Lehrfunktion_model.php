<?php
class Lehrfunktion_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lehrfunktion';
		$this->pk = 'lehrfunktion_kurzbz';
	}
}
