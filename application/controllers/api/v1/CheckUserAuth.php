<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (!defined('BASEPATH')) exit('No direct script access allowed');

class CheckUserAuth extends APIv1_Controller
{
	/**
	 * Course API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->model('CheckUserAuth_model', 'CheckUserAuthModel');
	}
	
	public function getCheckByUsernamePassword()
    {
		$username = $this->get("username");
		$password = $this->get("password");
		
		if (isset($username) && isset($password))
		{
			$result = $this->CheckUserAuthModel->checkByUsernamePassword($username, $password);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
    }
}