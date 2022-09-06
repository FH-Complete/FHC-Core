<?php
class Widget_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "( WITH  vals (widget_id, name, component_name, component_path, arguments) AS (VALUES 
			(0,'KPI Single','DbwKpi','./DBW/KPI.js',CONCAT('{\"data\":[1]',',','\"display\":0}')), 
			(1,'KPI Multi','DbwKpi','./DBW/KPI.js',CONCAT('{\"data\":[1,2,3]}'))
		) SELECT * FROM vals ) AS tbl_widget";
		$this->pk = 'widget_id';
	}
	
}
