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

class Firmatag extends API_Controller
{
	/**
	 * Firmatag API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Firmatag' => 'basis/firmatag:rw'));
		// Load model FirmatagModel
		$this->load->model('ressource/firmatag_model', 'FirmatagModel');


	}

	/**
	 * @return void
	 */
	public function getFirmatag()
	{
		$tag = $this->get('tag');
		$firma_id = $this->get('firma_id');

		if (isset($tag) && isset($firma_id))
		{
			$result = $this->FirmatagModel->load(array($tag, $firma_id));

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
	public function postFirmatag()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['tag']) && isset($this->post()['firma_id']))
			{
				$result = $this->FirmatagModel->update(array($this->post()['tag'], $this->post()['firma_id']), $this->post());
			}
			else
			{
				$result = $this->FirmatagModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($firmatag = NULL)
	{
		return true;
	}
}
