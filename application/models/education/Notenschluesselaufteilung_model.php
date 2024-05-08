<?php
class Notenschluesselaufteilung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_notenschluesselaufteilung';
		$this->pk = 'notenschluesselaufteilung_id';
	}
}
