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

class Lvregel extends API_Controller
{
	/**
	 * Lvregel API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Lvregel' => 'basis/lvregel:rw'));
		// Load model LvregelModel
		$this->load->model('education/Lvregel_model', 'LvregelModel');
	}

	/**
	 * @return void
	 */
	public function getLvregel()
	{
		$lvregel_id = $this->get('lvregel_id');

		if (isset($lvregel_id))
		{
			$result = $this->LvregelModel->load($lvregel_id);

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
	public function postLvregel()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['lvregel_id']))
			{
				$result = $this->LvregelModel->update($this->post()['lvregel_id'], $this->post());
			}
			else
			{
				$result = $this->LvregelModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($lvregel = NULL)
	{
		return true;
	}
}
