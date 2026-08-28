<?php

class Webservicelog_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->dbTable = 'system.tbl_webservicelog';
		$this->pk = 'webservicelog_id';
	}

	/**
	 * Counts the content views per content since a date.
	 * @param string|null $since timestamp in the form Y-m-d H:i:s, null for the whole log
	 * @return stdClass success with array of rows or error
	 */
	public function getContentClickCounts($since = null)
	{
		$query = '
			SELECT request_id, COUNT(*) AS hits
			FROM system.tbl_webservicelog
			WHERE webservicetyp_kurzbz = ?
		';
		$params = ['content'];

		if ($since !== null)
		{
			$query .= ' AND execute_time >= ?';
			$params[] = $since;
		}

		$query .= ' GROUP BY request_id';

		return $this->execReadOnlyQuery($query, $params);
	}
}
