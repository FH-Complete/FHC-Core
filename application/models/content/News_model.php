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
}
