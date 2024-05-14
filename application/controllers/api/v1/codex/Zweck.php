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

class Zweck extends API_Controller
{
	/**
	 * Zweck API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Zweck' => 'basis/zweck:rw'));
		// Load model ZweckModel
		$this->load->model('codex/zweck_model', 'ZweckModel');
	}

	/**
	 * @return void
	 */
	public function getZweck()
	{
		$zweck_code = $this->get('zweck_code');

		if (isset($zweck_code))
		{
			$result = $this->ZweckModel->load($zweck_code);

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
	public function postZweck()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['zweck_code']))
			{
				$result = $this->ZweckModel->update($this->post()['zweck_code'], $this->post());
			}
			else
			{
				$result = $this->ZweckModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($zweck = NULL)
	{
		return true;
	}
}
