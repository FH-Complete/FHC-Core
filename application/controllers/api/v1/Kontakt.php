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

defined('BASEPATH') || exit('No direct script access allowed');

class Kontakt extends REST_Controller
{

    //public $session;
    /**
     * Person API constructor.
     */
    public function __construct()
    {
	parent::__construct();

	$this->load->model('kontakt/Kontakt_model');
    }

    public function kontaktPerson_get()
    {
	$result = $this->Kontakt_model->getKontaktPerson($this->get("person_id"));
	if($result != FALSE)
	{
	    $httpstatus = REST_Controller::HTTP_OK;
	    $payload = [
		'success' => true,
		'message' => 'Kontakt found.'
	    ];
	    $payload['data'] = $result;
	}
	else
	{
	    $payload = [
		'success' => false,
		'message' => 'Could not find Kontakt.'
	    ];
	    $httpstatus = REST_Controller::HTTP_OK;
	}
	
	$this->response($payload, $httpstatus);
    }
    
    public function kontakt_post()
    {
	$result = $this->Kontakt_model->saveKontakt($this->post());
	if($result != FALSE)
	{
	    $httpstatus = REST_Controller::HTTP_OK;
	    $payload = [
		'success' => true,
		'message' => 'Kontakt saved.'
	    ];
	    $payload['data'] = $result;
	}
	else
	{
	    $payload = [
		'success' => false,
		'message' => 'Could not save Kontakt.'
	    ];
	    $httpstatus = REST_Controller::HTTP_OK;
	}
	
	$this->response($payload, $httpstatus);
    }

}
