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

class Gebiet extends API_Controller
{
	/**
	 * Gebiet API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Gebiet' => 'basis/gebiet:rw'));
		// Load model GebietModel
		$this->load->model('testtool/gebiet_model', 'GebietModel');


	}

	/**
	 * @return void
	 */
	public function getGebiet()
	{
		$gebietID = $this->get('gebiet_id');

		if (isset($gebietID))
		{
			$result = $this->GebietModel->load($gebietID);

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
	public function postGebiet()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['gebiet_id']))
			{
				$result = $this->GebietModel->update($this->post()['gebiet_id'], $this->post());
			}
			else
			{
				$result = $this->GebietModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($gebiet = NULL)
	{
		return true;
	}
}
