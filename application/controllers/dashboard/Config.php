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
				'index'			=> 'basis/mitarbeiter:r',
				'dummy'			=> 'basis/mitarbeiter:r',
				'genWidgetId'	=> 'basis/mitarbeiter:r'
			)
		);
		
		$this->load->library('dashboard/DashboardLib', null, 'DashboardLib');
	}
	
	public function index() 
	{
		$dashboard_kurzbz = $this->input->get('db');
		$uid = $this->input->get('uid');
		
		$dashboard = $this->DashboardLib->getDashboardByKurzbz($dashboard_kurzbz);
		if(!$dashboard) {
			$this->outputJsonError(array(
				'error' => 'Dashboard ' . $dashboard_kurzbz . ' not found.'
			));
		}
		
		$mergedconfig = $this->DashboardLib->getMergedConfig($dashboard->dashboard_id, $uid);
		$this->outputJsonSuccess($mergedconfig);
	}
	
	public function genWidgetId() 
	{
		$dashboard_kurzbz = $this->input->get('db');
		$widgetid = $this->DashboardLib->generateWidgetId($dashboard_kurzbz);
		$this->outputJsonSuccess(array(
			'widgetid' => $widgetid['widgetid']
		));
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
