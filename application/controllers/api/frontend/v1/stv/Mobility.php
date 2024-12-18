<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Mobility extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getMobilitaeten' => ['admin:r', 'assistenz:r'],
			'loadMobility' => ['admin:r', 'assistenz:r'],
			'insertMobility' => ['admin:rw', 'assistenz:rw'],
			'updateMobility' => ['admin:rw', 'assistenz:rw'],
			'deleteMobility' => ['admin:rw', 'assistenz:rw'],
			'getTypenMobility' => ['admin:rw', 'assistenz:rw'],
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);

		// Load models
		$this->load->model('codex/Mobilitaet_model', 'MobilitaetModel');
	}

	public function getMobilitaeten($prestudent_id)
	{
		$this->MobilitaetModel->addJoin('bis.tbl_mobilitaetsprogramm mp', 'ON (mp.mobilitaetsprogramm_code = bis.tbl_mobilitaet.mobilitaetsprogramm_code)', 'LEFT');

		$result = $this->MobilitaetModel->loadWhere(
			array('prestudent_id' => $prestudent_id)
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);

	}

}
