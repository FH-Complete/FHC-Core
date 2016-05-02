<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FHC_Model extends CI_Model
{
	//protected errormsg;
	function __construct($uid = null)
	{
		parent::__construct();
		$this->load->helper('language');
		$this->lang->load('fhc_model');
		//$this->load->helper('fhc_db_acl');
		$this->lang->load('fhcomplete');
		//$this->load->library('session');
		if (is_null($uid))
			$uid = $this->session->uid;
		$this->load->library('FHC_DB_ACL',array('uid' => $uid));
	}

	/** ---------------------------------------------------------------
	 * Success
	 *
	 * @param   mixed  $retval
	 * @return  array
	 */
	protected function _success($retval, $message = FHC_SUCCESS)
	{
		$return = new stdClass();
		$return->error = EXIT_SUCCESS;
		$return->code = $message;
		$return->msg = lang('fhc_' . $message);
		$return->retval = $retval;
		return $return;
	}

	/** ---------------------------------------------------------------
	 * General Error
	 *
	 * @return  array
	 */
	protected function _error($retval = '', $message = FHC_MODEL_ERROR)
	{
		$return = new stdClass();
		$return->error = EXIT_MODEL;
		$return->code = $message;
		$return->msg = lang('fhc_' . $message);
		$return->retval = $retval;
		return $return;
	}
}
