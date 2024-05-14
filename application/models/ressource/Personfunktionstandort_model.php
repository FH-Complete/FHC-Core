<?php
class Personfunktionstandort_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_personfunktionstandort';
		$this->pk = 'personfunktionstandort_id';
	}
}
