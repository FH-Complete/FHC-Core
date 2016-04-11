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

if (! defined('BASEPATH'))
	exit('No direct script access allowed');

class Person extends APIv1_Controller
{
    /**
     * Person API constructor.
     */
    public function __construct()
    {
		parent::__construct();
		$this->load->model('person/person_model');
    }

	/**
	 * @return void
	 */
	public function person_get()
    {
	//if(!$this->session_model->validate($this->get('session_id'), $this->get('device_id')))
	// $this->response(array(['success' => false, 'message' => 'access denied']), REST_Controller::HTTP_UNAUTHORIZED);

		$code = $this->get('code');
		$person_id = $this->get('person_id');

		if (! is_null($code))
		{
			$result = $this->person_model->getPersonByCode($code);
		}
		elseif (! is_null($person_id))
		{
			$result = $this->person_model->getPerson($person_id);
		}
		else
		{
			$result = $this->person_model->getPerson();
		}

		if ($result['err'])
        {
            $payload = [
				'success' => false,
				'message' => $result['msg'].': '.$result['retval']
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

    /**
	 * @return void
	 */
	public function person_post()
    {
		$result = $this->person_model->savePerson($this->post());
		if ($result != false)
		{
			$httpstatus = REST_Controller::HTTP_OK;
			$payload = [
				'success' => true,
				'message' => 'Person saved.'
			];
			$payload['data'] = $result;
		}
		else
		{
			$payload = [
				'success' => false,
				'message' => 'Could not save person.'
			];
			$httpstatus = REST_Controller::HTTP_OK;
		}
		$this->response($payload, $httpstatus);
    }

    /**
	 * @return void
	 */
	public function personUpdate_post()
    {
		$result = $this->person_model->updatePerson($this->post());
		if ($result != false)
		{
			$httpstatus = REST_Controller::HTTP_OK;
			$payload = [
				'success' => true,
				'message' => 'Person updated.'
			];
			$payload['data'] = $result;
		}
		else
		{
			$payload = [
				'success' => false,
				'message' => 'Could not update person.'
			];
			$httpstatus = REST_Controller::HTTP_OK;
		}
		$this->response($payload, $httpstatus);
    }

    /**
	 * @return void
	 */
	public function checkBewerbung_get()
    {
		$result = $this->person_model->checkBewerbung($this->get("email"), $this->get("studiensemester_kurzbz"));
		$httpstatus = REST_Controller::HTTP_OK;
		$payload = [
			'success' => true,
			'message' => 'Bewerbung exists.'
		];
		$payload['data'] = $result;
		$this->response($payload, $httpstatus);
    }

    /**
	 * @return void
	 */
	public function checkZugangscodePerson_get()
    {
		$result = $this->person_model->checkZugangscodePerson($this->get("code"));
		$httpstatus = REST_Controller::HTTP_OK;
		if (!empty($result))
		{
			$payload = [
				'success' => true,
				'message' => 'Zugangscode exists.'
			];
			$payload['data'] = $result;
		}
		else
		{
			$payload = [
				'success' => false,
				'message' => 'Zugangscode does not exist.'
			];
			$httpstatus = REST_Controller::HTTP_OK;
		}

		$this->response($payload, $httpstatus);
    }
}
