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

    public function getAll($limit = null)
    {
        // TODO check ob über content table. Aktuell sind die news texte NULL, deshalb über content table holen.
//        $this->addJoin('campus.tbl_content', 'content_id');
//        $this->addJoin('campus.tbl_contentsprache', 'content_id');

        return $this->loadWhere('
            text IS NOT NULL
            AND datum <= NOW() AND (datum_bis IS NULL OR datum_bis >= now()::date)
            ORDER BY datum DESC
            LIMIT '. $this->escape($limit)
        );
    }
}
