<?php

class DB_Model extends FHC_Model
{
	protected $dbTable = NULL;  // Name of the DB-Table for CI-Insert, -Update, ...
	// Addon ID, stored to let to check the permissions
	private $_addonID;

	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->lang->load('fhc_db');
	}

	public function insert($data)
	{
		if(!is_null($this->dbTable))
		{
			$this->db->insert($this->dbTable, $data);
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/** ---------------------------------------------------------------
	 * Invalid ID
	 *
	 * @param   integer  config.php error code numbers
	 * @return  array
	 */
	protected function _invalid_id($error = '')
	{
		return array(
			'err' => 1,
			'code' => $error,
			'msg' => lang('fhc_' . $error)
		);
	}
	
	/**
	 * Method setAddonID
	 * 
	 * @param $addonID
	 * @return void
	 */
	public function setAddonID($addonID)
	{
		$this->_addonID = $addonID;
	}
	
	/**
	 * Method getAddonID
	 * 
	 * @return string _addonID
	 */
	public function getAddonID()
	{
		return $this->_addonID;
	}
}