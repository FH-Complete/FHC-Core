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

class Pruefung extends API_Controller
{
	/**
	 * Pruefung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Pruefung' => 'basis/pruefung:rw'));
		// Load model PruefungModel
		$this->load->model('education/Pruefung_model', 'PruefungModel');
	}

	/**
	 * @return void
	 */
	public function getPruefung()
	{
		$pruefung_id = $this->get('pruefung_id');

		if (isset($pruefung_id))
		{
			$result = $this->PruefungModel->load($pruefung_id);

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
	public function postPruefung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['pruefung_id']))
			{
				$result = $this->PruefungModel->update($this->post()['pruefung_id'], $this->post());
			}
			else
			{
				$result = $this->PruefungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($pruefung = NULL)
	{
		return true;
	}
}
