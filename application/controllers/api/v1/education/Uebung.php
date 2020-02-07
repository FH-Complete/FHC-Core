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

class Uebung extends API_Controller
{
	/**
	 * Uebung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Uebung' => 'basis/uebung:rw'));
		// Load model UebungModel
		$this->load->model('education/uebung_model', 'UebungModel');
	}

	/**
	 * @return void
	 */
	public function getUebung()
	{
		$uebung_id = $this->get('uebung_id');

		if (isset($uebung_id))
		{
			$result = $this->UebungModel->load($uebung_id);

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
	public function postUebung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['uebung_id']))
			{
				$result = $this->UebungModel->update($this->post()['uebung_id'], $this->post());
			}
			else
			{
				$result = $this->UebungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($uebung = NULL)
	{
		return true;
	}
}
