<?php
class Vorlagestudiengang_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_vorlagestudiengang';
		$this->pk = 'vorlagestudiengang_id';
	}
}
