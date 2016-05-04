<?php
class Notizzuordnung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_notizzuordnung';
		$this->pk = 'notizzuordnung_id';
	}
}
