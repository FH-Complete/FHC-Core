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
			// load lektor object
			foreach($item->lektor as $lv_lektor){
				$this->MitarbeiterModel->addLimit(1);
				$lektor_obj = $this->MitarbeiterModel->loadWhere(["kurzbz"=>$lv_lektor]);
				if (isError($lektor_obj)) {
					$this->outputJsonError(getError($lektor_obj));
				}
				$lektor_obj = current(getData($lektor_obj));
				$lektor_obj_array[] = $lektor_obj;
			}
			// load gruppe object
			foreach ($item->gruppe as $lv_gruppe) {
				$lv_gruppe = strtr($lv_gruppe,['('=>'',')'=>'','"'=>'']); 
				$lv_gruppe_array = explode(",",$lv_gruppe);
				list($gruppe,$verband, $semester,$studiengang_kz) = $lv_gruppe_array;
				$this->LehrverbandModel->addLimit(1);
				$lehrverband_obj = $this->LehrverbandModel->loadWhere(["gruppe" => $gruppe, "verband" => $verband, "semester" => $semester, "studiengang_kz" => $studiengang_kz]);
				if (isError($lehrverband_obj)) {
					$this->outputJsonError(getError($lehrverband_obj));
				}
				$lehrverband_obj = current(getData($lehrverband_obj));
				$gruppe_obj_array[] = $lehrverband_obj;
			}
			// studiengangs object
			$this->load->model('organisation/Studiengang_model','StudiengangModel');
			$this->StudiengangModel->addLimit(1);
			$studiengang_object = $this->StudiengangModel->load($item->studiengang_kz);
			$studiengang_object = current(getData($studiengang_object));
			$item->studiengang = $studiengang_object;
			$item->lektor = $lektor_obj_array;
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
