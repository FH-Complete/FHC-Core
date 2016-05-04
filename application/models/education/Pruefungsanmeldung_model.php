<?php
class Pruefungsanmeldung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_pruefungsanmeldung';
		$this->pk = 'pruefungsanmeldung_id';
	}
}
