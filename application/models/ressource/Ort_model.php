<?php
class Ort_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_ort';
		$this->pk = 'ort_kurzbz';
	}
}
