<?php
class Zeitfenster_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_zeitfenster';
		$this->pk = array('wochentag', 'studiengang_kz', 'ort_kurzbz', 'stunde');
	}
}
