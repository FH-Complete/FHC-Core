<?php
class Dashboard_Override_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'dashboard.tbl_dashboard_benutzer_override';
		$this->pk = 'override_id';
	}


	/**
	 * Get Overrides of given uid.
	 * @param integer dashboard_id
	 * @param string $uid
	 * @return array
	 */
	public function getOverride($dashboard_id, $uid)
	{
		return $this->loadWhere(array('dashboard_id' => $dashboard_id, 'uid'=> $uid));
	}
}
