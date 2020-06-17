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
}
