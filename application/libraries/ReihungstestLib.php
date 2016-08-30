<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

/**
 * 
 */
class ReihungstestLib
{
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->ci =& get_instance();
		
		$this->ci->load->model("crm/RtPerson_model", "RtPersonModel");
	}
	
	/**
	 * @return void
	 */
	public function insertPersonReihungstest($ddReihungstest)
	{
		return $this->ci->RtPersonModel->insert($ddReihungstest);
	}
	
	/**
	 * @return void
	 */
	public function updatePersonReihungstest($ddReihungstest)
	{
		$pksArray = array($ddReihungstest["person_id"], $ddReihungstest["rt_id"]);
		
		return $this->ci->RtPersonModel->update($pksArray, $ddReihungstest);
	}
	
	/**
	 * @return void
	 */
	public function deletePersonReihungstest($ddReihungstest)
	{
		return $this->ci->RtPersonModel->delete($ddReihungstest["rt_person_id"], $ddReihungstest);
	}
	
	/**
	 * @return void
	 */
	public function getReihungstestByPersonID($person_id)
	{
		$this->ci->ReihungstestModel->addJoin("public.tbl_rt_person", "reihungstest_id = rt_id");
		$this->ci->ReihungstestModel->addJoin("public.tbl_person", "person_id");
		$this->ci->ReihungstestModel->addJoin("public.tbl_ort", "tbl_ort.ort_kurzbz = tbl_rt_person.ort_kurzbz", "LEFT");
		
		return $this->ci->ReihungstestModel->loadWhere(array("person_id" => $person_id));
	}
}