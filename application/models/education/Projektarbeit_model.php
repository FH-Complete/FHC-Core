<?php
class Projektarbeit_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_projektarbeit';
		$this->pk = 'projektarbeit_id';
	}
}
