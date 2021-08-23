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

class Bisorgform extends API_Controller
{
	/**
	 * Bisorgform API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Bisorgform' => 'basis/bisorgform:rw'));
		// Load model BisorgformModel
		$this->load->model('codex/bisorgform_model', 'BisorgformModel');
	}

	/**
	 * @return void
	 */
	public function getBisorgform()
	{
		$bisorgform_kurzbz = $this->get('bisorgform_kurzbz');

		if (isset($bisorgform_kurzbz))
		{
			$result = $this->BisorgformModel->load($bisorgform_kurzbz);

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
	public function postBisorgform()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['bisorgform_kurzbz']))
			{
				$result = $this->BisorgformModel->update($this->post()['bisorgform_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->BisorgformModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($bisorgform = NULL)
	{
		return true;
	}
}
