<?php

class SabreDAVAuthLib extends \Sabre\DAV\Auth\Backend\AbstractBasic
{
	private $_ci;
	protected $currentUser = null;

	public function __construct()
	{
		$this->_ci =& get_instance();
		
		$this->_ci->load->library('AuthLib', array(false));
		$this->_ci->load->helper('hlp_authentication');
	}

	public function getCurrentUser()
	{
		if ($this->currentUser)
			return $this->currentUser;

		if (isLogged())
			return getAuthUID();

		return null;
	}

	protected function validateUserPass($username, $password)
	{
		if (isSuccess($this->_ci->authlib->checkUserAuthByUsernamePassword($username, $password)))
		{
			$this->currentUser = $username;
			return true;
		}

		$this->currentUser = null;
		return false;
	}
}