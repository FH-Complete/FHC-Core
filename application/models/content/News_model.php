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
			LIMIT '. $this->escape($limit));
	}
}
