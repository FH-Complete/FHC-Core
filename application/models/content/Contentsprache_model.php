<?php
class Contentsprache_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_contentsprache';
		$this->pk = 'contentsprache_id';
	}

	/**
	 * Prueft ob der Content in der angegeben Sprache vorhanden ist
	 * 
	 * @param int				$content_id
	 * @param string			$sprache
	 * @param int | null		$version (optional)
	 * @param boolean | null	$sichtbar (optional)
	 * @return stdClass
	 */
	public function exists($content_id, $sprache, $version=null, $sichtbar=null)
	{
		$condition = ['content_id' => $content_id, 'sprache' => $sprache];

		if ($version)
			$condition['version'] = $version;

		if ($sichtbar !== null)
			$condition['sichtbar'] = $sichtbar;

		$result = $this->loadWhere($condition);
		
		if (isError($result))
			return $result;

		return success(!!getData($result));
	}

	/**
	 * All versions of a language, ascending by version.
	 * @param int $content_id
	 * @param string $sprache
	 * @return stdClass success with array of rows or error
	 */
	public function getVersions($content_id, $sprache)
	{
		$query = '
			SELECT contentsprache_id, version, sichtbar, titel,
				insertamum, insertvon, updateamum, updatevon, gesperrt_uid
			FROM campus.tbl_contentsprache
			WHERE content_id = ? AND sprache = ?
			ORDER BY version ASC
		';

		return $this->execReadOnlyQuery($query, [$content_id, $sprache]);
	}

	/**
	 * Distinct languages for a content. Returns a flat array of strings.
	 * @param int $content_id
	 * @return stdClass success with array of strings or error
	 */
	public function getLanguages($content_id)
	{
		$query = '
			SELECT DISTINCT sprache
			FROM campus.tbl_contentsprache
			WHERE content_id = ?
			ORDER BY sprache
		';

		$result = $this->execReadOnlyQuery($query, [$content_id]);
		if (isError($result))
			return $result;

		$languages = [];
		if (getData($result))
		{
			foreach (getData($result) as $row)
				$languages[] = $row->sprache;
		}

		return success($languages);
	}

	/**
	 * Highest version number. Returns 0 if no row exists.
	 * @param int $content_id
	 * @param string $sprache
	 * @return stdClass success with int or error
	 */
	public function getMaxVersion($content_id, $sprache)
	{
		$query = '
			SELECT COALESCE(MAX(version), 0) AS max_version
			FROM campus.tbl_contentsprache
			WHERE content_id = ? AND sprache = ?
		';

		$result = $this->execReadOnlyQuery($query, [$content_id, $sprache]);
		if (isError($result))
			return $result;

		return success((int) getData($result)[0]->max_version);
	}

	/**
	 * Row count for a content+language. Used for the delete guard.
	 * @param int $content_id
	 * @param string $sprache
	 * @return stdClass success with int or error
	 */
	public function getNumberOfVersions($content_id, $sprache)
	{
		$query = '
			SELECT COUNT(*) AS cnt
			FROM campus.tbl_contentsprache
			WHERE content_id = ? AND sprache = ?
		';

		$result = $this->execReadOnlyQuery($query, [$content_id, $sprache]);
		if (isError($result))
			return $result;

		return success((int) getData($result)[0]->cnt);
	}

	/**
	 * One version, all columns. If $version is empty, returns the highest version.
	 * @param int $content_id
	 * @param string $sprache
	 * @param int|null $version
	 * @return stdClass success with single row or error
	 */
	public function getOne($content_id, $sprache, $version = null)
	{
		$params = [$content_id, $sprache];

		$versionClause = '';
		if (!empty($version))
		{
			$versionClause = ' AND version = ?';
			$params[] = $version;
		}

		$query = '
			SELECT * FROM campus.tbl_contentsprache
			WHERE content_id = ? AND sprache = ?' . $versionClause . '
			ORDER BY version DESC LIMIT 1
		';

		$result = $this->execReadOnlyQuery($query, $params);
		if (isError($result))
			return $result;

		$data = getData($result);
		if (empty($data))
			return error('Version not found');

		return success($data[0]);
	}

	/**
	 * Clears the lock of a version. Without a uid it clears whoever holds it, which is
	 * what a forced release needs.
	 * @param int $contentsprache_id
	 * @param string|null $uid
	 * @return stdClass
	 */
	public function releaseLock($contentsprache_id, $uid = null)
	{
		$query = 'UPDATE campus.tbl_contentsprache SET gesperrt_uid = NULL
			WHERE contentsprache_id = ?';
		$params = [$contentsprache_id];

		if ($uid !== null)
		{
			$query .= ' AND gesperrt_uid = ?';
			$params[] = $uid;
		}

		return $this->execQuery($query, $params);
	}

	/**
	 * @param int $content_id
	 * @return stdClass
	 */
	public function deleteByContent($content_id)
	{
		return $this->execQuery(
			'DELETE FROM campus.tbl_contentsprache WHERE content_id = ?',
			[$content_id]
		);
	}

	/**
	 * @param int $content_id
	 * @param string $sprache
	 * @param int $version
	 * @return stdClass
	 */
	public function deleteVersion($content_id, $sprache, $version)
	{
		return $this->execQuery(
			'DELETE FROM campus.tbl_contentsprache
				WHERE content_id = ? AND sprache = ? AND version = ?',
			[$content_id, $sprache, $version]
		);
	}
}
