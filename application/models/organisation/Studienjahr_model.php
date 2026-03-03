<?php

class Studienjahr_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_studienjahr';
		$this->pk = 'studienjahr_kurzbz';
		$this->hasSequence = false;
	}

	/**
	 * Gets current Studienjahr, as determined by start and enddate of current semester
	 * @return array|null
	 */
	public function getCurrStudienjahr()
	{
		$query = 'SELECT *
					FROM public.tbl_studienjahr
					JOIN public.tbl_studiensemester using(studienjahr_kurzbz)
					WHERE start <= now()
					AND ende >= now()
					ORDER by start DESC
					LIMIT 1';

		return $this->execQuery($query);
	}
	public function getNextStudienjahr()
	{
		$this->addJoin('public.tbl_studiensemester', 'studienjahr_kurzbz');
		$this->addOrder('start');
		$this->addLimit(1);

		return $this->loadWhere(['start >' => 'NOW()']);
	}
	public function getNextFrom($studienjahr_kurzbz)
	{
		$this->addLimit(1);

		return $this->loadWhere([
			'studienjahr_kurzbz >' => $studienjahr_kurzbz
		]);
	}

	/**
	 * Get the current Studienjahr. During the summer term, continue using the previous Studienjahr.
	 *
	 * @param int $days
	 * @return array|stdClass|null
	 */
	public function getLastOrAktStudienjahr($days = 0)
	{
		$days = is_numeric($days) ? $this->escape($days) : 0;

		$query = '
			SELECT *
			FROM public.tbl_studienjahr
			JOIN public.tbl_studiensemester USING (studienjahr_kurzbz)
			WHERE start < NOW() - \'' . $days . ' DAYS\'::INTERVAL
			ORDER by start DESC
			LIMIT 1
		';

		return $this->execQuery($query);
	}

	/**
	 * Get the current Studienjahr. During the summer term, get the upcoming next Studienjahr.
	 *
	 * @param int $days
	 * @return array|stdClass|null
	 */
	public function getAktOrNextStudienjahr($days = 0)
	{
		$days = is_numeric($days) ? $this->escape($days) : 0;

		$query = '
				SELECT * FROM (
					SELECT
						jahr.*, MIN(sem.start) AS beginn,  MAX(sem.ende) AS ende
					FROM
						public.tbl_studienjahr jahr
						JOIN public.tbl_studiensemester sem using(studienjahr_kurzbz)
					GROUP BY
						studienjahr_kurzbz
				) jahre
				WHERE
					ende >= NOW() + \'' . $days . ' DAYS\'::INTERVAL
				ORDER BY
					ende
				LIMIT 1
		';

		return $this->execQuery($query);
	}
}
