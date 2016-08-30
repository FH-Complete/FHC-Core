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

if (!defined("BASEPATH")) exit("No direct script access allowed");

class Prestudent extends APIv1_Controller
{
	/**
	 * Prestudent API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PrestudentModel
		$this->load->model("crm/prestudent_model", "PrestudentModel");
		// Load library ReihungstestLib
		$this->load->library("ReihungstestLib");
	}

	/**
	 * @return void
	 */
	public function getPrestudent()
	{
		$prestudentID = $this->get("prestudent_id");
		
		if (isset($prestudentID))
		{
			$result = $this->PrestudentModel->load($prestudentID);
			
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
	public function getPrestudentByPersonID()
	{
		$person_id = $this->get("person_id");
		
		if (isset($person_id))
		{
			$result = $this->PrestudentModel->load(array("person_id" => $person_id));
			
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
	public function postPrestudent()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()["prestudent_id"]))
			{
				$result = $this->PrestudentModel->update($this->post()["prestudent_id"], $this->post());
			}
			else
			{
				$result = $this->PrestudentModel->insert($this->post());
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
	public function postAddReihungstest()
	{
		$ddReihungstest = $this->_parseData($this->post());
		
		if ($this->_validateReihungstest($ddReihungstest))
		{
			if(isset($ddReihungstest["new"]) && $ddReihungstest["new"] == true)
			{
				// Remove new parameter to avoid DB insert errors
				unset($ddReihungstest["new"]);
				
				$result = $this->reihungstestlib->insertPersonReihungstest($ddReihungstest);
			}
			else
			{
				$result = $this->reihungstestlib->updatePersonReihungstest($ddReihungstest);
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
	public function postDelReihungstest()
	{
		$ddReihungstest = $this->_parseData($this->post());
		
		if (isset($ddReihungstest["rt_person_id"]))
		{
			$result = $this->reihungstestlib->deletePersonReihungstest($ddReihungstest);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($prestudent = NULL)
	{
		return true;
	}
	
	private function _validateReihungstest($ddReihungstest = NULL)
	{
		if (!isset($ddReihungstest["person_id"]) || !isset($ddReihungstest["rt_id"]) || !isset($ddReihungstest["studienplan_id"]))
		{
			return false;
		}
		
		return true;
	}
}