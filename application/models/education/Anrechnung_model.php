<?php
class Anrechnung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_anrechnung';
		$this->pk = 'anrechnung_id';
	}
}
