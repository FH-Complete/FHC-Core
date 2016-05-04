<?php
class Firmentyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_firmentyp';
		$this->pk = 'firmentyp_kurzbz';
	}
}
