<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class OperationalResourceToCalenderAPI extends FHCAPI_Controller
{
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getAssignedResourcesByCalenderId' => ['admin:r', 'assistenz:r'],
			'storeResourcesToCalendarRelationship' => ['admin:w', 'assistenz:w'],
			'getSchedulableResourcesByCalendar' => ['admin:r', 'assistenz:r'],
		]);

		$this->load->model('ressource/BetriebsmittelKalender_model', 'BetriebsmittelKalenderModel');
		$this->load->model('ressource/Betriebsmittel_model', 'BetriebsmittelModel');
		$this->load->model('ressource/Kalender_model', 'KalenderModel');

		$this->load->library('CollisionChecker');
		$this->load->library('KalenderLib');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods
	public function getSchedulableResourcesByCalendar($calenderID)
	{ 
		if (!isset($calenderID)) $this->terminateWithError("Missing required parameter 'kalender_id'");

		$result = $this->KalenderModel->loadWhere(['kalender_id' => $calenderID]);
		if (isError($result)) $this->terminateWithError("Calendar with id '$calenderID' does not have a valid group id");
 
		$calender = $this->getDataOrTerminateWithError($result)[0];
		if (!isset($calender)) $this->terminateWithError("Calendar with id '$calenderID' not found");

		$result = $this->BetriebsmittelModel->getSchedulableEntriesByDatetimeInterval($calender->von, $calender->bis);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function getAssignedResourcesByCalenderId($calenderID)
	{
		if (!isset($calenderID)) $this->terminateWithError("Missing required parameter 'kalender_id'");

		$result = $this->KalenderModel->loadWhere(['kalender_id' => $calenderID]);
		if (empty($result)) $this->terminateWithError("Calendar with id '$calenderID' not found");
 
		$calenderGroupID = $this->getDataOrTerminateWithError($result)[0]->eindeutige_gruppen_id;
		if (!isset($calenderGroupID)) $this->terminateWithError("Calendar with id '$calenderID' does not have a valid group id");


		$this->BetriebsmittelKalenderModel->addSelect(['tbl_betriebsmittel_kalender.*', 'tbl_betriebsmittel.beschreibung', 'tbl_betriebsmittel.verplanen']);
		$this->BetriebsmittelKalenderModel->addJoin('wawi.tbl_betriebsmittel', 'betriebsmittel_id');
		$result = $this->BetriebsmittelKalenderModel->loadWhere([
			'eindeutige_kalender_gruppen_id' => $calenderGroupID,
			'tbl_betriebsmittel.verplanen' => true,
		]);

		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function storeResourcesToCalendarRelationship()
	{
		$calenderID = $this->input->post('kalender_id');
		$assignedResources = $this->input->post('assignedResources');

		if (!isset($calenderID)) $this->terminateWithError("Missing required parameter 'kalender_id'", 'general');
		if (!isset($assignedResources)) $this->terminateWithError("Missing required parameter 'assignedResources'", 'general');

		$result = $this->KalenderModel->loadWhere(['kalender_id' => $calenderID]);
		if (empty($result)) $this->terminateWithError("Calendar with id '$calenderID' not found");

		$calendar = $this->getDataOrTerminateWithError($result)[0];
		
		$calenderGroupID = $calendar->eindeutige_gruppen_id;
		if (!isset($calenderGroupID)) $this->terminateWithError("Calendar with id '$calenderID' does not have a valid group id");

		$result = $this->kalenderlib->addOperationalResourcesToKalenderEvent($calendar, $assignedResources);
		if (isError($result))
			$this->terminateWithError(getError($result));

		$this->terminateWithSuccess(['message' => 'Resources assigned successfully']);
	}
}
