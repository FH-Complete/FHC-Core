<?php
defined('BASEPATH') || exit('No direct script access allowed');
/**
 * Description of Widget
 *
 * @author chris
 */
class Widget extends Auth_Controller
{
	private $demoData = [
		[
			"id" => 1,
			"name" => 'Widget 1',
			"description" => 'Das ist ein Test Widget',
			"icon" => 'https://upload.wikimedia.org/wikipedia/commons/8/8a/Farben-Testbild.svg',
			"file" => 'DashboardWidget/Widget1.js',
			"arguments" => [
				"test" => 2
			],
			"size" => [
				"width" => [ "max" => 3 ],
				"height" => [ "max" => 3 ]
			]
		]
	];

	public function __construct()
	{
		parent::__construct(
			array(
				'index'							=> 'dashboard/benutzer:r',
				'getWidgetsForDashboard'		=> 'dashboard/benutzer:rw',
				'addWidgetToUserOverride'		=> 'dashboard/benutzer:rw',
				'updateWidgetsToUserOverride'	=> 'dashboard/benutzer:rw',
				'removeWidgetFromUserOverride'	=> 'dashboard/benutzer:rw'
			)
		);
		
		$this->load->library('dashboard/DashboardLib', null, 'DashboardLib');
	}
	
	public function index() 
	{
		$widget_id = $this->input->get('id');

		foreach ($this->demoData as $widget) {
			if ($widget["id"] == $widget_id)
				return $this->outputJsonSuccess($widget);
		}
		return $this->outputJsonSuccess([
			"id" => 0,
			"name" => 'Widget Not Found',
			"file" => 'DashboardWidget/Default.js',
			"arguments" => [
				"className" => 'alert-danger',
				"title" => 'Widget Not Found',
				"msg" => 'The widget with the id ' . $widget_id . ' could not be found'
			],
			"size" => [
				"width" => 1,
				"height" => 1
			]
		]);
	}
	
	public function getWidgetsForDashboard() 
	{
		$db = $this->input->get('db');

		$this->outputJsonSuccess($this->demoData);
	}
	
	public function addWidgetToUserOverride() 
	{
		$input = json_decode($this->input->raw_input_stream);
		$dashboard_kurzbz = $input->db;
		$uid = $input->uid;
		$funktion = $input->funktion;
		
		$override = $this->DashboardLib->getOverrideOrCreateEmptyOverride($dashboard_kurzbz, $uid);		
		
		$override_decoded = json_decode($override->override, true);
		$widgetid = isset($input->widgetid) ? array('widgetid' => $input->widgetid) 
			: $this->DashboardLib->generateWidgetId($dashboard_kurzbz);
		
		$override_decoded[$funktion][$widgetid['widgetid']] = $input->widget;
		
		$override->override = json_encode($override_decoded);
				
		$result = $this->DashboardLib->insertOrUpdateOverride($override);
		if( isError($result) ) {
			http_response_code(500);
			$this->terminateWithJsonError('override could not be saved');
		}
		
		$this->outputJsonSuccess($widgetid['widgetid']);
	}
	
	public function updateWidgetsToUserOverride() 
	{
		$input = json_decode($this->input->raw_input_stream, true);
		$dashboard_kurzbz = $input['db'];
		$uid = $input['uid'];
		$funktion = $input['funktion'];

		$override = $this->DashboardLib->getOverrideOrCreateEmptyOverride($dashboard_kurzbz, $uid);		
		
		$override_decoded = json_decode($override->override, true);
		if ($override_decoded[$funktion])
			$override_decoded[$funktion] = array_replace_recursive($override_decoded[$funktion], $input['widgets']);
		else
			$override_decoded[$funktion] = $input['widgets'];
		
		$override->override = json_encode($override_decoded);
				
		$result = $this->DashboardLib->insertOrUpdateOverride($override);
		if( isError($result) ) {
			http_response_code(500);
			$this->terminateWithJsonError('override could not be saved');
		}
		
		$this->outputJsonSuccess(['msg' => 'override successfully updated.']);
	}
	
	public function removeWidgetFromUserOverride()
	{
		$input = json_decode($this->input->raw_input_stream);
		$dashboard_kurzbz = $input->db;
		$uid = $input->uid;
		$funktion = $input->funktion;
		$widgetid = $input->widgetid;
		
		$override = $this->DashboardLib->getOverride($dashboard_kurzbz, $uid);
		if( empty($override) ) {
			http_response_code(404);
			$this->terminateWithJsonError('userconfig for dashboard ' 
				. $dashboard_kurzbz . ' not found.');
		}
		
		$override_decoded = json_decode($override->override, true);
		
		if( array_key_exists($widgetid, $override_decoded[$funktion]) )
		{
			unset($override_decoded[$funktion][$widgetid]);
		}
		else
		{
			http_response_code(404);
			$this->terminateWithJsonError('widgetid ' . $widgetid . ' not found in funktion ' . $funktion);
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
	
}
