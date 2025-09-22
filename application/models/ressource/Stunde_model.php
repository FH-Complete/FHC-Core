<?php
class Stunde_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_stunde';
		$this->pk = 'stunde';
	}

	/**
	 * $time needs to be of PGSQL TIME format
	 */
	public function getStundeForTime($time) {
		$query = "
			SELECT min(stunde) as stunde FROM (
				SELECT stunde, extract(epoch from (beginn-?)) AS delta FROM lehre.tbl_stunde
				UNION
				SELECT stunde, extract(epoch from (ende-?)) AS delta FROM lehre.tbl_stunde
				) foo WHERE delta>=0
		";

		return $this->execReadOnlyQuery($query, [$time, $time]);
	}
}
