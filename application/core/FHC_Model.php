<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FHC_Model extends CI_Model
{
	//protected errormsg;
	function __construct()
	{
		parent::__construct();
		$this->load->helper('language');
		$this->lang->load('fhcomplete');
	}

	/** ---------------------------------------------------------------
	 * Success
	 *
	 * @param   mixed  $retval
	 * @return  array
	 */
	protected function _success($retval = '', $message = FHC_SUCCESS)
	{
		return array(
			'err' => 0,
			'code' => FHC_SUCCESS,
			'msg' => lang('fhc_' . $message),
			'retval' => $retval
		);
	}

	/** ---------------------------------------------------------------
	 * General Error
	 *
	 * @return  array
	 */
	protected function _general_error($retval = '', $message = FHC_ERR_GENERAL)
	{
		return array(
			'err' => 1,
			'code' => FHC_ERR_GENERAL,
			'msg' => lang('fhc_' . $message),
			'retval' => $retval
		);
	}
}

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
	 * TODO
	 */
	protected function _checkPermissions()
	{
		// If _addonID is NOT set (core) or if _addonID is set (API call) and has permissions
		return !isset($this->_addonID) || (isset($this->_addonID) && TRUE);
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
}