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

class Preincoming extends API_Controller
{
	/**
	 * Preincoming API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Preincoming' => 'basis/preincoming:rw'));
		// Load model PreincomingModel
		$this->load->model('crm/preincoming_model', 'PreincomingModel');


	}

	/**
	 * @return void
	 */
	public function getPreincoming()
	{
		$preincomingID = $this->get('preincoming_id');

		if (isset($preincomingID))
		{
			$result = $this->PreincomingModel->load($preincomingID);

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
	public function postPreincoming()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['preincoming_id']))
			{
				$result = $this->PreincomingModel->update($this->post()['preincoming_id'], $this->post());
			}
			else
			{
				$result = $this->PreincomingModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($preincoming = NULL)
	{
		return true;
	}
}
