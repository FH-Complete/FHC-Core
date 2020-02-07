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

if (!defined("BASEPATH")) exit("No direct script access allowed");

class Studienplan extends API_Controller
{
	/**
	 * Course API constructor.
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'Studienplan' => 'lehre/studienplan:r',
				'Studienplaene' => 'lehre/studienplan:r',
				'StudienplaeneFromSem' => 'lehre/studienplan:r'
			)
		);
		// Load model PersonModel
		$this->load->model('organisation/studienplan_model', 'StudienplanModel');
	}

	public function getStudienplan()
	{
		$studienplan_id = $this->get("studienplan_id");

		if (isset($studienplan_id))
		{
			$result = $this->StudienplanModel->load($studienplan_id);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	public function getStudienplaene()
	{
		$studiengang_kz = $this->get("studiengang_kz");

		if (isset($studiengang_kz))
		{
			$result = $this->StudienplanModel->getStudienplaene($studiengang_kz);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	public function getStudienplaeneFromSem()
	{
		$studiengang_kz = $this->get("studiengang_kz");
		$studiensemester_kurzbz = $this->get("studiensemester_kurzbz");
		$ausbildungssemester = $this->get("ausbildungssemester");
		$orgform_kurzbz = $this->get("orgform_kurzbz");

		if (isset($studiengang_kz) && isset($studiensemester_kurzbz))
		{
			$result = $this->StudienplanModel->getStudienplaeneBySemester(
				$studiengang_kz,
				$studiensemester_kurzbz,
				$ausbildungssemester,
				$orgform_kurzbz
			);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
}
