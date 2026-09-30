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
			'lehre',
			'stv'
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

		$result = $this->VariableModel->getVariables(getAuthUID(), ['font_size']);
		$data = $this->getDataOrTerminateWithError($result);
		$config['font_size'] = [
			"type" => "select",
			"label" => $this->p->t('stv', 'settings_fontsize'),
			"value" => $data['font_size'] ?? "fs_normal",
			"options" => [
				"fs_xx-small" => $this->p->t('stv', 'settings_fontsize_xx-small'),
				"fs_x-small" => $this->p->t('stv', 'settings_fontsize_x-small'),
				"fs_small" => $this->p->t('stv', 'settings_fontsize_small'),
				"fs_normal" => $this->p->t('stv', 'settings_fontsize_normal'),
				"fs_big" => $this->p->t('stv', 'settings_fontsize_big'),
				"fs_huge" => $this->p->t('stv', 'settings_fontsize_huge')
			]
		];

		$this->terminateWithSuccess($config);
	}
	public function set()
	{
		$this->load->model('system/Variable_model', 'VariableModel');
		$this->load->library('form_validation');

		$this->form_validation->set_rules(
			'font_size',
			$this->p->t('stv', 'settings_fontsize'),
			'required|in_list[fs_xx-small,fs_x-small,fs_small,fs_normal,fs_big,fs_huge]'
		);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$this->VariableModel->setVariable(
			getAuthUID(),
			'font_size',
			$this->input->post('font_size')
		);

		if (!($this->permissionlib->isBerechtigt('basis/tempus')) && !($this->permissionlib->isBerechtigt('lv-plan')))
			$this->terminateWithSuccess([]);

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
