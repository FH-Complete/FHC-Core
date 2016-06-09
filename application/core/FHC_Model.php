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
		
		$uid = null;
		
		// Get UID from CI session
		if(isset($_SESSION['uid']))
			$uid = $this->session->uid;
		// Get UID from the environment (HTTP authentication via authentication.class.php)
		elseif(isset($_SERVER['PHP_AUTH_USER']))
			$uid = $_SERVER['PHP_AUTH_USER'];
		
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
