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

class Kriterien extends API_Controller
{
	/**
	 * Kriterien API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Kriterien' => 'basis/kriterien:rw'));
		// Load model KriterienModel
		$this->load->model('testtool/kriterien_model', 'KriterienModel');


	}

	/**
	 * @return void
	 */
	public function getKriterien()
	{
		$kriterienID = $this->get('kriterien_id');

		if (isset($kriterienID))
		{
			$result = $this->KriterienModel->load($kriterienID);

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
	public function postKriterien()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['kriterien_id']))
			{
				$result = $this->KriterienModel->update($this->post()['kriterien_id'], $this->post());
			}
			else
			{
				$result = $this->KriterienModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($kriterien = NULL)
	{
		return true;
	}
}
