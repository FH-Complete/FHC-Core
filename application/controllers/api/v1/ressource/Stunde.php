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

class Stunde extends API_Controller
{
	/**
	 * Stunde API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Stunde' => 'basis/stunde:rw'));
		// Load model StundeModel
		$this->load->model('ressource/stunde_model', 'StundeModel');


	}

	/**
	 * @return void
	 */
	public function getStunde()
	{
		$stunde = $this->get('stunde');

		if (isset($stunde))
		{
			$result = $this->StundeModel->load($stunde);

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
	public function postStunde()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['stunde']))
			{
				$result = $this->StundeModel->update($this->post()['stunde'], $this->post());
			}
			else
			{
				$result = $this->StundeModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($stunde = NULL)
	{
		return true;
	}
}
