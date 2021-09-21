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

class Lehrfach extends API_Controller
{
	/**
	 * Lehrfach API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Lehrfach' => 'basis/lehrfach:rw'));
		// Load model LehrfachModel
		$this->load->model('education/Lehrfach_model', 'LehrfachModel');
	}

	/**
	 * @return void
	 */
	public function getLehrfach()
	{
		$lehrfach_id = $this->get('lehrfach_id');

		if (isset($lehrfach_id))
		{
			$result = $this->LehrfachModel->load($lehrfach_id);

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
	public function postLehrfach()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['lehrfach_id']))
			{
				$result = $this->LehrfachModel->update($this->post()['lehrfach_id'], $this->post());
			}
			else
			{
				$result = $this->LehrfachModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($lehrfach = NULL)
	{
		return true;
	}
}
