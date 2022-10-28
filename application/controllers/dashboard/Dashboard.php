<?php
defined('BASEPATH') || exit('No direct script access allowed');
/**
 * Description of Widget
 *
 * @author chris
 */
class Dashboard extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct(
			array(
				'index'							=> 'dashboard/admin:r',
				'Create'						=> 'dashboard/admin:rw',
				'Update'						=> 'dashboard/admin:rw',
				'Delete'						=> 'dashboard/admin:rw'
			)
		);
		
		$this->load->library('dashboard/DashboardLib', null, 'DashboardLib');
		$this->load->model('dashboard/Dashboard_model', 'DashboardModel');
	}
	
	public function index() 
	{
		$result = $this->DashboardModel->load();

		if (isError($result)) {
			http_response_code(404);
			$this->terminateWithJsonError([
				'error' => getError($result)
			]);
		}
		
		return $this->outputJsonSuccess(getData($result) ?: []);
	}
	
	public function Create() 
	{
		$input = $this->getPostJSON();

		$result = $this->DashboardModel->insert($input);

		if (isError($result)) {
			http_response_code(404);
			$this->terminateWithJsonError([
				'error' => getError($result)
			]);
		}
		
		return $this->outputJsonSuccess(getData($result) ?: []);
	}
	
	public function Update() 
	{
		$input = $this->getPostJSON();

		$result = $this->DashboardModel->update($input->dashboard_id, $input);

		if (isError($result)) {
			http_response_code(404);
			$this->terminateWithJsonError([
				'error' => getError($result)
			]);
		}
		
		return $this->outputJsonSuccess(getData($result) ?: []);
	}
	
	public function Delete() 
	{
		$input = $this->getPostJSON();

		$result = $this->DashboardModel->delete($input->dashboard_id);

		if (isError($result)) {
			http_response_code(404);
			$this->terminateWithJsonError([
				'error' => getError($result)
			]);
		}
		
		return $this->outputJsonSuccess(getData($result) ?: []);
	}
	
}
