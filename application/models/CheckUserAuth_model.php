<?php

class CheckUserAuth_model extends FHC_Model
{
	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->library('fhc_auth');
	}
	
	public function checkByUsernamePassword($username, $password)
	{
		return success($this->fhc_auth->checkpassword($username, $password));
	}
}