<?php
defined('BASEPATH') || exit('No direct script access allowed');
/**
 * Description of DashboardLib
 *
 * @author bambi
 */
class DashboardLib
{
	const WIDGET_ID_RANDOM_BYTES = 16;
	const DEFAULT_DASHBOARD_KURZBZ = 'fhcomplete';
	
	private $_ci; // CI instance
	
	public function __construct($params=null)
	{
		// Loads CI instance
		$this->_ci =& get_instance();
		
		$this->_ci->load->model('dashboard/Dashboard_model', 'DashboardModel');
		$this->_ci->load->model('dashboard/Dashboard_Preset_model', 'DashboardPresetModel');
		$this->_ci->load->model('dashboard/Dashboard_Override_model', 'DashboardOverrideModel');
	}

	public function generateWidgetId($dashboard_kurzbz='') 
	{
		$dashboard_kurzbz = (!empty($dashboard_kurzbz)) ? $dashboard_kurzbz 
			: self::DEFAULT_DASHBOARD_KURZBZ;
		$widgetid_input = time() . '_' . $dashboard_kurzbz . '_' 
			. bin2hex(random_bytes(self::WIDGET_ID_RANDOM_BYTES));
		$widgetid = md5($widgetid_input);
		return array(
			'widgetid' => $widgetid,
			'widgetid_input' => $widgetid_input
		);
	}
	
	public function getDashboardByKurzbz($dashboard_kurzbz) 
	{
		$dashboard = null;
		$result = $this->_ci->DashboardModel->getDashboardByKurzbz($dashboard_kurzbz);
		if( isSuccess($result) && ($dashboards = getData($result)) ) 
		{
			$dashboard = $dashboards[0];
		}
		return $dashboard;
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
		
		if( isSuccess($res_presets) && hasData($res_presets) ) 
		{
			$presets = getData($res_presets);
			foreach ($presets as $presetobj)
			{
				if( null !== ($preset = json_decode($presetobj->preset, true)) )
				{
					$defaultconfig = array_replace_recursive($defaultconfig, 
						$preset);
				}
			}
		}
		
		return $defaultconfig;
	}
	
	public function getUserConfig($dashboard_id, $uid)
	{
		$res_userconfig = $this->_ci->DashboardOverrideModel->getOverride($dashboard_id, $uid);
		$userconfig = array();
		
		if( isSuccess($res_userconfig) && hasData($res_userconfig) ) 
		{
			$data = getData($res_userconfig);
			if( null !== ($decodedconfig = json_decode($data[0]->override, true)) )
			{
				$userconfig = $decodedconfig;
			}
		}
		
		return $userconfig;
	}
	
}
