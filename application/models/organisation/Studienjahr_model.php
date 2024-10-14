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

	/**
	 * Get the current Studienjahr. During the summer term, continue using the previous Studienjahr.
	 *
	 * @param int $days
	 * @return array|stdClass|null
	 */
	public function getLastOrAktStudienjahr($days = 60)
	{
		if (!is_numeric($days))
		{
			$days = 60;
		}

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
	public function getAktOrNextStudienjahr($days = 62)
	{
		if (!is_numeric($days))
		{
			$days = 62;
		}

		$query = '
			SELECT *
			FROM public.tbl_studienjahr
			JOIN public.tbl_studiensemester using(studienjahr_kurzbz)
			WHERE start < NOW() + \'' . $days . ' DAYS\'::INTERVAL
			ORDER by start DESC
			LIMIT 1
		';

		return $this->execQuery($query);
	}
}
