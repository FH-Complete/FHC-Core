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

if(!defined('BASEPATH')) exit('No direct script access allowed');

class Person extends REST_Controller
{
	/**
	 * Person API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PersonModel
		$this->load->model('person/person_model', 'PersonModel');
		// Load set the addonID of the model to let to check the permissions
		$this->PersonModel->setAddonID($this->_getAddonID());
	}

	/**
	 * @return void
	 */
	public function getPerson()
	{
		$personID = $this->get('person_id');
		$code = $this->get('code');
		$email = $this->get('email');

		$result = $this->PersonModel->getPerson($personID, $code, $email);

		if(!is_null($result) && $result->num_rows() > 0)
		{
			if($result->num_rows() > 1)
			{
				$payload = [
					'success'	=>	TRUE,
					'message'	=>	'People found',
					'data'		=>	$result->result()[0]
				];
				$httpstatus = REST_Controller::HTTP_OK;
			}
			else if($result->num_rows() == 1)
			{
				$payload = [
					'success'	=>	TRUE,
					'message'	=>	'Person found',
					'data'		=>	$result->result()[0]
				];
				$httpstatus = REST_Controller::HTTP_OK;
			}
		}
		else
		{
			$payload = [
				'success'	=>	FALSE,
				'message'	=>	'Person not found'
			];
			$httpstatus = REST_Controller::HTTP_OK;
		}
		
		$this->response($payload, $httpstatus);
	}

	/**
	 * @return void
	 */
	public function postPerson()
	{
		$result = $this->PersonModel->savePerson($this->post());
		
		if($result === TRUE)
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
	public function getCheckBewerbung()
	{
		$result = $this->PersonModel->checkBewerbung($this->get("email"), $this->get("studiensemester_kurzbz"));
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
	public function getCheckZugangscodePerson()
	{
		$result = $this->PersonModel->checkZugangscodePerson($this->get("code"));
		$httpstatus = REST_Controller::HTTP_OK;
		if(!empty($result))
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
	
	/**
	 * 
	 */
	public function postInterestedStudent()
	{
		$result = $this->PersonModel->saveInterestedStudent($this->post());
		
		if($result === TRUE)
		{
			$httpstatus = REST_Controller::HTTP_OK;
			$payload = [
				'success' => true,
				'message' => 'Interested student saved.'
			];
		}
		else
		{
			$payload = [
				'success' => false,
				'message' => 'Could not save interested student.'
			];
			$httpstatus = REST_Controller::HTTP_OK;
		}
		$this->response($payload, $httpstatus);
	}
}