<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Config extends FHCAPI_Controller
{
	private $_ci;
	private $_uid;

	public function __construct()
	{
		parent::__construct([
			'get' => ['admin:r', 'assistenz:r'],
			'set' => ['admin:r', 'assistenz:r'],
		]);

		$this->_ci = &get_instance();
		$this->_setAuthUID();

		$this->loadPhrases([
			'lehre'
		]);

		$this->_ci->load->library('VariableLib', ['uid' => $this->_uid]);
		$this->_ci->load->library('PermissionLib');

	}


	public function get()
	{
		if (!($this->permissionlib->isBerechtigt('basis/tempus')) && !($this->permissionlib->isBerechtigt('lv-plan')))
			$this->terminateWithSuccess([]);

		$ignore_kollision = $this->_ci->variablelib->getVar('ignore_kollision');
		$ignore_zeitsperre = $this->_ci->variablelib->getVar('ignore_zeitsperre');
		$ignore_reservierung = $this->_ci->variablelib->getVar('ignore_reservierung');

		$config['ignore_kollision'] = [
			"type" => "checkbox",
			"label" => 'ignore_kollision',
			"value" => $ignore_kollision,
		];

		$config['ignore_zeitsperre'] = [
			"type" => "checkbox",
			"label" => 'ignore_zeitsperre',
			"value" => $ignore_zeitsperre,
		];

		$config['ignore_reservierung'] = [
			"type" => "checkbox",
			"label" => 'ignore_reservierung',
			"value" => $ignore_reservierung,
		];

		$this->terminateWithSuccess($config);
	}
	public function set()
	{
		if (!($this->permissionlib->isBerechtigt('basis/tempus')) && !($this->permissionlib->isBerechtigt('lv-plan')))
			$this->terminateWithSuccess([]);

		$this->load->model('system/Variable_model', 'VariableModel');

		foreach (['ignore_kollision','ignore_zeitsperre','ignore_reservierung'] as $variable)
		{
			if ($this->_ci->input->post($variable) !== null)
			{
				$this->VariableModel->update(array('uid' => $this->_uid, 'name' => $variable), array('wert' => $this->input->post($variable)));

			}
		}

		$this->terminateWithSuccess();
	}

	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();

		if (!$this->_uid)
			show_error('User authentification failed');
	}
}
