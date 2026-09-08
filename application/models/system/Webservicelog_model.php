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
	 * The most viewed contents since a date, with the title of each one.
	 * request_id is a varchar, so the join casts the content_id to text. A cast of
	 * request_id to a number would fail on a row that holds no number.
	 * The join also drops a log row whose content is deleted.
	 * @param string|null $since timestamp in the form Y-m-d H:i:s, null for the whole log
	 * @param string $sprache language of the title
	 * @param int $limit length of the ranking
	 * @return stdClass success with array of rows or error
	 */
	public function getContentClickCounts($since, $sprache, $limit)
	{
		$params = [$sprache, 'content'];
		$sinceCondition = '';

		if ($since !== null)
		{
			$sinceCondition = ' AND w.execute_time >= ?';
			$params[] = $since;
		}

		$params[] = $limit;

		$query = '
			SELECT c.content_id, COUNT(*) AS hits,
				(SELECT titel FROM campus.tbl_contentsprache
				 WHERE content_id = c.content_id AND sprache = ?
				 ORDER BY version LIMIT 1) AS titel
			FROM system.tbl_webservicelog w
				JOIN campus.tbl_content c ON c.content_id::text = w.request_id
			WHERE w.webservicetyp_kurzbz = ?' . $sinceCondition . '
			GROUP BY c.content_id
			ORDER BY COUNT(*) DESC, c.content_id
			LIMIT ?
		';

		return $this->execReadOnlyQuery($query, $params);
	}

	/**
	 * Views of one content since a date. A content outside the ranking still needs its
	 * own number.
	 * @param int $content_id
	 * @param string|null $since timestamp in the form Y-m-d H:i:s, null for the whole log
	 * @return stdClass success with int or error
	 */
	public function getContentClickCount($content_id, $since)
	{
		$query = '
			SELECT COUNT(*) AS hits
			FROM system.tbl_webservicelog
			WHERE webservicetyp_kurzbz = ? AND request_id = ?
		';
		$params = ['content', (string) $content_id];

		if ($since !== null)
		{
			$query .= ' AND execute_time >= ?';
			$params[] = $since;
		}

		$result = $this->execReadOnlyQuery($query, $params);
		if (isError($result))
			return $result;

		return success((int) getData($result)[0]->hits);
	}
}
