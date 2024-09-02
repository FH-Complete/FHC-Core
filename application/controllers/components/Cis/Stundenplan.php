<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 */
class Stundenplan extends Auth_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'index' => ['student/anrechnung_beantragen:r','user:r'], // TODO(chris): permissions?
			'Reservierungen' => ['student/anrechnung_beantragen:r','user:r'], // TODO(chris): permissions?
			'Stunden' => ['student/anrechnung_beantragen:r','user:r'], // TODO(chris): permissions?
			'RoomInformation'=> ['student/anrechnung_beantragen:r','user:r'],
		]);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 */
	public function index()
	{
		$this->load->model('ressource/Stundenplan_model', 'StundenplanModel');

		/* $result = $this->StundenplanModel->loadForUid(get_uid());

		if (isError($result))
			return $this->outputJsonError(getError($result));
 */


		$res = $this->StundenplanModel->stundenplanGruppierung($this->StundenplanModel->getStudenPlanQuery(get_uid())); 
		
		$res = getData($res);

		// get the benutzer object for the lektor of the lv	
		$this->load->model('ressource/Mitarbeiter_model','MitarbeiterModel');
		$this->load->model('organisation/Lehrverband_model', 'LehrverbandModel');
		foreach($res as $item){
			$lektor_obj_array = array();
			$gruppe_obj_array = array();
			foreach($item->lektor as $lv_lektor){
				$lektor_obj = $this->MitarbeiterModel->loadWhere(["kurzbz"=>$lv_lektor]);
				if (isError($lektor_obj)) {
					$this->outputJsonError(getError($lektor_obj));
				}
				$lektor_obj = getData($lektor_obj);
				$lektor_obj_array[] = $lektor_obj;
			}
			foreach ($item->gruppe as $lv_gruppe) {
				$lv_gruppe = str_replace("(","",$lv_gruppe);
				$lv_gruppe = str_replace(")", "", $lv_gruppe);
				$lv_gruppe_array = explode(",",$lv_gruppe);
				$gruppe = str_replace("\"","",$lv_gruppe_array[0]);
				$verband = $lv_gruppe_array[1];
				$semester = $lv_gruppe_array[2];
				$studiengang_kz = $lv_gruppe_array[3];
				$lehrverband_obj = $this->LehrverbandModel->loadWhere(["gruppe" => $gruppe, "verband" => $verband, "semester" => $semester, "studiengang_kz" => $studiengang_kz]);
				if (isError($lehrverband_obj)) {
					$this->outputJsonError(getError($lehrverband_obj));
				}
				$lehrverband_obj = getData($lehrverband_obj);
				$gruppe_obj_array[] = $lehrverband_obj;
			}
			//replace the array of lektor strings with the objects of lektor information
			$item->lektor = $lektor_obj_array;
			//replace the array of gruppen string with the lehrverband object information
			$item->gruppe = $gruppe_obj_array;
			
		}
		
		$this->outputJsonSuccess($res);
	}

	/**
	 */
	public function Reservierungen()
	{
		$this->load->model('ressource/Reservierung_model', 'ReservierungModel');

		$result = $this->ReservierungModel->loadForUid(get_uid());

		if (isError($result))
			return $this->outputJsonError(getError($result));

		$this->outputJsonSuccess(getData($result));
	}

	/**
	 */
	public function Stunden()
	{
		$this->load->model('ressource/Stunde_model', 'StundeModel');

		$result = $this->StundeModel->load();

		if (isError($result))
			return $this->outputJsonError(getError($result));

		$this->outputJsonSuccess(getData($result));
	}


}
