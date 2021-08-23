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

class Frage extends API_Controller
{
	/**
	 * Frage API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Frage' => 'basis/frage:rw'));
		// Load model FrageModel
		$this->load->model('testtool/frage_model', 'FrageModel');


	}

	/**
	 * @return void
	 */
	public function getFrage()
	{
		$frageID = $this->get('frage_id');

		if (isset($frageID))
		{
			$result = $this->FrageModel->load($frageID);

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
	public function postFrage()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['frage_id']))
			{
				$result = $this->FrageModel->update($this->post()['frage_id'], $this->post());
			}
			else
			{
				$result = $this->FrageModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($frage = NULL)
	{
		return true;
	}
}
