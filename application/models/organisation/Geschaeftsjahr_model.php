<?php
class Geschaeftsjahr_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_geschaeftsjahr';
		$this->pk = 'geschaeftsjahr_kurzbz';
	}
}
