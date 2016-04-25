<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FHC_Model extends CI_Model
{
	//protected errormsg;
	function __construct()
	{
		parent::__construct();
		$this->load->helper('language');
		$this->load->helper('fhc_db_acl');
		$this->lang->load('fhcomplete');
		//$this->load->library('FHC_DB_ACL');
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