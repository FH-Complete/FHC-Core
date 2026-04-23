<?php
class Dashboard_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'dashboard.tbl_dashboard';
		$this->pk = 'dashboard_id';
	}
}
