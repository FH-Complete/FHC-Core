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

class Lgartcode extends API_Controller
{
	/**
	 * Lgartcode API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Lgartcode' => 'basis/lgartcode:rw'));
		// Load model LgartcodeModel
		$this->load->model('codex/lgartcode_model', 'LgartcodeModel');
	}

	/**
	 * @return void
	 */
	public function getLgartcode()
	{
		$lgartcode = $this->get('lgartcode');

		if (isset($lgartcode))
		{
			$result = $this->LgartcodeModel->load($lgartcode);

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
	public function postLgartcode()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['lgartcode']))
			{
				$result = $this->LgartcodeModel->update($this->post()['lgartcode'], $this->post());
			}
			else
			{
				$result = $this->LgartcodeModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($lgartcode = NULL)
	{
		return true;
	}
}
