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

class Zgvmaster extends API_Controller
{
	/**
	 * Zgvmaster API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Zgvmaster' => 'basis/zgvmaster:rw'));
		// Load model ZgvmasterModel
		$this->load->model('codex/zgvmaster_model', 'ZgvmasterModel');
	}

	/**
	 * @return void
	 */
	public function getZgvmaster()
	{
		$zgvmas_code = $this->get('zgvmas_code');

		if (isset($zgvmas_code))
		{
			$result = $this->ZgvmasterModel->load($zgvmas_code);

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
	public function postZgvmaster()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['zgvmas_code']))
			{
				$result = $this->ZgvmasterModel->update($this->post()['zgvmas_code'], $this->post());
			}
			else
			{
				$result = $this->ZgvmasterModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($zgvmaster = NULL)
	{
		return true;
	}
}
