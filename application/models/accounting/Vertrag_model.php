<?php
class Vertrag_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_vertrag';
		$this->pk = 'vertrag_id';
	}
}
