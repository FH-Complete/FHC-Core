<?php
class Raumtyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_raumtyp';
		$this->pk = 'raumtyp_kurzbz';
	}
}
