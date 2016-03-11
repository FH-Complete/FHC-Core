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
 */

// ------------------------------------------------------------------------

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

/**
 * Handles ping attempts of applications
 */
class Ping extends REST_Controller {

    /**
     * Ping-Controller constructor.
     * A more elaborate description of the constructor.
     */
    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['ping_get']['limit'] = 500; // 500 requests per hour per user/key
    }

    /**
     * Responds to ping attempts of applications
     * @return string JSON which acknowledges the ping attempt
     * @example http://wsp.fortyseeds.at/backend/api/ping
     */
    public function index_get()
    {
        $payload = [
          'success' => true,
          'message' => 'ping received'
        ];

        // Set the response and exit
        $this->response($payload, REST_Controller::HTTP_OK);
    }
}
