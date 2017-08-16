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

class Studiengang2 extends APIv1_Controller
{
	/**
	 * Course API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PersonModel
		$this->load->model("organisation/studiengang_model", "StudiengangModel");
	}
	
	public function getStudiengang()
	{
		$studiengang_kz = $this->get("studiengang_kz");
		
		if (isset($studiengang_kz))
		{
			$result = $this->StudiengangModel->load($studiengang_kz);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	public function getAllForBewerbung()
	{
		$this->response($this->StudiengangModel->getAllForBewerbung(), REST_Controller::HTTP_OK);
	}
	
	/**
	 * Method getStudiengangStudienplan
	 */
	public function getStudiengangStudienplan()
	{
		// Getting HTTP GET parameters
		$studiensemester_kurzbz = $this->get("studiensemester_kurzbz");
		$ausbildungssemester = $this->get("ausbildungssemester");
		$aktiv = $this->get("aktiv");
		$onlinebewerbung = $this->get("onlinebewerbung");
		
		// If $studiensemester_kurzbz and $ausbildungssemester are present
		if (isset($studiensemester_kurzbz) && isset($ausbildungssemester))
		{
			// Check & set
			if (!isset($aktiv)) $aktiv = "TRUE";
			if (!isset($onlinebewerbung)) $onlinebewerbung = "TRUE";
			
			$result = $this->StudiengangModel->getStudienplan($studiensemester_kurzbz, $ausbildungssemester, $aktiv, $onlinebewerbung);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	public function getStudiengangBewerbung()
	{
		$result = $this->StudiengangModel->getStudiengangBewerbung();
			
		$this->response($result, REST_Controller::HTTP_OK);
	}
	
	/**
	 * @return void
	 */
	public function getAppliedStudiengang()
	{
		$person_id = $this->get('person_id');
		$studiensemester_kurzbz = $this->get('studiensemester_kurzbz');
		$titel = $this->get('titel');
		
		if (isset($person_id) && isset($studiensemester_kurzbz) && isset($titel))
		{
			$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
			
			$result = $this->StudiengangModel->getAppliedStudiengang(
				$person_id,
				$studiensemester_kurzbz,
				$titel
			);
			
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
	public function getAppliedStudiengangFromNow()
	{
		$person_id = $this->get('person_id');
		$titel = $this->get('titel');
		
		if (isset($person_id) && isset($titel))
		{
			$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
			
			$result = $this->StudiengangModel->getAppliedStudiengangFromNow(
				$person_id,
				$titel
			);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
}