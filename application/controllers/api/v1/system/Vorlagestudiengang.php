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

class Vorlagestudiengang extends API_Controller
{
	/**
	 * Vorlagestudiengang API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Vorlagestudiengang' => 'system/vorlagestudiengang:rw'));
		// Load model VorlagestudiengangModel
		$this->load->model('system/vorlagestudiengang_model', 'VorlagestudiengangModel');


	}

	/**
	 * @return void
	 */
	public function getVorlagestudiengang()
	{
		$vorlagestudiengangID = $this->get('vorlagestudiengang_id');

		if (isset($vorlagestudiengangID))
		{
			$result = $this->VorlagestudiengangModel->load($vorlagestudiengangID);

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
	public function postVorlagestudiengang()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['vorlagestudiengang_id']))
			{
				$result = $this->VorlagestudiengangModel->update($this->post()['vorlagestudiengang_id'], $this->post());
			}
			else
			{
				$result = $this->VorlagestudiengangModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($vorlagestudiengang = NULL)
	{
		return true;
	}
}
