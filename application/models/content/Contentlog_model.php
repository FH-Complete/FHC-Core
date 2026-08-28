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
	 * The open lock entry of a user (ende IS NULL). Returns null if none exists.
	 * Match the holder and take the newest, otherwise the age belongs to an abandoned entry.
	 * @param int $contentsprache_id
	 * @param string $uid holder of the lock
	 * @return stdClass success with row or null, or error
	 */
	public function getOpenLock($contentsprache_id, $uid)
	{
		$query = '
			SELECT contentlog_id, uid, contentsprache_id, start
			FROM campus.tbl_contentlog
			WHERE contentsprache_id = ? AND uid = ? AND ende IS NULL
			ORDER BY start DESC
			LIMIT 1
		';

		$result = $this->execReadOnlyQuery($query, [$contentsprache_id, $uid]);
		if (isError($result))
			return $result;

		$data = getData($result);
		return success(empty($data) ? null : $data[0]);
	}

	/**
	 * Ends every open lock entry of a version. Without a uid it ends the entry of every
	 * holder, which is what a take-over and a forced release need.
	 * @param int $contentsprache_id
	 * @param string|null $uid
	 * @return stdClass
	 */
	public function closeOpenEntries($contentsprache_id, $uid = null)
	{
		$query = 'UPDATE campus.tbl_contentlog SET ende = now()
			WHERE contentsprache_id = ? AND ende IS NULL';
		$params = [$contentsprache_id];

		if ($uid !== null)
		{
			$query .= ' AND uid = ?';
			$params[] = $uid;
		}

		return $this->execQuery($query, $params);
	}

	/**
	 * @param int $contentsprache_id
	 * @return stdClass
	 */
	public function deleteByContentsprache($contentsprache_id)
	{
		return $this->execQuery(
			'DELETE FROM campus.tbl_contentlog WHERE contentsprache_id = ?',
			[$contentsprache_id]
		);
	}

	/**
	 * @param int $content_id
	 * @return stdClass
	 */
	public function deleteByContent($content_id)
	{
		return $this->execQuery(
			'DELETE FROM campus.tbl_contentlog WHERE contentsprache_id IN
				(SELECT contentsprache_id FROM campus.tbl_contentsprache WHERE content_id = ?)',
			[$content_id]
		);
	}
}
