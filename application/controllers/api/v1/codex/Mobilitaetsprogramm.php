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

class Mobilitaetsprogramm extends API_Controller
{
	/**
	 * Mobilitaetsprogramm API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Mobilitaetsprogramm' => 'basis/mobilitaetsprogramm:rw'));
		// Load model MobilitaetsprogrammModel
		$this->load->model('codex/mobilitaetsprogramm_model', 'MobilitaetsprogrammModel');
	}

	/**
	 * @return void
	 */
	public function getMobilitaetsprogramm()
	{
		$mobilitaetsprogramm_code = $this->get('mobilitaetsprogramm_code');

		if (isset($mobilitaetsprogramm_code))
		{
			$result = $this->MobilitaetsprogrammModel->load($mobilitaetsprogramm_code);

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
	public function postMobilitaetsprogramm()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['mobilitaetsprogramm_code']))
			{
				$result = $this->MobilitaetsprogrammModel->update($this->post()['mobilitaetsprogramm_code'], $this->post());
			}
			else
			{
				$result = $this->MobilitaetsprogrammModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($mobilitaetsprogramm = NULL)
	{
		return true;
	}
}
