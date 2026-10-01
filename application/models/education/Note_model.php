<?php
class Note_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_note';
		$this->pk = 'note';
	}

	public function getAllActive() {
		$qry ="SELECT *
			FROM lehre.tbl_note
			WHERE aktiv = true
			ORDER BY CASE WHEN note BETWEEN 1 AND 5 THEN 0 ELSE 1 END,
				CASE WHEN note BETWEEN 1 AND 5 THEN note END,
				bezeichnung";


		return $this->execReadOnlyQuery($qry);
	}
	
	public function getAll() {
		$qry ="SELECT *
			FROM lehre.tbl_note
			ORDER BY CASE WHEN note BETWEEN 1 AND 5 THEN 0 ELSE 1 END,
				CASE WHEN note BETWEEN 1 AND 5 THEN note END,
				bezeichnung";

		return $this->execReadOnlyQuery($qry);
	}
}