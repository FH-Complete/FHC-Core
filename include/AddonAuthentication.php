<?php

/* 
 * This class requires only DB functionalities from CI
 * CI_Hack couldn't be included here because this class is called every time an addon
 * makes an API call, so it would raise a lot of "already declared" errors
 */
require_once(dirname(__FILE__).'/../vendor/codeigniter/framework/system/core/Model.php');
require_once(dirname(__FILE__).'/../application/core/FHC_Model.php');
require_once(dirname(__FILE__).'/../application/core/DB_Model.php');

/**
 * This class is used to authenticate the addons on the core system
 */
class AddonAuthentication extends DB_Model
{
	public function __construct()
	{
		parent::__construct(NULL);
	}

	/**
	 * It retrieves the password with the given username from ci_addons table
	 * CREATE TABLE ci_addons (
	 *	username       varchar(10),
	 *	password       varchar(40),
	 *	enabled        integer,
	 *	CONSTRAINT ci_addons_pk UNIQUE(username));
	 */
	public function getPasswordByUsername($username)
	{
		$password = NULL;
		$sql = "SELECT password FROM public.ci_addons WHERE enabled = 1 AND username = ?";
		
		$result = $this->db->query($sql, array($username));
		
		if(!is_null($result) && $result->num_rows() > 0)
		{
			$password = $result->row()->password;
		}
		
		return $password;
	}
}