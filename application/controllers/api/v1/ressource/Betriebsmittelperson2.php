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

class Betriebsmittelperson2 extends API_Controller
{
	/**
	 * Betriebsmittelperson API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Betriebsmittelperson' => 'basis/betriebsmittelperson:rw'));
		// Load model BetriebsmittelpersonModel
		$this->load->model('ressource/betriebsmittelperson_model', 'BetriebsmittelpersonModel');


	}

	/**
	 * @return void
	 */
	public function getBetriebsmittelperson()
	{
		$betriebsmittelpersonID = $this->get('betriebsmittelperson_id');

		if (isset($betriebsmittelpersonID))
		{
			$result = $this->BetriebsmittelpersonModel->load($betriebsmittelpersonID);

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
	public function postBetriebsmittelperson()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['betriebsmittelperson_id']))
			{
				$result = $this->BetriebsmittelpersonModel->update($this->post()['betriebsmittelperson_id'], $this->post());
			}
			else
			{
				$result = $this->BetriebsmittelpersonModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($betriebsmittelperson = NULL)
	{
		return true;
	}
}
