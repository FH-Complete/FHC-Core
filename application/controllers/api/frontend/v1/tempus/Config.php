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
		$visible_status = $this->_ci->KalenderStatusModel->load();

		$visible_status = getData($visible_status);

		$config['visible_status'] = [
			"type" => "select",
			"label" => $this->p->t('ui', 'status'),
			"multiple" => true,
			"value" => 'all'
		];
		foreach ($visible_status as $status)
		{
			$config['visible_status']['options'][$status->status_kurzbz] = $status->status_kurzbz;
		}

		$this->terminateWithSuccess($config);
	}
	public function getHeader()
	{
		$visible_status = $this->_ci->KalenderStatusModel->load();

		$visible_status = getData($visible_status);


		$config['visible_status']['all'] = 'all';

		foreach ($visible_status as $status)
		{
			$config['visible_status'][$status->status_kurzbz] = $status->bezeichnung;
		}

		$this->terminateWithSuccess($config);
	}

	public function set()
	{

		$this->terminateWithSuccess();
	}

}
