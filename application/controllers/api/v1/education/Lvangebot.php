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

class Lvangebot extends API_Controller
{
	/**
	 * Lvangebot API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Lvangebot' => 'basis/lvangebot:rw'));
		// Load model LvangebotModel
		$this->load->model('education/Lvangebot_model', 'LvangebotModel');
	}

	/**
	 * @return void
	 */
	public function getLvangebot()
	{
		$lvangebot_id = $this->get('lvangebot_id');

		if (isset($lvangebot_id))
		{
			$result = $this->LvangebotModel->load($lvangebot_id);

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
	public function postLvangebot()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['lvangebot_id']))
			{
				$result = $this->LvangebotModel->update($this->post()['lvangebot_id'], $this->post());
			}
			else
			{
				$result = $this->LvangebotModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($lvangebot = NULL)
	{
		return true;
	}
}
