<?php
class Bundesland_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_bundesland';
		$this->pk = 'bundesland_code';
	}
}
