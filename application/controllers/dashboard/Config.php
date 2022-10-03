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
				'addWidgetToPreset'				=> 'dashboard/admin:rw',
				'removeWidgetFromPreset'		=> 'dashboard/admin:rw',
				'addWidgetToUserOverride'		=> 'dashboard/benutzer:rw',
				'removeWidgetFromUserOverride'	=> 'dashboard/benutzer:rw'
			)
		);
		
		$this->load->library('dashboard/DashboardLib', null, 'DashboardLib');
	}
	
	public function index() 
	{
		$dashboard_kurzbz = $this->input->get('db');
		$uid = $this->input->get('uid');
		
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
			'widgetid' => $widgetid['widgetid']
		));
	}
	
	public function addWidgetToPreset() 
	{
		$input = json_decode($this->input->raw_input_stream);
		$dashboard_kurzbz = $input->db;
		$funktion_kurzbz = $input->funktion_kurzbz;
		
		$preset = $this->DashboardLib->getPresetOrCreateEmptyPreset($dashboard_kurzbz, $funktion_kurzbz);		
		
		$preset_decoded = json_decode($preset->preset, true);
		$widgetid = $this->DashboardLib->generateWidgetId($dashboard_kurzbz);
		
		$this->DashboardLib->addWidgetToWidgets($preset_decoded['widgets'], 
			$funktion_kurzbz, $input->widget, $widgetid['widgetid']);
		
		$preset->preset = json_encode($preset_decoded);
				
		$result = $this->DashboardLib->insertOrUpdatePreset($preset);
		if( isError($result) ) {
			http_response_code(500);
			$this->terminateWithJsonError('preset could not be saved');
		}
		
		$this->outputJsonSuccess(array('msg' => 'preset successfully stored.'));
	}
	
	public function removeWidgetFromPreset()
	{
		$input = json_decode($this->input->raw_input_stream);
		$dashboard_kurzbz = $input->db;
		$funktion_kurzbz = $input->funktion_kurzbz;
		$widgetid = $input->widgetid;
				
		$preset = $this->DashboardLib->getPreset($dashboard_kurzbz, $funktion_kurzbz);
		if( $preset === null ) {
			http_response_code(404);
			$this->terminateWithJsonError('preset for dashboard ' 
				. $dashboard_kurzbz . ' and funktion ' . $funktion_kurzbz 
				. ' not found.');
		}
		
		$preset_decoded = json_decode($preset->preset, true);
		
		if( $this->DashboardLib->removeWidgetFromWidgets($preset_decoded['widgets'], 
			$funktion_kurzbz, $widgetid) )
		{
			http_response_code(404);
			$this->terminateWithJsonError('widgetid ' . $widgetid . ' not found');
		}
		
		$preset->preset = json_encode($preset_decoded);
		$result = $this->DashboardLib->insertOrUpdatePreset($preset);
		if( isError($result) ) 
		{
			http_response_code(500);
			$this->terminateWithJsonError('failed to remove widget');	
		}
		$this->outputJsonSuccess(array('msg' => 'preset successfully updated.'));
	}

	public function addWidgetToUserOverride() 
	{
		$input = json_decode($this->input->raw_input_stream);
		$dashboard_kurzbz = $input->db;
		$funktion_kurzbz = $input->funktion_kurzbz;
		$uid = $input->uid;
		
		$override = $this->DashboardLib->getOverrideOrCreateEmptyOverride($dashboard_kurzbz, $uid);		
		
		$override_decoded = json_decode($override->override, true);
		$widgetid = isset($input->widgetid) ? array('widgetid' => $input->widgetid) 
			: $this->DashboardLib->generateWidgetId($dashboard_kurzbz);

		$this->DashboardLib->addWidgetToWidgets($override_decoded['widgets'], 
			$funktion_kurzbz, $input->widget, $widgetid['widgetid']);
		
		$override->override = json_encode($override_decoded);
				
		$result = $this->DashboardLib->insertOrUpdateOverride($override);
		if( isError($result) ) {
			http_response_code(500);
			$this->terminateWithJsonError('override could not be saved');
		}
		
		$this->outputJsonSuccess(array('msg' => 'override successfully stored.'));
	}
	
	public function removeWidgetFromUserOverride()
	{
		$input = json_decode($this->input->raw_input_stream);
		$dashboard_kurzbz = $input->db;
		$funktion_kurzbz = $input->funktion_kurzbz;
		$uid = $input->uid;
		$widgetid = $input->widgetid;
		
		$override = $this->DashboardLib->getOverride($dashboard_kurzbz, $uid);
		if( empty($override) ) {
			http_response_code(404);
			$this->terminateWithJsonError('userconfig for dashboard ' 
				. $dashboard_kurzbz . ' not found.');
		}
		
		$override_decoded = json_decode($override->override, true);
		
		if( !$this->DashboardLib->removeWidgetFromWidgets($override_decoded['widgets'], 
			$funktion_kurzbz, $widgetid) )
		{
			http_response_code(404);
			$this->terminateWithJsonError('widgetid ' . $widgetid . ' not found');
		}
		
		$override->override = json_encode($override_decoded);
		$result = $this->DashboardLib->insertOrUpdateOverride($override, $uid);
		if( isError($result) ) 
		{
			http_response_code(500);
			$this->terminateWithJsonError('failed to remove widget');	
		}
		$this->outputJsonSuccess(array('msg' => 'override successfully updated.'));
	}
	
	public function dummy() 
	{
		$defaultconfig = array(
			'title' => 'CIS Dashboard',
			'widgets' => array(
				'nofunction' => array(
					'd39ba153ac9e60a21ed694cc3728f4dd' => array(
						'title' => 'test1',
						'type' => 'kpi',
						'config' => array(),
						'place' => array(
							'row' => 1,
							'col' => 3,
							'width' => 1,
							'height' => 1
						)
					),
					'c6c526b78a0e4bc3a0b67e00f983fc33' => array(
						'title' => 'test2',
						'type' => 'url',
						'config' => array(),
						'place' => array(
							'row' => 3,
							'col' => 1,
							'width' => 1,
							'height' => 1
						)
					),
				),
				'leitung' => array(
					'3f1ebb24bdaa2b82fbdacf7d55977412' => array(
						'title' => 'test3',
						'type' => 'chart',
						'config' => array(),
						'place' => array(
							'row' => 2,
							'col' => 1,
							'width' => 3,
							'height' => 3
						)
					)
				)
			)
		);
		
		$userconfig = array(
			'widgets' => array(
				'nofunction' => array(
					'd39ba153ac9e60a21ed694cc3728f4dd' => array(
						'modifier' => 'bold',
						'visible' => false,
						'place' => array(
							'width' => 2,
							'height' => 2
						)
					),
					'c6c526b78a0e4bc3a0b67e00f983fc33' => array(
						'type' => 'test'
					)
				)
			)
		);
		
		$merged = array_replace_recursive($defaultconfig, $userconfig);
		
		$ret = array(
			'defaultconfig' => $defaultconfig,
			'userconfig' => $userconfig,
			'merged' => $merged
		);
		
		$this->outputJsonSuccess($ret);
	}
}
