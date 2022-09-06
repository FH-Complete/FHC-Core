<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends API_Controller
{
	/**
	 * Appdaten API constructor.
	 */
	public function __construct()
	{
		parent::__construct([
			'Widgets' => 'user:r'
		]);
	}

	/**
	 * @return void
	 */
	public function getWidgets()
	{
		$this->load->model('dashboard/Dashboard_model', 'DashboardModel');
		$result = $this->DashboardModel->loadWhere(['name'=>$this->get('dashboard')]);
		if (isError($result))
			return $this->response($result, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		if (!hasData($result))
			return $this->response('No Dashboard with the name "' . $this->get('dashboard') . '" found.', REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		$dashboard_id = current(getData($result))->dashboard_id;

		$this->load->model('dashboard/Dashboardwidget_model', 'DashboardwidgetModel');
		$result = $this->DashboardwidgetModel->loadWidgets($dashboard_id);
		if (isError($result))
			return $this->response($result, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		if (!hasData($result))
			return $this->response('No Widgets for Dashboard "' . $this->get('dashboard') . '" found.', REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		
		$this->response(getData($result), REST_Controller::HTTP_OK);
	}

	protected function _check_whitelist_auth() {}

}
