<?php
class Bookmark_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'dashboard.tbl_bookmark';
		$this->pk = 'bookmark_id';
	}

	/**
	 * returns all bookmark tags of a user
	 */
	public function getAllBookmarkTags($uid)
	{
		$qry = "
			SELECT array_agg(DISTINCT tag) AS data
			FROM (
			  SELECT jsonb_array_elements_text(tag) AS tag
			  FROM dashboard.tbl_bookmark
			  WHERE uid = ?
			) t;
		";

		return $this->execQuery($qry, array('uid' => $uid));
	}
}
