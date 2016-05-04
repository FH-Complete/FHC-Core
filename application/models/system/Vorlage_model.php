<?php
class Vorlage_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_vorlage';
		$this->pk = 'vorlage_kurzbz';
	}
}
