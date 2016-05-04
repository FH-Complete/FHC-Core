<?php
class Notenschluesseluebung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_notenschluesseluebung';
		$this->pk = array('note', 'uebung_id');
	}
}
