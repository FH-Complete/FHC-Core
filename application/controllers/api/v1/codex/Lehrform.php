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

class Lehrform extends API_Controller
{
	/**
	 * Lehrform API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Lehrform' => 'basis/lehrform:rw'));
		// Load model LehrformModel
		$this->load->model('codex/lehrform_model', 'LehrformModel');
	}

	/**
	 * @return void
	 */
	public function getLehrform()
	{
		$lehrform_kurzbz = $this->get('lehrform_kurzbz');

		if (isset($lehrform_kurzbz))
		{
			$result = $this->LehrformModel->load($lehrform_kurzbz);

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
	public function postLehrform()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['lehrform_kurzbz']))
			{
				$result = $this->LehrformModel->update($this->post()['lehrform_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->LehrformModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($lehrform = NULL)
	{
		return true;
	}
}
