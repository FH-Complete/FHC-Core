<?php
defined('BASEPATH') || exit('No direct script access allowed');
/**
 * Description of Widget
 *
 * @author chris
 */
class Widget extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct(
			array(
				'index'							=> ['dashboard/benutzer:r', 'dashboard/admin:r'],
				'getAll'						=> 'dashboard/admin:r',
				'getWidgetsForDashboard'		=> ['dashboard/benutzer:rw', 'dashboard/admin:r'],
				'setAllowed'					=> 'dashboard/admin:rw'
			)
		);
		
		$this->load->library('dashboard/DashboardLib', null, 'DashboardLib');
		$this->load->model('dashboard/Widget_model', 'WidgetModel');
		$this->load->model('dashboard/Dashboard_Widget_model', 'DashboardWidgetModel');
	}
	
	public function index()
	{
		$widget_id = $this->input->get('id');

		$widget = $this->WidgetModel->load($widget_id);

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
	
	public function getAll()
	{
		$dashboard_id = $this->input->get('dashboard_id');
		$result = $this->WidgetModel->getWithAllowedForDashboard($dashboard_id);

		if (isError($result))
			return $this->outputJsonError(getError($result));

		$this->outputJsonSuccess(getData($result) ?: []);
	}
	
	public function getWidgetsForDashboard()
	{
		$db = $this->input->get('db');
		$result = $this->WidgetModel->getForDashboard($db);

		if (isError($result)) {
			http_response_code(404);
			$this->terminateWithJsonError([
				'error' => getError($result)
			]);
		}

		$this->outputJsonSuccess(getData($result) ?: []);
	}
	
	public function setAllowed()
	{
		$input = $this->getPostJSON();

		$dashboard_id = $input->dashboard_id;
		$widget_id = $input->widget_id;
		$action = $input->action;

		if ($action == 'add') {
			$result = $this->DashboardWidgetModel->insert([
				'dashboard_id' => $dashboard_id,
				'widget_id' => $widget_id
			]);
		} elseif ($action == 'delete') {
			$result = $this->DashboardWidgetModel->delete([
				'dashboard_id' => $dashboard_id,
				'widget_id' => $widget_id
			]);
		} else {
			http_response_code(404); // TODO(chris): 400?
			$this->terminateWithJsonError([
				'error' => 'action value invalid'
			]);
		}
		if (isError($result)) {
			http_response_code(404);
			$this->terminateWithJsonError([
				'error' => getError($result)
			]);
		}
		return $this->outputJsonSuccess(getData($result));
	}
}
