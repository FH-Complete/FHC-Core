<?php
class Dashboard_Preset_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'dashboard.tbl_dashboard_preset';
		$this->pk = 'preset_id';
	}
}
