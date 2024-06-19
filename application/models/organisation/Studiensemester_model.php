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
	 * Get actual Studiensemester.
	 *
	 * @return array
	 */
	public function getAkt()
	{
		return $this->loadWhere(array(
				'start <= ' => 'NOW()',
				'ende >= '  => 'NOW()'
			)
		);
	}

	// Get next study semester
	public function getNext()
	{
		$query = '
			SELECT *
			FROM
				public.tbl_studiensemester
			WHERE
				start > now()
			ORDER BY start
			LIMIT 1;
		';

		return $this->execQuery($query);
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

		$query = 'SELECT studiensemester_kurzbz, start, ende
					FROM public.tbl_studiensemester
				   WHERE start < NOW() - \'' . $days . ' DAYS\'::INTERVAL
				ORDER BY start DESC
				   LIMIT 1';

		return $this->execQuery($query);
	}

	/**
	 * getNextOrAktSemester
	 * 62 days - in july and august, semester after summer is returned
	 */
	public function getAktOrNextSemester($days = 62)
	{
		if (!is_numeric($days))
		{
			$days = 62;
		}

		$query = 'SELECT studiensemester_kurzbz, start, ende
					FROM public.tbl_studiensemester
					WHERE start < NOW() + \'' . $days . ' DAYS\':: INTERVAL
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
				   WHERE start >= (
									SELECT ende
									  FROM public.tbl_studiensemester
									 WHERE studiensemester_kurzbz = ?
								)
				ORDER BY start
				   LIMIT 1';

		return $this->execQuery($query, array($studiensemester_kurzbz));
	}

	/**
	 * getPreviousFrom
	 */
	public function getPreviousFrom($studiensemester_kurzbz, $limit = 1)
	{
		$query = 'SELECT studiensemester_kurzbz,
						 start,
						 ende
					FROM public.tbl_studiensemester
				   WHERE ende <= (
									SELECT start
									  FROM public.tbl_studiensemester
									 WHERE studiensemester_kurzbz = ?
								)
				ORDER BY start DESC
				   LIMIT ?';

		return $this->execQuery($query, array($studiensemester_kurzbz, $limit));
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

		$query .= ' ORDER BY delta, start LIMIT 1';

		return $this->execQuery($query);
	}

	/**
	 * Gets valid Ausbildungssemester of a Studiensemester with a Studiengang
	 * @param $studiensemester_kurzbz
	 * @param $studiengang_kz
	 * @return array|null
	 */
	public function getAusbildungssemesterByStudiensemesterAndStudiengang($studiensemester_kurzbz, $studiengang_kz)
	{
		$query = "SELECT DISTINCT semester
							FROM lehre.tbl_studienplan
							JOIN lehre.tbl_studienordnung USING(studienordnung_id)
							JOIN lehre.tbl_studienplan_semester USING(studienplan_id)
							WHERE tbl_studienplan_semester.studiensemester_kurzbz = ?
							AND tbl_studienordnung.studiengang_kz = ?
							ORDER BY semester";

		return $this->execQuery($query, array($studiensemester_kurzbz, $studiengang_kz));
	}

	/**
	 * Gets all Studiensemester between two dates
	 * @param $from
	 * @param $to
	 * @return array|null
	 */
	public function getByDate($from, $to)
	{
		if (date_format(date_create($from), 'Y-m-d') > (date_format(date_create($to), 'Y-m-d')))
			return success(array());

		$query = "
			SELECT  *
			FROM	public.tbl_studiensemester
			WHERE   ( ?::date < ende AND ?::date > start )
			ORDER BY start DESC";

		return $this->execQuery($query, array($from, $to));
	}

	/**
	 * Liefert das Studiensemester das aktuell am naehesten zu $studiensemester_kurzbz liegt
	 *
	 * @param $studiensemester_kurzbz
	 * @return array | null
	 */
	public function getNearestFrom($studiensemester_kurzbz)
	{
		$query = "SELECT studiensemester_kurzbz, start, ende FROM public.vw_studiensemester
				WHERE studiensemester_kurzbz <> ?
				ORDER BY delta, start LIMIT 1";

		return $this->execQuery($query, array($studiensemester_kurzbz));
	}

	public function getAktAndFutureSemester()
	{
		$query = 'SELECT studiensemester_kurzbz
					FROM public.tbl_studiensemester
					WHERE start >= NOW() OR (start <= NOW() AND ende >= NOW())
					ORDER BY start';

		return $this->execQuery($query);
	}

	/**
	 * Liefert ausgehend von heutigen Datum $plus studiensemester in die Zukunft und $minus Studiensemester in die Vergangenheit
	 *
	 * @param integer $plus Optional. Wieviele Studiensemester in die Zukunft sollen ausgegeben werden. Wenn NULL werden alle zukuenftigen geliefert.
	 * @param integer $minus Optional. Wieviele Studiensemester in die Vergangenheit sollen ausgegeben werden. Wenn NULL werden alle vergangenen geliefert.
	 *
	 * @return stdClass
	 */
	public function addPlusMinus($plus = null, $minus = null)
	{
		$this->addSelect($this->pk);
		$this->addOrder('ende');
		if ($plus)
			$this->addLimit($plus);
		$this->db->where('start >= NOW()', null, false);
		$plus = $this->db->get_compiled_select($this->dbTable);

		$this->addSelect($this->pk);
		$this->addOrder('start', 'DESC');
		if ($minus)
			$this->addLimit($minus);
		$this->db->where('start <= NOW()', null, false);
		$minus = $this->db->get_compiled_select($this->dbTable);
		
		$this->db->where_in($this->pk, '(' . $plus . ') UNION (' . $minus . ')', false);
	}

	/**
	 * Holt letzen zwei Ziffern des Studienjahres von Studiensemester, z.B. 24 für WS2024 und SS2025
	 * @param studiensemester_kurzbz
	 * @return string Studienjahr Nummer
	 */
	public function getStudienjahrNumberFromStudiensemester($studiensemester_kurzbz)
	{
		$studienjahrNumber = mb_substr($studiensemester_kurzbz, 4, 2);
		if (is_numeric($studienjahrNumber) && mb_substr($studiensemester_kurzbz, 0, 2) == 'SS') (int)$studienjahrNumber -= 1;
		return $studienjahrNumber;
	}
}
