<?php
defined('BASEPATH') || exit('No direct script access allowed');
/**
 * Description of Config
 *
 * @author bambi
 */
class Config extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct(
			array(
				'index'							=> 'dashboard/benutzer:r',
				'dummy'							=> 'dashboard/benutzer:r',
				'genWidgetId'					=> 'dashboard/benutzer:rw',
				'addWidgetsToPreset'			=> 'dashboard/admin:rw',
				'removeWidgetFromPreset'		=> 'dashboard/admin:rw',
				'addWidgetsToUserOverride'		=> 'dashboard/benutzer:rw',
				'removeWidgetFromUserOverride'	=> 'dashboard/benutzer:rw',
				'funktionen'					=> 'dashboard/admin:r',
				'preset'						=> 'dashboard/admin:r',
				'presetBatch'					=> 'dashboard/admin:r'
			)
		);
		
		$this->load->library('dashboard/DashboardLib', null, 'DashboardLib');
		$this->load->library('AuthLib', null, 'AuthLib');
		$this->load->model('ressource/Funktion_model', 'FunktionModel');
	}
	
	public function index()
	{
		$dashboard_kurzbz = $this->input->get('db');
		$uid = $this->AuthLib->getAuthObj()->username;

		$dashboard = $this->DashboardLib->getDashboardByKurzbz($dashboard_kurzbz);
		if(!$dashboard) {
			http_response_code(404);
			$this->terminateWithJsonError(array(
				'error' => 'Dashboard ' . $dashboard_kurzbz . ' not found.'
			));
		}
		
		$mergedconfig = $this->DashboardLib->getMergedConfig($dashboard->dashboard_id, $uid);
		$this->outputJsonSuccess($mergedconfig);
	}
	
	public function genWidgetId()
	{
		$dashboard_kurzbz = $this->input->get('db');
		$widgetid = $this->DashboardLib->generateWidgetId($dashboard_kurzbz);
		$this->outputJsonSuccess(array(
			'widgetid' => $widgetid
		));
	}
	
	public function addWidgetsToPreset()
	{
		$input = json_decode($this->input->raw_input_stream);
		$dashboard_kurzbz = $input->db;
		$funktion_kurzbz = $input->funktion_kurzbz;
		
		$preset = $this->DashboardLib->getPresetOrCreateEmptyPreset($dashboard_kurzbz, $funktion_kurzbz);
		
		$preset_decoded = json_decode($preset->preset, true);
		
		$this->DashboardLib->addWidgetsToWidgets($preset_decoded['widgets'], $dashboard_kurzbz, $funktion_kurzbz, $input->widgets);
		
		$preset->preset = json_encode($preset_decoded);
				
		$result = $this->DashboardLib->insertOrUpdatePreset($preset);
		if (isError($result)) {
			http_response_code(500);
			$this->terminateWithJsonError('preset could not be saved');
		}
		
		$this->outputJsonSuccess(array('msg' => 'preset successfully stored.', 'data' => $preset_decoded));
	}
	
	public function removeWidgetFromPreset()
	{
		$input = json_decode($this->input->raw_input_stream);
		$dashboard_kurzbz = $input->db;
		$funktion_kurzbz = $input->funktion_kurzbz;
		$widgetid = $input->widgetid;
				
		$preset = $this->DashboardLib->getPreset($dashboard_kurzbz, $funktion_kurzbz);
		if ($preset === null) {
			http_response_code(404);
			$this->terminateWithJsonError('preset for dashboard ' . $dashboard_kurzbz . ' and funktion ' . $funktion_kurzbz . ' not found.');
		}
		
		$preset_decoded = json_decode($preset->preset, true);
		if (!$this->DashboardLib->removeWidgetFromWidgets($preset_decoded['widgets'], $funktion_kurzbz, $widgetid))
		{
			http_response_code(404);
			$this->terminateWithJsonError('widgetid ' . $widgetid . ' not found');
		}
		
		$preset->preset = json_encode($preset_decoded);
		$result = $this->DashboardLib->insertOrUpdatePreset($preset);
		if (isError($result))
		{
			http_response_code(500);
			$this->terminateWithJsonError('failed to remove widget');
		}
		$this->outputJsonSuccess(array('msg' => 'preset successfully updated.'));
	}

	public function addWidgetsToUserOverride()
	{
		$input = json_decode($this->input->raw_input_stream);
		$dashboard_kurzbz = $input->db;
		$funktion_kurzbz = $input->funktion_kurzbz;
		$uid = $this->AuthLib->getAuthObj()->username;
		
		$override = $this->DashboardLib->getOverrideOrCreateEmptyOverride($dashboard_kurzbz, $uid);
		
		$override_decoded = json_decode($override->override, true);

		$this->DashboardLib->addWidgetsToWidgets($override_decoded['widgets'], $dashboard_kurzbz, $funktion_kurzbz, $input->widgets);
		
		$override->override = json_encode($override_decoded);
				
		$result = $this->DashboardLib->insertOrUpdateOverride($override);
		if (isError($result)) {
			http_response_code(500);
			$this->terminateWithJsonError('override could not be saved');
		}
		
		$this->outputJsonSuccess(array('msg' => 'override successfully stored.', 'data' => $override_decoded));
	}
	
	public function removeWidgetFromUserOverride()
	{
		$input = json_decode($this->input->raw_input_stream);
		$dashboard_kurzbz = $input->db;
		$funktion_kurzbz = $input->funktion_kurzbz;
		$uid = $this->AuthLib->getAuthObj()->username;
		$widgetid = $input->widgetid;
		
		$override = $this->DashboardLib->getOverride($dashboard_kurzbz, $uid);
		if (empty($override)) {
			http_response_code(404);
			$this->terminateWithJsonError('userconfig for dashboard ' . $dashboard_kurzbz . ' not found.');
		}
		
		$override_decoded = json_decode($override->override, true);
		
		if (!$this->DashboardLib->removeWidgetFromWidgets($override_decoded['widgets'], $funktion_kurzbz, $widgetid))
		{
			http_response_code(404);
			$this->terminateWithJsonError('widgetid ' . $widgetid . ' not found');
		}
		
		$override->override = json_encode($override_decoded);
		$result = $this->DashboardLib->insertOrUpdateOverride($override, $uid);
		if (isError($result))
		{
			http_response_code(500);
			$this->terminateWithJsonError('failed to remove widget');
		}
		$this->outputJsonSuccess(array('msg' => 'override successfully updated.'));
	}
	
	public function funktionen()
	{
		$funktionen = $this->FunktionModel->load();

		if (isError($funktionen)) {
			http_response_code(404);
			$this->terminateWithJsonError([
				'error' => getError($funktionen)
			]);
		}
		
		return $this->outputJsonSuccess(getData($funktionen) ?: []);
	}
	
	public function preset()
	{
		$db = $this->input->get('db');
		$funktion = $this->input->get('funktion');

		$conf = $this->DashboardLib->getPreset($db, $funktion);

		if (!$conf)
			return $this->outputJsonSuccess(['widgets' => [$funktion => []]]);

		return $this->outputJsonSuccess(json_decode($conf->preset, true));
	}
	
	public function presetBatch()
	{
		$db = $this->input->get('db');
		$funktionen = $this->input->get('funktionen');
		$result = [];

		foreach ($funktionen as $funktion) {
			$conf = $this->DashboardLib->getPreset($db, $funktion);
			if ($conf)
			{
				$preset = json_decode($conf->preset, true);
				if (!isset($preset['widgets']) || !isset($preset['widgets'][$funktion]))
					$result[$funktion] = [];
				else
					$result[$funktion] = $preset['widgets'][$funktion];
			}
			else
				$result[$funktion] = [];
		}

		return $this->outputJsonSuccess($result);
	}
}
