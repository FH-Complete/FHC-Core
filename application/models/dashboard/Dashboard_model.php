<?php
class Dashboard_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "( WITH  vals (dashboard_id, name) AS (VALUES 
			(0,'CIS'), 
			(1,'PV21')
		) SELECT * FROM vals ) AS tbl_dashboard";
		$this->pk = 'dashboard_id';
	}
	
}
