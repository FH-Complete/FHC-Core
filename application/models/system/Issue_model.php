<?php
class Issue_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_issue';
		$this->pk = 'issue_id';
	}

	/**
	 * Gets issues which are open, i.e. not resolved.
	 * @param array $fehlercodes only issues for given fehler are retrieved
	 * @param int $person_id
	 * @param string $oe_kurzbz
	 * @param string $fehlercode_extern
	 * @return object success with issues or error
	 */
	public function getOpenIssues($fehlercodes, $person_id = null, $oe_kurzbz = null, $fehlercode_extern = null)
	{
		$params = array();
		// issue exists for a fehlercode (or fehlercode_extern), person_id, oe_kurzbz, if not verarbeitet yet
		$qry = 'SELECT
					iss.issue_id, iss.fehlercode, fe.fehler_kurzbz, iss.inhalt, iss.fehlercode_extern,
					iss.inhalt_extern, iss.person_id, iss.oe_kurzbz, iss.behebung_parameter,
					iss.datum, iss.verarbeitetvon, iss.verarbeitetamum
				FROM
					system.tbl_issue iss
					JOIN system.tbl_fehler fe USING (fehlercode)
				WHERE
					verarbeitetamum IS NULL';

		if (!isEmptyArray($fehlercodes))
		{
			$qry .= ' AND fehlercode IN ?';
			$params[] = $fehlercodes;
		}

		if (!isEmptyString($fehlercode_extern))
		{
			$qry .= ' AND fehlercode_extern = ?';
			$params[] = $fehlercode_extern;
		}

		if (isset($person_id))
		{
			$qry .= ' AND person_id = ?';
			$params[] = $person_id;
		}

		if (isset($oe_kurzbz))
		{
			$qry .= ' AND oe_kurzbz = ?';
			$params[] = $oe_kurzbz;
		}

		return $this->execQuery($qry, $params);
	}

	/**
	 * Gets number of open (non-resolved) issues.
	 * @param string $fehlercode unique error code
	 * @param int $person_id if provided, only issues with this person_id are counted.
	 * @param string $oe_kurzbz if provided, only issues with this oe_kurzbz are counted.
	 * @param string $fehlercode_extern if provided, only issues with this external fehlercode are counted (for identifying issues from external systems).
	 * @return Object success with number of issues or error
	 */
	public function getOpenIssueCount($fehlercode, $person_id = null, $oe_kurzbz = null, $fehlercode_extern = null, $behebung_parameter = null)
	{
		$params = array($fehlercode);
		// issue exists for a fehlercode (or fehlercode_extern), person_id, oe_kurzbz, if not verarbeitet yet
		$qry = 'SELECT count(*) as anzahl_open_issues FROM system.tbl_issue
				WHERE fehlercode = ?
				AND verarbeitetamum IS NULL';

		if (!isEmptyString($fehlercode_extern))
		{
			$qry .= ' AND fehlercode_extern = ?';
			$params[] = $fehlercode_extern;
		}

		if (isset($person_id))
		{
			$qry .= ' AND person_id = ?';
			$params[] = $person_id;
		}

		if (isset($oe_kurzbz))
		{
			$qry .= ' AND oe_kurzbz = ?';
			$params[] = $oe_kurzbz;
		}

		if (isset($behebung_parameter) && !isEmptyArray($behebung_parameter))
		{
			// convert array to JSON string for postgres
			$behebung_parameter_string = json_encode($behebung_parameter);

			if ($behebung_parameter_string)
			{
				// check if jsonb value is equal to the passed parameters array (if value contains array and array contains value)
				$qry .= ' AND behebung_parameter @> ? AND behebung_parameter <@ ?';
				$params = array_merge($params, array($behebung_parameter_string, $behebung_parameter_string));
			}
		}

		return $this->execQuery($qry, $params);
	}
}
