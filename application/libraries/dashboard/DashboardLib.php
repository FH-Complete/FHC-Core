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
		$result = $this->_ci->DashboardModel->loadWhere([
			'dashboard_kurzbz' => $dashboard_kurzbz
		]);

		if (hasData($result))
		{
			return current(getData($result));
		}

		return null;
	}
	
	public function getMergedUserConfig($dashboard_id, $uid)
	{
		$defaultconfig = $this->getUserBaseConfig($dashboard_id);
		$userconfig = $this->getUserOverrideConfig($dashboard_id, $uid);
		
		$sourceconfig = array_map(function ($value) {
			return ['source' => $value['source']];
		}, $defaultconfig);
		
		$mergedconfig = array_replace_recursive($defaultconfig, $userconfig, $sourceconfig);
		
		return $mergedconfig;
	}
	
	protected function getUserBaseConfig($dashboard_id)
	{
		$funktion_kurzbzs = [];
		$rights = $this->_ci->permissionlib->getAccessRights();
		if ($rights)
			$funktion_kurzbzs = array_unique(array_map(function ($right) {
				return $right->funktion_kurzbz;
			}, $rights));
		
		$this->_ci->DashboardPresetModel->db
			->group_start()
				->where_in('funktion_kurzbz', $funktion_kurzbzs)
				->or_where('funktion_kurzbz IS NULL')
			->group_end();

		$this->_ci->DashboardPresetModel->addOrder('funktion_kurzbz', 'DESC');
		
		$result = $this->_ci->DashboardPresetModel->loadWhere([
			'dashboard_id' => $dashboard_id
		]);
		$defaultconfig = array();
		
		if (hasData($result))
		{
			$presets = getData($result);
			foreach ($presets as $presetobj)
			{
				$preset = json_decode($presetobj->preset, true);
				if (null !== $preset)
				{
					$preset = array_map(function ($value) use ($presetobj) {
						$value['source'] = $presetobj->funktion_kurzbz ?: self::SECTION_IF_FUNKTION_KURZBZ_IS_NULL;
						return $value;
					}, $preset);
					$defaultconfig = array_merge_recursive($defaultconfig, $preset);
				}
			}
		}
		
		return $defaultconfig;
	}
	
	protected function getUserOverrideConfig($dashboard_id, $uid)
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
		$emptyoverride->override = '[]';
		
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
		$emptypreset->preset = '[]';
		
		return $emptypreset;
	}
	
	public function getPreset($dashboard_kurzbz, $section)
	{
		$dashboard = $this->getDashboardByKurzbz($dashboard_kurzbz);
		
		$funktion_kurzbz = ($section === self::SECTION_IF_FUNKTION_KURZBZ_IS_NULL) ? null : $section;
		$result = $this->_ci->DashboardPresetModel->loadWhere([
			'dashboard_id' => $dashboard->dashboard_id,
			'funktion_kurzbz' => $funktion_kurzbz
		]);
		
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
}
