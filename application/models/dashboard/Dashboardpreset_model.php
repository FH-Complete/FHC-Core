<?php
class Dashboardpreset_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "( WITH  vals (dashboard_preset_id, config) AS (VALUES 
			(0,CONCAT('[[0',',','0',',','0',',','0',',','[]]',',','[1',',','2',',','2',',','2',',','{\"display\":2}]]')), 
			(1,CONCAT('[[0',',','1',',','0',',','1',',','{}]',',','[1',',','0',',','1',',','0',',','{\"display\":1}]]'))
		) SELECT * FROM vals ) AS tbl_dashboard_preset";
		$this->pk = 'dashboard_preset_id';
	}

	public function loadForUser($dashboard_id, $uid)
	{
		$this->addJoin("( WITH  vals (dashboard_preset_user_id, uid, dashboard_id, dashboard_preset_id) AS (VALUES 
			(0,'ma0168', 0, 0)
		) SELECT * FROM vals ) AS tbl_dashboard_preset_user", 'dashboard_preset_id');
		return $this->loadWhere(['uid' => $uid, 'dashboard_id' => $dashboard_id]);

		return success();
	}
	
}
