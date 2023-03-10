<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 */
class Test extends Auth_Controller
{
    private $_uid;  // uid of the logged user

    /**
     * Constructor
     */
    public function __construct()
    {
        // Set required permissions
        parent::__construct(
            array(
                'index' => 'dashboard/benutzer:r',
                'db' => 'dashboard/benutzer:r',
                'admin' => 'dashboard/admin:r',
            )
        );

        $this->load->library('AuthLib');

        $this->_setAuthUID(); // sets property uid

        $this->setControllerId(); // sets the controller id
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Public methods
	public function index()
	{
		$this->load->view('test/Test.php', ['dashboard' => 'CIS']);
	}

    // Public methods
    public function db($dashboard)
    {
        $this->load->view('test/Test.php', ['dashboard' => $dashboard]);
    }

    public function admin()
    {
        $this->load->view('test/Admin.php', []);
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Private methods

    /**
     * Retrieve the UID of the logged user and checks if it is valid
     */
    private function _setAuthUID()
    {
        $this->_uid = getAuthUID();

        if (!$this->_uid) show_error('User authentification failed');
    }
}
