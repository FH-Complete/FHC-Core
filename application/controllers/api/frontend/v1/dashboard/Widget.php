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

use \DirectoryIterator as DirectoryIterator;

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
			'listAll'						=> 'dashboard/admin:r',
			'listAllOriginal'				=> 'dashboard/admin:rw',
			'setAllowed'					=> 'dashboard/admin:rw',
			'create'						=> 'dashboard/admin:rw',
			'update'						=> 'dashboard/admin:rw',
			'generators'					=> 'dashboard/admin:rw',
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

	public function listAll()
	{
		$result = $this->WidgetModel->load();

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

	public function listAllOriginal()
	{
		$result = $this->WidgetModel->load();

		$widgets = $this->getDataOrTerminateWithError($result);

		$widgets = array_map(function ($widget) {
			$widget->arguments = json_decode($widget->arguments);
			$widget->setup = json_decode($widget->setup);
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

	public function create()
	{
		$this->loadPhrases(['mobility', 'dashboard']);
		$this->load->library('form_validation');

		$this->form_validation->set_rules(
			'widget_kurzbz',
			$this->p->t('mobility', 'kurzbz'),
			'required|is_not_in_db[dashboard/Widget_model:widget_kurzbz]',
			[
				'is_not_in_db' => 'TODO(chris): error phrase'
			]
		);
		$this->form_validation->set_rules(
			'setup[generator]',
			$this->p->t('dashboard', 'generator'),
			'required'
		);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$setup = $this->input->post('setup');
		
		$file = explode(':', $setup['generator']);
		if (count($file) == 1) {
			$setup['file'] = 'public/js/components/DashboardWidget/' . $file[0] . '.js';
		} else {
			$setup['file'] = 'public/extensions/' . $file[0] . '/js/components/DashboardWidget/' . $file[1] . '.js';
		}
		
		$data = [
			'widget_kurzbz' => $this->input->post('widget_kurzbz'),
			'setup' => json_encode($setup),
			'arguments' => json_encode([]),
		];
		$result = $this->WidgetModel->insert($data);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function update()
	{
		$this->loadPhrases(['zeitaufzeichnung']);
		$this->load->library('form_validation');

		$this->form_validation->set_rules(
			'widget_id',
			$this->p->t('zeitaufzeichnung', 'id'),
			'required'
		);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$widget_id = $this->input->post('widget_id');
		$data = [];
		foreach ([
			'widget_kurzbz',
			'beschreibung',
			'setup',
			'arguments',
			'berechtigung_kurzbz',
		] as $post) {
			$value = $this->input->post($post);
			if ($value) {
				if ($post == 'setup' || $post == 'arguments')
					$value = json_encode($value);
				$data[$post] = $value;
			}
		}
		if (!$data)
			$this->terminateWithSuccess();
		
		$result = $this->WidgetModel->update($widget_id, $data);

		$this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess();
	}

	public function generators()
	{
		$result = $this->getGeneratorsRecursive();

		$this->load->library('ExtensionsLib');
		$extensions = $this->extensionslib->getInstalledExtensions();
		if (hasData($extensions)) {
			foreach (getData($extensions) as $extension) {
				$result = array_merge($result, $this->getGeneratorsRecursive($extension->name));
			}
		}

		return $this->terminateWithSuccess($result);
	}

	protected function getGeneratorsRecursive($extension = '', $path = '')
	{
		$result = [];
		
		$dashboard_widget_path = 'public/js/components/DashboardWidget/';

		$name = '';
		$file_path = $dashboard_widget_path . $path;
		$generator_path = $dashboard_widget_path . 'Generators/' . $path;
		
		if ($extension) {
			$name = $extension . ':';
			$file_path = APPPATH . 'extensions/' . $extension . '/' . $file_path;
			$generator_path = APPPATH . 'extensions/' . $extension . '/' . $generator_path;
		} else {
			$file_path = FHCPATH . $file_path;
			$generator_path = FHCPATH . $generator_path;
		}

		$name .= $path;
		
		$generator_path = str_replace('/', DIRECTORY_SEPARATOR, $generator_path);

		if (is_dir($generator_path)) {
			$dir = new DirectoryIterator($generator_path);

			foreach ($dir as $file) {
				if ($file->isDot())
					continue;
				$filename = $file->getFilename();
				if ($file->isDir()) {
					if (is_dir(str_replace('/', DIRECTORY_SEPARATOR, $file_path . $filename))) {
						$res = $this->getGeneratorsRecursive($extension, $path . $filename . '/');
						$result = array_merge($result, $res);
					}
				} else {
					if (file_exists(str_replace('/', DIRECTORY_SEPARATOR, $file_path . $filename))) {
						if ($file->getExtension() == 'js')
							$result[] = $name . $file->getBasename('.js');
					}
				}
			}
		}

		return $result;
	}
}
