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

class Antwort extends API_Controller
{
	/**
	 * Antwort API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Antwort' => 'basis/antwort:rw'));
		// Load model AntwortModel
		$this->load->model('testtool/antwort_model', 'AntwortModel');


	}

	/**
	 * @return void
	 */
	public function getAntwort()
	{
		$antwortID = $this->get('antwort_id');

		if (isset($antwortID))
		{
			$result = $this->AntwortModel->load($antwortID);

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
	public function postAntwort()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['antwort_id']))
			{
				$result = $this->AntwortModel->update($this->post()['antwort_id'], $this->post());
			}
			else
			{
				$result = $this->AntwortModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($antwort = NULL)
	{
		return true;
	}
}
