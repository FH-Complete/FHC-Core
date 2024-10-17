<?php
class Adressentyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_adressentyp';
		$this->pk = 'adressentyp_kurzbz';
	}
}
