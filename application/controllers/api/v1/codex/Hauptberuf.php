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

class Hauptberuf extends API_Controller
{
	/**
	 * Hauptberuf API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Hauptberuf' => 'basis/hauptberuf:rw'));
		// Load model HauptberufModel
		$this->load->model('codex/hauptberuf_model', 'HauptberufModel');
	}

	/**
	 * @return void
	 */
	public function getHauptberuf()
	{
		$hauptberufcode = $this->get('hauptberufcode');

		if (isset($hauptberufcode))
		{
			$result = $this->HauptberufModel->load($hauptberufcode);

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
	public function postHauptberuf()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['hauptberufcode']))
			{
				$result = $this->HauptberufModel->update($this->post()['hauptberufcode'], $this->post());
			}
			else
			{
				$result = $this->HauptberufModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($hauptberuf = NULL)
	{
		return true;
	}
}
