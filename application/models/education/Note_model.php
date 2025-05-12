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
	
	public function getAll() {
		$qry ="SELECT *
			FROM lehre.tbl_note";
		
		return $this->execReadOnlyQuery($qry);
	}
	
	
}