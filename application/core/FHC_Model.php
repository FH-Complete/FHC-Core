<?php
if (! defined('BASEPATH'))
	exit('No direct script access allowed');

class FHC_Model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$this->load->helper('language');
		$this->lang->load('fhc_model');
		$this->lang->load('fhcomplete');
		
		$uid = NULL;
		if(is_null($uid) && isset($this->session->uid))
		{
			$uid = $this->session->uid;
		}
		$this->load->library('FHC_DB_ACL', array('uid' => $uid));
	}

	/** ---------------------------------------------------------------
	 * Set UID
	 *
	 * @param   string  $uid
	 * @return  bool
	 */
	public function setUID($uid)
	{
		return $this->fhc_db_acl->setUID($uid);
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
		$return->fhcCode = $message;
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
		$return->fhcCode = $message;
		$return->msg = lang('fhc_' . $message);
		$return->retval = $retval;
		return $return;
	}
}
