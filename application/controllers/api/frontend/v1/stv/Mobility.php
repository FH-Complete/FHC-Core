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
			'getProgramsMobility' => ['admin:rw', 'assistenz:rw'],
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);

		// Load models
		$this->load->model('codex/Bisio_model', 'BisioModel');
	}

	public function getMobilitaeten($student_uid)
	{
		$this->BisioModel->addSelect("*");
		$this->BisioModel->addSelect("TO_CHAR( tbl_bisio.von::timestamp, 'DD.MM.YYYY') AS format_von");
		$this->BisioModel->addSelect("TO_CHAR( tbl_bisio.bis::timestamp, 'DD.MM.YYYY') AS format_bis");
		$this->BisioModel->addJoin('bis.tbl_mobilitaetsprogramm mp', 'ON (mp.mobilitaetsprogramm_code = bis.tbl_bisio.mobilitaetsprogramm_code)', 'LEFT');

		$result = $this->BisioModel->loadWhere(
			array('student_uid' => $student_uid)
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getProgramsMobility()
	{
		$this->load->model('codex/Mobilitaetsprogramm_model', 'MobilitaetsprogrammModel');

		$result = $this->MobilitaetsprogrammModel->load();
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

}
