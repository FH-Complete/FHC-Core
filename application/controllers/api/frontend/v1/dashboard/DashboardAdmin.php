<?php
/**
 * Copyright (C) 2024 fhcomplete.org
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
 * Provides data to the ajax get calls about addresses
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class DashboardAdmin extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getAllDashboards'				=> 'dashboard/admin:r',
			'createDashboard'				=> 'dashboard/admin:rw',
			'updateDashboard'				=> 'dashboard/admin:rw',
			'deleteDashboard'				=> 'dashboard/admin:rw',
			'loadWidget'					=> ['dashboard/benutzer:r', 'dashboard/admin:r'],
			'getAllWidgets'					=> 'dashboard/admin:r',
			'getWidgetsForDashboard'		=> ['dashboard/benutzer:rw', 'dashboard/admin:r'],
			'setWidgetAllowed'				=> 'dashboard/admin:rw',

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
		]);

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);

		$this->load->library('dashboard/DashboardLib', null, 'DashboardLib');

		$this->load->model('dashboard/Dashboard_model', 'DashboardModel');
		$this->load->model('dashboard/Widget_model', 'WidgetModel');
		$this->load->model('dashboard/Dashboard_Widget_model', 'DashboardWidgetModel');
		$this->load->model('ressource/Funktion_model', 'FunktionModel');
	}

	public function getAllDashboards()
	{
		$result = $this->DashboardModel->load();

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess($result);
	}

	public function createDashboard()
	{
		$dashboard_kurzbz = $this->input->post('dashboard_kurzbz');
		$result = $this->DashboardModel->insert(
			[
				'dashboard_kurzbz' => $dashboard_kurzbz
			]
		);

		if (isError($result))
		{
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function updateDashboard()
	{
		$dashboard_id = $this->input->post('dashboard_id');
		$dashboard_kurzbz = $this->input->post('dashboard_kurzbz');
		$beschreibung = $this->input->post('beschreibung');
		if(!$dashboard_id)
		{
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Dashboard ID']), self::ERROR_TYPE_GENERAL);
		}

		$result = $this->DashboardModel->update(
			[
			'dashboard_id' => $dashboard_id,
			],
			[
				'dashboard_kurzbz' => $dashboard_kurzbz,
				'beschreibung' => $beschreibung,
			]
		);

		if (isError($result))
		{
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess($result);
	}

	public function deleteDashboard()
	{
		$dashboard_id = $this->input->post('dashboard_id');
		if(!$dashboard_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Dashboard ID']), self::ERROR_TYPE_GENERAL);
		}

		//delete all presets
		$this->load->model('dashboard/Dashboard_Preset_model', 'DashboardPresetModel');
		$resultPresets = $this->DashboardPresetModel->delete(
			array('dashboard_id' => $dashboard_id)
		);
		if ($resultPresets === false)
		{
			return $this->terminateWithError($resultPresets, self::ERROR_TYPE_GENERAL);
		}

		//delete all widgets
		$this->load->model('dashboard/Dashboard_Widget_model', 'DashboardWidgetModel');

		$resultWidgets = $this->DashboardWidgetModel->delete(
			array('dashboard_id' => $dashboard_id)
		);
		if ($resultWidgets === false)
		{
			return $this->terminateWithError($resultWidgets, self::ERROR_TYPE_GENERAL);
		}

		$result = $this->DashboardModel->delete(
			array('dashboard_id' => $dashboard_id)
		);

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess($result);
	}

	public function getAllWidgets()
	{
		$dashboard_id = $this->input->get('dashboard_id');
		if(!$dashboard_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Dashboard ID']), self::ERROR_TYPE_GENERAL);
		}
		//$this->terminateWithError($dashboard_id);
		$result = $this->WidgetModel->getWithAllowedForDashboard($dashboard_id);

		if (isError($result))
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);

		$this->terminateWithSuccess(getData($result) ?: []);
	}

	public function setWidgetAllowed()
	{
		$dashboard_id = $this->input->post('dashboard_id');
		$widget_id = $this->input->post('widget_id');
		$action = $this->input->post('action');

		if ($action == 'add')
		{
			$result = $this->DashboardWidgetModel->insert([
				'dashboard_id' => $dashboard_id,
				'widget_id' => $widget_id
			]);
		}
		elseif ($action == 'delete')
		{
			$result = $this->DashboardWidgetModel->delete([
				'dashboard_id' => $dashboard_id,
				'widget_id' => $widget_id
			]);
		}
		else
		{
			$this->terminateWithError("action value invalid", self::ERROR_TYPE_GENERAL);
		}
		if (isError($result))
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);

		$this->terminateWithSuccess(getData($result) ?: []);
	}

	//Presets
	public function funktionen()
	{
		$result = $this->FunktionModel->load();

		if (isError($result))
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);

		$this->terminateWithSuccess(getData($result) ?: []);
	}

	public function addWidgetsToPreset()
	{
		$raw = $this->input->raw_input_stream;
		$json = json_decode($raw);

		$dashboard_kurzbz = $json->db;
		$funktion_kurzbz = $json->funktion_kurzbz;
		$widgets = $json->widgets;

		$preset = $this->DashboardLib->getPresetOrCreateEmptyPreset($dashboard_kurzbz, $funktion_kurzbz);

		$preset_decoded = json_decode($preset->preset, true);
		$this->DashboardLib->addWidgetsToWidgets($preset_decoded, $dashboard_kurzbz, $funktion_kurzbz, $widgets);

		$preset->preset = json_encode($preset_decoded);
		$result = $this->DashboardLib->insertOrUpdatePreset($preset);

		if (isError($result))
			$this->terminateWithError($this->p->t('dashboard', 'error_savePreset'), self::ERROR_TYPE_GENERAL);
		$this->terminateWithSuccess($preset_decoded);
	}

	public function removeWidgetFromPreset()
	{
		$raw = $this->input->raw_input_stream;
		$json = json_decode($raw);

		$dashboard_kurzbz = $json->db;
		$funktion_kurzbz = $json->funktion_kurzbz;
		$widgetid = $json->widgetid;

		$preset = $this->DashboardLib->getPreset($dashboard_kurzbz, $funktion_kurzbz);
		if ($preset === null)
		{
			$this->terminateWithError($this->p->t('ui', 'error_presetAndFunctionNotFound', ['dashboard'=> $dashboard_kurzbz, 'funktion'=> $funktion_kurzbz]), self::ERROR_TYPE_GENERAL);
		}

		$preset_decoded = json_decode($preset->preset, true);
		if (!$this->DashboardLib->removeWidgetFromWidgets($preset_decoded, $funktion_kurzbz, $widgetid))
		{
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Widget ID']), self::ERROR_TYPE_GENERAL);
		}

		$preset->preset = json_encode($preset_decoded);
		$result = $this->DashboardLib->insertOrUpdatePreset($preset);
		if (isError($result))
		{
			$this->terminateWithError($this->p->t('dashboard', 'error_deleteWidget'), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess(array('msg' => $this->p->t('dashboard', 'success_savePreset')));
	}

	public function presetBatch()
	{
		$db = $this->input->get('db');
		$funktionen = $this->input->get('funktionen');
		$result = [];

		if($funktionen)
		{
			foreach ($funktionen as $funktion) {
				$conf = $this->DashboardLib->getPreset($db, $funktion);
				if ($conf) {
					$preset = json_decode($conf->preset, true);
					if (!isset($preset[$funktion]) || !isset($preset[$funktion]['widgets']))
						$result[$funktion] = [];
					else {
						$result[$funktion] = $preset[$funktion]['widgets'];
					}
				} else
					$result[$funktion] = [];
			}
		}
		else
			$result = [];

		return $this->terminateWithSuccess($result);
	}
}
