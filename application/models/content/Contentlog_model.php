<?php
class Contentlog_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_contentlog';
		$this->pk = 'contentlog_id';
	}

	/**
	 * The open lock entry (ende IS NULL). Returns null if none exists.
	 * @param int $contentsprache_id
	 * @return stdClass success with row or null, or error
	 */
	public function getOpenLock($contentsprache_id)
	{
		$query = '
			SELECT contentlog_id, uid, contentsprache_id, start
			FROM campus.tbl_contentlog
			WHERE contentsprache_id = ? AND ende IS NULL
			LIMIT 1
		';

		$result = $this->execReadOnlyQuery($query, [$contentsprache_id]);
		if (isError($result))
			return $result;

		$data = getData($result);
		return success(empty($data) ? null : $data[0]);
	}
}
