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

class Pruefling extends API_Controller
{
	/**
	 * Pruefling API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Pruefling' => 'basis/pruefling:rw'));
		// Load model PrueflingModel
		$this->load->model('testtool/pruefling_model', 'PrueflingModel');


	}

	/**
	 * @return void
	 */
	public function getPruefling()
	{
		$prueflingID = $this->get('pruefling_id');

		if (isset($prueflingID))
		{
			$result = $this->PrueflingModel->load($prueflingID);

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
	public function postPruefling()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['pruefling_id']))
			{
				$result = $this->PrueflingModel->update($this->post()['pruefling_id'], $this->post());
			}
			else
			{
				$result = $this->PrueflingModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($pruefling = NULL)
	{
		return true;
	}
}
