<?php
/**
 * Copyright (C) 2026 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller operates between (interface) the JS (GUI) and the back-end
 * Provides data to the ajax get calls about the users dashboard
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Widget extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'get'							=> ['dashboard/benutzer:r', 'dashboard/admin:r'],
			'list'							=> 'dashboard/admin:r',
			'listAllowed'					=> ['dashboard/benutzer:rw', 'dashboard/admin:r'],
			'setAllowed'					=> 'dashboard/admin:rw'
		]);

		// Libraries
		$this->load->library('dashboard/DashboardLib');

		// Models
		$this->load->model('dashboard/Widget_model', 'WidgetModel');
	}

	public function get($id)
	{
		$result = $this->WidgetModel->load($id);

		$widget = $this->getDataOrTerminateWithError($result);

		if (!$widget)
			return $this->terminateWithSuccess([
				"widget_id" => 0,
				"widget_kurzbz" => "notfound",
				"arguments" => [
					"className" => 'alert-danger',
					"title" => 'Widget Not Found',
					"msg" => 'The widget with the id ' . $id . ' could not be found'
				],
				"setup" => [
					"name" => 'Widget Not Found',
					"file" => absoluteJsImportUrl('public/js/components/DashboardWidget/Default.js'),
					"width" => 1,
					"height" => 1
				]
			]);

		$widget = current($widget);
		$widget->arguments = json_decode($widget->arguments);
		$tmpsetup = json_decode($widget->setup);
		$tmpsetup->file = absoluteJsImportUrl($tmpsetup->file);
		$widget->setup = $tmpsetup;

		$this->terminateWithSuccess($widget);
	}

	public function list($dashboard)
	{
		$result = $this->WidgetModel->getWithAllowedForDashboard($dashboard);

		$widgets = $this->getDataOrTerminateWithError($result);

		$widgets = array_map(function ($widget) {
			$widget->arguments = json_decode($widget->arguments);
			$tmpsetup = json_decode($widget->setup);
			$tmpsetup->file = absoluteJsImportUrl($tmpsetup->file);
			$widget->setup = $tmpsetup;
			return $widget;
		}, $widgets);

		$this->terminateWithSuccess($widgets);
	}

	public function listAllowed($dashboard)
	{
		$result = $this->WidgetModel->getForDashboard($dashboard);

		$widgets = $this->getDataOrTerminateWithError($result);

		$widgets = array_map(function ($widget) {
			$widget->arguments = json_decode($widget->arguments);
			$tmpsetup = json_decode($widget->setup);
			$tmpsetup->file = absoluteJsImportUrl($tmpsetup->file);
			$widget->setup = $tmpsetup;
			return $widget;
		}, $widgets);

		$this->terminateWithSuccess($widgets);
	}

	public function setAllowed()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('dashboard_id', 'Dashboard', 'required');
		$this->form_validation->set_rules('widget_id', 'Widget', 'required');
		$this->form_validation->set_rules('allowed', 'Allowed', 'is_bool');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$data = [
			'dashboard_id' => $this->input->post('dashboard_id'),
			'widget_id' => $this->input->post('widget_id')
		];

		$this->load->model('dashboard/Dashboard_Widget_model', 'DashboardWidgetModel');
		
		if ($this->input->post('allowed'))
			$result = $this->DashboardWidgetModel->insert($data);
		else
			$result = $this->DashboardWidgetModel->delete($data);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}
}
