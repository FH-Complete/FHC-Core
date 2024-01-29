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

class Reihungstest extends API_Controller
{
	/**
	 * Reihungstest API constructor.
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'Reihungstest' => 'basis/reihungstest:rw',
				'ByStudiengangStudiensemester' => 'basis/reihungstest:r',
				'ReihungstestByPersonID' => 'basis/reihungstest:r',
				'AvailableReihungstestByPersonId' => 'basis/reihungstest:r'
			)
		);
		// Load model ReihungstestModel
		$this->load->model('crm/Reihungstest_model', 'ReihungstestModel');
		// Load library ReihungstestLib
		$this->load->library('ReihungstestLib');
	}

	/**
	 * @return void
	 */
	public function getReihungstest()
	{
		$reihungstestID = $this->get('reihungstest_id');

		if (isset($reihungstestID))
		{
			$result = $this->ReihungstestModel->load($reihungstestID);

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
		$available = $this->get('available');

		if (isset($studiengang_kz))
		{
			$parametersArray = array('studiengang_kz' => $studiengang_kz);
			if (isset($studiensemester_kurzbz))
			{
				$parametersArray['studiensemester_kurzbz'] = $studiensemester_kurzbz;
			}
			if (isset($available))
			{
				$parametersArray['anmeldefrist >='] = 'NOW()';
			}
			$result = $this->ReihungstestModel->loadWhere($parametersArray);

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
	public function getReihungstestByPersonID()
	{
		$person_id = $this->get('person_id');
		$available = $this->get('available');

		if (isset($person_id))
		{
			$result = $this->reihungstestlib->getReihungstestByPersonID($person_id, $available);

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
	public function getAvailableReihungstestByPersonId()
	{
		$person_id = $this->get('person_id');

		if (isset($person_id))
		{
			$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

			$result = $this->StudiengangModel->getAvailableReihungstestByPersonId($person_id);

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
	public function postReihungstest()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['reihungstest_id']))
			{
				$result = $this->ReihungstestModel->update($this->post()['reihungstest_id'], $this->post());
			}
			else
			{
				$result = $this->ReihungstestModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($reihungstest = NULL)
	{
		return true;
	}
}
