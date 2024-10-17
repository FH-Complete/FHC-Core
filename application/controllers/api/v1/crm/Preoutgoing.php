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

class Preoutgoing extends API_Controller
{
	/**
	 * Preoutgoing API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Preoutgoing' => 'basis/preoutgoing:rw'));
		// Load model PreoutgoingModel
		$this->load->model('crm/preoutgoing_model', 'PreoutgoingModel');


	}

	/**
	 * @return void
	 */
	public function getPreoutgoing()
	{
		$preoutgoingID = $this->get('preoutgoing_id');

		if (isset($preoutgoingID))
		{
			$result = $this->PreoutgoingModel->load($preoutgoingID);

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
	public function postPreoutgoing()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['preoutgoing_id']))
			{
				$result = $this->PreoutgoingModel->update($this->post()['preoutgoing_id'], $this->post());
			}
			else
			{
				$result = $this->PreoutgoingModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($preoutgoing = NULL)
	{
		return true;
	}
}
