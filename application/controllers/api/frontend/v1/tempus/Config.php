<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');


class Config extends FHCAPI_Controller
{
	private $_ci;

	public function __construct()
	{
		parent::__construct([
			'get' => ['admin:r', 'assistenz:rw', 'lehre/lvplan:rw'],
			'getHeader' => ['admin:r', 'assistenz:rw', 'lehre/lvplan:rw'],
			'set' => ['admin:r', 'assistenz:rw', 'lehre/lvplan:rw'],
			'updateCollision' => ['admin:r', 'assistenz:rw', 'lehre/lvplan:rw'],
		]);

		// Load Phrases
		$this->loadPhrases([
			'ui',
		]);

		$this->_ci = &get_instance();
		$this->_ci->load->model('ressource/Kalenderstatus_model', 'KalenderStatusModel');
		$this->_ci->load->model('system/Variable_model', 'VariableModel');
		$this->_ci->load->library('VariableLib', array('uid' => getAuthUID()));
	}

	public function get()
	{
		$config = [];

		$var_array = array(
			'ignore_kollision',
			'kollision_student',
			'ignore_reservierung',
			'ignore_zeitsperre',
			'ignore_resources_collisions',
			'roomless_planning',
			'room_planning',
		);
		$result = $this->_ci->VariableModel->getVariables(getAuthUID(), $var_array);

		$data = $this->getDataOrTerminateWithError($result);
		$config['ignore_kollision'] = [
			"type"  => "checkbox",
			"label" => $this->p->t('ui', 'ignore_kollision'),
			"value" => ($data['ignore_kollision'] ?? 'false') === 'true'
		];

		$config['kollision_student'] = [
			"type"  => "checkbox",
			"label" => $this->p->t('ui', 'kollision_student'),
			"value" => ($data['kollision_student'] ?? 'false') === 'true'
		];

		$config['ignore_reservierung'] = [
			"type"  => "checkbox",
			"label" => $this->p->t('ui', 'ignore_reservierung'),
			"value" => ($data['ignore_reservierung'] ?? 'false') === 'true'

		];

		$config['ignore_zeitsperre'] = [
			"type"  => "checkbox",
			"label" => $this->p->t('ui', 'ignore_zeitsperre'),
			"value" => ($data['ignore_zeitsperre'] ?? 'false') === 'true'
		];

		$config['ignore_resources_collisions'] = [
			"type"  => "checkbox",
			"label" => $this->p->t('ui', 'ignore_resources_collisions'),
			"value" => ($data['ignore_resources_collisions'] ?? 'false') === 'true'
		];

		$config['roomless_planning'] = [
			"type"  => "checkbox",
			"label" => $this->p->t('ui', 'roomless_planning'),
			"value" => ($data['roomless_planning'] ?? 'false') === 'true'
		];

		$config['room_planning'] = [
			"type"  => "select",
			"label" => $this->p->t('ui', 'room_planning'),
			"value" => $data['room_planning'] ?? "dialog_room_planning",
			"options" => [
				'dialog_room_planning' => $this->p->t('ui', 'dialog_room_planning'),
				'priority_room_planning' => $this->p->t('ui', 'priority_room_planning'),
			],
		];

		$this->terminateWithSuccess($config);
	}
	public function getHeader()
	{
		$language = getUserLanguage() == 'German' ? 0 : 1;

		$this->_ci->KalenderStatusModel->addSelect('*, array_to_json(bezeichnung_mehrsprachig::varchar[])->>' . $language .' AS status');
		$this->_ci->KalenderStatusModel->addOrder('sort');
		$this->_ci->KalenderStatusModel->db->where_not_in('status_kurzbz', array('archived', 'deleted'));
		$visible_status = $this->_ci->KalenderStatusModel->load();

		$visible_status = getData($visible_status);

		$config['visible_status']['all'] = 'Alle';

		foreach ($visible_status as $status)
		{
			$config['visible_status'][$status->status_kurzbz] = $status->status;
		}

		$this->terminateWithSuccess($config);
	}

	public function set()
	{
		$this->_ci->load->library('form_validation');

		$configs = array(
			'ignore_kollision',
			'kollision_student',
			'ignore_reservierung',
			'ignore_zeitsperre',
			'ignore_resources_collisions',
			'roomless_planning',
		);

		$this->form_validation->set_rules(
			'room_planning',
			$this->p->t('ui', 'room_planning'),
			'required|in_list[dialog_room_planning,priority_room_planning]'
		);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());


		foreach ($configs as $config)
		{
			$this->_ci->VariableModel->setVariable(
				getAuthUID(),
				$config,
				$this->input->post($config) === true ? 'true' : 'false'
			);
		}

		$this->VariableModel->setVariable(
			getAuthUID(),
			'room_planning',
			$this->input->post('room_planning')
		);

		$this->terminateWithSuccess();
	}

	public function updateCollision()
	{
		$original_ignore = $this->_ci->variablelib->getVar('ignore_kollision');
		$this->_ci->VariableModel->setVariable(
			getAuthUID(),
			'ignore_kollision',
			$original_ignore === 'true' ? 'false' : 'true'
		);
	}

}
