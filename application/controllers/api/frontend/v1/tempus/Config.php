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
			'global',
		]);

		$this->_ci = &get_instance();
		$this->_ci->load->model('ressource/Kalenderstatus_model', 'KalenderStatusModel');
	}

	public function get()
	{

		$language = getUserLanguage() == 'German' ? 0 : 1;

		$this->_ci->KalenderStatusModel->addSelect('*, array_to_json(bezeichnung_mehrsprachig::varchar[])->>' . $language .' AS status');
		$this->_ci->KalenderStatusModel->addOrder('sort');
		$visible_status = $this->_ci->KalenderStatusModel->load();

		$visible_status = getData($visible_status);

		$config['visible_status'] = [
			"type" => "select",
			"label" => $this->p->t('ui', 'status'),
			"value" => 'all'
		];


		foreach ($visible_status as $status)
		{
			$config['visible_status']['options'][$status->status_kurzbz] = $status->status;
		}

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

		$this->terminateWithSuccess();
	}

}
