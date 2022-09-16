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


	/**
	 * Get Dashboard by kurzbz.
	 * @param string dashboard_kurzbz
	 * @return array
	 */
	public function getDashboardByKurzbz($dashboard_kurzbz)
	{
		return $this->loadWhere(array('dashboard_kurzbz' => $dashboard_kurzbz));
	}
}
