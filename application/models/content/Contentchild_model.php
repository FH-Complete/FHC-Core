<?php
class Contentchild_model extends DB_Model
{
	// swapSort reports the two boundary cases with this. See swapSort().
	const NO_NEIGHBOUR = 'no neighbour';


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
	 * Parents of a content, with the title of each parent.
	 * A content can hang under more than one parent, so this returns a list.
	 * @param int $content_id
	 * @param string $sprache
	 * @return stdClass success with array of rows or error
	 */
	public function getParents($content_id, $sprache)
	{
		$query = '
			SELECT cc.contentchild_id, cc.content_id, cc.sort,
				(SELECT titel FROM campus.tbl_contentsprache
				 WHERE content_id = cc.content_id AND sprache = ?
				 ORDER BY version LIMIT 1) AS titel
			FROM campus.tbl_contentchild cc
			WHERE cc.child_content_id = ?
			ORDER BY cc.content_id
		';

		return $this->execReadOnlyQuery($query, [$sprache, $content_id]);
	}

	/**
	 * Children of a content as a reader may see them.
	 *
	 * Differs from getChilds() on purpose. getChilds() serves the admin and returns every
	 * child with the lowest version. A reader needs the newest visible version, only active
	 * contents, and no page that a group rule keeps from them.
	 *
	 * The group rule follows CmsLib::getContent(): a content without a row in
	 * tbl_contentgruppe is open, a content with rows needs membership in one of them.
	 *
	 * @param int $content_id
	 * @param string $sprache
	 * @param string $uid
	 * @return stdClass success with array of rows or error
	 */
	public function getChildsForReader($content_id, $sprache, $uid)
	{
		$query = '
			SELECT
				cc.child_content_id AS content_id,
				cc.sort,
				COALESCE(
					(SELECT cs.titel FROM campus.tbl_contentsprache cs
						WHERE cs.content_id = cc.child_content_id
							AND cs.sprache = ?
							AND cs.sichtbar
							AND LENGTH(cs.titel) > 0
						ORDER BY cs.version DESC NULLS LAST LIMIT 1),
					(SELECT cs.titel FROM campus.tbl_contentsprache cs
						WHERE cs.content_id = cc.child_content_id
							AND cs.sichtbar
							AND LENGTH(cs.titel) > 0
						ORDER BY cs.version DESC NULLS LAST LIMIT 1),
					c.beschreibung
				) AS titel
			FROM campus.tbl_contentchild cc
				JOIN campus.tbl_content c ON c.content_id = cc.child_content_id
			WHERE cc.content_id = ?
				AND c.aktiv
				-- Without a visible version the reader cannot open the page at all.
				AND EXISTS (
					SELECT 1 FROM campus.tbl_contentsprache cs
					WHERE cs.content_id = cc.child_content_id AND cs.sichtbar
				)
				AND (
					NOT EXISTS (
						SELECT 1 FROM campus.tbl_contentgruppe cg
						WHERE cg.content_id = cc.child_content_id
					)
					OR EXISTS (
						SELECT 1 FROM campus.tbl_contentgruppe cg
							JOIN public.vw_gruppen vg ON vg.gruppe_kurzbz = cg.gruppe_kurzbz
						WHERE cg.content_id = cc.child_content_id AND vg.uid = ?
					)
				)
			ORDER BY cc.sort, cc.contentchild_id
		';

		return $this->execReadOnlyQuery($query, [$sprache, $content_id, $uid]);
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
		// A marker, not a message. The controller owns the wording, because a model has no
		// phrases. Returning a phrase key here put the key itself in front of the user.
		if (empty($neighbourData))
			return error(self::NO_NEIGHBOUR);

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

	/**
	 * Writes the whole order of one content in one transaction. Serves the drag and drop.
	 * The list must hold every child of the content exactly once, or the rows left out
	 * would keep a stale sort value.
	 * @param int $content_id
	 * @param array $contentchild_ids in the wanted order
	 * @return stdClass success(true) or error
	 */
	public function setSortOrder($content_id, $contentchild_ids)
	{
		$currentResult = $this->execReadOnlyQuery(
			'SELECT contentchild_id FROM campus.tbl_contentchild WHERE content_id = ?',
			[$content_id]
		);
		if (isError($currentResult))
			return $currentResult;

		$current = [];
		foreach ((array) getData($currentResult) as $row)
			$current[] = (int) $row->contentchild_id;

		$wanted = array_map('intval', array_values($contentchild_ids));

		$check = $wanted;
		sort($current);
		sort($check);
		if ($current !== $check)
			return error('Sort order does not match the children of the content');

		$this->db->trans_start();

		foreach ($wanted as $position => $contentchild_id)
		{
			$this->execQuery(
				'UPDATE campus.tbl_contentchild SET sort = ? WHERE contentchild_id = ?',
				[$position + 1, $contentchild_id]
			);
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === false)
			return error('Sort order update failed');

		return success(true);
	}

	/**
	 * Every ancestor of the given contents, with the distance to the content it belongs to.
	 *
	 * The tree shows a search result out of its place: a title like "Team" says nothing
	 * about which unit it belongs to. This delivers the way down to it.
	 *
	 * A content can hang under more than one parent, so a level can hold more than one
	 * ancestor. Every one of them comes back, with tiefe 1 for the direct parent. The
	 * caller decides how to draw that.
	 *
	 * tiefe also ends a cycle: a content whose parent chain leads back to itself would
	 * otherwise recurse forever.
	 *
	 * @param array $content_ids contents to walk up from
	 * @param int $maxTiefe how many levels to climb
	 * @param string $sprache language of the ancestor titles
	 * @return stdClass success with array of rows or error
	 */
	public function getAncestors($content_ids, $maxTiefe, $sprache)
	{
		if (empty($content_ids))
			return success([]);

		$platzhalter = implode(', ', array_fill(0, count($content_ids), '?'));

		// The aggregate and the title sit in the final select. The recursive term names
		// the working table once and carries no subquery, which is what Postgres allows.
		$query = '
			WITH RECURSIVE auf(start_id, content_id, tiefe) AS (
				SELECT cc.child_content_id, cc.content_id, 1
				FROM campus.tbl_contentchild cc
				WHERE cc.child_content_id IN (' . $platzhalter . ')
				UNION ALL
				SELECT auf.start_id, cc.content_id, auf.tiefe + 1
				FROM campus.tbl_contentchild cc
					JOIN auf ON cc.child_content_id = auf.content_id
				WHERE auf.tiefe < ?
			)
			SELECT auf.start_id, auf.content_id, MIN(auf.tiefe) AS tiefe,
				(SELECT titel FROM campus.tbl_contentsprache cs
				 WHERE cs.content_id = auf.content_id AND cs.sprache = ?
				 ORDER BY cs.version LIMIT 1) AS titel,
				-- The caller turns this into the entitled flag, so the path can offer a
				-- link only for an ancestor the editor may open.
				(SELECT c.oe_kurzbz FROM campus.tbl_content c
				 WHERE c.content_id = auf.content_id) AS oe_kurzbz
			FROM auf
			GROUP BY auf.start_id, auf.content_id
			ORDER BY auf.start_id, MIN(auf.tiefe) DESC, auf.content_id
		';

		$params = array_map('intval', array_values($content_ids));
		$params[] = (int) $maxTiefe;
		$params[] = $sprache;

		return $this->execReadOnlyQuery($query, $params);
	}

	/**
	 * Removes a content from the child table, as parent and as child.
	 * @param int $content_id
	 * @return stdClass
	 */
	public function deleteByContent($content_id)
	{
		return $this->execQuery(
			'DELETE FROM campus.tbl_contentchild
				WHERE content_id = ? OR child_content_id = ?',
			[$content_id, $content_id]
		);
	}
}
