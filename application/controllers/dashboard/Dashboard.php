<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 */
class Dashboard extends Auth_Controller
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
                'index' => 'user:r',
            )
        );

        $this->load->library('AuthLib');
        $this->load->library('WidgetLib');

        $this->_setAuthUID(); // sets property uid

        $this->setControllerId(); // sets the controller id
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Public methods
	public function index()
	{
		$this->load->view('dashboard/dashboard.php', []);
	}
    // TODO löschen
    public function test(){
        echo "<pre>"; print_r('in Dashboard Test function'); echo "</pre>";
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
