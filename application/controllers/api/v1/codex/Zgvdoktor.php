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

class Zgvdoktor extends API_Controller
{
	/**
	 * Zgvdoktor API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Zgvdoktor' => 'basis/zgvdoktor:rw'));
		// Load model ZgvdoktorModel
		$this->load->model('codex/zgvdoktor_model', 'ZgvdoktorModel');
	}

	/**
	 * @return void
	 */
	public function getZgvdoktor()
	{
		$zgvdoktor_code = $this->get('zgvdoktor_code');

		if (isset($zgvdoktor_code))
		{
			$result = $this->ZgvdoktorModel->load($zgvdoktor_code);

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
	public function postZgvdoktor()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['zgvdoktor_code']))
			{
				$result = $this->ZgvdoktorModel->update($this->post()['zgvdoktor_code'], $this->post());
			}
			else
			{
				$result = $this->ZgvdoktorModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($zgvdoktor = NULL)
	{
		return true;
	}
}
