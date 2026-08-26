<?php
class Contentchild_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_contentchild';
		$this->pk = 'contentchild_id';
	}

	/**
	 * Children with titel, sorted by sort.
	 * @param int $content_id
	 * @param string $sprache
	 * @return stdClass success with array of rows or error
	 */
	public function getChilds($content_id, $sprache)
	{
		$query = '
			SELECT cc.contentchild_id, cc.child_content_id, cc.sort,
				(SELECT titel FROM campus.tbl_contentsprache
				 WHERE content_id = cc.child_content_id AND sprache = ?
				 ORDER BY version LIMIT 1) AS titel
			FROM campus.tbl_contentchild cc
			WHERE cc.content_id = ?
			ORDER BY cc.sort, cc.contentchild_id
		';

		return $this->execReadOnlyQuery($query, [$sprache, $content_id]);
	}

	/**
	 * Highest sort value. Returns 0 if no row exists.
	 * @param int $content_id
	 * @return stdClass success with int or error
	 */
	public function getMaxSort($content_id)
	{
		$query = '
			SELECT COALESCE(MAX(sort), 0) AS max_sort
			FROM campus.tbl_contentchild
			WHERE content_id = ?
		';

		$result = $this->execReadOnlyQuery($query, [$content_id]);
		if (isError($result))
			return $result;

		return success((int) getData($result)[0]->max_sort);
	}

	/**
	 * All descendant content_ids recursively. Returns a flat array.
	 * @param int $content_id
	 * @return stdClass success with array of ints or error
	 */
	public function getAllChildIds($content_id)
	{
		$query = '
			WITH RECURSIVE childs(content_id, child_content_id) AS (
				SELECT content_id, child_content_id FROM campus.tbl_contentchild WHERE content_id = ?
				UNION ALL
				SELECT cc.content_id, cc.child_content_id
				FROM campus.tbl_contentchild cc, childs
				WHERE cc.content_id = childs.child_content_id)
			SELECT DISTINCT child_content_id FROM childs
		';

		$result = $this->execReadOnlyQuery($query, [$content_id]);
		if (isError($result))
			return $result;

		$ids = [];
		if (getData($result))
		{
			foreach (getData($result) as $row)
				$ids[] = (int) $row->child_content_id;
		}

		return success($ids);
	}

	/**
	 * Swap sort value with the neighbour. Direction is 'up' or 'down'.
	 * @param int $contentchild_id
	 * @param string $direction 'up' or 'down'
	 * @return stdClass success or error
	 */
	public function swapSort($contentchild_id, $direction)
	{
		$currentResult = $this->load($contentchild_id);
		if (isError($currentResult))
			return $currentResult;
		$current = getData($currentResult)[0];

		if ($direction === 'up')
		{
			$neighbourQuery = '
				SELECT contentchild_id, sort
				FROM campus.tbl_contentchild
				WHERE content_id = ? AND sort < ?
				ORDER BY sort DESC
				LIMIT 1
			';
		}
		else
		{
			$neighbourQuery = '
				SELECT contentchild_id, sort
				FROM campus.tbl_contentchild
				WHERE content_id = ? AND sort > ?
				ORDER BY sort ASC
				LIMIT 1
			';
		}

		$neighbourResult = $this->execReadOnlyQuery($neighbourQuery, [$current->content_id, $current->sort]);
		if (isError($neighbourResult))
			return $neighbourResult;

		$neighbourData = getData($neighbourResult);
		if (empty($neighbourData))
			return error($direction === 'up' ? 'cms/bereitsGanzOben' : 'cms/bereitsGanzUnten');

		$neighbour = $neighbourData[0];

		// Transaction wraps the swap; the legacy does not.
		$this->db->trans_start();

		$this->execQuery(
			'UPDATE campus.tbl_contentchild SET sort = ? WHERE contentchild_id = ?',
			[$neighbour->sort, $current->contentchild_id]
		);
		$this->execQuery(
			'UPDATE campus.tbl_contentchild SET sort = ? WHERE contentchild_id = ?',
			[$current->sort, $neighbour->contentchild_id]
		);

		$this->db->trans_complete();

		if ($this->db->trans_status() === false)
			return error('Sort swap failed');

		return success(true);
	}
}
