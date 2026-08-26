<?php
class Contentgruppe_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_contentgruppe';
		$this->pk = array('gruppe_kurzbz', 'content_id');
	}

	/**
	 * Prueft ob der Zugriff auf den Content eingeschraenkt ist auf
	 * eine bestimmte Benutzergruppe
	 * 
	 * @param int			$content_id
	 * 
	 * @return stdClass		success(true) wenn eingeschraenkt sonst success(false)
	 */
	public function islocked($content_id)
	{
		$islocked = $this->loadWhere(['content_id' => $content_id]);

		if (isError($islocked))
			return $islocked;
		return success(!!getData($islocked));
	}
	
	/**
	 * Prueft ob ein User die Berechtigung fuer das Anzeigen des 
	 * Contents besitzt
	 * 
	 * @param int			$content_id	ID des Contents
	 * @param string		$uid		User der versucht auf den Content zuzugreifen
	 * 
	 * @return stdClass
	 */
	public function berechtigt($content_id, $uid)
	{
		$islocked = $this->islocked($content_id);
		if (isError($islocked))
			return $islocked;
		
		$condition = ['uid' => $uid];
		if (getData($islocked)) {
			$condition['content_id'] = $content_id;
		}
		$this->addJoin('public.vw_gruppen', 'gruppe_kurzbz');

		$result = $this->loadWhere($condition);

		if (isError($result))
			return $result;
		return success(!!getData($result));
	}

	/**
	 * Groups assigned to a content, with bezeichnung.
	 * @param int $content_id
	 * @return stdClass success with array of rows or error
	 */
	public function getGruppen($content_id)
	{
		$query = '
			SELECT cg.gruppe_kurzbz, g.bezeichnung
			FROM campus.tbl_contentgruppe cg
				JOIN public.tbl_gruppe g USING (gruppe_kurzbz)
			WHERE cg.content_id = ?
			ORDER BY cg.gruppe_kurzbz
		';

		return $this->execReadOnlyQuery($query, [$content_id]);
	}

	/**
	 * All selectable groups. Returns gruppe_kurzbz and bezeichnung.
	 * @return stdClass success with array of rows or error
	 */
	public function getAllGruppen()
	{
		// content_visible marks a group as selectable for content rights. Generated groups
		// carry FALSE. admin.php filters the same way via getgruppe(...,$content_visible=true).
		$query = '
			SELECT gruppe_kurzbz, bezeichnung
			FROM public.tbl_gruppe
			WHERE content_visible = TRUE
			ORDER BY gruppe_kurzbz
		';

		return $this->execReadOnlyQuery($query);
	}

	/**
	 * Groups for multiple contents in one query. Returns content_id => [gruppe_kurzbz, ...].
	 * @param array $contentIds array of content_id values
	 * @return stdClass success with associative array or error
	 */
	public function getGruppenForContents($contentIds)
	{
		if (empty($contentIds))
			return success([]);

		$placeholders = implode(',', array_fill(0, count($contentIds), '?'));
		$query = '
			SELECT content_id, gruppe_kurzbz
			FROM campus.tbl_contentgruppe
			WHERE content_id IN (' . $placeholders . ')
			ORDER BY content_id, gruppe_kurzbz
		';

		$result = $this->execReadOnlyQuery($query, array_values($contentIds));
		if (isError($result))
			return $result;

		$map = [];
		if (getData($result))
		{
			foreach (getData($result) as $row)
			{
				$id = (int) $row->content_id;
				if (!isset($map[$id]))
					$map[$id] = [];
				$map[$id][] = $row->gruppe_kurzbz;
			}
		}

		return success($map);
	}
}
