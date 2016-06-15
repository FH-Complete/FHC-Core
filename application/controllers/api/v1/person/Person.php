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

class Person extends APIv1_Controller
{
	/**
	 * Person API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PersonModel
		$this->load->model('person/person_model', 'PersonModel');
		// Load set the uid of the model to let to check the permissions
		$this->PersonModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getPerson()
	{
		$personID = $this->get('person_id');
		$code = $this->get('code');
		$email = $this->get('email');
		
		if (isset($code) || isset($email) || isset($personID))
		{
			if (isset($code) && isset($email))
			{
				$result = $this->PersonModel->addJoin('public.tbl_kontakt', 'person_id');
				if ($result->error == EXIT_SUCCESS)
				{
					$result = $this->PersonModel->loadWhere(array('zugangscode' => $code, 'kontakt' => $email));
				}
			}
			elseif (isset($code))
			{
				$result = $this->PersonModel->loadWhere(array('zugangscode' => $code));
			}
			else
			{
				$result = $this->PersonModel->load($personID);
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function postPerson()
	{
		$person = $this->_parseData($this->post());
		$validation = $this->PersonModel->_validate($this->post());
		
		if (is_object($validation) && $validation->error == EXIT_SUCCESS)
		{
			if(isset($person['person_id']) && !(is_null($person["person_id"])) && ($person["person_id"] != ""))
			{
				$result = $this->PersonModel->update($person['person_id'], $person);
			}
			else
			{
				$result = $this->PersonModel->insert($person);
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response($validation, REST_Controller::HTTP_OK);
		}
	}
	
	/**
	 * @return void
	 */
	public function getCheckBewerbung()
	{
		$email = $this->get('email');
		$studiensemester_kurzbz = $this->get('studiensemester_kurzbz');
		
		if (isset($email))
		{
			$result = $this->PersonModel->checkBewerbung($email, $studiensemester_kurzbz);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
}