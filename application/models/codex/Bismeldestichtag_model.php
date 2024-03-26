<?php
class Bismeldestichtag_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_bismeldestichtag';
		$this->pk = 'meldestichtag_id';
	}

	/**
	 * Gets last Bismeldestichtag for a Studiensemester.
	 * @param $studiensemester_kurzbz
	 * @return object success or error
	 */
	public function getByStudiensemester($studiensemester_kurzbz)
	{
		$query = '
				SELECT
					meldestichtag
				FROM
					bis.tbl_bismeldestichtag
					JOIN public.tbl_studiensemester USING (studiensemester_kurzbz)
				WHERE
					studiensemester_kurzbz = ?
				ORDER BY meldestichtag DESC
				LIMIT 1';

		return $this->execQuery($query, array($studiensemester_kurzbz));
	}
}
