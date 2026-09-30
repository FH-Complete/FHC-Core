<?php

class Nation_model extends DB_Model
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_nation';
		$this->pk = 'nation_code';
	}

	public function searchNations($filter=null)
	{
		$params = [];

		if ($filter)
		{
			if (preg_match('/%[0-9A-Fa-f]{2}/', $filter)) {
				$filter = urldecode($filter);
			}
			$filter = mb_strtolower($filter, 'UTF-8'); //for umlaute
		}

		$qry = "
			SELECT
				n.nation_code, n.langtext as label, n.kurztext, n.sperre
			FROM
				bis.tbl_nation n";

		if ($filter)
		{
			$escapedFilter = $this->db->escape('%' . $filter . '%');

			$qry .= "
				WHERE
					n.kurztext ILIKE $escapedFilter
					OR n.engltext ILIKE $escapedFilter
					OR n.nation_code ILIKE $escapedFilter
			";
		}

		$qry .= " ORDER BY n.kontinent IS NULL DESC, n.kurztext ASC";

		return $this->execQuery($qry, $params);
	}
}
