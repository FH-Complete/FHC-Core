<?php
class Mobilitaetstyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_mobilitaetstyp';
		$this->pk = 'mobilitaetstyp_kurzbz';
	}
}
