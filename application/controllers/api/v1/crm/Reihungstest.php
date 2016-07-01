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

class Reihungstest extends APIv1_Controller
{
	/**
	 * Reihungstest API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model ReihungstestModel
		$this->load->model('crm/reihungstest_model', 'ReihungstestModel');
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
		
		if (isset($studiengang_kz))
		{
			$parameters = array('studiengang_kz' => $studiengang_kz);
			if (isset($studiensemester_kurzbz))
			{
				$parameters['studiensemester_kurzbz'] = $studiensemester_kurzbz;
			}
			$result = $this->ReihungstestModel->loadWhere($parameters);
			
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
		
		if (isset($person_id))
		{
			$result = $this->ReihungstestModel->addJoin('public.tbl_rt_person', 'reihungstest_id = rt_id');
			if ($result->error == EXIT_SUCCESS)
			{
				$result = $this->ReihungstestModel->addJoin('public.tbl_person', 'person_id');
				if ($result->error == EXIT_SUCCESS)
				{
					$result = $this->ReihungstestModel->addJoin('public.tbl_ort', 'tbl_ort.ort_kurzbz = tbl_rt_person.ort_kurzbz');
					if ($result->error == EXIT_SUCCESS)
					{
						$result = $this->ReihungstestModel->loadWhere(array('person_id' => $person_id));
					}
				}
			}
			
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