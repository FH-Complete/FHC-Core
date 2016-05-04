<?php
class Benutzerfunktion_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_benutzerfunktion';
		$this->pk = 'benutzerfunktion_id';
	}
}
