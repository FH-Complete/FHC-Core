<?php
class Notiztyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_notiz_typ';
		$this->pk = 'typ_kurzbz';
	}
}
