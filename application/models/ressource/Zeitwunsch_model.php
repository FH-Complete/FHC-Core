<?php
class Zeitwunsch_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_zeitwunsch';
		$this->pk = array('tag', 'mitarbeiter_uid', 'stunde');
	}
}
