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
	
	public function getStudiengangStudienplan()
	{
		$studiensemester_kurzbz = $this->get("studiensemester_kurzbz");
		$ausbildungssemester = $this->get("ausbildungssemester");
		$aktiv = $this->get("aktiv");
		$onlinebewerbung = $this->get("onlinebewerbung");
		
		if (isset($studiensemester_kurzbz) && isset($ausbildungssemester))
		{
			$this->load->model("organisation/Studienplan_model", "StudienplanModel");
			$result = $this->StudienplanModel->addJoin("lehre.tbl_studienplan_semester", "studienplan_id");
			if ($result->error == EXIT_SUCCESS)
			{
				$result = $this->StudienplanModel->addJoin("lehre.tbl_studienordnung", "studienordnung_id");
				if ($result->error == EXIT_SUCCESS)
				{
					$this->StudienplanModel->addSelect("tbl_studienplan.*, lehre.tbl_studienordnung.studiengang_kz");

					$this->StudienplanModel->addOrder("lehre.tbl_studienordnung.studiengang_kz");

					if (!isset($aktiv)) $aktiv = "TRUE";
					if (!isset($onlinebewerbung)) $onlinebewerbung = "TRUE";

					$resultStudienplan = $this->StudienplanModel->loadWhere(
						array("semester" => $ausbildungssemester,
								"studiensemester_kurzbz" => $studiensemester_kurzbz)
					);
					
					if (is_object($resultStudienplan) && $resultStudienplan->error == EXIT_SUCCESS &&
						is_array($resultStudienplan->retval) && count($resultStudienplan->retval) > 0)
					{
						$studiengangCount = 0;
						$prevStudiengang_kz = "";
						$studiengangArray = array();

						for ($i = 0; $i < count($resultStudienplan->retval); $i++)
						{
							if ($prevStudiengang_kz == $resultStudienplan->retval[$i]->studiengang_kz)
							{
								if (isset($studiengangArray[$studiengangCount - 1]) && is_array($studiengangArray[$studiengangCount - 1]->studienplaene))
								{
									array_push($studiengangArray[$studiengangCount - 1]->studienplaene, $resultStudienplan->retval[$i]);
								}
							}
							else
							{
								$resultStudiengang = $this->StudiengangModel->loadWhere(
									array("studiengang_kz" => $resultStudienplan->retval[$i]->studiengang_kz,
											"aktiv" => $aktiv,
											"onlinebewerbung" => $onlinebewerbung)
								);
								
								if (is_object($resultStudiengang) && $resultStudiengang->error == EXIT_SUCCESS &&
									is_array($resultStudiengang->retval) && count($resultStudiengang->retval) > 0)
								{
									$resultStudiengang->retval[0]->studienplaene = array($resultStudienplan->retval[$i]);
									$studiengangArray[$studiengangCount++] = $resultStudiengang->retval[0];
								}
								
								$prevStudiengang_kz = $resultStudienplan->retval[$i]->studiengang_kz;
							}
						}

						$result = $this->_success($studiengangArray);
					}
				}
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _getStudienplaene($studiengang_kz, $studiensemester_kurzbz, $ausbildungssemester)
	{
		$studienplaene = null;
		
		// Loading model for Studienplan
		$this->load->model("organisation/Studienplan_model", "StudienplanModel");
		
		$result = $this->StudienplanModel->addJoin("lehre.tbl_studienplan_semester", "studienplan_id");
		if ($result->error == EXIT_SUCCESS)
		{
			$result = $this->StudienplanModel->addJoin("lehre.tbl_studienordnung", "studienordnung_id");
			if ($result->error == EXIT_SUCCESS)
			{
				$this->StudienplanModel->addSelect("tbl_studienplan.*, lehre.tbl_studienordnung.studiengang_kz");

				$resultStudienplan = $this->StudienplanModel->loadWhere(array(
					"studiengang_kz" => $studiengang_kz,
					"semester" => $ausbildungssemester,
					"studiensemester_kurzbz" => $studiensemester_kurzbz
				));
				if (is_object($resultStudienplan) && $resultStudienplan->error == EXIT_SUCCESS &&
					is_array($resultStudienplan->retval))
				{
					$studienplaene = $resultStudienplan->retval;
				}
			}
		}
		
		return $studienplaene;
	}
	
	private function _getBewerbungstermine($studiengang_kz, $studiensemester_kurzbz)
	{
		$bewerbungstermine = null;
		
		// Loading model for Bewerbungstermine
		$this->load->model("crm/bewerbungstermine_model", "BewerbungstermineModel");
		
		$resultBewerbungstermine = $this->BewerbungstermineModel->loadWhere(array(
			"studiengang_kz" => $studiengang_kz,
			"studiensemester_kurzbz" => $studiensemester_kurzbz
		));
		if (is_object($resultBewerbungstermine) && $resultBewerbungstermine->error == EXIT_SUCCESS &&
			is_array($resultBewerbungstermine->retval))
		{
			$bewerbungstermine = $resultBewerbungstermine->retval;
		}
		
		return $bewerbungstermine;
	}
	
	private function _getReihungstests($studiengang_kz, $studiensemester_kurzbz)
	{
		$reihungstests = null;
		
		// Loading model for Reihungstests
		$this->load->model("crm/reihungstest_model", "ReihungstestModel");
		
		$resultReihungstests = $this->ReihungstestModel->loadWhere(array(
			"studiengang_kz" => $studiengang_kz,
			"studiensemester_kurzbz" => $studiensemester_kurzbz
		));
		if (is_object($resultReihungstests) && $resultReihungstests->error == EXIT_SUCCESS &&
			is_array($resultReihungstests->retval))
		{
			$reihungstests = $resultReihungstests->retval;
		}
		
		return $reihungstests;
	}
	
	public function getCompleteStudiengang()
	{
		$studiensemester_kurzbz = $this->get("studiensemester_kurzbz");
		$ausbildungssemester = $this->get("ausbildungssemester");
		$aktiv = $this->get("aktiv");
		$onlinebewerbung = $this->get("onlinebewerbung");
		
		if (isset($studiensemester_kurzbz) && isset($ausbildungssemester))
		{
			if (!isset($aktiv)) $aktiv = "TRUE";
			if (!isset($onlinebewerbung)) $onlinebewerbung = "TRUE";
			
			$resultStudiengang = $this->StudiengangModel->loadWhere(
				array("aktiv" => $aktiv,
					  "onlinebewerbung" => $onlinebewerbung)
			);
			if (is_object($resultStudiengang) && $resultStudiengang->error == EXIT_SUCCESS &&
				is_array($resultStudiengang->retval))
			{
				for ($i = 0; $i < count($resultStudiengang->retval); $i++)
				{
					$studiengang_kz = $resultStudiengang->retval[$i]->studiengang_kz;
					
					// Getting all studienplaene for this studiengang
					$resultStudiengang->retval[$i]->studienplaene = $this->_getStudienplaene(
						$studiengang_kz,
						$studiensemester_kurzbz,
						$ausbildungssemester
					);
					// Getting all bewerbungstermine for this studiengang
					$resultStudiengang->retval[$i]->bewerbungstermine = $this->_getBewerbungstermine(
						$studiengang_kz,
						$studiensemester_kurzbz
					);
					// Getting all reihungstests for this studiengang
					$resultStudiengang->retval[$i]->reihungstests = $this->_getReihungstests(
						$studiengang_kz,
						$studiensemester_kurzbz
					);
				}
			}
			
			$this->response($resultStudiengang, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
}