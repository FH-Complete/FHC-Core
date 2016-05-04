<?php
class Ortraumtyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_ortraumtyp';
		$this->pk = array('hierarchie', 'ort_kurzbz');
	}
}
