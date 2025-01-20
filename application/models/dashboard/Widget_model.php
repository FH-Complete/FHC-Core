<?php
class Widget_model extends DB_Model
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

	public function getWithAllowedForDashboard($dashboard_id)
	{
		$this->addSelect($this->dbTable . '.*');
		$this->addSelect('CASE WHEN dashboard_id IS NULL THEN 0 ELSE 1 END AS allowed', false);
		$this->db->join('dashboard.tbl_dashboard_widget dw', $this->dbTable . '.widget_id=dw.widget_id AND dashboard_id = ?', 'LEFT', false);

		return $this->execQuery($this->db->get_compiled_select($this->dbTable), [$dashboard_id]);
	}

	public function getForDashboard($db)
	{
		$this->addSelect($this->dbTable . '.*');
		$this->addJoin('dashboard.tbl_dashboard_widget', 'widget_id');
		$this->addJoin('dashboard.tbl_dashboard', 'dashboard_id');

		return $this->loadWhere(['dashboard_kurzbz' => $db]);
	}
}
