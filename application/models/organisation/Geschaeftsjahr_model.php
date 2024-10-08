<?php
class Geschaeftsjahr_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_geschaeftsjahr';
		$this->pk = 'geschaeftsjahr_kurzbz';
	}

	/**
	 * Gets current Geschaeftsjahr, as determined by its start date
	 * @return array|null
	 */
	public function getCurrGeschaeftsjahr()
	{
		$query = 'SELECT *
					FROM public.tbl_geschaeftsjahr
					WHERE start <= CURRENT_DATE
					AND ende >= CURRENT_DATE
					ORDER BY start DESC
					LIMIT 1';

		return $this->execQuery($query);
	}

	/**
	 * Gets next Geschaeftsjahr, as determined by its start date
	 * @return array|null
	 */
	public function getNextGeschaeftsjahr($offsetDays=null)
	{
		$query = 'SELECT *
					FROM public.tbl_geschaeftsjahr WHERE ';

		if(!is_null($offsetDays))
		{
			$query .= "start > now() - '".$offsetDays." days'::interval";
		}
		else
		{
			$query .= 'start > now()';
		}
		$query .= '
					ORDER BY start
					LIMIT 1';

		return $this->execQuery($query);
	}
}
