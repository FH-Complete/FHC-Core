<?php
class Dashboard_Widget_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'dashboard.tbl_widget';
		$this->pk = 'widget_id';
	}

	public function getAllForDashboard($db)
	{
		$this->addSelect($this->dbTable . '.*');
		$this->addJoin('dashboard.tbl_dashboard_widget', 'widget_id');
		$this->addJoin('dashboard.tbl_dashboard', 'dashboard_id');

		return $this->loadWhere(['dashboard_kurzbz' => $db]);
	}

}
