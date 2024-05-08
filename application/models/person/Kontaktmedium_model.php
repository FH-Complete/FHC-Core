<?php
class Kontaktmedium_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_kontaktmedium';
		$this->pk = 'kontaktmedium_kurzbz';
	}
}
