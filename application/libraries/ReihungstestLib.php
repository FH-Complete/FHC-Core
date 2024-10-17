<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class ReihungstestLib
{
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->ci =& get_instance();
		
		$this->ci->load->model('crm/RtPerson_model', 'RtPersonModel');
		$this->ci->load->model('crm/Reihungstest_model', 'ReihungstestModel');
	}
	
	/**
	 * @return void
	 */
	public function insertPersonReihungstest($ddReihungstest)
	{
		if (isset($ddReihungstest['rt_id']) && $this->checkAvailability($ddReihungstest['rt_id']))
		{
  			return $this->ci->RtPersonModel->insert($ddReihungstest);
		}
		else
		{
			return error('This test is not more available');
		}
	}
	
	/**
	 * @return void
	 */
	public function updatePersonReihungstest($ddReihungstest)
	{
		$pksArray = array($ddReihungstest['person_id'], $ddReihungstest['rt_id']);
		
		return $this->ci->RtPersonModel->update($pksArray, $ddReihungstest);
	}
	
	/**
	 * @return void
	 */
	public function deletePersonReihungstest($ddReihungstest)
	{
		return $this->ci->RtPersonModel->delete($ddReihungstest['rt_person_id'], $ddReihungstest);
	}
	
	/**
	 * @return void
	 */
	public function getReihungstestByPersonID($person_id, $available = null)
	{
		$this->ci->ReihungstestModel->addJoin('public.tbl_rt_person', 'reihungstest_id = rt_id');
		$this->ci->ReihungstestModel->addJoin('public.tbl_person', 'person_id');
		$this->ci->ReihungstestModel->addJoin('public.tbl_ort', 'tbl_ort.ort_kurzbz = tbl_rt_person.ort_kurzbz', 'LEFT');
		
		$parametersArray = array('person_id' => $person_id);
		
		if (isset($available))
		{
			$parametersArray['anmeldefrist >='] = 'NOW()';
		}
		
		return $this->ci->ReihungstestModel->loadWhere($parametersArray);
	}
	
	/**
	 * It checks if the test is available
	 */
	public function checkAvailability($reihungstest_id)
	{
		$result = $this->ci->ReihungstestModel->checkAvailability($reihungstest_id);
		
		if (hasData($result))
		{
			return true;
		}
		
		return false;
	}
}
