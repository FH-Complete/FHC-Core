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

class Statusgrund extends API_Controller
{
	/**
	 * Status API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Statusgrund' => 'basis/statusgrund:rw'));
		// Load model StatusModel
		$this->load->model('crm/Statusgrund_model', 'StatusgrundModel');
	}

	/**
	 * @return void
	 */
	public function getStatusgrund()
	{
		$statusgrund_kurzbz = $this->get('statusgrund_kurzbz');

		if (isset($statusgrund_kurzbz))
		{
			$result = $this->StatusgrundModel->load($statusgrund_kurzbz);

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
	public function postStatusgrund()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['statusgrund_kurzbz']))
			{
				$result = $this->StatusgrundModel->update($this->post()['statusgrund_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->StatusgrundModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($statusgrund = NULL)
	{
		return true;
	}
}
