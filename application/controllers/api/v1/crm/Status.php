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

class Status extends API_Controller
{
	/**
	 * Status API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Status' => 'basis/status:rw'));
		// Load model StatusModel
		$this->load->model('crm/status_model', 'StatusModel');


	}

	/**
	 * @return void
	 */
	public function getStatus()
	{
		$status_kurzbz = $this->get('status_kurzbz');

		if (isset($status_kurzbz))
		{
			$result = $this->StatusModel->load($status_kurzbz);

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
	public function postStatus()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['status_kurzbz']))
			{
				$result = $this->StatusModel->update($this->post()['status_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->StatusModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($status = NULL)
	{
		return true;
	}
}
