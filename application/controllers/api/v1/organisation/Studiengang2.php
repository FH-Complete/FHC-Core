<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Studiengang2 extends API_Controller
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'Studiengang' => 'basis/studiengang:r',
				'AllForBewerbung' => 'basis/studiengang:r',
				'StudiengangStudienplan' => 'basis/studiengang:r',
				'StudiengangBewerbung' => 'basis/studiengang:r',
				'AppliedStudiengang' => 'basis/studiengang:r',
				'AppliedStudiengangFromNow' => 'basis/studiengang:r',
				'AppliedStudiengangFromNowOE' => 'basis/studiengang:r'
			)
		);

		// Load model PersonModel
		$this->load->model('organisation/studiengang_model', 'StudiengangModel');
	}

	/**
	 * getStudiengang
	 */
	public function getStudiengang()
	{
		$studiengang_kz = $this->get('studiengang_kz');

		if (isset($studiengang_kz))
		{
			$result = $this->StudiengangModel->load($studiengang_kz);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * getAllForBewerbung
	 */
	public function getAllForBewerbung()
	{
		$this->response($this->StudiengangModel->getAllForBewerbung(), REST_Controller::HTTP_OK);
	}

	/**
	 * getStudiengangStudienplan
	 */
	public function getStudiengangStudienplan()
	{
		// Getting HTTP GET parameters
		$studiensemester_kurzbz = $this->get('studiensemester_kurzbz');
		$ausbildungssemester = $this->get('ausbildungssemester');
		$aktiv = $this->get('aktiv');
		$onlinebewerbung = $this->get('onlinebewerbung');

		// If $studiensemester_kurzbz and $ausbildungssemester are present
		if (isset($studiensemester_kurzbz) && isset($ausbildungssemester))
		{
			// Check & set
			if (!isset($aktiv)) $aktiv = 'TRUE';
			if (!isset($onlinebewerbung)) $onlinebewerbung = 'TRUE';

			$result = $this->StudiengangModel->getStudienplan($studiensemester_kurzbz, $ausbildungssemester, $aktiv, $onlinebewerbung);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * getStudiengangBewerbung
	 */
	public function getStudiengangBewerbung()
	{
		$oe_kurzbz = $this->get('oe_kurzbz');

		$result = $this->StudiengangModel->getStudiengangBewerbung($oe_kurzbz);

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * getAppliedStudiengang
	 */
	public function getAppliedStudiengang()
	{
		$person_id = $this->get('person_id');
		$studiensemester_kurzbz = $this->get('studiensemester_kurzbz');
		$titel = $this->get('titel');

		if (isset($person_id) && isset($studiensemester_kurzbz) && isset($titel))
		{
			$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

			$result = $this->StudiengangModel->getAppliedStudiengang(
				$person_id,
				$studiensemester_kurzbz,
				$titel
			);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * getAppliedStudiengangFromNow
	 */
	public function getAppliedStudiengangFromNow()
	{
		$person_id = $this->get('person_id');
		$titel = $this->get('titel');

		if (isset($person_id) && isset($titel))
		{
			$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

			$result = $this->StudiengangModel->getAppliedStudiengangFromNow(
				$person_id,
				$titel
			);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * getAppliedStudiengangFromNowOE
	 */
	public function getAppliedStudiengangFromNowOE()
	{
		$person_id = $this->get('person_id');
		$titel = $this->get('titel');
		$oe_kurzbz = $this->get('oe_kurzbz');

		if (isset($person_id) && isset($titel) && isset($oe_kurzbz))
		{
			$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

			$result = $this->StudiengangModel->getAppliedStudiengangFromNowOE(
				$person_id,
				$titel,
				$oe_kurzbz
			);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
}
