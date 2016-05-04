<?php
class Abschlusspruefung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_abschlusspruefung';
		$this->pk = 'abschlusspruefung_id';
	}
}
