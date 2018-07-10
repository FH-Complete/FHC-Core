<?php

require_once APPPATH.'/libraries/REST_Controller.php';

class APIv1_Controller extends REST_Controller
{
	/**
	 * Standard constructor for all the RESTful resources
	 */
    public function __construct($requiredPermissions)
    {
        parent::__construct();

		// Loads helper message to manage returning messages
		$this->load->helper('message');

		// Loads helper with generic utility function
		$this->load->helper('fhc');

		// Loads permission lib
		$this->load->library('PermissionLib');

		log_message('debug', 'Called API: '.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);

		$this->_isAllowed($requiredPermissions);
    }

	/**
	 * Checks if the caller is allowed to access to this content with the given permissions
	 * If it is not allowed will set the HTTP header with code 401
	 * Wrapper for permissionlib->isEntitled
	 */
	private function _isAllowed($requiredPermissions)
	{
		if (!$this->permissionlib->isEntitled($requiredPermissions, $this->router->method))
		{
			$this->response(error('You are not allowed to access to this content'), REST_Controller::HTTP_UNAUTHORIZED);
		}
	}
}
