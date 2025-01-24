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
			'getProgramsMobility' => ['admin:r', 'assistenz:r'],
			'getLVList' => ['admin:r', 'assistenz:r'],
			'getPurposes' => ['admin:r', 'assistenz:r'],
			'getListPurposes' => ['admin:r', 'assistenz:r'],
			'getListSupports' => ['admin:r', 'assistenz:r'],
			'deleteMobilityPurpose' => ['admin:r', 'assistenz:r'],
			'addMobilityPurpose' => ['admin:r', 'assistenz:r'],


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

	public function insertMobility()
	{
		//TODO(Manu) Validations
		//Pflicht gast und herkunftsland
		$authUID = getAuthUID();

		$formData = $this->input->post('formData');

		$von =	(isset($formData['von']) && !empty($formData['von'])) ? $formData['von'] : null;
$nation_code =	(isset($formData['nation_code']) && !empty($formData['nation_code'])) ? $formData['nation_code'] : 'A';
$mobilitaetsprogramm_code =	(isset($formData['mobilitaetsprogramm_code']) && !empty($formData['mobilitaetsprogramm_code'])) ? $formData['mobilitaetsprogramm_code'] : null;
$herkunftsland_code =	(isset($formData['herkunftsland_code']) && !empty($formData['herkunftsland_code'])) ? $formData['herkunftsland_code'] : 'A';
$ort =	(isset($formData['ort']) && !empty($formData['ort'])) ? $formData['ort'] : null;
$universitaet =	(isset($formData['universitaet']) && !empty($formData['universitaet'])) ? $formData['universitaet'] : null;
$ects_erworben =	(isset($formData['ects_erworben']) && !empty($formData['ects_erworben'])) ? $formData['ects_erworben'] : null;
$ects_angerechnet =	(isset($formData['ects_angerechnet']) && !empty($formData['ects_angerechnet'])) ? $formData['ects_angerechnet'] : null;
$localPurposes =	(isset($formData['localPurposes']) && !empty($formData['localPurposes'])) ? $formData['localPurposes'] : null;


		//strange fields
		/*			'zweck_code' => $this->input->post('zweck_code'),
					'aufenthalt_foerderung' => $this->input->post('aufenthalt_foerderung'),
					'lehreinheit_id' => $this->input->post('lehreinheit_id'),
					'lehrveranstaltung_id' => $this->input->post('lehrveranstaltung_id'),*/


		$result = $this->BisioModel->insert([
			'student_uid' => $this->input->post('uid'),
			'von' => $von,
			'bis' => $formData['bis'],
			'mobilitaetsprogramm_code' => $mobilitaetsprogramm_code,
			'nation_code' => $nation_code,
			'herkunftsland_code' => $herkunftsland_code,
			'ort' => $ort,
			'universitaet' => $universitaet,
			'ects_erworben' => $ects_erworben,
			'ects_angerechnet' => $ects_angerechnet,
			'insertamum' => date('c'),
			'insertvon' => $authUID,
		]);

		$bisio_id = $this->getDataOrTerminateWithError($result);

		//check if localData (purposes)
		if(count($localPurposes) > 0){

		//	$this->terminateWithError('Speichern von Zweck notwendig mit neuer  bisio_id ' . $bisio_id,  self::ERROR_TYPE_GENERAL);

			foreach ($localPurposes as $zweck){
				$zweck = (int) $zweck;
				$this->addMobilityPurpose($bisio_id, $zweck);
			}
		}

		$this->terminateWithSuccess();
	}

	public function loadMobility($bisio_id)
	{
		$result = $this->BisioModel->load($bisio_id);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}

	public function updateMobility()
	{
		$authUID = getAuthUID();
		$formData = $this->input->post('formData');

		$result = $this->BisioModel->update(
			[
				'bisio_id' =>  $formData['bisio_id']
			],
			[
				'student_uid' => $this->input->post('uid'),
				'von' => $formData['von'],
				'bis' => $formData['bis'],
				'mobilitaetsprogramm_code' => $formData['mobilitaetsprogramm_code'],
				'nation_code' => $formData['nation_code'],
				'herkunftsland_code' => $formData['herkunftsland_code'],
				'ort' => $formData['ort'],
				'universitaet' => $formData['universitaet'],
				'ects_erworben' => $formData['ects_erworben'],
				'ects_angerechnet' => $formData['ects_angerechnet'],
				'updateamum' => date('c'),
				'updatevon' => $authUID,
			]
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}

	public function deleteMobility($bisio_id)
	{
	//	$this->terminateWithError('test ' . $bisio_id,  self::ERROR_TYPE_GENERAL);

		$result = $this->BisioModel->delete(
			array('bisio_id' => $bisio_id)
		);

		//TODO(Manu) foreign key restraint
		//nämlich Extension mo
		//fk_mobisioidzuordnung_prestudent_id" on table "tbl_mo_bisioidzuordnung"

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getLVList($studiengang_kz)
	{
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		$result = $this->LehrveranstaltungModel->getLvsByStudiengangkz($studiengang_kz);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getPurposes($bisio_id)
	{
		$bisio_id = (int) $bisio_id;

		$this->load->model('codex/Bisiozweck_model', 'BisiozweckModel');

		$this->BisiozweckModel->addSelect("*");
		$this->BisiozweckModel->addJoin('bis.tbl_zweck zw', 'ON (zw.zweck_code = bis.tbl_bisio_zweck.zweck_code)');

		$result = $this->BisiozweckModel->loadWhere(
			array('bisio_id' => $bisio_id)
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getListPurposes()
	{
		$this->load->model('codex/Zweck_model', 'ZweckModel');

		$result = $this->ZweckModel->load();

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getListSupports()
	{
		$this->load->model('codex/Aufenthaltfoerderung_model', 'AufenthaltfoerderungModel');

		$result = $this->AufenthaltfoerderungModel->load();

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function addMobilityPurpose($bisio_id, $local_purpose = null)
	{
		$zweck_code = $this->input->post('zweck_code');

		if($local_purpose){
			$zweck_code = $local_purpose;
		}

		$this->load->model('codex/Bisiozweck_model', 'BisiozweckModel');

		$result = $this->BisiozweckModel->insert(
			array(
				'bisio_id' => $bisio_id,
				'zweck_code' => $zweck_code
			)
		);

		$data = $this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess(current($data));
	}

	public function deleteMobilityPurpose($bisio_id)
	{
		$zweck_code = $this->input->post('zweck_code');

		$this->load->model('codex/Bisiozweck_model', 'BisiozweckModel');


		$result = $this->BisiozweckModel->delete(
			array(
				'bisio_id' => $bisio_id,
				'zweck_code' => $zweck_code
			)
		);

		$data = $this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess(current($data));
	}
}
