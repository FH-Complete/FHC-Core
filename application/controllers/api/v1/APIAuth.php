<?php

/**
 * Whisperocity
 *
 * @package		Whisperocity
 * @author		WSP-Team
 * @copyright	Copyright (c) 2015, Whisperocity
 * @license		proprietary
 * @link		http://whisperocity.com/
 * @since		Version 1.0
 * @filesource
 * @example curl -H "FHC-API-Key: testapikey@fhcomplete.org" http://localhost/fhcomplete/index.ci.php/api/v1/APIAuth/login?code=aladsfasdf
 */

// ------------------------------------------------------------------------

if (! defined('BASEPATH'))
	exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
//require APPPATH . '/libraries/REST_Controller.php';

/**
 * Handles user authentication and registration process
 */
class APIAuth extends APIv1_Controller
{
    /**
     * Userauth-Controller constructor.
     * A more elaborate description of the constructor.
     * {@inheritdoc}
     */
    public function __construct()
    {
        // Construct the parent class
        parent::__construct();

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['login_get']['limit'] = 500; // 500 requests per hour per user/key
        
        // Load helper
        //$this->load->helper('fhcauth');
		$this->load->library('session');
		$this->load->library('Fhcauth');
    }

    /**
     * Checks user credentials and creates a new session
     *
     * @example normal account: http://wsp.fortyseeds.at/backend/api/userauth/login/username/foo%40bar.at/password/secret/device_id/abcdef123
     * @example OAuth Google: http://wsp.fortyseeds.at/backend/api/userauth/login/username/foo%40bar.at/device_id/abcdef123/google_token/qwert321
     * @example OAuth Facebook: http://wsp.fortyseeds.at/backend/api/userauth/login/username/foo%40bar.at/device_id/abcdef123/fb_token/qwert321
	 * @return void		JSON that indicates success/failure of login.
     */
    public function login_get()
    {
        $payload = array();
        $errormsg = "";
        $httpstatus = null;
        $username = urldecode($this->get('username'));
        $password = urldecode($this->get('password'));
		
		$account = $this->fhcauth->auth($username, $password);

        // perform login checks
        if (!$account)
            $errormsg = "Auth not accepted!";

        if (empty($errormsg))
        {
            // generate new session
			$this->session->sess_regenerate();
            $token = session_id();

            $payload = [
                'success' => true,
                'message' => 'User successfully logged in',
                'session_id' => $token
            ];
            $httpstatus = REST_Controller::HTTP_OK;
        }
        else
        {
            $payload = [
                'success' => false,
                'message' => $errormsg
            ];
            $httpstatus = REST_Controller::HTTP_UNAUTHORIZED;
        }

        // Set the response and exit
        $this->response($payload, $httpstatus);
    }

    /**
     * Logs out user by destroying session
     *
     * @example http://wsp.fortyseeds.at/backend/api/userauth/logout/username/foo%40bar.at/session_id/55afab8ba6f1b/device_id/abcdef123
	 * @return void JSON that indicates success/failure of logout
     */
    public function logout_get()
    {
        $payload = array();
        $httpstatus = null;
        $token = $this->get('session_id');
        $username = urldecode($this->get('username'));
        $deviceid = $this->get('device_id');
        $account = $this->user_model->load($username);

        // destroy session
        if ($this->session_model->destroy($account, $token, $deviceid))
        {
            $payload = [
              'success' => true,
              'message' => 'user successfully logged out'
            ];
            $httpstatus = REST_Controller::HTTP_OK;
        }
        else
        {
            $payload = [
              'success' => false,
              'message' => 'user could not be logged out'
            ];
            $httpstatus = REST_Controller::HTTP_BAD_REQUEST;
        }

        // Set the response and exit
        $this->response($payload, $httpstatus);
    }
}
