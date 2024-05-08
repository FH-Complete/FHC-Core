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

class Betriebsmittel extends API_Controller
{
	/**
	 * Betriebsmittel API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Betriebsmittel' => 'basis/betriebsmittel:rw'));
		// Load model BetriebsmittelModel
		$this->load->model('ressource/betriebsmittel_model', 'BetriebsmittelModel');


	}

	/**
	 * @return void
	 */
	public function getBetriebsmittel()
	{
		$betriebsmittelID = $this->get('betriebsmittel_id');

		if (isset($betriebsmittelID))
		{
			$result = $this->BetriebsmittelModel->load($betriebsmittelID);

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
	public function postBetriebsmittel()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['betriebsmittel_id']))
			{
				$result = $this->BetriebsmittelModel->update($this->post()['betriebsmittel_id'], $this->post());
			}
			else
			{
				$result = $this->BetriebsmittelModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($betriebsmittel = NULL)
	{
		return true;
	}
}
