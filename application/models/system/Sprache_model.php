<?php

class Sprache_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_sprache';
		$this->pk = 'sprache';
	}

	/**
	 * @param array		$sprachen
	 * 
	 * @return stdClass
	 */
	public function loadMultiple($sprachen)
	{
		$this->db->where_in('sprache', $sprachen);

		$this->addOrder('index');

		return $this->load();
	}

}
