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

class Bewerbungstermine extends API_Controller
{
	/**
	 * Bewerbungstermine API constructor.
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'Bewerbungstermine' => 'basis/bewerbungstermine:rw',
				'ByStudiengangStudiensemester' => 'basis/bewerbungstermine:r',
				'ByStudienplan' => 'basis/bewerbungstermine:r',
				'Current' => 'basis/bewerbungstermine:r'
			)
		);
		// Load model BewerbungstermineModel
		$this->load->model('crm/Bewerbungstermine_model', 'BewerbungstermineModel');


	}

	/**
	 * @return void
	 */
	public function getBewerbungstermine()
	{
		$bewerbungstermineID = $this->get('bewerbungstermine_id');

		if (isset($bewerbungstermineID))
		{
			$result = $this->BewerbungstermineModel->load($bewerbungstermineID);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function getByStudiengangStudiensemester()
	{
		$studiengang_kz = $this->get('studiengang_kz');
		$studiensemester_kurzbz = $this->get('studiensemester_kurzbz');

		if (isset($studiengang_kz) && isset($studiensemester_kurzbz))
		{
			$result = $this->BewerbungstermineModel->loadWhere(array(
				'studiengang_kz' => $studiengang_kz,
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
			));

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function getByStudienplan()
	{
		$studienplan_id = $this->get('studienplan_id');

		if (isset($studienplan_id))
		{
			$result = $this->BewerbungstermineModel->loadWhere(array(
				'studienplan_id' => $studienplan_id
			));

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function getCurrent()
	{
		$result = $this->BewerbungstermineModel->loadWhere(array(
				'beginn <=' => 'now()',
				'ende >=' => 'now()',
			));

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function postBewerbungstermine()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['bewerbungstermine_id']))
			{
				$result = $this->BewerbungstermineModel->update($this->post()['bewerbungstermine_id'], $this->post());
			}
			else
			{
				$result = $this->BewerbungstermineModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($bewerbungstermine = NULL)
	{
		return true;
	}
}
