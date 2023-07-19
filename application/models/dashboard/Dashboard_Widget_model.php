<?php
class Dashboard_Widget_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'dashboard.tbl_dashboard_widget';
		$this->pk = ['dashboard_id', 'widget_id'];
		$this->hasSequence = false;
	}
}
