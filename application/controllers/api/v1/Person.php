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

defined('BASEPATH') OR exit('No direct script access allowed');

class Person extends API_Controller
{
	//public $session;
    /**
     * Person API constructor.
     */
    function __construct()
    {
        parent::__construct();

        $this->load->model('person/person_model');
    }

    public function person_get()
    {
        //if (!$this->session_model->validate($this->get('session_id'), $this->get('device_id')))
        //    $this->response(array(['success' => false, 'message' => 'access denied']), REST_Controller::HTTP_UNAUTHORIZED);

        $code = $this->get('code');
        
        if (!is_null($code))
			$result = $this->person_model->getPersonByCode($code);
		else
			$result = $this->person_model->getPerson();
		//	var_dump($result[0]);

        if (empty($result))
        {
            $payload = [
                        'success' => false,
                        'message' => 'Person not found'
                    ];
                    $httpstatus = REST_Controller::HTTP_OK;
        }
		else
		{
			// return all available persons
            $payload = [
                'success' => true,
                'message' => 'Persons found'
            ];
			$payload['data'] = $result;
            $httpstatus = REST_Controller::HTTP_OK;
		}

        // Set the response and exit
        $this->response($payload, $httpstatus);
    }

}
