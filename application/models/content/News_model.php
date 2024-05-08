<?php
class News_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_news';
		$this->pk = 'news_id';
	}

	/**
	 * Get all News ordered by date. (most actual on top)
	 * @param null $limit   Amount of news.
	 * @return array
	 */
	public function getAll($limit = null)
	{
		return $this->loadWhere('
			text IS NOT NULL
			AND datum <= NOW() AND (datum_bis IS NULL OR datum_bis >= now()::date)
			ORDER BY datum DESC
			LIMIT '. $this->escape($limit)
		);
	}

	/**
	 * @param string            $sprache
	 * @param string            $studiengang_kz
	 * @param integer | null    $semester
	 * @param string            $fachbereich_kurzbz
	 * @param boolean           $sichtbar
	 * @param integer           $maxalter
	 * @param integer           $maxnews
	 * @param boolean           $all
	 * @param boolean           $mischen
	 * 
	 * TODO(chris): this is not a good function -> the params are all over the place
	 * 
	 * @return stdObj
	 */
	public function getNewsWithContent($sprache, $studiengang_kz, $semester, $fachbereich_kurzbz = null, $sichtbar = true, $maxalter = 0, $maxnews = 0, $all = false, $mischen = true)
	{
		$studiengang_kz = trim($studiengang_kz);
		$fachbereich_kurzbz = trim($fachbereich_kurzbz);

		$where = [];
		$params = [];
		if (trim($maxalter) != '0')
		{
			$where[] = "(now()-datum) < interval ? days";
			$params[] = $maxalter;
		}
		if (!$all)
		{
			$where[] = "datum <= now()";
			$where[] = "(datum_bis >= now()::date OR datum_bis IS NULL)";
		}
		if ($fachbereich_kurzbz != '*')
		{
			if ($fachbereich_kurzbz == '')
			{
				$where[] = "fachbereich_kurzbz IS NULL";
			}
			else
			{
				$where[] = "fachbereich_kurzbz = ?";
				$params[] = $fachbereich_kurzbz;
			}
		}
		if ($studiengang_kz == '0')
		{
			$where[] = "studiengang_kz = ?";
			$params[] = $studiengang_kz;
			if ($semester === NULL)
				$where[] = "semester IS NULL";
			elseif ($semester === 0)
				$where[] = "semester = 0";
		}
		elseif ($studiengang_kz != '')
		{
			$add = $mischen === true ? " OR (studiengang_kz = 0 AND semester IS NULL)" : "";
			$where[] = "((studiengang_kz = ? AND semester = ?) OR (studiengang_kz = ? AND semester = 0) OR (studiengang_kz = 0 AND semester = ?)" . $add . ")";
			$params[] = $studiengang_kz;
			$params[] = $semester;
			$params[] = $studiengang_kz;
			$params[] = $semester;
		}
		$this->addJoin('campus.tbl_contentsprache cs', 'content_id');
		
		$where[] = "cs.sichtbar = " . ($sichtbar ? "true" : "false");
		
		$where[] = "cs.sprache = (CASE WHEN EXISTS(SELECT 1 FROM campus.tbl_contentsprache cs2 WHERE cs2.content_id=" . $this->dbTable . ".content_id AND sprache=?) THEN ? ELSE ? END)";
		$params[] = $sprache;
		$params[] = $sprache;
		$params[] = DEFAULT_LANGUAGE;
		
		$where[] = "cs.version = (SELECT MAX(version) FROM campus.tbl_contentsprache cs3 WHERE cs3.content_id=" . $this->dbTable . ".content_id AND cs3.sprache = (CASE WHEN EXISTS(SELECT 1 FROM campus.tbl_contentsprache cs2 WHERE cs2.content_id=" . $this->dbTable . ".content_id AND sprache=?) THEN ? ELSE ? END))";
		$params[] = $sprache;
		$params[] = $sprache;
		$params[] = DEFAULT_LANGUAGE;

		$where = implode(" AND ", $where);

		$this->addOrder('datum', 'DESC');
		if ($maxnews)
			$this->addLimit($maxnews);


		$this->db->where($where, NULL, FALSE);

		$sql = $this->db->get_compiled_select($this->dbTable);

		return $this->execQuery($sql, $params);
	}

}
