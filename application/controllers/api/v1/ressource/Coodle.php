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

class Coodle extends API_Controller
{
	/**
	 * Coodle API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Coodle' => 'basis/coodle:rw'));
		// Load model CoodleModel
		$this->load->model('ressource/coodle_model', 'CoodleModel');


	}

	/**
	 * @return void
	 */
	public function getCoodle()
	{
		$coodleID = $this->get('coodle_id');

		if (isset($coodleID))
		{
			$result = $this->CoodleModel->load($coodleID);

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
	public function postCoodle()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['coodle_id']))
			{
				$result = $this->CoodleModel->update($this->post()['coodle_id'], $this->post());
			}
			else
			{
				$result = $this->CoodleModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($coodle = NULL)
	{
		return true;
	}
}
