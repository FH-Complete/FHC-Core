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

class Zeitaufzeichnung extends API_Controller
{
	/**
	 * Zeitaufzeichnung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Zeitaufzeichnung' => 'basis/zeitaufzeichnung:rw'));
		// Load model ZeitaufzeichnungModel
		$this->load->model('ressource/zeitaufzeichnung_model', 'ZeitaufzeichnungModel');


	}

	/**
	 * @return void
	 */
	public function getZeitaufzeichnung()
	{
		$zeitaufzeichnungID = $this->get('zeitaufzeichnung_id');

		if (isset($zeitaufzeichnungID))
		{
			$result = $this->ZeitaufzeichnungModel->load($zeitaufzeichnungID);

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
	public function postZeitaufzeichnung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['zeitaufzeichnung_id']))
			{
				$result = $this->ZeitaufzeichnungModel->update($this->post()['zeitaufzeichnung_id'], $this->post());
			}
			else
			{
				$result = $this->ZeitaufzeichnungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($zeitaufzeichnung = NULL)
	{
		return true;
	}
}
