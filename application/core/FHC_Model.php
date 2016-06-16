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
		$this->load->helper('Message');
		
		$uid = null;
		
		// Get UID from CI session
		if(isset($_SESSION['uid']))
			$uid = $this->session->uid;
		// Get UID from the environment (HTTP authentication via authentication.class.php)
		elseif(isset($_SERVER['PHP_AUTH_USER']))
			$uid = $_SERVER['PHP_AUTH_USER'];

		// After getting UID for the first time, it saves it in CI session
		if (isset($uid) && !isset($this->session->uid))
		{
			$this->session->uid = $uid;
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
	protected function _success($retval, $message = null)
	{
		return success($retval, $message);
	}

	/** ---------------------------------------------------------------
	 * General Error
	 *
	 * @return  array
	 */
	protected function _error($retval, $message = null)
	{
		return error($retval, $message);
	}
}