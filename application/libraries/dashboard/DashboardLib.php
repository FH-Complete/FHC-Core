<?php
defined('BASEPATH') || exit('No direct script access allowed');

use \stdClass as stdClass;

/**
 * Description of DashboardLib
 *
 * @author bambi
 */
class DashboardLib
{
	const WIDGET_ID_RANDOM_BYTES = 16;
	const DEFAULT_DASHBOARD_KURZBZ = 'fhcomplete';
	const SECTION_IF_FUNKTION_KURZBZ_IS_NULL = 'general';
	const USEROVERRIDE_SECTION = 'custom';
	
	private $_ci; // CI instance
	
	public function __construct()
	{
		// Loads CI instance
		$this->_ci =& get_instance();
		
		$this->_ci->load->model('dashboard/Dashboard_model', 'DashboardModel');
		$this->_ci->load->model('dashboard/Dashboard_Preset_model', 'DashboardPresetModel');
		$this->_ci->load->model('dashboard/Dashboard_Override_model', 'DashboardOverrideModel');
	}

	public function generateWidgetId($dashboard_kurzbz = '')
	{
		$dashboard_kurzbz = (!empty($dashboard_kurzbz)) ? $dashboard_kurzbz : self::DEFAULT_DASHBOARD_KURZBZ;
		$widgetid_input = time() . '_' . $dashboard_kurzbz . '_' . bin2hex(random_bytes(self::WIDGET_ID_RANDOM_BYTES));
		$widgetid = md5($widgetid_input);
		return $widgetid;
	}
	
	public function getDashboardByKurzbz($dashboard_kurzbz)
	{
		$result = $this->_ci->DashboardModel->getDashboardByKurzbz($dashboard_kurzbz);

		if (hasData($result))
		{
			return current(getData($result));
		}

		return null;
	}
	
	public function getMergedConfig($dashboard_id, $uid)
	{
		$defaultconfig = $this->getDefaultConfig($dashboard_id, $uid);
		$userconfig = $this->getUserConfig($dashboard_id, $uid);
		
		$mergedconfig = array_replace_recursive($defaultconfig, $userconfig);
		
		return $mergedconfig;
	}
	
	public function getDefaultConfig($dashboard_id, $uid)
	{
		$res_presets = $this->_ci->DashboardPresetModel->getPresets($dashboard_id, $uid);
		$defaultconfig = array();
		
		if (hasData($res_presets))
		{
			$presets = getData($res_presets);
			foreach ($presets as $presetobj)
			{
				$preset = json_decode($presetobj->preset, true);
				if (null !== $preset)
				{
					$defaultconfig = array_replace_recursive($defaultconfig, $preset);
				}
			}
		}
		
		return $defaultconfig;
	}
	
	public function getUserConfig($dashboard_id, $uid)
	{
		$res_userconfig = $this->_ci->DashboardOverrideModel->getOverride($dashboard_id, $uid);
		
		if (hasData($res_userconfig))
		{
			$data = getData($res_userconfig);
			$decodedconfig = json_decode(current($data)->override, true);
			if (null !==  $decodedconfig)
			{
				return $decodedconfig;
			}
		}
		
		return [];
	}
	
	public function getOverrideOrCreateEmptyOverride($dashboard_kurzbz, $uid)
	{
		$override = $this->getOverride($dashboard_kurzbz, $uid);
		if (null !== $override) {
			return $override;
		}
		
		$dashboard = $this->getDashboardByKurzbz($dashboard_kurzbz);
		
		$emptyoverride = new stdClass();
		$emptyoverride->dashboard_id = $dashboard->dashboard_id;
		$emptyoverride->uid = $uid;
		$emptyoverride->override = '{"widgets": {"' . self::USEROVERRIDE_SECTION . '": {}}}';
		
		return $emptyoverride;
	}
	
	public function getPresetOrCreateEmptyPreset($dashboard_kurzbz, $funktion_kurzbz)
	{
		if ($funktion_kurzbz === self::SECTION_IF_FUNKTION_KURZBZ_IS_NULL)
			$funktion_kurzbz = null;
		$preset = $this->getPreset($dashboard_kurzbz, $funktion_kurzbz);
		if (null !== $preset) {
			return $preset;
		}
		
		$dashboard = $this->getDashboardByKurzbz($dashboard_kurzbz);
		
		$emptypreset = new stdClass();
		$emptypreset->dashboard_id = $dashboard->dashboard_id;
		$emptypreset->funktion_kurzbz = $funktion_kurzbz;
		$section = ($funktion_kurzbz !== null) ? $funktion_kurzbz : self::SECTION_IF_FUNKTION_KURZBZ_IS_NULL;
		$emptypreset->preset = '{"widgets": {"' . $section . '": {}}}';
		
		return $emptypreset;
	}
	
	public function getPreset($dashboard_kurzbz, $section)
	{
		$dashboard = $this->getDashboardByKurzbz($dashboard_kurzbz);
		
		$funktion_kurzbz = ($section === self::SECTION_IF_FUNKTION_KURZBZ_IS_NULL) ? null : $section;
		$result = $this->_ci->DashboardPresetModel
			->getPresetByDashboardAndFunktion($dashboard->dashboard_id, $funktion_kurzbz);
		
		if (hasData($result))
		{
			return current(getData($result));
		}
		
		return null;
	}

	public function getOverride($dashboard_kurzbz, $uid)
	{
		$dashboard = $this->getDashboardByKurzbz($dashboard_kurzbz);
		
		$result = $this->_ci->DashboardOverrideModel
			->getOverride($dashboard->dashboard_id, $uid);
		
		if (hasData($result))
		{
			return current(getData($result));
		}
		
		return null;
	}
	
	public function insertOrUpdatePreset($preset)
	{
		if (isset($preset->preset_id) && $preset->preset_id > 0)
		{
			$result = $this->_ci->DashboardPresetModel->update($preset->preset_id, $preset);
		}
		else
		{
			$result = $this->_ci->DashboardPresetModel->insert($preset);
		}
		
		return $result;
	}
	
	public function insertOrUpdateOverride($override)
	{
		if (isset($override->override_id) && $override->override_id > 0)
		{
			$result = $this->_ci->DashboardOverrideModel->update($override->override_id, $override);
		}
		else
		{
			$result = $this->_ci->DashboardOverrideModel->insert($override);
		}
		
		return $result;
	}

	public function addWidgetsToWidgets(&$widgets, $dashboard_kurzbz, $section, $addwigets)
	{
		foreach ($addwigets as $widget)
		{
			if(!isset($widget->widgetid))
			{
				$widget->widgetid = $this->generateWidgetId($dashboard_kurzbz);
			}
			$this->addWidgetToWidgets($widgets, $section, $widget, $widget->widgetid);
		}
	}
	
	public function addWidgetToWidgets(&$widgets, $section, $widget, $widgetid)
	{
		$section = ($section !== null) ? $section : self::SECTION_IF_FUNKTION_KURZBZ_IS_NULL;
		if (!isset($widgets[$section]) || !is_array($widgets[$section]))
		{
			$widgets[$section] = array();
		}
		
		$widgets[$section][$widgetid] = $widget;
	}
	
	public function removeWidgetFromWidgets(&$widgets, $section, $widgetid)
	{
		$section = ($section !== null) ? $section : self::SECTION_IF_FUNKTION_KURZBZ_IS_NULL;
		if (isset($widgets[$section]) && isset($widgets[$section][$widgetid]))
		{
			unset($widgets[$section][$widgetid]);
			if(empty($widgets[$section]) && $section !== self::USEROVERRIDE_SECTION) {
				unset($widgets[$section]);
			}
			return true;
		}
		else {
			return false;
		}
	}
}
