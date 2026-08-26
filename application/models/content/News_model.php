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
	public function getAll($limit = null, $maxAlter = 0)
	{
		$this->addJoin("campus.tbl_content","content_id");

		$maxAlterQueryFilter = null;
		if ($maxAlter !== 0) {
			$maxAlterQueryFilter .= " (NOW()-datum) < interval '" . $this->escape($maxAlter) . " days'";
		}

		return $this->execReadOnlyQuery('
			SELECT *, TO_CHAR(campus.tbl_news.datum, ?) as datumformatted
			FROM campus.tbl_news
			JOIN campus.tbl_content content ON content.content_id = campus.tbl_news.content_id
			WHERE 
			--text IS NOT NULL AND 	
			datum <= NOW() AND (datum_bis >= NOW()::date OR datum_bis IS NULL)' . ($maxAlterQueryFilter ? ' AND ' . $maxAlterQueryFilter : '') . '
			ORDER BY datum DESC
			LIMIT ' . $this->escape($limit)
		, ['DD/MM/YYYY']);
	}

	public function getNewsContentIDs($limit=10){
		$this->addSelect(['content_id']);
		return $this->loadWhere("datum <= NOW() AND (datum_bis IS NULL OR datum_bis >= now()::date)
		ORDER BY datum DESC
		LIMIT " . $this->escape($limit));

	}



	/**
	 * @param string            $sprache
	 * @param string            $studiengang_kz
	 * @param integer | null    $semester
	 * @param string            $fachbereich_kurzbz
	 * @param boolean           $sichtbar
	 * @param integer           $maxalter
	 * @param integer           $page
	 * @param integer           $page_size
	 * @param boolean           $all
	 * @param boolean           $mischen
	 * 
	 * TODO(chris): this is not a good function -> the params are all over the place
	 * 
	 */
	protected function prepareNewsWithContent($sprache, $studiengang_kz, $semester, $fachbereich_kurzbz = null, $sichtbar = true, $maxalter = 0, $page = 1, $page_size = 10, $all = false, $mischen = true, $filterForDegreePrograms = false, $allowedDegreePrograms = [])
	{

		$this->addSelect(["*", "COUNT(*) OVER() AS full_count"]);
		$this->addOrder('datum', 'DESC');

		$studiengang_kz = trim($studiengang_kz);
		$fachbereich_kurzbz = trim($fachbereich_kurzbz);

		$where = [];
		
		if (!$all) {
			$where[] = "datum <= NOW() AND (datum_bis >= NOW()::date OR datum_bis IS NULL)";
		}
		if ($maxalter !== 0 && is_numeric($maxalter)) {
			$maxalter = (int)$maxalter;
			$where[] = "(NOW()-datum) < interval '" . $this->escape($maxalter) . " days'";

		}

		if ($fachbereich_kurzbz != '*') {
			if ($fachbereich_kurzbz == '') {
				$where[] = "fachbereich_kurzbz IS NULL";
			} else {
				$where[] = "fachbereich_kurzbz = " . $this->db->escape($fachbereich_kurzbz);

			}
		}
		if ($studiengang_kz == '0') {
			$where[] = "studiengang_kz = " . $this->db->escape($studiengang_kz);

			if ($semester === NULL)
				$where[] = "semester IS NULL";
			elseif ($semester === 0)
				$where[] = "semester = 0";
			else 
				$where[] = "semester = " . $this->db->escape($semester);
		} elseif ($studiengang_kz != '') {
			$add = $mischen === true ? " OR (studiengang_kz = 0 AND semester IS NULL)" : "";
			$allowedSemesterValues = [$semester, 0, null];
			$where[] = "(studiengang_kz = " . $this->db->escape($studiengang_kz) . " AND (semester in (" . implode(',', array_map([$this->db, 'escape'], $allowedSemesterValues)) . ") OR semester IS NULL) OR (studiengang_kz = 0 AND semester = " . $this->db->escape($semester) . ")" . $add . ")";

		} elseif ($semester !== NULL) {
			$where[] = "semester = " . $this->db->escape($semester);
		}

		$this->addJoin('campus.tbl_contentsprache cs', 'content_id');

		if (is_bool($sichtbar) || $sichtbar === 'true' || $sichtbar === 'false') {
			$isSichtbar = filter_var($sichtbar, FILTER_VALIDATE_BOOLEAN);
			$where[] = "cs.sichtbar = " . ($isSichtbar ? "true" : "false");
		}

		$where[] = "cs.sprache = (CASE WHEN EXISTS(SELECT 1 FROM campus.tbl_contentsprache cs2 WHERE cs2.content_id=" . $this->dbTable . ".content_id AND sprache=" . $this->db->escape($sprache) . ") THEN " . $this->db->escape($sprache) . " ELSE " . $this->db->escape(DEFAULT_LANGUAGE) . " END)";


		$where[] = "cs.version = (SELECT MAX(version) FROM campus.tbl_contentsprache cs3 WHERE cs3.content_id=" . $this->dbTable . ".content_id AND cs3.sprache = (CASE WHEN EXISTS(SELECT 1 FROM campus.tbl_contentsprache cs2 WHERE cs2.content_id=" . $this->dbTable . ".content_id AND sprache=" . $this->db->escape($sprache) . ") THEN " . $this->db->escape($sprache) . " ELSE " . $this->db->escape(DEFAULT_LANGUAGE) . " END))";

		if ($filterForDegreePrograms) {
			if (empty($allowedDegreePrograms)) {
				$where[] = "1=0";
			} else {
				$where[] = "studiengang_kz IN (" . implode(',', array_map([$this->db, 'escape'], $allowedDegreePrograms)) . ")";
			}
		}

		$where = implode(" AND ", $where);

		$this->db->where($where, NULL, FALSE);

	}

	public function getNewsWithContent($sprache, $studiengang_kz, $semester, $fachbereich_kurzbz = null, $sichtbar = true, $maxalter = 0, $page = 1, $page_size = 10, $all = false, $mischen = true, $filterForDegreePrograms = false, $allowedDegreePrograms = [])
	{
		$this->prepareNewsWithContent($sprache, $studiengang_kz, $semester, $fachbereich_kurzbz, $sichtbar, $maxalter, $page, $page_size, $all, $mischen, $filterForDegreePrograms, $allowedDegreePrograms);
		// getting the number of rows of the query and adding pagination to the query result
		$num_rows = $this->getNumRows(true);
		$this->addPagination($page, $page_size, $num_rows);

		// preparing the query again because every call to get_compiled_select or cour_all_results will add the from clause to the query
		$this->prepareNewsWithContent($sprache, $studiengang_kz, $semester, $fachbereich_kurzbz, $sichtbar, $maxalter, $page, $page_size, $all, $mischen, $filterForDegreePrograms, $allowedDegreePrograms);

		return $this->load();
	}
}
