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
	private static $PROPERTIES_SEPARATOR = "properties_separator";

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
			$result = null; // return variable
			
			// Check & set
			if (!isset($aktiv)) $aktiv = "TRUE";
			if (!isset($onlinebewerbung)) $onlinebewerbung = "TRUE";
			
			// Join table public.tbl_studiengang with table lehre.tbl_studienordnung on column studiengang_kz
			$result = $this->StudiengangModel->addJoin("lehre.tbl_studienordnung", "studiengang_kz");
			if ($result->error == EXIT_SUCCESS) // If the API caller has the rights
			{
				// Then join with table lehre.tbl_studienplan on column studienordnung_id
				$result = $this->StudiengangModel->addJoin("lehre.tbl_studienplan", "studienordnung_id");
				if ($result->error == EXIT_SUCCESS) // If the API caller has the rights
				{
					// Then join with table lehre.tbl_studienplan_semester on column studienplan_id
					$result = $this->StudiengangModel->addJoin("lehre.tbl_studienplan_semester", "studienplan_id");
					if ($result->error == EXIT_SUCCESS) // If the API caller has the rights
					{
						// Select all fields from table public.tbl_studiengang and table lehre.tbl_studienplan
						// The separator is used to keep data separated between table public.tbl_studiengang
						// and table lehre.tbl_studienplan (keep 'em separated!)
						$this->StudiengangModel->addSelect(
							"public.tbl_studiengang.*,
							'' as " . Studiengang2::$PROPERTIES_SEPARATOR . ",
							lehre.tbl_studienplan.*"
						);
						
						// Ordering by studiengang_kz and studienplan_id
						$this->StudiengangModel->addOrder("public.tbl_studiengang.studiengang_kz");
						$this->StudiengangModel->addOrder("lehre.tbl_studienplan.studienplan_id");
						
						// Execute the query
						$result = $this->StudiengangModel->loadWhere(array(
							"lehre.tbl_studienplan_semester.studiensemester_kurzbz" => $studiensemester_kurzbz,
							"lehre.tbl_studienplan_semester.semester" => $ausbildungssemester,
							"public.tbl_studiengang.aktiv" => $aktiv,
							"public.tbl_studiengang.onlinebewerbung" => $onlinebewerbung
						));
					}
				}
			}
			
			// If everything went ok...
			if (is_object($result) && $result->error == EXIT_SUCCESS &&
				is_array($result->retval) && count($result->retval) > 0)
			{
				$studiengangArray = array();	// Array that will contain all the studiengang
				$countReturnArray = 0;			// Array counter
				$prevStudiengang_kz = null;		// Previous studiengang key
				
				// Iterates the array that contains data from database
				for ($i = 0; $i < count($result->retval); $i++)
				{
					$objStudiengang = new stdClass(); // New object that represent a studiengang
					$objStudienplan = new stdClass(); // New object that represent a studienplan
					$separator = false;
					
					// Getting all the properties as an array, of an element of the array that
					// represents a single studiengang with its own studienplan
					foreach (get_object_vars($result->retval[$i]) as $key => $value)
					{
						// If the current element is the separator then ignore it!
						if ($key != Studiengang2::$PROPERTIES_SEPARATOR)
						{
							// Before the separator: studiengang
							if (!$separator)
							{
								$objStudiengang->{$key} = $value;
							}
							else // After the separator: studienplan
							{
								$objStudienplan->{$key} = $value;
							}
						}
						else
						{
							$separator = true;
						}
					}
					
					// If the current studiengang is the same as before, adds to it the studienplan
					if ($prevStudiengang_kz == $objStudiengang->studiengang_kz)
					{
						array_push($studiengangArray[$countReturnArray - 1]->studienplaene, $objStudienplan);
					}
					// Otherwise creates the property studienplaene and adds the first studienplan,
					// then adds the new studiengang to studiengangArray and sets prevStudiengang_kz
					else
					{
						$objStudiengang->studienplaene = array($objStudienplan);
						$studiengangArray[$countReturnArray++] = $objStudiengang;
						$prevStudiengang_kz = $objStudiengang->studiengang_kz;
					}
				}
				
				// Sets result with the standard success object that contains all the studiengang
				$result = $this->_success($studiengangArray);
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
}