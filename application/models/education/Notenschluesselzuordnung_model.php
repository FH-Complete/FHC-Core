<?php
class Notenschluesselzuordnung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_notenschluesselzuordnung';
		$this->pk = 'notenschluesselzuordnung_id';
	}
}
