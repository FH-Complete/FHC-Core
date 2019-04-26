<?php
class Organisationseinheittyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_organisationseinheittyp';
		$this->pk = 'organisationseinheittyp_kurzbz';
	}
}
