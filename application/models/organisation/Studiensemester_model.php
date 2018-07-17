<?php

class Studiensemester_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_studiensemester';
		$this->pk = 'studiensemester_kurzbz';
		$this->hasSequence = false;
	}

	/**
	 * getLastOrAktSemester
	 */
	public function getLastOrAktSemester($days = 60)
	{
		if (!is_numeric($days))
		{
			$days = 60;
		}

		$query = 'SELECT studiensemester_kurzbz
					FROM public.tbl_studiensemester
				   WHERE start < NOW() - \'' . $days . ' DAYS\'::INTERVAL
				ORDER BY start DESC
				   LIMIT 1';

		return $this->execQuery($query);
	}

	/**
	 * getNextFrom
	 */
	public function getNextFrom($studiensemester_kurzbz)
	{
		$query = 'SELECT studiensemester_kurzbz,
						 start,
						 ende
					FROM public.tbl_studiensemester
				   WHERE start > (
									SELECT ende
									  FROM public.tbl_studiensemester
									 WHERE studiensemester_kurzbz = ?
								)
				ORDER BY start
				   LIMIT 1';

		return $this->execQuery($query, array($studiensemester_kurzbz));
	}

	/**
	 * getNearest
	 */
	public function getNearest($semester = '')
	{
		$query = 'SELECT studiensemester_kurzbz,
						 start,
						 ende
					FROM public.vw_studiensemester';

		if (is_numeric($semester))
		{
			if ($semester % 2 == 0)
			{
				$ss = 'SS';
			}
			else
			{
				$ss = 'WS';
			}

			$query .= ' WHERE SUBSTRING(studiensemester_kurzbz FROM 1 FOR 2) = \'' . $ss . '\'';
		}

		$query .= ' ORDER BY delta LIMIT 1';

		return $this->execQuery($query);
	}
}
