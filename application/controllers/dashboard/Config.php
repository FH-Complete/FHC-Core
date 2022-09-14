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
				'index' => 'basis/mitarbeiter:r',
				'dummy' => 'basis/mitarbeiter:r',
			)
		);
		
		$this->load->model('dashboard/Dashboard_Preset_model', 'DashboardPresetModel');
		$this->load->model('dashboard/Dashboard_Override_model', 'DashboardOverrideModel');
	}
	
	public function index() 
	{
		$res_presets = $this->DashboardPresetModel->getPresets(1, 'ma0080');
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
		
		$res_userconfig = $this->DashboardOverrideModel->getOverride(1, 'ma0080');
		$mergedconfig = array();
		if( isSuccess($res_userconfig) && hasData($res_userconfig) ) 
		{
			$data = getData($res_userconfig);
			if( null !== ($userconfig = json_decode($data[0]->override, true)) )
			{
				$mergedconfig = array_replace_recursive($defaultconfig, $userconfig);
			}
		}
/*
		header('Content-Type: text/plain');
		print_r($defaultconfig);
		print_r($userconfig);
		print_r($mergedconfig);
		die();
*/
/*
		$ret = array(
			'defaultconfig' => $defaultconfig,
			'userconfig' => $userconfig,
			'mergedconfig' => $mergedconfig
		);
		$this->outputJsonSuccess($ret);
 */
		$this->outputJsonSuccess($mergedconfig);
	}
	
	public function dummy() 
	{
		$defaultconfig = array(
			'title' => 'CIS Dashboard',
			'widgets' => array(
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
		);
		
		$userconfig = array(
			'widgets' => array(
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
