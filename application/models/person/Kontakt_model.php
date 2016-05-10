<?php
class Kontakt_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_kontakt';
		$this->pk = 'kontakt_id';
	}
}