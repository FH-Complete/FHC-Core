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

class Betriebsmittelstatus extends API_Controller
{
	/**
	 * Betriebsmittelstatus API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Betriebsmittelstatus' => 'basis/betriebsmittelstatus:rw'));
		// Load model BetriebsmittelstatusModel
		$this->load->model('ressource/betriebsmittelstatus_model', 'BetriebsmittelstatusModel');


	}

	/**
	 * @return void
	 */
	public function getBetriebsmittelstatus()
	{
		$betriebsmittelstatus_kurzbz = $this->get('betriebsmittelstatus_kurzbz');

		if (isset($betriebsmittelstatus_kurzbz))
		{
			$result = $this->BetriebsmittelstatusModel->load($betriebsmittelstatus_kurzbz);

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
	public function postBetriebsmittelstatus()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['betriebsmittelstatus_kurzbz']))
			{
				$result = $this->BetriebsmittelstatusModel->update($this->post()['betriebsmittelstatus_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->BetriebsmittelstatusModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($betriebsmittelstatus = NULL)
	{
		return true;
	}
}
