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

class Ampel extends API_Controller
{
	/**
	 * Ampel API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Ampel' => 'basis/ampel:rw'));
		// Load model AmpelModel
		$this->load->model('content/ampel_model', 'AmpelModel');
	}

	/**
	 * @return void
	 */
	public function getAmpel()
	{
		$ampelID = $this->get('ampel_id');

		if (isset($ampelID))
		{
			$result = $this->AmpelModel->load($ampelID);

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
	public function postAmpel()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['ampel_id']))
			{
				$result = $this->AmpelModel->update($this->post()['ampel_id'], $this->post());
			}
			else
			{
				$result = $this->AmpelModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($ampel = NULL)
	{
		return true;
	}
}
