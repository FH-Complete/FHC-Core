<?php
class Lehreinheitgruppe_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lehreinheitgruppe';
		$this->pk = 'lehreinheitgruppe_id';
	}

	public function getDirectGroup($lehreinheit_id)
	{
		$this->addJoin('public.tbl_gruppe', 'gruppe_kurzbz');
		return $this->loadWhere(array(
			'tbl_gruppe.direktinskription' => true,
			'lehreinheit_id' => $lehreinheit_id
			)
		);
	}
}
