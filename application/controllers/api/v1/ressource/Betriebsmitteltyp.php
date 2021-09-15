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

class Betriebsmitteltyp extends API_Controller
{
	/**
	 * Betriebsmitteltyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Betriebsmitteltyp' => 'basis/betriebsmitteltyp:rw'));
		// Load model BetriebsmitteltypModel
		$this->load->model('ressource/betriebsmitteltyp_model', 'BetriebsmitteltypModel');


	}

	/**
	 * @return void
	 */
	public function getBetriebsmitteltyp()
	{
		$betriebsmitteltyp = $this->get('betriebsmitteltyp');

		if (isset($betriebsmitteltyp))
		{
			$result = $this->BetriebsmitteltypModel->load($betriebsmitteltyp);

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
	public function postBetriebsmitteltyp()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['betriebsmitteltyp']))
			{
				$result = $this->BetriebsmitteltypModel->update($this->post()['betriebsmitteltyp'], $this->post());
			}
			else
			{
				$result = $this->BetriebsmitteltypModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($betriebsmitteltyp = NULL)
	{
		return true;
	}
}
