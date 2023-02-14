<?php
class Zeitaufzeichnung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_zeitaufzeichnung';
		$this->pk = 'zeitaufzeichnung_id';
	}

	public function deleteEntriesForCurrentDay()
	{
		$today = date('Y-m-d');
		$qry = "DELETE FROM " . $this->dbTable . "
			WHERE start >= '" . $today . " 00:00:00'
			AND start <= '" . $today . " 23:59:59';";

			return $this->execQuery($qry);
	}

	/**
	 * Get list of Persons with Timerecordings within the last week
	 */
	public function zeitaufzeichnungExistsForLastWeekList()
	{
		$qry = "SELECT
					DISTINCT uid
				FROM campus.tbl_zeitaufzeichnung
				WHERE date_trunc('day',ende) >= (date_trunc('day', current_date-interval '7' day));";

		return $this->execQuery($qry);

	}
}
