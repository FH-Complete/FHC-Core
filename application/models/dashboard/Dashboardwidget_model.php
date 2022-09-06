<?php
class Dashboardwidget_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "( WITH  vals (dashboard_widget_id, dashboard_id, widget_id) AS (VALUES 
			(0,0,0), 
			(1,0,1), 
			(2,1,0)
		) SELECT * FROM vals ) AS tbl_dashboard_widget";
		$this->pk = 'dashboard_widget_id';
	}

	public function loadWidgets($dashboard_id)
	{
		$this->addJoin("( WITH  vals (widget_id, name, component_name, component_path, arguments) AS (VALUES 
			(0,'KPI Single','DbwKpi','./DBW/KPI.js',CONCAT('{\"data\":[1]',',','\"display\":0}')), 
			(1,'KPI Multi','DbwKpi','./DBW/KPI.js',CONCAT('{\"data\":[1,2,3]}'))
		) SELECT * FROM vals ) AS tbl_widget", 'widget_id');

		return $this->loadWhere(['dashboard_id' => $dashboard_id]);
	}
	
}
