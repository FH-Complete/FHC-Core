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
	 * Gets number of open (non-resolved) issues.
	 * @param string $fehlercode unique error code
	 * @param int $person_id if provided, only issues with this person_id are counted.
	 * @param string $oe_kurzbz if provided, only issues with this oe_kurzbz are counted.
	 * @param string $fehlercode_extern if provided, only issues with this external fehlercode are counted (for identifying issues from external systems).
	 * @return Object success with number of issues or error
	 */
	public function getOpenIssueCount($fehlercode, $person_id = null, $oe_kurzbz = null, $fehlercode_extern = null)
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

		return $this->execQuery($qry, $params);
	}
}
