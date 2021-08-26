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

class Vorlage extends API_Controller
{
	/**
	 * Vorlage API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Vorlage' => 'system/vorlage:rw'));
		// Load model VorlageModel
		$this->load->model('system/vorlage_model', 'VorlageModel');


	}

	/**
	 * @return void
	 */
	public function getVorlage()
	{
		$vorlage_kurzbz = $this->get('vorlage_kurzbz');

		if (isset($vorlage_kurzbz))
		{
			$result = $this->VorlageModel->load($vorlage_kurzbz);

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
	public function postVorlage()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['vorlage_kurzbz']))
			{
				$result = $this->VorlageModel->update($this->post()['vorlage_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->VorlageModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($vorlage = NULL)
	{
		return true;
	}
}
