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

class Ablauf extends API_Controller
{
	/**
	 * Ablauf API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Ablauf' => 'basis/ablauf:rw'));
		// Load model AblaufModel
		$this->load->model('testtool/ablauf_model', 'AblaufModel');


	}

	/**
	 * @return void
	 */
	public function getAblauf()
	{
		$ablaufID = $this->get('ablauf_id');

		if (isset($ablaufID))
		{
			$result = $this->AblaufModel->load($ablaufID);

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
	public function postAblauf()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['ablauf_id']))
			{
				$result = $this->AblaufModel->update($this->post()['ablauf_id'], $this->post());
			}
			else
			{
				$result = $this->AblaufModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($ablauf = NULL)
	{
		return true;
	}
}
