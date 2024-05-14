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

class Pruefungstermin extends API_Controller
{
	/**
	 * Pruefungstermin API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Pruefungstermin' => 'basis/pruefungstermin:rw'));
		// Load model PruefungsterminModel
		$this->load->model('education/Pruefungstermin_model', 'PruefungsterminModel');
	}

	/**
	 * @return void
	 */
	public function getPruefungstermin()
	{
		$pruefungstermin_id = $this->get('pruefungstermin_id');

		if (isset($pruefungstermin_id))
		{
			$result = $this->PruefungsterminModel->load($pruefungstermin_id);

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
	public function postPruefungstermin()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['pruefungstermin_id']))
			{
				$result = $this->PruefungsterminModel->update($this->post()['pruefungstermin_id'], $this->post());
			}
			else
			{
				$result = $this->PruefungsterminModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($pruefungstermin = NULL)
	{
		return true;
	}
}
