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

class Server extends API_Controller
{
	/**
	 * Server API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Server' => 'basis/server:rw'));
		// Load model ServerModel
		$this->load->model('system/server_model', 'ServerModel');


	}

	/**
	 * @return void
	 */
	public function getServer()
	{
		$server_kurzbz = $this->get('server_kurzbz');

		if (isset($server_kurzbz))
		{
			$result = $this->ServerModel->load($server_kurzbz);

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
	public function postServer()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['server_id']))
			{
				$result = $this->ServerModel->update($this->post()['server_id'], $this->post());
			}
			else
			{
				$result = $this->ServerModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($server = NULL)
	{
		return true;
	}
}
