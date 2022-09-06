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

class User extends API_Controller
{
	/**
	 * Appdaten API constructor.
	 */
	public function __construct()
	{
		parent::__construct([
			'AuthObj' => 'user:r',
			'Widget' => 'user:r',
			'Widgets' => 'user:r'
		]);
	}

	/**
	 * @return void
	 */
	public function getAuthObj()
	{
		$result = $this->authlib->getAuthObj();

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function getWidget()
	{
		$this->load->model('dashboard/Widget_model', 'WidgetModel');
		$result = $this->WidgetModel->load($this->get('widget_id'));
		if (isError($result))
			return $this->response($result, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		if (!hasData($result))
			return $this->response('No Widget with the id "' . $this->get('widget_id') . '" found.', REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		$result = current(getData($result));

		$this->response($result, REST_Controller::HTTP_OK);
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

		$authObj = $this->authlib->getAuthObj();

		$this->load->model('dashboard/Dashboardpreset_model', 'DashboardpresetModel');
		$result = $this->DashboardpresetModel->loadForUser($dashboard_id, $authObj->username);
		if (isError($result))
			return $this->response($result, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		if (!hasData($result))
			return $this->response('No Dashboard for user "' . $authObj->username . '" found.', REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		
		$result = (current(getData($result))->config);
		$result = json_decode($result);
		if ($result === null)
			return $this->response(json_last_error_msg(), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

		$this->response($result, REST_Controller::HTTP_OK);
	}

	protected function _check_whitelist_auth() {}

}
