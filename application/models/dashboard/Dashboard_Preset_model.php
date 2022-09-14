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


	/**
	 * Get Presets of given uid.
	 * @param integer dashboard_id
	 * @param string $uid
	 * @return array
	 */
	public function getPresets($dashboard_id, $uid)
	{
		return $this->loadWhere(array('dashboard_id' => $dashboard_id, 'funktion_kurzbz'=> null));
	}
}
