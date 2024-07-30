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
	 * Get Bookmarks of UID.
	 * @param string user uid
	 * @return array
	 */
	public function getAll($uid)
	{
		return $this->execReadOnlyQuery("
            SELECT * 
            FROM dashboard.tbl_bookmark 
            WHERE uid = ?
            ",[$uid]);
	}

    /**
	 * Gets Bookmark by bookmark_id.
	 * @param int $bookmark_id 
	 * @return array
	 */
    public function get($bookmark_id)
	{
		return $this->execReadOnlyQuery("
            SELECT * 
            FROM dashboard.tbl_bookmark 
            WHERE bookmark_id = ?
            ",[$bookmark_id]);
	}

    /**
	 * Gets Bookmark by bookmark_id.
	 * @param int $bookmark_id
	 * @return array
	 */
	public function delete($bookmark_id)
	{
		return $this->execReadOnlyQuery("
            DELETE
            FROM dashboard.tbl_bookmark 
            WHERE bookmark_id = ?
            ",[$bookmark_id]);
	}
}
