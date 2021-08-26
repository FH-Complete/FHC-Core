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

class Pruefungsanmeldung extends API_Controller
{
	/**
	 * Pruefungsanmeldung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Pruefungsanmeldung' => 'basis/pruefungsanmeldung:rw'));
		// Load model PruefungsanmeldungModel
		$this->load->model('education/Pruefungsanmeldung_model', 'PruefungsanmeldungModel');
	}

	/**
	 * @return void
	 */
	public function getPruefungsanmeldung()
	{
		$pruefungsanmeldung_id = $this->get('pruefungsanmeldung_id');

		if (isset($pruefungsanmeldung_id))
		{
			$result = $this->PruefungsanmeldungModel->load($pruefungsanmeldung_id);

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
	public function postPruefungsanmeldung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['pruefungsanmeldung_id']))
			{
				$result = $this->PruefungsanmeldungModel->update($this->post()['pruefungsanmeldung_id'], $this->post());
			}
			else
			{
				$result = $this->PruefungsanmeldungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($pruefungsanmeldung = NULL)
	{
		return true;
	}
}
