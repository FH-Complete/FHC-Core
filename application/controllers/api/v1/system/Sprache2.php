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

class Sprache2 extends API_Controller
{
	/**
	 * Sprache API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Sprache' => 'basis/sprache:rw'));
		// Load model SpracheModel
		$this->load->model('system/sprache_model', 'SpracheModel');


	}

	/**
	 * @return void
	 */
	public function getSprache()
	{
		$sprache = $this->get('sprache');

		$result = $this->SpracheModel->load($sprache);

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function postSprache()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['sprache_id']))
			{
				$result = $this->SpracheModel->update($this->post()['sprache_id'], $this->post());
			}
			else
			{
				$result = $this->SpracheModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($sprache = NULL)
	{
		return true;
	}
}
