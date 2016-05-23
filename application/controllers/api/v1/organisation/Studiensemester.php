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

class Studiensemester extends APIv1_Controller
{
	/**
	 * Studiensemester API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model StudiensemesterModel
		$this->load->model('organisation/studiensemester_model', 'StudiensemesterModel');
		// Load set the uid of the model to let to check the permissions
		$this->StudiensemesterModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getStudiensemester()
	{
		$studiensemester_kurzbz = $this->get('studiensemester_kurzbz');
		
		if (isset($studiensemester_kurzbz))
		{
			$result = $this->StudiensemesterModel->load($studiensemester_kurzbz);
			
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
	public function getNextStudiensemester()
	{
		$art = $this->get('art');
		
		$result = $this->StudiensemesterModel->addOrder('start');
		if ($result->error == EXIT_SUCCESS)
		{
			$result = $this->StudiensemesterModel->addLimit(1);
			if ($result->error == EXIT_SUCCESS)
			{
				if (isset($art))
				{
					$result = $this->StudiensemesterModel->loadWhere(array('start >' => 'NOW()', 'SUBSTRING(studiensemester_kurzbz FROM 1 FOR 2) = ' => $art));
				}
				else
				{
					$result = $this->StudiensemesterModel->loadWhere(array('start >' => 'NOW()'));
				}
			}
		}

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function postStudiensemester()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['studiensemester_kurzbz']))
			{
				$result = $this->StudiensemesterModel->update($this->post()['studiensemester_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->StudiensemesterModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($studiensemester = NULL)
	{
		return true;
	}
}