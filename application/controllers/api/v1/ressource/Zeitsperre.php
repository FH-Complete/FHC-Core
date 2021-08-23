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

class Zeitsperre extends API_Controller
{
	/**
	 * Zeitsperre API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Zeitsperre' => 'basis/zeitsperre:rw'));
		// Load model ZeitsperreModel
		$this->load->model('ressource/zeitsperre_model', 'ZeitsperreModel');


	}

	/**
	 * @return void
	 */
	public function getZeitsperre()
	{
		$zeitsperreID = $this->get('zeitsperre_id');

		if (isset($zeitsperreID))
		{
			$result = $this->ZeitsperreModel->load($zeitsperreID);

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
	public function postZeitsperre()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['zeitsperre_id']))
			{
				$result = $this->ZeitsperreModel->update($this->post()['zeitsperre_id'], $this->post());
			}
			else
			{
				$result = $this->ZeitsperreModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($zeitsperre = NULL)
	{
		return true;
	}
}
