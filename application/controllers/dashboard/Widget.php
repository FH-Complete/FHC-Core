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
		$this->load->model('dashboard/Dashboard_Widget_model', 'DashboardWidgetModel');
	}
	
	public function index() 
	{
		$widget_id = $this->input->get('id');

		$widget = $this->DashboardWidgetModel->load($widget_id);

		if (isError($widget) || !getData($widget))
			return $this->outputJsonSuccess([
				"widget_id" => 0,
				"widget_kurzbz" => "notfound",
				"arguments" => json_encode([
					"className" => 'alert-danger',
					"title" => 'Widget Not Found',
					"msg" => 'The widget with the id ' . $widget_id . ' could not be found'
				]),
				"setup" => json_encode([
					"name" => 'Widget Not Found',
					"file" => 'DashboardWidget/Default.js',
					"width" => 1,
					"height" => 1
				])
			]);
		return $this->outputJsonSuccess(current(getData($widget)));
	}
	
	public function getWidgetsForDashboard() 
	{
		$db = $this->input->get('db');
		$result = $this->DashboardWidgetModel->getAllForDashboard($db);

		if (isError($result))
			return $this->outputJsonError(getError($result));

		$this->outputJsonSuccess(getData($result));
	}
	
}
