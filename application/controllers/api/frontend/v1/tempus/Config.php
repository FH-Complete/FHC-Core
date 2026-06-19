<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');


class Config extends FHCAPI_Controller
{
	private $_ci;

	public function __construct()
	{
		parent::__construct([
			'get' => ['admin:r', 'assistenz:r'],
			'getHeader' => ['admin:r', 'assistenz:r'],
			'set' => ['admin:r', 'assistenz:r'],
		]);

		// Load Phrases
		$this->loadPhrases([
			'ui',
		]);

		$this->_ci = &get_instance();
		$this->_ci->load->model('ressource/Kalenderstatus_model', 'KalenderStatusModel');
	}

	public function get()
	{
		$this->_ci->load->model('system/Variable_model', 'VariableModel');

		$config = [];

		$result = $this->_ci->VariableModel->getVariables(getAuthUID(), ['ignore_kollision', 'kollision_student', 'ignore_reservierung', 'ignore_zeitsperre', 'ignore_resources_collisions']);

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
		$this->_ci->load->model('system/Variable_model', 'VariableModel');

		$this->_ci->VariableModel->setVariable(
			getAuthUID(),
			'ignore_kollision',
			$this->input->post('ignore_kollision') === true ? 'true' : 'false'
		);
		$this->_ci->VariableModel->setVariable(
			getAuthUID(),
			'kollision_student',
			$this->input->post('kollision_student') === true ? 'true' : 'false'
		);
		$this->_ci->VariableModel->setVariable(
			getAuthUID(),
			'ignore_reservierung',
			$this->input->post('ignore_reservierung') === true ? 'true' : 'false'
		);
		$this->_ci->VariableModel->setVariable(
			getAuthUID(),
			'ignore_zeitsperre',
			$this->input->post('ignore_zeitsperre') === true ? 'true' : 'false'
		);
		$this->_ci->VariableModel->setVariable(
			getAuthUID(),
			'ignore_resources_collisions',
			$this->input->post('ignore_resources_collisions') === true ? 'true' : 'false'
		);
		$this->terminateWithSuccess();
	}


}
