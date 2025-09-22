<?php
class Projects_Employees_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'sync.tbl_projects_employees';
		$this->pk = 'projects_employees_id';
	}

	public function deleteByProjectTaskId($ids)
	{
		$qry = "DELETE FROM " . $this->dbTable . " 
				WHERE project_task_id IN ?";

		return $this->execQuery($qry, array($ids));
	}
}
