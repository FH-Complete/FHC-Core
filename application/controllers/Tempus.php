<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Tempus extends Auth_Controller
{
	public function __construct()
	{
		$permissions = [];
		$router = load_class('Router');
		$permissions[$router->method] = ['admin:r', 'assistenz:r'];
		parent::__construct($permissions);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		// Load Config
		$this->load->config('calendar');
	}

	public function index()
	{
		$this->_loadView();
	}

	public function sync()
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
		$this->load->model('ressource/Kalenderstatus_model', 'KalenderStatusModel');

		$this->StudiengangModel->addOrder('typ');
		$this->StudiengangModel->addOrder('kurzbz');
		$this->StudiengangModel->addJoin('tbl_organisationseinheit', 'oe_kurzbz', 'LEFT');
		$organisationen = getData($this->StudiengangModel->loadWhere(array('tbl_studiengang.aktiv' => 'true')));

		$this->StudiensemesterModel->addOrder('start', 'DESC');
		$studiensemestern = getData($this->StudiensemesterModel->load());

		$this->_loadView(array('organisationen' => $organisationen, 'studiensemestern' => $studiensemestern));
	}

	private function _loadView($extraVariables = [])
	{
		$this->load->view('Tempus', [
			'permissions' => [
				'admin' => $this->permissionlib->isBerechtigt('admin')
			],
			'variables' => array_merge([
				'semester_aktuell' => $this->variablelib->getVar('semester_aktuell'),
				'timezone' => $this->config->item('timezone')
			], $extraVariables)
		]);
	}
}