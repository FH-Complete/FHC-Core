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
		// issue exists for a fehlercode (or fehlercode_extern), person_id, oe_kurzbz, if not verarbeitet yet
		return $this->_getIssues(
			$person_id,
			$oe_kurzbz,
			$fehlertyp_kurzbz = null,
			$apps = null,
			$ist_verarbeitet = false,
			$behebung_parameter = null,
			$fehlercodes,
			$fehlercode_extern
		);
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

		if (isset($person_id) && is_numeric($person_id))
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

	/**
	 * Gets issues which are open, i.e. not resolved.
	 * @param int $person_id
	 * @param string $oe_kurzbz
	 * @param string $fehlertyp_kurzbz
	 * @param string|array $apps
	 * @param array $behebung_parameter

	 * @return object success with issues or error
	 */
	public function getOpenIssuesByProperties(
		$person_id = null,
		$oe_kurzbz = null,
		$fehlertyp_kurzbz = null,
		$apps = null,
		$behebung_parameter = null
	) {
		return $this->_getIssues($person_id, $oe_kurzbz, $fehlertyp_kurzbz, $apps, $ist_verarbeitet = false, $behebung_parameter);
	}

	/**
	 * Gets issues which are open, i.e. not resolved.
	 * @param int $person_id
	 * @param string $oe_kurzbz
	 * @param string $fehlertyp_kurzbz
	 * @param array $apps only issues for given apps are retrieved
	 * @param bool $ist_verarbeitet wether the issue has already been resolved
	 * @param array $behebung_parameter
	 * @param array $fehlercodes
	 * @param string $fehlercode_extern
	 * @return object success with issues or error
	 */
	private function _getIssues(
		$person_id = null,
		$oe_kurzbz = null,
		$fehlertyp_kurzbz = null,
		$apps = null,
		$ist_verarbeitet = null,
		$behebung_parameter = null,
		$fehlercodes = null,
		$fehlercode_extern = null
	) {
		$params = array();

		$qry = 'SELECT
					iss.issue_id, iss.fehlercode, fe.fehler_kurzbz, iss.inhalt, iss.fehlercode_extern,
					iss.inhalt_extern, iss.person_id, iss.oe_kurzbz, iss.behebung_parameter,
					iss.datum, iss.verarbeitetvon, iss.verarbeitetamum
				FROM
					system.tbl_issue iss
					JOIN system.tbl_fehler fe USING (fehlercode)
				WHERE
					TRUE';

		if (isset($person_id) && is_numeric($person_id))
		{
			$qry .= ' AND person_id = ?';
			$params[] = $person_id;
		}

		if (isset($oe_kurzbz))
		{
			$qry .= ' AND oe_kurzbz = ?';
			$params[] = $oe_kurzbz;
		}

		if (isset($fehlertyp_kurzbz))
		{
			$qry .= ' AND fehlertyp_kurzbz = ?';
			$params[] = $fehlertyp_kurzbz;
		}

		if (isset($apps))
		{
			if (is_string($apps)) $apps = [$apps];

			if (is_array($apps))
			{
				$qry .= ' AND app IN ?';
				$params[] = $apps;
			}
		}

		if (is_bool($ist_verarbeitet))
		{
			$qry .= $ist_verarbeitet ? ' AND verarbeitetamum IS NOT NULL' : ' AND verarbeitetamum IS NULL';
		}

		if (!isEmptyArray($behebung_parameter))
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

		if (!isEmptyArray($fehlercodes))
		{
			$qry .= ' AND fehlercode IN ?';
			$params[] = $fehlercodes;
		}

		if (isset($fehlercode_extern))
		{
			$qry .= ' AND fehlercode_extern = ?';
			$params[] = $fehlercode_extern;
		}

		return $this->execQuery($qry, $params);
	}
}
