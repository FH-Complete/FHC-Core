<?php
class Ferientyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_ferientyp';
		$this->pk = 'ferientyp_kurzbz';
		$this->hasSequence = false;
	}
}
